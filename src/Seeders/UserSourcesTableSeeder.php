<?php

namespace NickKlein\News\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSourcesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_sources')->insert([
            ['user_sources_id' => 2, 'user_id' => 1, 'source_id' => 2],
            ['user_sources_id' => 8, 'user_id' => 1, 'source_id' => 8],
            ['user_sources_id' => 108, 'user_id' => 1, 'source_id' => 1],
            ['user_sources_id' => 109, 'user_id' => 1, 'source_id' => 3],
            ['user_sources_id' => 110, 'user_id' => 1, 'source_id' => 5],
            ['user_sources_id' => 111, 'user_id' => 1, 'source_id' => 12],
            ['user_sources_id' => 112, 'user_id' => 1, 'source_id' => 11],
            ['user_sources_id' => 115, 'user_id' => 1, 'source_id' => 16],
            ['user_sources_id' => 116, 'user_id' => 1, 'source_id' => 18],
            ['user_sources_id' => 120, 'user_id' => 1, 'source_id' => 24],
            ['user_sources_id' => 121, 'user_id' => 1, 'source_id' => 14],
            ['user_sources_id' => 122, 'user_id' => 1, 'source_id' => 21],
            ['user_sources_id' => 123, 'user_id' => 1, 'source_id' => 22],
            ['user_sources_id' => 126, 'user_id' => 1, 'source_id' => 6],
            ['user_sources_id' => 127, 'user_id' => 1, 'source_id' => 4],
        ]);
    }
}
