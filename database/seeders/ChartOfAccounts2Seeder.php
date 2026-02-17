<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\User;
use Carbon\Carbon;

class ChartOfAccounts2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            $this->command->error('No users found in the database. Please create a user before running this seeder.');
            return;
        }

        // 1. Load the JSON data from the file
        $jsonPath = database_path('data/chart_of_accounts.json');
        if (!File::exists($jsonPath)) {
            $this->command->error("JSON file not found at: {$jsonPath}");
            return;
        }

        $accounts = json_decode(File::get($jsonPath), true);
        $now = Carbon::now();

        foreach ($accounts as $account) {
            // Insert the parent account and get its ID
            DB::table('chart_of_accounts')->insert([
                'id' => $account['id'],
                'user_id' => $user->id,
                'parent_id' => $account['parent_id'],
                'code' => $account['code'],
                'name' => $account['name'],
                'type' => $account['type'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info('Chart of accounts has been successfully seeded from JSON!');
    }
}
