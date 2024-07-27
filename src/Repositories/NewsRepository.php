<?php

namespace NickKlein\News\Repositories;

use NickKlein\News\Models\NewsSummary;
use NickKlein\News\Models\SourceLinks;
use NickKlein\News\Models\SourcesFavourites;
use App\Services\LogsService;
use Carbon\Carbon;

class NewsRepository
{
    /**
     * Get list of personalized user lists
     *
     * @return collection
     */
    const DAYS = 1;

    public function list(int $userId, $paginate)
    {
        try {
            //code...
            $expiredDate = Carbon::now()
                ->subDays(self::DAYS)
                ->format('Y-m-d H:m:s');

            return NewsSummary::selectRaw('sum(points) as points, GROUP_CONCAT(tag_name SEPARATOR ", ") as tag_name, count(tag_name) as tag_count, source_links.source_link_id, source_links.source_title, source_links.source_date, source_links.source_link, sources.source_name, sources.web_archive')
                ->join('source_links', 'news_summary.source_link_id', '=', 'source_links.source_link_id')
                ->join('sources', 'source_links.source_id', '=', 'sources.source_id')
                ->join('tags', 'tags.tag_id', 'news_summary.tag_id')
                ->where('news_summary.user_id', $userId)
                ->where([
                    ['active', 1],
                    ['source_links.created_at', '>', $expiredDate]
                ])
                ->groupBy('news_summary.source_link_id')
                ->orderBy('points', 'DESC')
                ->orderBy('tag_count', 'ASC')
                ->orderBy('source_date', 'DESC')
                ->paginate($paginate);
        } catch (\Throwable $th) {
            (new LogsService)->handle('error.newsSummaryList', 'Summary ' . $th->getMessage());
        }
    }

    public function listFavourite(int $userId, $paginate)
    {
        return SourceLinks::selectRaw('source_links.source_link_id, source_links.source_title, source_links.source_date, source_links.source_link, sources.source_name, sources.web_archive')
            ->join('sources', 'source_links.source_id', '=', 'sources.source_id')
            ->join('sources_favorites', 'sources_favorites.source_link_id', 'source_links.source_link_id')
            ->where('sources_favorites.user_id', $userId)
            ->orderBy('source_date', 'DESC')
            ->paginate($paginate);
    }

    /**
     * Check if source is favorited
     *
     * @return collection
     */
    public function favoriteExist(int $sourceLinkId, int $userId): bool
    {
        return SourcesFavourites::where([
            'source_link_id' => $sourceLinkId,
            'user_id' => $userId
        ])->exists();
    }
}
