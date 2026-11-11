<?php

namespace Database\Seeders;

use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($number = 1; $number <= 5; $number++) {
            $this->createUser(
                sprintf('subadmin%02d@example.com', $number),
                sprintf('Sub-Admin %02d', $number),
                'sub_admin',
            );
        }

        for ($number = 1; $number <= 2; $number++) {
            $this->createUser(
                sprintf('admin%02d@example.com', $number),
                sprintf('Admin %02d', $number),
                'admin',
            );
        }

        for ($number = 1; $number <= 2; $number++) {
            $this->createUser(
                sprintf('guard%02d@example.com', $number),
                sprintf('Guard %02d', $number),
                'guard',
            );
        }

        $sections = [
            ['name' => 'Grade 7 - Sampaguita', 'grade_level' => 'Grade 7'],
            ['name' => 'Grade 7 - Narra', 'grade_level' => 'Grade 7'],
        ];
        $sectionModels = [];

        $subAdmins = User::where('role', 'sub_admin')
            ->where('email', 'like', 'subadmin%@example.com')
            ->orderBy('email')
            ->get();

        foreach ($sections as $index => $sectionData) {
            $sectionModels[] = Section::firstOrCreate([
                'name' => $sectionData['name'],
                'grade_level' => $sectionData['grade_level'],
                'school_year' => '2026-2027',
            ], [
                'adviser_id' => $subAdmins[$index]->id ?? null,
            ]);
        }

        $students = [
            ['lrn' => '100000000001', 'first_name' => 'Noah', 'middle_name' => 'Gabriel', 'last_name' => 'Santos'],
            ['lrn' => '100000000002', 'first_name' => 'Mia', 'middle_name' => 'Isabel', 'last_name' => 'Santos'],
            ['lrn' => '100000000003', 'first_name' => 'Ethan', 'middle_name' => 'Luis', 'last_name' => 'Reyes'],
            ['lrn' => '100000000004', 'first_name' => 'Sophia', 'middle_name' => 'Marie', 'last_name' => 'Reyes'],
            ['lrn' => '100000000005', 'first_name' => 'Lucas', 'middle_name' => 'Jose', 'last_name' => 'Garcia'],
            ['lrn' => '100000000006', 'first_name' => 'Emma', 'middle_name' => 'Rose', 'last_name' => 'Dela Cruz'],
            ['lrn' => '100000000007', 'first_name' => 'James', 'middle_name' => 'Miguel', 'last_name' => 'Dela Cruz'],
            ['lrn' => '100000000008', 'first_name' => 'Olivia', 'middle_name' => 'Grace', 'last_name' => 'Mendoza'],
            ['lrn' => '100000000009', 'first_name' => 'Liam', 'middle_name' => 'Rafael', 'last_name' => 'Torres'],
            ['lrn' => '100000000010', 'first_name' => 'Ava', 'middle_name' => 'Nicole', 'last_name' => 'Flores'],
        ];
        $studentModels = [];

        foreach ($students as $index => $studentData) {
            $number = $index + 1;
            $studentUser = $this->createUser(
                sprintf('student%02d@example.com', $number),
                $studentData['first_name'].' '.$studentData['last_name'],
                'student',
            );

            $studentModels[$number] = Student::updateOrCreate(
                ['lrn' => $studentData['lrn']],
                [
                    ...$studentData,
                    'section_id' => $sectionModels[$index % count($sectionModels)]->id,
                    'qr_code' => sprintf('DUMMY-STUDENT-%03d', $number),
                    'status' => 'active',
                    'user_id' => $studentUser->id,
                ],
            );
        }

        $guardians = [
            ['first_name' => 'Maria', 'last_name' => 'Santos', 'phone_number' => '09170000001', 'students' => [1, 2]],
            ['first_name' => 'Carlos', 'last_name' => 'Reyes', 'phone_number' => '09170000002', 'students' => [3, 4]],
            ['first_name' => 'Ana', 'last_name' => 'Garcia', 'phone_number' => '09170000003', 'students' => [5]],
            ['first_name' => 'Jose', 'last_name' => 'Dela Cruz', 'phone_number' => '09170000004', 'students' => [6, 7]],
            ['first_name' => 'Elena', 'last_name' => 'Mendoza', 'phone_number' => '09170000005', 'students' => [8]],
            ['first_name' => 'Roberto', 'last_name' => 'Torres', 'phone_number' => '09170000006', 'students' => [9]],
            ['first_name' => 'Angela', 'last_name' => 'Flores', 'phone_number' => '09170000007', 'students' => [10]],
        ];

        foreach ($guardians as $index => $guardianData) {
            $number = $index + 1;
            $email = sprintf('parent%02d@example.com', $number);
            $guardianUser = $this->createUser(
                $email,
                $guardianData['first_name'].' '.$guardianData['last_name'],
                'parent',
            );
            $guardian = Guardian::updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => $guardianData['first_name'],
                    'last_name' => $guardianData['last_name'],
                    'phone_number' => $guardianData['phone_number'],
                    'relationship_to_student' => 'Parent',
                    'address' => sprintf('Dummy Address %02d, School District', $number),
                    'status' => 'active',
                    'user_id' => $guardianUser->id,
                ],
            );

            $guardian->students()->sync(
                collect($guardianData['students'])
                    ->map(fn (int $studentNumber) => $studentModels[$studentNumber]->id)
                    ->all(),
            );
        }

        $this->command?->info('Dummy accounts created. Every password is: password');
    }

    private function createUser(string $email, string $name, string $role): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'role' => $role,
                'is_active' => true,
            ],
        );
    }
}
