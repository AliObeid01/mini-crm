<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'R&D',
            ],
            [
                'name' => 'Integration',
            ],
            [
                'name' => 'Sales',
            ],
            [
                'name' => 'Marketing',
            ],
            [
                'name' => 'HR',
            ],
            [
                'name' => 'Finance',

            ],
            [
                'name' => 'IT',
            ],
            [
                'name' => 'Operations',
            ],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['name' => $department['name']],
                $department
            );
        }

        $this->command->info('Departments seeded successfully!');
    }
}
