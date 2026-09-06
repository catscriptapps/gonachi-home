<?php
// /server/api/listing-lookups.php
//
// All the static lookup lists the create/edit listing form's dropdowns need
// in one round trip — listing categories, category types (all, for
// client-side filtering by category, mirroring legacy's data-all-types
// approach), unit types, house types, bedrooms, bathrooms, agreement types,
// and amenities grouped by category. No auth required — same as
// quotation-lookups.php, it's just static data.

declare(strict_types=1);

use App\Models\ListingCategory;
use App\Models\ListingCategoryType;
use App\Models\UnitType;
use App\Models\HouseType;
use App\Models\Bedroom;
use App\Models\Bathroom;
use App\Models\AgreementType;
use App\Models\AmenityCategory;

header('Content-Type: application/json');

json_response([
    'success' => true,
    'listingCategories' => ListingCategory::orderBy('category_id')->get(['category_id', 'category']),
    'listingCategoryTypes' => ListingCategoryType::orderBy('category_type_id')->get(['category_type_id', 'category_id', 'category_type']),
    'unitTypes' => UnitType::orderBy('unit_type')->get(['unit_type_id', 'unit_type']),
    'houseTypes' => HouseType::orderBy('house_type')->get(['house_type_id', 'house_type']),
    'bedrooms' => Bedroom::orderBy('bedroom_id')->get(['bedroom_id', 'bedroom']),
    'bathrooms' => Bathroom::orderBy('bathroom_id')->get(['bathroom_id', 'bathroom']),
    'agreementTypes' => AgreementType::orderBy('agreement_type_id')->get(['agreement_type_id', 'agreement_type']),
    'amenityGroups' => AmenityCategory::with('amenities')->orderBy('category_id')->get(),
]);
