<?php

namespace NickKlein\News\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SourcesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sources')->insert([
            ['source_id' => 1, 'source_name' => 'Polygon', 'source_domain' => 'www.polygon.com', 'source_main_url' => 'https://www.polygon.com/', 'language' => 'en'],
            ['source_id' => 3, 'source_name' => 'PC Gamer', 'source_domain' => 'www.pcgamer.com', 'source_main_url' => 'https://www.pcgamer.com/', 'language' => 'en'],
            ['source_id' => 4, 'source_name' => 'The Next Web', 'source_domain' => 'thenextweb.com', 'source_main_url' => 'https://thenextweb.com/latest/', 'language' => 'en'],
            ['source_id' => 5, 'source_name' => 'CBC', 'source_domain' => 'www.cbc.ca', 'source_main_url' => 'http://www.cbc.ca/news', 'language' => 'en'],
            ['source_id' => 6, 'source_name' => 'BBC', 'source_domain' => 'www.bbc.com', 'source_main_url' => 'http://www.bbc.com/news', 'language' => 'en'],
            ['source_id' => 7, 'source_name' => 'The Guardian', 'source_domain' => 'www.theguardian.com', 'source_main_url' => 'https://www.theguardian.com/international', 'language' => 'en'],
            ['source_id' => 11, 'source_name' => 'IGN', 'source_domain' => 'www.ign.com', 'source_main_url' => 'https://www.ign.com/ca', 'language' => 'en'],
            ['source_id' => 12, 'source_name' => 'Politico', 'source_domain' => 'www.politico.com', 'source_main_url' => 'https://www.politico.com/', 'language' => 'en'],
            ['source_id' => 14, 'source_name' => 'Sueddeutsche Zeitung', 'source_domain' => 'www.sueddeutsche.de', 'source_main_url' => 'https://www.sueddeutsche.de', 'language' => 'de'],
            ['source_id' => 15, 'source_name' => 'Forbes', 'source_domain' => 'www.forbes.com', 'source_main_url' => 'https://www.forbes.com/', 'language' => 'en'],
            ['source_id' => 16, 'source_name' => 'North Shore News', 'source_domain' => 'www.nsnews.com', 'source_main_url' => 'https://www.nsnews.com', 'language' => 'en'],
            ['source_id' => 17, 'source_name' => 'Georgia Straight', 'source_domain' => 'www.straight.com', 'source_main_url' => 'https://www.straight.com/', 'language' => 'en'],
        ]);
    }
}
