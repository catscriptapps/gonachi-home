<?php
// /scripts/reset/faqs.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Faq;

function resetFaqsTable(): array
{
    $messages = [];

    try {
        $tableName = (new Faq())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('status_id')->default(Faq::STATUS_ACTIVE)->index();
            $table->integer('display_order')->default(0)->index();
            $table->unsignedBigInteger('orig_user_id')->nullable()->index();
            $table->timestamps();

            // Foreign key to users table
            $table->foreign('orig_user_id')->references('id')->on('users')->onDelete('set null');
        });

        $messages[] = "created {$tableName} table";

        // ---------------------------------------------------------
        // Default FAQ entries for PMB Portfolio
        // ---------------------------------------------------------
        $defaultFaqs = [
            [
                'question' => 'Experience and pricing second to none?',
                'answer'   => 'Property Management Brokers manages all types of residential properties from a single family home to large multiplex apt buildings. If our customer service expertise, friendly and approachable staff don\'t win you over we guarantee that our pricing will. We look forward to winning your business over.'
            ],
            [
                'question' => 'Where does PMB service/Where can I find a PMB Rental?',
                'answer'   => 'Property Management Brokers head office is located in Central Barrie Simcoe County and service properties from Orillia to Wasaga Beach, from Innisfil to Midland from our head office. We are also located and service the The Greater Sudbury Area. Our winning formula is coming soon to Windsor.'
            ],
            [
                'question' => 'How Do I Submit a Maintenance Request?',
                'answer'   => 'From the main page on our website, there is a green box to click on at the top titled ‘Maintenance Issues’. You will be directed to sign-in through your Payprop Tenant Portal and from there you will be able to submit your maintenance request to have our maintenance team contact you via the portal.'
            ],
            [
                'question' => 'What is Property Management Brokers?',
                'answer'   => 'We are a company who professionally manages your investment properties to maximize your rental investment income. We work directly with Landlords and Tenants as the frontline middleman to save you both time and money. We handle all management services based on your agreement with us and provide background services such as property maintenance, inspections and eviction services and more.'
            ],
            [
                'question' => 'How to Contact Us',
                'answer'   => 'Our Head Office is located at 137 Essa Rd, Barrie ON L4N 3K8. We accept applications and key drop-offs in our locked mailbox labeled ‘Unit 1\'. Our phone number is 1(866)-709-9416. We are on social media platforms such as Facebook (http://www.facebook.com/PropertyManagementBrokers) and Instagram (https://www.instagram.com/propertymanagementbrokers/). Our Office Hours are Monday-Friday 9am-5pm.'
            ]
        ];

        foreach ($defaultFaqs as $index => $faq) {
            Faq::create([
                'question'      => $faq['question'],
                'answer'        => $faq['answer'],
                'status_id'     => Faq::STATUS_ACTIVE,
                'display_order' => $index + 1,
                'orig_user_id'  => 1,
            ]);
        }

        $messages[] = "seeded " . count($defaultFaqs) . " faqs with active status";
    } catch (\Throwable $e) {
        $messages[] = "Error resetting " . ($tableName ?? 'faqs') . " table: " . $e->getMessage();
    }

    return $messages;
}
