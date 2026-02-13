<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassEnrollment;
use App\Models\Student;
use App\Models\CourseClass;
use Carbon\Carbon;

class ClassEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        $classes = CourseClass::where('type', 'weekly')->get();

        if ($students->isEmpty() || $classes->isEmpty()) {
            $this->command?->warn('ClassEnrollmentSeeder: No students or classes found. Run TestStudentsSeeder and CourseClassSeeder first.');
            return;
        }

        // Get specific classes by title
        $painting = $classes->firstWhere('title', 'Ζωγραφική Αρχαρίων');
        $drawing = $classes->firstWhere('title', 'Σχέδιο (Μέσο επίπεδο)');
        $ceramics = $classes->firstWhere('title', 'Κεραμική');
        $advancedPainting = $classes->firstWhere('title', 'Ζωγραφική Προχωρημένων');
        $childPainting = $classes->firstWhere('title', 'Παιδική Ζωγραφική');
        $openStudio = $classes->firstWhere('title', 'Open Studio');

        // Get students by name
        $anna = $students->firstWhere('first_name', 'Anna');           // Adult member
        $nikolas = $students->firstWhere('first_name', 'Nikolas');     // Adult member
        $dimitris = $students->firstWhere('first_name', 'Dimitris');   // Minor member
        $stella = $students->firstWhere('first_name', 'Stella');       // Minor member
        $panagiotis = $students->firstWhere('first_name', 'Panagiotis'); // Minor member
        $katerina = $students->firstWhere('first_name', 'Katerina');   // Adult member

        $enrollmentCount = 0;

        /*
        |--------------------------------------------------------------------------
        | 1. Simple Active Enrollments
        |--------------------------------------------------------------------------
        */
        if ($painting && $stella) {
            ClassEnrollment::create([
                'student_id'  => $stella->id,
                'class_id'    => $painting->id,
                'status'      => 'active',
                'enrolled_at' => Carbon::today()->subMonths(2),
                'notes'       => 'Beginner - shows great enthusiasm',
            ]);
            $enrollmentCount++;
        }

        if ($childPainting && $panagiotis) {
            ClassEnrollment::create([
                'student_id'  => $panagiotis->id,
                'class_id'    => $childPainting->id,
                'status'      => 'active',
                'enrolled_at' => Carbon::today()->subMonths(3),
            ]);
            $enrollmentCount++;
        }

        if ($advancedPainting && $anna) {
            ClassEnrollment::create([
                'student_id'  => $anna->id,
                'class_id'    => $advancedPainting->id,
                'status'      => 'active',
                'enrolled_at' => Carbon::today()->subMonths(2),
                'notes'       => 'Very talented, preparing portfolio',
            ]);
            $enrollmentCount++;
        }

        if ($drawing && $dimitris) {
            ClassEnrollment::create([
                'student_id'  => $dimitris->id,
                'class_id'    => $drawing->id,
                'status'      => 'active',
                'enrolled_at' => Carbon::today()->subMonths(4),
            ]);
            $enrollmentCount++;
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Withdrawn Enrollment
        |--------------------------------------------------------------------------
        */
        if ($ceramics && $nikolas) {
            ClassEnrollment::create([
                'student_id'   => $nikolas->id,
                'class_id'     => $ceramics->id,
                'status'       => 'withdrawn',
                'enrolled_at'  => Carbon::today()->subMonths(5),
                'withdrawn_at' => Carbon::today()->subMonths(3),
                'notes'        => 'Withdrew due to schedule conflict',
            ]);
            $enrollmentCount++;
        }

        /*
        |--------------------------------------------------------------------------
        | 3. RE-ENROLLMENT SCENARIO (Demonstrates history feature!)
        |    Katerina: enrolled -> withdrawn -> re-enrolled (2 records)
        |--------------------------------------------------------------------------
        */
        if ($painting && $katerina) {
            // First enrollment period (Sep 2024 - Dec 2024)
            ClassEnrollment::create([
                'student_id'   => $katerina->id,
                'class_id'     => $painting->id,
                'status'       => 'withdrawn',
                'enrolled_at'  => Carbon::create(2024, 9, 1),
                'withdrawn_at' => Carbon::create(2024, 12, 15),
                'notes'        => 'First enrollment - withdrew for personal reasons',
            ]);
            $enrollmentCount++;

            // Second enrollment period (Feb 2025 - present)
            ClassEnrollment::create([
                'student_id'  => $katerina->id,
                'class_id'    => $painting->id,
                'status'      => 'active',
                'enrolled_at' => Carbon::create(2025, 2, 1),
                'notes'       => 'Re-enrolled after break - continuing from where she left',
            ]);
            $enrollmentCount++;
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Another Re-enrollment (Anna in Open Studio)
        |--------------------------------------------------------------------------
        */
        if ($openStudio && $anna) {
            // First period
            ClassEnrollment::create([
                'student_id'   => $anna->id,
                'class_id'     => $openStudio->id,
                'status'       => 'withdrawn',
                'enrolled_at'  => Carbon::create(2024, 6, 1),
                'withdrawn_at' => Carbon::create(2024, 8, 31),
                'notes'        => 'Summer session - completed',
            ]);
            $enrollmentCount++;

            // Second period
            ClassEnrollment::create([
                'student_id'   => $anna->id,
                'class_id'     => $openStudio->id,
                'status'       => 'withdrawn',
                'enrolled_at'  => Carbon::create(2024, 10, 1),
                'withdrawn_at' => Carbon::create(2024, 12, 20),
                'notes'        => 'Fall session - completed',
            ]);
            $enrollmentCount++;

            // Third period (current)
            ClassEnrollment::create([
                'student_id'  => $anna->id,
                'class_id'    => $openStudio->id,
                'status'      => 'active',
                'enrolled_at' => Carbon::create(2025, 1, 15),
                'notes'       => 'Third enrollment - regular participant',
            ]);
            $enrollmentCount++;
        }

        $this->command?->info("ClassEnrollmentSeeder: Created {$enrollmentCount} enrollment records (including re-enrollments).");
    }
}
