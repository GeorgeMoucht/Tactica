<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseClass;
use App\Models\User;

class CourseClassSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@tactica.com')->first();
        $teacher = User::where('email', 'teacher@tactica.com')->first();

        if (!$admin || !$teacher) {
            $this->command?->warn('CourseClassSeeder: users not found. Run UserSeeder first.');
            return;
        }

        $classes = [
            // Teacher schedule (Monday)
            [
                'title'       => 'Ζωγραφική Αρχαρίων',
                'description' => 'Βασικές τεχνικές ζωγραφικής και χρώμα.',
                'day_of_week' => 1, // Mon
                'starts_time' => '17:00:00',
                'ends_time'   => '18:30:00',
                'capacity'    => 12,
                'teacher_id'  => $teacher->id,
            ],
            [
                'title'       => 'Σχέδιο (Μέσο επίπεδο)',
                'description' => 'Φόρμα, αναλογίες και σκίαση.',
                'day_of_week' => 1, // Mon
                'starts_time' => '18:45:00',
                'ends_time'   => '20:15:00',
                'capacity'    => 10,
                'teacher_id'  => $teacher->id,
            ],

            // Teacher schedule (Wednesday)
            [
                'title'       => 'Κεραμική',
                'description' => 'Βασικές τεχνικές πηλού και φόρμες.',
                'day_of_week' => 3, // Wed
                'starts_time' => '17:30:00',
                'ends_time'   => '19:00:00',
                'capacity'    => 8,
                'teacher_id'  => $teacher->id,
            ],
            [
                'title'       => 'Ζωγραφική Προχωρημένων',
                'description' => 'Σύνθεση, ένταση χρώματος και προσωπικό ύφος.',
                'day_of_week' => 3, // Wed
                'starts_time' => '19:15:00',
                'ends_time'   => '20:45:00',
                'capacity'    => 10,
                'teacher_id'  => $teacher->id,
            ],

            // Admin as teacher (Friday)
            [
                'title'       => 'Portfolio Lab',
                'description' => 'Καθοδήγηση για portfolio και projects.',
                'day_of_week' => 5, // Fri
                'starts_time' => '18:00:00',
                'ends_time'   => '19:30:00',
                'capacity'    => 6,
                'teacher_id'  => $admin->id,
            ],
            [
                'title'       => 'Open Studio',
                'description' => 'Ελεύθερη ώρα με υποστήριξη/feedback.',
                'day_of_week' => 5, // Fri
                'starts_time' => '19:45:00',
                'ends_time'   => '21:15:00',
                'capacity'    => 12,
                'teacher_id'  => $admin->id,
            ],

            // Unassigned class example (no teacher / no schedule)
            [
                'title'       => 'Workshop (ανακοίνωση)',
                'description' => 'Προσεχώς - θα οριστεί πρόγραμμα και καθηγητής.',
                'day_of_week' => null,
                'starts_time' => null,
                'ends_time'   => null,
                'capacity'    => null,
                'teacher_id'  => null,
            ],
        ];

        foreach ($classes as $payload) {
            // Use title + day + start as a natural uniqueness combo
            CourseClass::firstOrCreate(
                [
                    'title'       => $payload['title'],
                    'day_of_week' => $payload['day_of_week'],
                    'starts_time' => $payload['starts_time'],
                ],
                $payload
            );
        }
    }
}