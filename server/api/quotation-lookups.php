<?php
// /server/api/quotation-lookups.php
//
// All the static lookup lists the create/edit quotation form's dropdowns
// need in one round trip — contractor types, skilled trades, unit types,
// house types, quotation types (labour scope), and destinations (visibility
// scope). No auth required — same as advert-ctas.php, it's just static data.

declare(strict_types=1);

use App\Models\ContractorType;
use App\Models\SkilledTrade;
use App\Models\UnitType;
use App\Models\HouseType;
use App\Models\QuotationType;
use App\Models\QuotationDestination;

header('Content-Type: application/json');

json_response([
    'success' => true,
    'contractorTypes' => ContractorType::orderBy('contractor_type')->get(['contractor_type_id', 'contractor_type']),
    'skilledTrades' => SkilledTrade::orderBy('skilled_trade')->get(['skilled_trade_id', 'skilled_trade']),
    'unitTypes' => UnitType::orderBy('unit_type')->get(['unit_type_id', 'unit_type']),
    'houseTypes' => HouseType::orderBy('house_type')->get(['house_type_id', 'house_type']),
    'quotationTypes' => QuotationType::orderBy('quotation_type')->get(['quotation_type_id', 'quotation_type']),
    'destinations' => QuotationDestination::orderBy('quotation_dest')->get(['quotation_dest_id', 'quotation_dest']),
]);
