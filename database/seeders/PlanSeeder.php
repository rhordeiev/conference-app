<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plans = [
            [
                'price' => 15,
                'join_count' => 5,
                'stripe_plan' => 'price_1MqLCSI09tYv3MOyS3c2c3z5',
            ],
            [
                'price' => 25,
                'join_count' => 50,
                'stripe_plan' => 'price_1MqLCvI09tYv3MOy94UNrQJf',
            ],
            [
                'price' => 100,
                'join_count' => null,
                'stripe_plan' => 'price_1MqLDKI09tYv3MOyu0MoctBv',
            ],
        ];

        foreach ($plans as $plan) {
            (new Plan($plan))->save();
        }
    }
}
