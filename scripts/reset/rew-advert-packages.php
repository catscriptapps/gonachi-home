<?php
// /scripts/reset/rew-advert-packages.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\AdvertPackage;

function resetRewAdvertPackagesTable(): array
{
    $messages = [];

    try {
        $tableName = (new AdvertPackage())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->unsignedInteger('package_id')->primary();
            $table->string('package_name', 50);
            $table->string('package_description', 100);
            $table->text('package_icon');
            $table->integer('package_order')->default(0);
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";

        // Exact tiers/icons (Heroicon outline paths) from the legacy platform.
        $packages = [
            ['package_id' => 1, 'package_name' => 'Free', 'package_description' => '3 Days', 'package_order' => 1,
                'package_icon' => 'M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z'],
            ['package_id' => 2, 'package_name' => 'Standard', 'package_description' => '1 Week', 'package_order' => 2,
                'package_icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z'],
            ['package_id' => 3, 'package_name' => 'Pro', 'package_description' => '1 Month', 'package_order' => 3,
                'package_icon' => 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z'],
            ['package_id' => 4, 'package_name' => 'Business', 'package_description' => '6 Months', 'package_order' => 4,
                'package_icon' => 'M20.25 14.15v4.25c0 .621-.504 1.125-1.125 1.125H4.875A1.125 1.125 0 013.75 18.4V14.15m16.5 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 14.15m16.5 0V9a2.25 2.25 0 00-2.25-2.25H15M3.75 14.15V9a2.25 2.25 0 012.25-2.25H9m6 0V4.5A2.25 2.25 0 0012.75 2.25h-1.5A2.25 2.25 0 009 4.5V6.75m6 0H9'],
            ['package_id' => 5, 'package_name' => 'Ultimate', 'package_description' => '1 Year', 'package_order' => 5,
                'package_icon' => 'M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z'],
        ];

        foreach ($packages as $pkg) {
            AdvertPackage::create($pkg);
        }

        $messages[] = "seeded " . count($packages) . " advert packages";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}
