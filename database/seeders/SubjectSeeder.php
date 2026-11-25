<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'Filipino', 'code' => 'FIL'],
            ['name' => 'English', 'code' => 'ENG'],
            ['name' => 'Mathematics', 'code' => 'MATH'],
            ['name' => 'Science', 'code' => 'SCI'],
            ['name' => 'Araling Panlipunan', 'code' => 'AP'],
            ['name' => 'Edukasyon sa Pagpapakatao', 'code' => 'ESP'],
            ['name' => 'TLE', 'code' => 'TLE'],
            ['name' => 'MAPEH', 'code' => 'MAPEH'],
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(['code' => $subject['code']], $subject + ['status' => 'active']);
        }
    }
}
