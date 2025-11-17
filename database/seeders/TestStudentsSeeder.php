<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\Student;
use App\Models\Guardian;

class TestStudentsSeeder extends Seeder
{
    public function run(): void
    {
        // === 1. One guardian, one minor ===
        $guardian1 = Guardian::create([
            'first_name'         => 'Maria',
            'last_name'          => 'Papadopoulou',
            'email'              => 'maria.guardian@example.com',
            'phone'              => '2101111111',
            'address'            => ['street' => 'Kifisias 10', 'city' => 'Athens', 'zip' => '11523'],
            'preferred_contact'  => 'phone',
            'notes'              => 'Mother of Giorgos',
            'newsletter_consent' => true,
        ]);

        $student1 = Student::create([
            'first_name'    => 'Giorgos',
            'last_name'     => 'Papadopoulos',
            'birthdate'     => Carbon::today()->subYears(10),
            'level'         => 'beginner',
            'interests'     => ['painting'],
            'notes'         => 'Minor student — contact guardian for info',
            'consent_media' => false,
        ]);

        $student1->guardians()->attach($guardian1->id, ['relation' => 'mother']);

        // === 2. Two guardians, one minor ===
        $guardian2a = Guardian::create([
            'first_name'         => 'Nikos',
            'last_name'          => 'Koutris',
            'email'              => 'nikos.guardian@example.com',
            'phone'              => '2102222222',
            'address'            => ['street' => 'Patision 100', 'city' => 'Athens', 'zip' => '11251'],
            'preferred_contact'  => 'email',
            'notes'              => 'Father of Petros',
            'newsletter_consent' => false,
        ]);

        $guardian2b = Guardian::create([
            'first_name'         => 'Eleni',
            'last_name'          => 'Koutri',
            'email'              => 'eleni.guardian@example.com',
            'phone'              => '2102222233',
            'address'            => ['street' => 'Patision 100', 'city' => 'Athens', 'zip' => '11251'],
            'preferred_contact'  => 'phone',
            'notes'              => 'Mother of Petros',
            'newsletter_consent' => true,
        ]);

        $student2 = Student::create([
            'first_name'    => 'Petros',
            'last_name'     => 'Koutris',
            'birthdate'     => Carbon::today()->subYears(15),
            'level'         => 'intermediate',
            'interests'     => ['drawing', 'ceramics'],
            'consent_media' => true,
        ]);

        $student2->guardians()->attach([
            $guardian2a->id => ['relation' => 'father'],
            $guardian2b->id => ['relation' => 'mother'],
        ]);

        // === 3. Adult student (no guardians) ===
        $student3 = Student::create([
            'first_name'        => 'Anna',
            'last_name'         => 'Georgiou',
            'birthdate'         => Carbon::today()->subYears(22),
            'email'             => 'anna.georgiou@example.com',
            'phone'             => '6993333333',
            'address'           => ['street' => 'Syngrou 5', 'city' => 'Athens', 'zip' => '11743'],
            'preferred_contact' => 'email',
            'level'             => 'advanced',
            'interests'         => ['ceramics'],
            'notes'             => 'Adult student',
            'consent_media'     => true,
        ]);

        // === 4. Another minor sharing the same guardians as Petros ===
        $student4 = Student::create([
            'first_name'    => 'Ioanna',
            'last_name'     => 'Koutri',
            'birthdate'     => Carbon::today()->subYears(12),
            'level'         => 'beginner',
            'interests'     => ['painting'],
            'notes'         => 'Younger sibling of Petros',
            'consent_media' => true,
        ]);

        $student4->guardians()->attach([
            $guardian2a->id => ['relation' => 'father'],
            $guardian2b->id => ['relation' => 'mother'],
        ]);

        // === 5. Another minor sharing Maria as guardian ===
        $student5 = Student::create([
            'first_name'    => 'Christina',
            'last_name'     => 'Papadopoulou',
            'birthdate'     => Carbon::today()->subYears(8),
            'level'         => 'beginner',
            'interests'     => ['drawing'],
            'notes'         => 'Sister of Giorgos',
            'consent_media' => false,
        ]);

        $student5->guardians()->attach($guardian1->id, ['relation' => 'mother']);

        $this->command->info('✅ Test students & guardians (with siblings) seeded successfully!');
    }
}
