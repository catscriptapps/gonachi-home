<?php
// /resources/views/components/hero-slideshow.php
//
// Ken-Burns style crossfade slideshow: each image fades in, holds while
// zooming out gently, then fades out — cycling forever, pure CSS.
//
// Usage: set $slideshowImages (array of image URLs) and optionally
// $slideshowSecondsPerSlide before including this file, inside a
// `<section class="relative overflow-hidden">` (or similar positioned
// ancestor). This renders the <style> block plus the blurred, absolutely
// positioned slide layer — add your own light/dark overlay div and
// foreground content after including it.

declare(strict_types=1);

/**
 * @var string[] $slideshowImages
 * @var int|null $slideshowSecondsPerSlide
 */

$slideshowSecondsPerSlide = $slideshowSecondsPerSlide ?? 6;
$slideshowSlideCount = count($slideshowImages);
$slideshowCycleDuration = max($slideshowSlideCount, 1) * $slideshowSecondsPerSlide;
$slideshowSlotPercent = $slideshowSlideCount > 0 ? 100 / $slideshowSlideCount : 100;
?>
<?php if ($slideshowSlideCount > 0): ?>
    <style>
        @keyframes gonachiHeroSlide {
            0% { opacity: 0; transform: scale(1.15); }
            <?= round($slideshowSlotPercent * 0.08, 4) ?>% { opacity: 1; transform: scale(1.15); }
            <?= round($slideshowSlotPercent * 0.85, 4) ?>% { opacity: 1; transform: scale(1); }
            <?= round($slideshowSlotPercent, 4) ?>% { opacity: 0; transform: scale(1); }
            100% { opacity: 0; transform: scale(1); }
        }
        .gonachi-hero-slide {
            animation: gonachiHeroSlide <?= $slideshowCycleDuration ?>s ease-in-out infinite;
        }
        @media (prefers-reduced-motion: reduce) {
            .gonachi-hero-slide { animation: none; opacity: 1; }
        }
    </style>

    <div class="absolute inset-0 blur-[2px] scale-105" aria-hidden="true">
        <?php foreach ($slideshowImages as $i => $src): ?>
            <div class="gonachi-hero-slide absolute inset-0 bg-cover bg-center"
                style="background-image: url('<?= htmlspecialchars($src) ?>'); animation-delay: -<?= $i * $slideshowSecondsPerSlide ?>s;"></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
