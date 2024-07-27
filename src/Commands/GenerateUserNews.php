<?php

namespace NickKlein\News\Commands;

use NickKlein\News\Models\NewsSummary;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateUserNews extends Command
{
    protected $signature = 'run:GenerateUserNews';

    protected $description = 'Process user tags and update the news_summary table';

    const PT_TITLE = 3;
    const PT_RAW = 1;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->clearRank();
        $this->fetchUserTags();
    }

    private function fetchUserTags()
    {
        $userTags = User::leftJoin('user_tags', 'user_tags.user_id', '=', 'users.id')
            ->join('tags', 'user_tags.tag_id', '=', 'tags.tag_id')
            ->orderBy('users.id')
            ->get(['users.id', 'tags.tag_id', 'tags.tag_name']);

        $links = [];

        foreach ($userTags as $tag) {
            $srcIds = $this->returnSourceIds('SELECT source_id FROM user_sources WHERE user_id = ?', [$tag->id]);

            $articles = $this->processRanking($tag->tag_name, $srcIds);

            foreach ($articles as $article) {
                $links[] = [
                    'source_link_id' => $article['source_link_id'],
                    'user_id' => $tag->id,
                    'tag_id' => $tag->tag_id,
                    'points' => $article['rank'],
                ];
            }
        }

        NewsSummary::insertOrIgnore($links);
    }

    private function processRanking($word, $srcIds)
    {
        $srcIdsPlaceholder = rtrim(str_repeat('?,', count($srcIds)), ',');

        $it_titles = $this->returnLinkIds("SELECT source_link_id FROM source_links WHERE active = 1 AND source_id IN($srcIdsPlaceholder) AND source_title LIKE ?", array_merge($srcIds, ["%$word%"]));
        $it_raws = $this->returnLinkIds("SELECT source_link_id FROM source_links WHERE active = 1 AND source_id IN($srcIdsPlaceholder) AND source_raw LIKE ?", array_merge($srcIds, ["% $word %"]));

        $ids = [];

        foreach ($it_titles as $item) {
            $number = self::PT_TITLE;

            if (in_array($item, $it_raws)) {
                $number += self::PT_RAW;
            }

            $ids[] = ['source_link_id' => $item, 'rank' => $number];
        }

        foreach ($it_raws as $item) {
            if (!in_array($item, $it_titles)) {
                $ids[] = ['source_link_id' => $item, 'rank' => self::PT_RAW];
            }
        }

        return $ids;
    }

    private function returnSourceIds($query, $bindings)
    {
        return array_column(DB::select($query, $bindings), 'source_id');
    }

    private function returnLinkIds($query, $bindings)
    {
        return array_column(DB::select($query, $bindings), 'source_link_id');
    }

    private function clearRank()
    {
        NewsSummary::truncate();
    }
}
