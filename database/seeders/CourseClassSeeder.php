<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseClass;
use App\Models\ClassSession;
use App\Models\User;
use Carbon\Carbon;

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

        // ============ WEEKLY CLASSES ============
        $weeklyClasses = [
            // Teacher schedule (Monday)
            [
                'title'       => 'Ζωγραφική Αρχαρίων',
                'description' => 'Βασικές τεχνικές ζωγραφικής και χρώμα.',
                'type'        => 'weekly',
                'day_of_week' => 1, // Mon
                'starts_time' => '17:00',
                'ends_time'   => '18:30',
                'capacity'    => 12,
                'teacher_id'  => $teacher->id,
            ],
            [
                'title'       => 'Σχέδιο (Μέσο επίπεδο)',
                'description' => 'Φόρμα, αναλογίες και σκίαση.',
                'type'        => 'weekly',
                'day_of_week' => 1, // Mon
                'starts_time' => '18:45',
                'ends_time'   => '20:15',
                'capacity'    => 10,
                'teacher_id'  => $teacher->id,
            ],

            // Teacher schedule (Wednesday)
            [
                'title'       => 'Κεραμική',
                'description' => 'Βασικές τεχνικές πηλού και φόρμες.',
                'type'        => 'weekly',
                'day_of_week' => 3, // Wed
                'starts_time' => '17:30',
                'ends_time'   => '19:00',
                'capacity'    => 8,
                'teacher_id'  => $teacher->id,
            ],
            [
                'title'       => 'Ζωγραφική Προχωρημένων',
                'description' => 'Σύνθεση, ένταση χρώματος και προσωπικό ύφος.',
                'type'        => 'weekly',
                'day_of_week' => 3, // Wed
                'starts_time' => '19:15',
                'ends_time'   => '20:45',
                'capacity'    => 10,
                'teacher_id'  => $teacher->id,
            ],

            // Admin as teacher (Friday)
            [
                'title'       => 'Portfolio Lab',
                'description' => 'Καθοδήγηση για portfolio και projects.',
                'type'        => 'weekly',
                'day_of_week' => 5, // Fri
                'starts_time' => '18:00',
                'ends_time'   => '19:30',
                'capacity'    => 6,
                'teacher_id'  => $admin->id,
            ],
            [
                'title'       => 'Open Studio',
                'description' => 'Ελεύθερη ώρα με υποστήριξη/feedback.',
                'type'        => 'weekly',
                'day_of_week' => 5, // Fri
                'starts_time' => '19:45',
                'ends_time'   => '21:15',
                'capacity'    => 12,
                'teacher_id'  => $admin->id,
            ],

            // Saturday class
            [
                'title'       => 'Παιδική Ζωγραφική',
                'description' => 'Ζωγραφική για παιδιά 6-12 ετών.',
                'type'        => 'weekly',
                'day_of_week' => 6, // Sat
                'starts_time' => '11:00',
                'ends_time'   => '12:30',
                'capacity'    => 15,
                'teacher_id'  => $teacher->id,
            ],
        ];

        foreach ($weeklyClasses as $payload) {
            CourseClass::firstOrCreate(
                [
                    'title'       => $payload['title'],
                    'type'        => 'weekly',
                    'day_of_week' => $payload['day_of_week'],
                    'starts_time' => $payload['starts_time'],
                ],
                $payload
            );
        }

        // ============ WORKSHOPS ============
        $workshops = [
            [
                'title'       => 'Workshop Ακουαρέλας',
                'description' => 'Εντατικό σεμινάριο τεχνικών ακουαρέλας για αρχάριους και μεσαίους.',
                'type'        => 'workshop',
                'capacity'    => 10,
                'teacher_id'  => $teacher->id,
                'sessions'    => $this->generateWorkshopSessions(3, '10:00', '14:00'), // 3 sessions
            ],
            [
                'title'       => 'Workshop Πορτραίτου',
                'description' => 'Μάθε να ζωγραφίζεις πορτραίτα με λάδι.',
                'type'        => 'workshop',
                'capacity'    => 8,
                'teacher_id'  => $admin->id,
                'sessions'    => $this->generateWorkshopSessions(4, '17:00', '20:00'), // 4 sessions
            ],
            [
                'title'       => 'Summer Art Camp',
                'description' => 'Καλοκαιρινό εργαστήρι τέχνης για εφήβους.',
                'type'        => 'workshop',
                'capacity'    => 20,
                'teacher_id'  => $teacher->id,
                'active'      => false, // Inactive workshop
                'sessions'    => $this->generateWorkshopSessions(5, '09:00', '13:00', 60), // 5 sessions, starting in 60 days
            ],
        ];

        foreach ($workshops as $workshopData) {
            $sessions = $workshopData['sessions'];
            unset($workshopData['sessions']);

            $workshop = CourseClass::firstOrCreate(
                [
                    'title' => $workshopData['title'],
                    'type'  => 'workshop',
                ],
                $workshopData
            );

            // Create sessions if workshop was just created (no existing sessions)
            if ($workshop->sessions()->count() === 0) {
                foreach ($sessions as $session) {
                    $workshop->sessions()->create($session);
                }
            }
        }

        $this->command?->info('CourseClassSeeder: Created ' . count($weeklyClasses) . ' weekly classes and ' . count($workshops) . ' workshops.');
    }

    /**
     * Generate workshop session dates starting from a future date.
     */
    private function generateWorkshopSessions(int $count, string $startsTime, string $endsTime, int $startDaysFromNow = 14): array
    {
        $sessions = [];
        $startDate = Carbon::now()->addDays($startDaysFromNow);

        // Find next Saturday
        while ($startDate->dayOfWeek !== Carbon::SATURDAY) {
            $startDate->addDay();
        }

        for ($i = 0; $i < $count; $i++) {
            $sessions[] = [
                'date'        => $startDate->copy()->addWeeks($i)->format('Y-m-d'),
                'starts_time' => $startsTime,
                'ends_time'   => $endsTime,
            ];
        }

        return $sessions;
    }
}
