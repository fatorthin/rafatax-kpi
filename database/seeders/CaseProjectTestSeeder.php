<?php

namespace Database\Seeders;

use App\Models\CaseProject;
use App\Models\Staff;
use App\Models\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CaseProjectTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil beberapa staff dan client yang ada
        $staffs = Staff::limit(5)->get();
        $clients = Client::limit(3)->get();

        if ($staffs->isEmpty() || $clients->isEmpty()) {
            echo "❌ Tidak ada data Staff atau Client. Silakan run seeder Staff dan Client dulu.\n";
            return;
        }

        echo "🔄 Membuat test data CaseProject...\n";

        // Buat case projects untuk setiap staff
        foreach ($staffs as $staff) {
            // Buat 2-4 case project per staff
            $caseCount = rand(2, 4);

            for ($i = 1; $i <= $caseCount; $i++) {
                CaseProject::create([
                    'description' => "Kasus {$i} untuk staff {$staff->name} - " . fake()->sentence(6),
                    'case_date' => fake()->dateTimeBetween('-6 months', 'now'),
                    'status' => fake()->randomElement(['open', 'in_progress', 'closed']),
                    'staff_id' => $staff->id,
                    'client_id' => $clients->random()->id,
                    'link_dokumen' => fake()->url(),
                ]);
            }

            echo "✅ Dibuat {$caseCount} case project untuk staff: {$staff->name}\n";
        }

        echo "🎉 Seeder CaseProject selesai!\n";
    }
}
