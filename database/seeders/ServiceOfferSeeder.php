<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ServiceOfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service_offer = [
            [
                'service_id'        => 1,
                'price'             => 100,
                'duration'          => 30,
                'image'             => 'service-offer-1.jpg',
                'status'            => 1,
            ],
            [
                'service_id'        => 2,
                'price'             => 200,
                'duration'          => 60,
                'image'             => 'service-offer-2.jpg',
                'status'            => 1,
            ],
            [
                'service_id'        => 3,
                'price'             => 300,
                'duration'          => 90,
                'image'             => 'service-offer-3.jpg',
                'status'            => 1,
            ],
            [
                'service_id'        => 4,
                'price'             => 400,
                'duration'          => 120,
                'image'             => 'service-offer-4.jpg',
                'status'            => 1,
            ],
            [
                'service_id'        => 5,
                'price'             => 500,
                'duration'          => 150,
                'image'             => 'service-offer-5.jpg',
                'status'            => 1,
            ],
        ];

        foreach ($service_offer as $offer) {
            $service_offer = \App\Models\ServiceOffer::create([
                'service_id'        => $offer['service_id'],
                'price'             => $offer['price'],
                'duration'          => $offer['duration'],
                'image'             => $offer['image'],
                'status'            => $offer['status'],
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $service_offer->translateOrNew('ar')->name = 'عرض خدمة ' . $offer['service_id'];
            $service_offer->translateOrNew('en')->name = 'Service Offer ' . $offer['service_id'];

            $service_offer->save();
        }


    }
}
