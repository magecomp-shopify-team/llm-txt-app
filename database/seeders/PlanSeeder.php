<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Osiset\ShopifyApp\Storage\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::insert([
            [
                'id' => 1,
                'type' => 'RECURRING',
                'name' => 'Basic plan',
                'price' => 3.99,
                'interval' => 'EVERY_30_DAYS',
                'capped_amount' => null,
                'terms' => null,
                'trial_days' => 0,
                'test' => 0,
                'on_install' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'type' => 'RECURRING',
                'name' => 'Basic plan - Annual',
                'price' => 39.99,
                'interval' => 'ANNUAL',
                'capped_amount' => null,
                'terms' => null,
                'trial_days' => 0,
                'test' => 0,
                'on_install' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'type' => 'RECURRING',
                'name' => 'Advance plan',
                'price' => 5.99,
                'interval' => 'EVERY_30_DAYS',
                'capped_amount' => null,
                'terms' => null,
                'trial_days' => 0,
                'test' => 0,
                'on_install' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'type' => 'RECURRING',
                'name' => 'Advance plan - Annual',
                'price' => 59.99,
                'interval' => 'ANNUAL',
                'capped_amount' => null,
                'terms' => null,
                'trial_days' => 0,
                'test' => 0,
                'on_install' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'type' => 'RECURRING',
                'name' => 'Pro plan',
                'price' => 6.99,
                'interval' => 'EVERY_30_DAYS',
                'capped_amount' => null,
                'terms' => null,
                'trial_days' => 0,
                'test' => 0,
                'on_install' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 6,
                'type' => 'RECURRING',
                'name' => 'Pro plan - Annual',
                'price' => 69.99,
                'interval' => 'ANNUAL',
                'capped_amount' => null,
                'terms' => null,
                'trial_days' => 0,
                'test' => 0,
                'on_install' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}
