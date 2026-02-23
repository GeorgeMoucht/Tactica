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

        if ($students->count() < 10 || $classes->isEmpty()) {
            $this->command?->warn('ClassEnrollmentSeeder: Need at least 10 students and classes. Run TestStudentsSeeder and CourseClassSeeder first.');
            return;
        }

        // Map classes by title for easy reference
        $painting       = $classes->firstWhere('title', 'Ζωγραφική Αρχαρίων');
        $drawing        = $classes->firstWhere('title', 'Σχέδιο (Μέσο επίπεδο)');
        $ceramics       = $classes->firstWhere('title', 'Κεραμική');
        $advPainting    = $classes->firstWhere('title', 'Ζωγραφική Προχωρημένων');
        $portfolioLab   = $classes->firstWhere('title', 'Portfolio Lab');
        $openStudio     = $classes->firstWhere('title', 'Open Studio');
        $childPainting  = $classes->firstWhere('title', 'Παιδική Ζωγραφική');

        // Index students by first_name for convenience
        $s = [];
        foreach ($students as $student) {
            $s[$student->first_name] = $student;
        }

        $count = 0;

        /*
        |--------------------------------------------------------------------------
        | Ζωγραφική Αρχαρίων (capacity 12) — 10 active + 1 withdrawn
        |--------------------------------------------------------------------------
        */
        if ($painting) {
            $enrollees = ['Stella', 'Katerina', 'Nikolas', 'Michalis', 'Stavros',
                          'Despina', 'Maria', 'Nefeli', 'Vasiliki', 'Thodoris'];
            foreach ($enrollees as $i => $name) {
                if (!isset($s[$name])) continue;
                ClassEnrollment::create([
                    'student_id'  => $s[$name]->id,
                    'class_id'    => $painting->id,
                    'status'      => 'active',
                    'enrolled_at' => Carbon::today()->subMonths(rand(2, 5)),
                ]);
                $count++;
            }
            // Re-enrollment scenario: Katerina withdrew and came back
            if (isset($s['Katerina'])) {
                ClassEnrollment::create([
                    'student_id'   => $s['Katerina']->id,
                    'class_id'     => $painting->id,
                    'status'       => 'withdrawn',
                    'enrolled_at'  => Carbon::create(2024, 9, 1),
                    'withdrawn_at' => Carbon::create(2024, 12, 15),
                    'notes'        => 'First enrollment — withdrew for personal reasons',
                ]);
                $count++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Σχέδιο Μέσο επίπεδο (capacity 10) — 9 active
        |--------------------------------------------------------------------------
        */
        if ($drawing) {
            $enrollees = ['Dimitris', 'Alexandros', 'Spiros', 'Ioanna', 'Kostas',
                          'Thanasis', 'Anna', 'Eleftheria', 'Stavros'];
            foreach ($enrollees as $name) {
                if (!isset($s[$name])) continue;
                ClassEnrollment::create([
                    'student_id'  => $s[$name]->id,
                    'class_id'    => $drawing->id,
                    'status'      => 'active',
                    'enrolled_at' => Carbon::today()->subMonths(rand(2, 6)),
                ]);
                $count++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Κεραμική (capacity 8) — 8 active + 1 withdrawn
        |--------------------------------------------------------------------------
        */
        if ($ceramics) {
            $enrollees = ['Eleftheria', 'Thanasis', 'Ioanna', 'Dimitra', 'Spiros',
                          'Despina', 'Petros', 'Anna'];
            foreach ($enrollees as $name) {
                if (!isset($s[$name])) continue;
                ClassEnrollment::create([
                    'student_id'  => $s[$name]->id,
                    'class_id'    => $ceramics->id,
                    'status'      => 'active',
                    'enrolled_at' => Carbon::today()->subMonths(rand(2, 5)),
                ]);
                $count++;
            }
            // Nikolas withdrew
            if (isset($s['Nikolas'])) {
                ClassEnrollment::create([
                    'student_id'   => $s['Nikolas']->id,
                    'class_id'     => $ceramics->id,
                    'status'       => 'withdrawn',
                    'enrolled_at'  => Carbon::today()->subMonths(5),
                    'withdrawn_at' => Carbon::today()->subMonths(3),
                    'notes'        => 'Withdrew due to schedule conflict',
                ]);
                $count++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Ζωγραφική Προχωρημένων (capacity 10) — 8 active
        |--------------------------------------------------------------------------
        */
        if ($advPainting) {
            $enrollees = ['Anna', 'Kostas', 'Eleftheria', 'Alexandros', 'Dimitra',
                          'Stella', 'Katerina', 'Dimitris'];
            foreach ($enrollees as $name) {
                if (!isset($s[$name])) continue;
                ClassEnrollment::create([
                    'student_id'  => $s[$name]->id,
                    'class_id'    => $advPainting->id,
                    'status'      => 'active',
                    'enrolled_at' => Carbon::today()->subMonths(rand(2, 5)),
                ]);
                $count++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Portfolio Lab (capacity 6) — 6 active
        |--------------------------------------------------------------------------
        */
        if ($portfolioLab) {
            $enrollees = ['Kostas', 'Anna', 'Alexandros', 'Dimitra', 'Eleftheria', 'Spiros'];
            foreach ($enrollees as $name) {
                if (!isset($s[$name])) continue;
                ClassEnrollment::create([
                    'student_id'  => $s[$name]->id,
                    'class_id'    => $portfolioLab->id,
                    'status'      => 'active',
                    'enrolled_at' => Carbon::today()->subMonths(rand(2, 4)),
                ]);
                $count++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Open Studio (capacity 12) — 10 active + re-enrollment history
        |--------------------------------------------------------------------------
        */
        if ($openStudio) {
            $enrollees = ['Anna', 'Nikolas', 'Katerina', 'Thanasis', 'Alexandros',
                          'Kostas', 'Stavros', 'Maria', 'Dimitra', 'Ioanna'];
            foreach ($enrollees as $name) {
                if (!isset($s[$name])) continue;
                ClassEnrollment::create([
                    'student_id'  => $s[$name]->id,
                    'class_id'    => $openStudio->id,
                    'status'      => 'active',
                    'enrolled_at' => Carbon::today()->subMonths(rand(2, 5)),
                ]);
                $count++;
            }
            // Anna's re-enrollment history
            if (isset($s['Anna'])) {
                ClassEnrollment::create([
                    'student_id'   => $s['Anna']->id,
                    'class_id'     => $openStudio->id,
                    'status'       => 'withdrawn',
                    'enrolled_at'  => Carbon::create(2024, 6, 1),
                    'withdrawn_at' => Carbon::create(2024, 8, 31),
                    'notes'        => 'Summer session — completed',
                ]);
                $count++;

                ClassEnrollment::create([
                    'student_id'   => $s['Anna']->id,
                    'class_id'     => $openStudio->id,
                    'status'       => 'withdrawn',
                    'enrolled_at'  => Carbon::create(2024, 10, 1),
                    'withdrawn_at' => Carbon::create(2024, 12, 20),
                    'notes'        => 'Fall session — completed',
                ]);
                $count++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Παιδική Ζωγραφική (capacity 15) — 10 active (minors)
        |--------------------------------------------------------------------------
        */
        if ($childPainting) {
            $enrollees = ['Panagiotis', 'Giorgos', 'Christina', 'Ioanna', 'Petros',
                          'Michalis', 'Nefeli', 'Despina', 'Aggelos', 'Thodoris'];
            foreach ($enrollees as $name) {
                if (!isset($s[$name])) continue;
                ClassEnrollment::create([
                    'student_id'  => $s[$name]->id,
                    'class_id'    => $childPainting->id,
                    'status'      => 'active',
                    'enrolled_at' => Carbon::today()->subMonths(rand(2, 5)),
                ]);
                $count++;
            }
        }

        $this->command?->info("ClassEnrollmentSeeder: Created {$count} enrollment records across all weekly classes.");
    }
}
