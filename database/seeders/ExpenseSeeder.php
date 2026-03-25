<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory;
use App\Models\Expense;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Categories ──────────────────────────────────────────
        $categories = collect([
            'Ενοίκιο',
            'Λογαριασμοί (ΔΕΗ/Νερό/Internet)',
            'Υλικά & Αναλώσιμα',
            'Συντήρηση & Επισκευές',
            'Μισθοδοσία',
            'Ασφάλειες',
            'Διαφήμιση & Marketing',
            'Εξοπλισμός',
            'Καθαριότητα',
            'Λοιπά',
        ])->mapWithKeys(function ($name) {
            $cat = ExpenseCategory::create(['name' => $name]);
            return [$name => $cat];
        });

        $this->command?->info('ExpenseSeeder: Created ' . $categories->count() . ' expense categories.');

        // ── Expense data ────────────────────────────────────────
        $today = Carbon::today();
        $expenseCount = 0;

        // 1. Ενοίκιο — μηνιαίο πάγιο, πληρωμένο τους τελευταίους 6 μήνες
        for ($i = 5; $i >= 0; $i--) {
            $month = $today->copy()->subMonths($i);
            Expense::create([
                'expense_category_id' => $categories['Ενοίκιο']->id,
                'description'         => 'Ενοίκιο ' . $month->translatedFormat('F Y'),
                'amount'              => 650.00,
                'date'                => $month->copy()->startOfMonth()->toDateString(),
                'status'              => $i === 0 ? 'pending' : 'paid',
                'paid_at'             => $i === 0 ? null : $month->copy()->setDay(rand(1, 5))->setTime(10, 0),
                'notes'               => null,
            ]);
            $expenseCount++;
        }

        // 2. Λογαριασμοί — ΔΕΗ κάθε 2 μήνες, νερό, internet μηνιαίο
        for ($i = 5; $i >= 0; $i--) {
            $month = $today->copy()->subMonths($i);

            // Internet μηνιαίο
            Expense::create([
                'expense_category_id' => $categories['Λογαριασμοί (ΔΕΗ/Νερό/Internet)']->id,
                'description'         => 'Internet ' . $month->translatedFormat('F Y'),
                'amount'              => 35.00,
                'date'                => $month->copy()->setDay(15)->toDateString(),
                'status'              => $i <= 1 ? 'pending' : 'paid',
                'paid_at'             => $i <= 1 ? null : $month->copy()->setDay(rand(16, 20))->setTime(11, 30),
                'notes'               => null,
            ]);
            $expenseCount++;

            // ΔΕΗ κάθε 2 μήνες
            if ($i % 2 === 0) {
                Expense::create([
                    'expense_category_id' => $categories['Λογαριασμοί (ΔΕΗ/Νερό/Internet)']->id,
                    'description'         => 'Λογαριασμός ΔΕΗ ' . $month->translatedFormat('F Y'),
                    'amount'              => round(rand(8500, 14000) / 100, 2),
                    'date'                => $month->copy()->setDay(10)->toDateString(),
                    'status'              => $i === 0 ? 'pending' : 'paid',
                    'paid_at'             => $i === 0 ? null : $month->copy()->setDay(rand(12, 18))->setTime(9, 0),
                    'notes'               => null,
                ]);
                $expenseCount++;
            }
        }

        // 3. Υλικά & Αναλώσιμα — σποραδικά
        $materialItems = [
            ['Χρώματα ακρυλικά (σετ 24 τεμ.)', 45.90, 3],
            ['Καμβάδες 40x50 (10 τεμ.)', 32.00, 2],
            ['Πηλός κεραμικής 25kg', 28.50, 4],
            ['Πινέλα διάφορα μεγέθη', 18.75, 1],
            ['Γυαλόχαρτα & υλικά φινιρίσματος', 12.30, 2],
            ['Μολύβια σχεδίου (σετ)', 15.00, 5],
            ['Χαρτί σχεδίου Α3 (πακέτο)', 8.50, 3],
        ];
        foreach ($materialItems as [$desc, $amount, $monthsAgo]) {
            $d = $today->copy()->subMonths($monthsAgo)->setDay(rand(1, 28));
            Expense::create([
                'expense_category_id' => $categories['Υλικά & Αναλώσιμα']->id,
                'description'         => $desc,
                'amount'              => $amount,
                'date'                => $d->toDateString(),
                'status'              => 'paid',
                'paid_at'             => $d->copy()->addDays(rand(0, 2))->setTime(rand(10, 17), rand(0, 59)),
                'notes'               => null,
            ]);
            $expenseCount++;
        }

        // 4. Συντήρηση & Επισκευές
        Expense::create([
            'expense_category_id' => $categories['Συντήρηση & Επισκευές']->id,
            'description'         => 'Επισκευή κλιματιστικού',
            'amount'              => 180.00,
            'date'                => $today->copy()->subMonths(2)->setDay(8)->toDateString(),
            'status'              => 'paid',
            'paid_at'             => $today->copy()->subMonths(2)->setDay(8)->setTime(14, 0),
            'notes'               => 'Τεχνικός: Παπαδόπουλος, τιμολόγιο #1234',
        ]);
        $expenseCount++;

        Expense::create([
            'expense_category_id' => $categories['Συντήρηση & Επισκευές']->id,
            'description'         => 'Βαψίμο αίθουσας Β',
            'amount'              => 350.00,
            'date'                => $today->copy()->subWeeks(1)->toDateString(),
            'status'              => 'pending',
            'paid_at'             => null,
            'notes'               => 'Αναμένεται τιμολόγιο',
        ]);
        $expenseCount++;

        // 5. Μισθοδοσία — τελευταίοι 3 μήνες
        $teachers = ['Μαρία Κ.', 'Γιώργος Π.'];
        for ($i = 2; $i >= 0; $i--) {
            $month = $today->copy()->subMonths($i);
            foreach ($teachers as $teacher) {
                Expense::create([
                    'expense_category_id' => $categories['Μισθοδοσία']->id,
                    'description'         => "Μισθοδοσία {$teacher} - " . $month->translatedFormat('F Y'),
                    'amount'              => $teacher === 'Μαρία Κ.' ? 800.00 : 650.00,
                    'date'                => $month->copy()->endOfMonth()->toDateString(),
                    'status'              => $i === 0 ? 'pending' : 'paid',
                    'paid_at'             => $i === 0 ? null : $month->copy()->endOfMonth()->setTime(12, 0),
                    'notes'               => null,
                ]);
                $expenseCount++;
            }
        }

        // 6. Ασφάλειες — ετήσια, πληρωμένη
        Expense::create([
            'expense_category_id' => $categories['Ασφάλειες']->id,
            'description'         => 'Ετήσιο ασφάλιστρο χώρου 2026',
            'amount'              => 420.00,
            'date'                => $today->copy()->subMonths(4)->setDay(1)->toDateString(),
            'status'              => 'paid',
            'paid_at'             => $today->copy()->subMonths(4)->setDay(3)->setTime(10, 30),
            'notes'               => 'Ασφαλιστική εταιρεία: Eurolife',
        ]);
        $expenseCount++;

        // 7. Διαφήμιση & Marketing
        Expense::create([
            'expense_category_id' => $categories['Διαφήμιση & Marketing']->id,
            'description'         => 'Facebook/Instagram ads Φεβρουαρίου',
            'amount'              => 120.00,
            'date'                => $today->copy()->subMonths(1)->setDay(28)->toDateString(),
            'status'              => 'paid',
            'paid_at'             => $today->copy()->subMonths(1)->setDay(28)->setTime(9, 0),
            'notes'               => null,
        ]);
        $expenseCount++;

        Expense::create([
            'expense_category_id' => $categories['Διαφήμιση & Marketing']->id,
            'description'         => 'Εκτύπωση φυλλαδίων (500 τεμ.)',
            'amount'              => 75.00,
            'date'                => $today->copy()->subMonths(3)->setDay(12)->toDateString(),
            'status'              => 'paid',
            'paid_at'             => $today->copy()->subMonths(3)->setDay(12)->setTime(16, 0),
            'notes'               => 'Τυπογραφείο Αθηνών',
        ]);
        $expenseCount++;

        Expense::create([
            'expense_category_id' => $categories['Διαφήμιση & Marketing']->id,
            'description'         => 'Google Ads Μαρτίου',
            'amount'              => 95.00,
            'date'                => $today->copy()->setDay(5)->toDateString(),
            'status'              => 'pending',
            'paid_at'             => null,
            'notes'               => null,
        ]);
        $expenseCount++;

        // 8. Εξοπλισμός — μεγάλη αγορά
        Expense::create([
            'expense_category_id' => $categories['Εξοπλισμός']->id,
            'description'         => 'Κεραμικός φούρνος (μεταχειρισμένος)',
            'amount'              => 1200.00,
            'date'                => $today->copy()->subMonths(5)->setDay(20)->toDateString(),
            'status'              => 'paid',
            'paid_at'             => $today->copy()->subMonths(5)->setDay(22)->setTime(11, 0),
            'notes'               => 'Αγορά από marketplace, κατάσταση: πολύ καλή',
        ]);
        $expenseCount++;

        Expense::create([
            'expense_category_id' => $categories['Εξοπλισμός']->id,
            'description'         => 'Projector για παρουσιάσεις',
            'amount'              => 380.00,
            'date'                => $today->copy()->subWeeks(2)->toDateString(),
            'status'              => 'paid',
            'paid_at'             => $today->copy()->subWeeks(2)->addDay()->setTime(13, 0),
            'notes'               => null,
        ]);
        $expenseCount++;

        // 9. Καθαριότητα — μηνιαίο
        for ($i = 3; $i >= 0; $i--) {
            $month = $today->copy()->subMonths($i);
            Expense::create([
                'expense_category_id' => $categories['Καθαριότητα']->id,
                'description'         => 'Υπηρεσία καθαρισμού ' . $month->translatedFormat('F'),
                'amount'              => 150.00,
                'date'                => $month->copy()->setDay(1)->toDateString(),
                'status'              => $i === 0 ? 'pending' : 'paid',
                'paid_at'             => $i === 0 ? null : $month->copy()->setDay(rand(2, 5))->setTime(10, 0),
                'notes'               => null,
            ]);
            $expenseCount++;
        }

        // 10. Λοιπά — διάφορα μικροέξοδα
        Expense::create([
            'expense_category_id' => $categories['Λοιπά']->id,
            'description'         => 'Καφές & σνακ για εκδήλωση',
            'amount'              => 45.00,
            'date'                => $today->copy()->subMonths(1)->setDay(15)->toDateString(),
            'status'              => 'paid',
            'paid_at'             => $today->copy()->subMonths(1)->setDay(15)->setTime(12, 0),
            'notes'               => 'Εκδήλωση τέλους τριμήνου',
        ]);
        $expenseCount++;

        Expense::create([
            'expense_category_id' => $categories['Λοιπά']->id,
            'description'         => 'Ταχυδρομικά έξοδα',
            'amount'              => 8.50,
            'date'                => $today->copy()->subDays(5)->toDateString(),
            'status'              => 'paid',
            'paid_at'             => $today->copy()->subDays(5)->setTime(9, 30),
            'notes'               => null,
        ]);
        $expenseCount++;

        // 11. Expense χωρίς κατηγορία (nullable FK case)
        Expense::create([
            'expense_category_id' => null,
            'description'         => 'Έκτακτο έξοδο - κλειδαράς',
            'amount'              => 60.00,
            'date'                => $today->copy()->subDays(3)->toDateString(),
            'status'              => 'pending',
            'paid_at'             => null,
            'notes'               => 'Χωρίς κατηγορία, αναμένεται ταξινόμηση',
        ]);
        $expenseCount++;

        Expense::create([
            'expense_category_id' => null,
            'description'         => 'Δωρεά σε τοπικό σύλλογο',
            'amount'              => 100.00,
            'date'                => $today->copy()->subMonths(2)->setDay(20)->toDateString(),
            'status'              => 'paid',
            'paid_at'             => $today->copy()->subMonths(2)->setDay(20)->setTime(14, 0),
            'notes'               => null,
        ]);
        $expenseCount++;

        $this->command?->info("ExpenseSeeder: Created {$expenseCount} expense records.");
    }
}
