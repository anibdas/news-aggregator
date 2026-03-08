<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('settings')->truncate();
        DB::table('settings')->insert([
            'parameter' => 'news_sync_date',
            'value' => Carbon::now()->toDateString(),
        ]);

        DB::table('settings')->insert([
            'parameter' => 'page',
            'value' => 1,
        ]);
    }
}
