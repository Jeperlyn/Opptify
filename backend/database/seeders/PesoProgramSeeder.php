<?php

namespace Database\Seeders;

use App\Models\PesoProgram;
use Illuminate\Database\Seeder;

class PesoProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            [
                'title' => 'SPES',
                'description' => 'Special Program for Employment of Students for seasonal youth opportunities.',
                'requirements' => [
                    'Valid school ID or proof of enrollment',
                    'Government-issued ID',
                    'Birth certificate',
                ],
                'steps_to_avail' => [
                    'Create or update your CityJobLink account profile.',
                    'Submit SPES requirements through the portal upload form.',
                    'Wait for PESO pre-screening and SMS confirmation.',
                    'Report to the assigned desk for final briefing.',
                ],
                'category' => 'Youth Employment',
                'display_order' => 1,
                'is_active' => true,
                'contact' => [
                    'focal_person_name' => 'Maria Santos',
                    'desk_number' => 'PESO Desk 2',
                    'department_desk' => 'Youth and Employment Programs',
                    'contact_details' => '09171234567 / spes@quezoncity.gov.ph',
                    'office_hours' => 'Mon-Fri, 8:00 AM - 5:00 PM',
                ],
            ],
            [
                'title' => 'TUPAD',
                'description' => 'Emergency employment support for displaced and vulnerable workers.',
                'requirements' => [
                    'Barangay certificate',
                    'Valid ID',
                    'Signed beneficiary form',
                ],
                'steps_to_avail' => [
                    'Check available TUPAD slots in the directory.',
                    'Complete online application and required declarations.',
                    'Attend orientation schedule sent via SMS.',
                    'Coordinate with focal desk for deployment schedule.',
                ],
                'category' => 'Emergency Employment',
                'display_order' => 2,
                'is_active' => true,
                'contact' => [
                    'focal_person_name' => 'Jose Ramirez',
                    'desk_number' => 'PESO Desk 4',
                    'department_desk' => 'Emergency Livelihood Unit',
                    'contact_details' => '09179876543 / tupad@quezoncity.gov.ph',
                    'office_hours' => 'Mon-Fri, 8:00 AM - 5:00 PM',
                ],
            ],
        ];

        foreach ($programs as $programData) {
            $contactData = $programData['contact'];
            unset($programData['contact']);

            $program = PesoProgram::query()->updateOrCreate(
                ['title' => $programData['title']],
                $programData
            );

            $program->contact()->updateOrCreate(
                ['program_id' => $program->id],
                $contactData
            );
        }
    }
}
