<?php
// /resources/views/partials/layout-footer.php

declare(strict_types=1);

/**
 * Gonachi Real Estate Lead Engine - Core Visual Footer
 */
?>
<footer class="mt-auto border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 py-6 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center space-x-2">
            <span class="text-sm font-bold tracking-tight text-gray-900 dark:text-white">Gonachi</span>
            <span class="text-xs text-gray-400 dark:text-gray-500">© <?= date('Y') ?> Lead Engine. All rights reserved.</span>
        </div>
        
        <!-- System Navigation Footprint Links -->
        <div class="flex space-x-6 text-xs font-medium text-gray-500 dark:text-gray-400">
            <a href="#" class="hover:text-primary-600 transition-colors">Terms of Service</a>
            <a href="#" class="hover:text-primary-600 transition-colors">Privacy Policy</a>
            <a href="#" class="hover:text-primary-600 transition-colors">Data Collection Standards</a>
        </div>
    </div>
</footer>