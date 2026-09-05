<?php
// /scripts/reset/rew-skilled-trades.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\SkilledTrade;

function resetRewSkilledTradesTable(): array
{
    $messages = [];

    try {
        $tableName = (new SkilledTrade())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->id('skilled_trade_id');
            $table->string('skilled_trade', 150);
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";

        $trades = [
            'Appliance Repair & Installation', 'Architectural Services', 'Basement Renovation & Finishing',
            'Brick, Masonry & Concrete', 'Carpentry, Crown Moulding & Trimwork', 'Cleaning Services',
            'Drywall & Stucco Removal', 'Electrician', 'Excavation, Demolition & Waterproofing',
            'Fence, Deck, Railing & Siding', 'Flooring', 'Garage Door', 'General Labour',
            'Heating, Ventilation & Air Conditioning', 'Home Building & Construction',
            'Hot Tub installation & Services', 'House Renovation', 'Insulation',
            'Interlock, Paving & Driveways', 'Junk Removal', 'Kitchen Renovation & Installation',
            'Lawn, Tree Maintenance & Eavestrough', 'Moving Assistant', 'Painters & Painting',
            'Permit Services', 'Phone, Network, Cable & Home-wiring', 'Plumbing',
            'Pool Installation & Services', 'Renovations & General Contracting', 'Roofing',
            'Snow Removal & Property Maintenance', 'Washroom Renovation & Installation', 'Welding',
            'Windows & Doors', 'Other',
        ];

        foreach ($trades as $i => $trade) {
            SkilledTrade::create(['skilled_trade_id' => $i + 1, 'skilled_trade' => $trade]);
        }

        $messages[] = "seeded " . count($trades) . " skilled trades";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}
