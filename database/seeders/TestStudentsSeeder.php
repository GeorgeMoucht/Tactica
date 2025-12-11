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
            'first_name'        => 'Giorgos',
            'last_name'         => 'Papadopoulos',
            'birthdate'         => Carbon::today()->subYears(10),
            'level'             => 'beginner',
            'interests'         => ['painting'],
            'notes'             => 'Minor student — contact guardian for info',
            'consent_media'     => false,
            'is_member'         => false,
            'registration_date' => null,
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
            'first_name'        => 'Petros',
            'last_name'         => 'Koutris',
            'birthdate'         => Carbon::today()->subYears(15),
            'level'             => 'intermediate',
            'interests'         => ['drawing', 'ceramics'],
            'consent_media'     => true,
            'is_member'         => false,
            'registration_date' => null,
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
            'is_member'         => true,
            'registration_date' => Carbon::today()->subMonth(rand(0,12)),
        ]);

        // === 4. Another minor sharing the same guardians as Petros ===
        $student4 = Student::create([
            'first_name'        => 'Ioanna',
            'last_name'         => 'Koutri',
            'birthdate'         => Carbon::today()->subYears(12),
            'level'             => 'beginner',
            'interests'         => ['painting'],
            'notes'             => 'Younger sibling of Petros',
            'consent_media'     => true,
            'is_member'         => false,
            'registration_date' => null,
        ]);

        $student4->guardians()->attach([
            $guardian2a->id => ['relation' => 'father'],
            $guardian2b->id => ['relation' => 'mother'],
        ]);

        // === 5. Another minor sharing Maria as guardian ===
        $student5 = Student::create([
            'first_name'        => 'Christina',
            'last_name'         => 'Papadopoulou',
            'birthdate'         => Carbon::today()->subYears(8),
            'level'             => 'beginner',
            'interests'         => ['drawing'],
            'notes'             => 'Sister of Giorgos',
            'consent_media'     => false,
            'is_member'         => false,
            'registration_date' => null,
        ]);

        $student5->guardians()->attach($guardian1->id, ['relation' => 'mother']);

        // === 6. Adult member, no guardians ===
        $student6 = Student::create([
            'first_name'        => 'Nikolas',
            'last_name'         => 'Chalkias',
            'birthdate'         => Carbon::today()->subYears(30),
            'email'             => 'nikolas@example.com',
            'phone'             => '6994444444',
            'address'           => ['street' => 'Asklipiou 12', 'city' => 'Athens', 'zip' => '11472'],
            'preferred_contact' => 'phone',
            'level'             => 'beginner',
            'interests'         => ['painting'],
            'notes'             => 'New adult member',
            'consent_media'     => true,
            'is_member'         => true,
            'registration_date' => Carbon::today()->subMonths(rand(0, 12)),
        ]);

        // === 7. Minor who IS a member (paid registration) ===
        $guardian3 = Guardian::create([
            'first_name'         => 'Sofia',
            'last_name'          => 'Lazarou',
            'email'              => 'sofia.lazarou@example.com',
            'phone'              => '2103333333',
            'address'            => ['street' => 'Vasilissis Sofias 90', 'city' => 'Athens', 'zip' => '11521'],
            'preferred_contact'  => 'email',
            'notes'              => 'Mother of Dimitris',
            'newsletter_consent' => false,
        ]);

        $student7 = Student::create([
            'first_name'        => 'Dimitris',
            'last_name'         => 'Lazarou',
            'birthdate'         => Carbon::today()->subYears(14),
            'level'             => 'intermediate',
            'interests'         => ['drawing'],
            'notes'             => 'Minor member',
            'consent_media'     => true,
            'is_member'         => true,
            'registration_date' => Carbon::today()->subMonths(rand(0, 12)),
        ]);

        $student7->guardians()->attach($guardian3->id, ['relation' => 'mother']);

        // === 8. Adult member with no interests yet ===
        $student8 = Student::create([
            'first_name'        => 'Katerina',
            'last_name'         => 'Theodorou',
            'birthdate'         => Carbon::today()->subYears(26),
            'email'             => 'kat@example.com',
            'phone'             => '6935555555',
            'level'             => 'beginner',
            'interests'         => [],
            'notes'             => 'Just registered',
            'consent_media'     => false,
            'is_member'         => true,
            'registration_date' => Carbon::today()->subMonths(rand(0, 12)),
        ]);

        // === 9. Minor sibling pair — both members ===
        $guardian4 = Guardian::create([
            'first_name'         => 'Giannis',
            'last_name'          => 'Manolis',
            'email'              => 'giannis.manolis@example.com',
            'phone'              => '2107777777',
            'address'            => ['street' => 'Nea Smyrni 22', 'city' => 'Athens', 'zip' => '17121'],
            'preferred_contact'  => 'sms',
            'notes'              => 'Father',
            'newsletter_consent' => true,
        ]);

        $student9 = Student::create([
            'first_name'        => 'Stella',
            'last_name'         => 'Manoli',
            'birthdate'         => Carbon::today()->subYears(11),
            'level'             => 'advanced',
            'interests'         => ['painting', 'ceramics'],
            'consent_media'     => true,
            'is_member'         => true,
            'registration_date' => Carbon::today()->subMonths(rand(0, 12)),

        ]);

        $student10 = Student::create([
            'first_name'        => 'Panagiotis',
            'last_name'         => 'Manolis',
            'birthdate'         => Carbon::today()->subYears(9),
            'level'             => 'beginner',
            'interests'         => ['painting'],
            'consent_media'     => false,
            'is_member'         => true,
            'registration_date' => Carbon::today()->subMonths(rand(0, 12)),

        ]);

        $student9->guardians()->attach($guardian4->id, ['relation' => 'father']);
        $student10->guardians()->attach($guardian4->id, ['relation' => 'father']);

        // Final log
        $this->command->info('✅ Test students & guardians seeded with MANY members!');
    }
}
