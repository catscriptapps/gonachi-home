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
        entryFileNames: chunk => chunk.name === 'app' ? 'js/app.min.js' : `js/${chunk.name}.min.js`,
        chunkFileNames: 'js/[name].min.js',
        assetFileNames: assetInfo => assetInfo.name?.endsWith('.css') ? 'css/app.min.css' : 'assets/[name]-[hash][extname]',
      },
      preserveEntrySignatures: 'strict', // ✅ prevents empty chunks from being removed
    },
  },
});
