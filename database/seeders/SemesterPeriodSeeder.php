<?php

namespace Database\Seeders;

use App\Models\SemesterPeriod;
use Illuminate\Database\Seeder;

class SemesterPeriodSeeder extends Seeder
{
    public function run(): void
    {
        SemesterPeriod::create([
            'year' => 2026,
            'start_month' => 2,
            'end_month' => 5,
        ]);

        SemesterPeriod::create([
            'year' => 2026,
            'start_month' => 8,
            'end_month' => 11,
        ]);
    }
}
