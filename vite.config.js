// /vite.config.js

// Vite configuration for Laravel with auto-collection of page scripts and manifest generation
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
import path from 'path';

// === Auto-collect all page JS files in resources/js/pages ===
const pageScriptsDir = path.resolve(__dirname, 'resources/js/pages');
const pageScripts = fs.existsSync(pageScriptsDir)
  ? fs.readdirSync(pageScriptsDir)
      .filter(file => file.endsWith('.js'))
      .map(file => path.join('resources/js/pages', file))
  : [];

// === Helper: Generate page manifest after build ===
function generatePageManifest() {
  return {
    name: 'generate-page-manifest',
    closeBundle() {
      try {
        const outputDir = path.resolve(__dirname, 'public/assets/js');
        if (!fs.existsSync(outputDir)) return;

        const files = fs.readdirSync(outputDir)
          .filter(f => f.endsWith('-page.min.js'))
          .map(f => f.replace('.min.js', ''));

        const manifestPath = path.join(outputDir, 'page-manifest.json');
        fs.writeFileSync(manifestPath, JSON.stringify(files, null, 2));

        console.log(`✅ page-manifest.json generated with ${files.length} entries.`);
      } catch (err) {
        console.error('⚠️ Failed to generate page-manifest.json:', err);
      }
    },
  };
}

// === Main Vite Config ===
export default defineConfig({
  plugins: [
    laravel({
      // Force Vite to treat all page scripts as entry points
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
        ...pageScripts, // ✅ every page JS included
      ],
      refresh: [
        './resources/views/**/*.php',
        './resources/js/**/*.js',
      ],
    }),
    generatePageManifest(), // Generate manifest for SPA dynamic loading
  ],
  build: {
    outDir: 'public/assets',
    rollupOptions: {
      output: {
        // Entry points (app.min.js and every {page}.min.js) keep fixed,
        // unhashed names — resources/js/app.js's loadModule() constructs
        // their URLs directly from page-manifest.json's slugs, so the
        // filename has to stay predictable. Cache-busting for these comes
        // from the ?v= query string instead (see the <script> tags in each
        // layout, and loadModule()'s own ?v= param below).
        entryFileNames: chunk => chunk.name === 'app' ? 'js/app.min.js' : `js/${chunk.name}.min.js`,
        // Shared chunks (anything imported by 2+ entry scripts — spa-router.js,
        // toast.js, form-validator.js, etc.) MUST be content-hashed. These
        // have no <script> tag of their own to carry a ?v= cache-buster —
        // they're reached only via a plain relative import() inside an entry
        // file — so a fixed filename here means a browser with last
        // deploy's cached copy keeps serving it after a new deploy changes
        // its content. Minified export names aren't stable across builds,
        // so an old cached chunk paired with a freshly-fetched entry file
        // throws "does not provide an export named 'x'" — exactly the
        // production bug this fixes. Hashing the filename means any content
        // change produces a new URL, so there's never a stale/fresh mismatch
        // to begin with (old hashed files left on the server by a
        // non-deleting FTP deploy are simply orphaned, not a correctness risk).
        chunkFileNames: 'js/[name]-[hash].min.js',
        assetFileNames: assetInfo => assetInfo.name?.endsWith('.css') ? 'css/app.min.css' : 'assets/[name]-[hash][extname]',
      },
      preserveEntrySignatures: 'strict', // ✅ prevents empty chunks from being removed
    },
  },
});
