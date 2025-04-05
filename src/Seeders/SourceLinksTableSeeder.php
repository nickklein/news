<?php

namespace NickKlein\News\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SourceLinksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('source_links')->insert([
            [
                'source_id'    => 1,
                'source_link'  => 'https://www.polygon.com/2025/04/03/new-game-announcement',
                'source_title' => 'New Game Announced by Top Studio',
                'source_date'  => Carbon::parse('2025-04-03 10:00:00'),
                'source_raw'   => '',
                'created_at'   => now(),
                'updated_at'   => now(),
                'active'       => 1,
            ],
            [
                'source_id'    => 3,
                'source_link'  => 'https://www.pcgamer.com/spring-sale-top-picks/',
                'source_title' => 'PC Gamer Spring Sale: Our Top Picks',
                'source_date'  => Carbon::parse('2025-04-02 14:30:00'),
                'source_raw'   => '',
                'created_at'   => now(),
                'updated_at'   => now(),
                'active'       => 1,
            ],
            [
                'source_id'    => 4,
                'source_link'  => 'https://thenextweb.com/news/meta-unveils-new-vr-headset',
                'source_title' => 'Meta Unveils New VR Headset With Eye Tracking',
                'source_date'  => Carbon::parse('2025-04-01 08:45:00'),
                'source_raw'   => '',
                'created_at'   => now(),
                'updated_at'   => now(),
                'active'       => 1,
            ],
            [
                'source_id'    => 5,
                'source_link'  => 'https://www.cbc.ca/news/canada/budget-2025-key-points-1.1234567',
                'source_title' => 'Federal Budget 2025: What You Need to Know',
                'source_date'  => Carbon::parse('2025-03-30 17:00:00'),
                'source_raw'   => '',
                'created_at'   => now(),
                'updated_at'   => now(),
                'active'       => 1,
            ],
            [
                'source_id'    => 6,
                'source_link'  => 'https://www.bbc.com/news/world-asia-67230458',
                'source_title' => 'Tensions Rise in East Asia Amid Military Drills',
                'source_date'  => Carbon::parse('2025-04-03 13:20:00'),
                'source_raw'   => '',
                'created_at'   => now(),
                'updated_at'   => now(),
                'active'       => 1,
            ],
            [
                'source_id'    => 7,
                'source_link'  => 'https://www.theguardian.com/technology/2025/apr/01/ai-regulation-update',
                'source_title' => 'Global Push for AI Regulation Intensifies',
                'source_date'  => Carbon::parse('2025-04-01 09:15:00'),
                'source_raw'   => '',
                'created_at'   => now(),
                'updated_at'   => now(),
                'active'       => 1,
            ],
            [
                'source_id'    => 11,
                'source_link'  => 'https://www.ign.com/articles/top-10-rpgs-of-2025',
                'source_title' => 'IGN Picks: Top 10 RPGs of 2025 (So Far)',
                'source_date'  => Carbon::parse('2025-03-29 12:00:00'),
                'source_raw'   => '',
                'created_at'   => now(),
                'updated_at'   => now(),
                'active'       => 1,
            ],
            [
                'source_id'    => 12,
                'source_link'  => 'https://www.politico.com/news/2025/04/02/us-election-preview',
                'source_title' => 'Everything You Need to Know About the 2025 US Election',
                'source_date'  => Carbon::parse('2025-04-02 11:10:00'),
                'source_raw'   => '',
                'created_at'   => now(),
                'updated_at'   => now(),
                'active'       => 1,
            ],
            [
                'source_id'    => 14,
                'source_link'  => 'https://www.sueddeutsche.de/politik/klimawandel-2025-maerz',
                'source_title' => 'Klimawandel und Politik: Neue Maßnahmen im März',
                'source_date'  => Carbon::parse('2025-03-31 18:00:00'),
                'source_raw'   => '',
                'created_at'   => now(),
                'updated_at'   => now(),
                'active'       => 1,
            ],
            [
                'source_id'    => 17,
                'source_link'  => 'https://www.straight.com/arts/music-festival-vancouver-2025',
                'source_title' => 'Vancouver’s Biggest Music Festival Returns',
                'source_date'  => Carbon::parse('2025-04-04 12:00:00'),
                'source_raw'   => '',
                'created_at'   => now(),
                'updated_at'   => now(),
                'active'       => 1,
            ],
        ]);
    }
}
