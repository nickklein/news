<?php

namespace NickKlein\News\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewsSummaryTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('news_summary')->insert([
            ['source_link_id' => 1, 'user_id' => 1, 'tag_id' => 1, 'points' => 5],
            ['source_link_id' => 2, 'user_id' => 2, 'tag_id' => 2, 'points' => 4],
            ['source_link_id' => 3, 'user_id' => 3, 'tag_id' => 3, 'points' => 3],
            ['source_link_id' => 4, 'user_id' => 1, 'tag_id' => 1, 'points' => 2],
            ['source_link_id' => 5, 'user_id' => 2, 'tag_id' => 4, 'points' => 5],
            ['source_link_id' => 6, 'user_id' => 3, 'tag_id' => 2, 'points' => 1],
            ['source_link_id' => 7, 'user_id' => 1, 'tag_id' => 5, 'points' => 3],
            ['source_link_id' => 8, 'user_id' => 2, 'tag_id' => 3, 'points' => 2],
            ['source_link_id' => 9, 'user_id' => 3, 'tag_id' => 4, 'points' => 4],
            ['source_link_id' => 10, 'user_id' => 1, 'tag_id' => 5, 'points' => 5],
        ]);
    }
}
