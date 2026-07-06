<?php
// /src/Utils/CuratedPhotos.php

declare(strict_types=1);

namespace Src\Utils;

/**
 * Shared source for the curated property photos used in project hero
 * slideshows (real-estate-leads, contractor-discovery,
 * landlord-tenant-validation).
 */
class CuratedPhotos
{
    /**
     * Numerically-named photos only (1.jpg, 2.jpg, 3.JPG, ...) from
     * public/images/home/ — excludes the generic news-* stock photos
     * that live in the same folder.
     *
     * @return string[]
     */
    public static function fromHomeFolder(string $assetBase): array
    {
        $photos = [];
        $path = __DIR__ . '/../../public/images/home';

        if (is_dir($path)) {
            $files = scandir($path) ?: [];
            $files = array_values(array_filter($files, fn($f) => preg_match('/^\d+\.(jpe?g|png|webp|gif)$/i', $f)));
            sort($files);

            foreach ($files as $file) {
                $photos[] = $assetBase . 'images/home/' . rawurlencode($file);
            }
        }

        return $photos;
    }
}
