<?php

namespace Database\Seeders;

use App\Models\Interests;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InterestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Interests::create([
            'interests' => 'Reading',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        Interests::create([
            'interests' => 'Video Games',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        Interests::create([
            'interests' => 'Sports',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        Interests::create([
            'interests' => 'Travelling',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
