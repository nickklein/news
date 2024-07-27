<?php

namespace NickKlein\News\Services;

use NickKlein\News\Repositories\NewsRepository;
use NickKlein\News\Models\SourcesFavourites;
use NickKlein\News\Models\UserSources;
use NickKlein\News\Models\Sources;
use App\Repositories\TagsRepository;
use Carbon\Carbon;

class NewsService
{
    private $newsRepository;
    private $tagsRepository;

    const PAGINATE_LIMIT = 50;

    public function __construct(NewsRepository $service, TagsRepository $tagsRepository)
    {
        $this->newsRepository = $service;
        $this->tagsRepository = $tagsRepository;
    }

    /**
     * Get list of personalized user lists
     *
     * @return collection
     */
    public function list(int $userId): object
    {
        $list = $this->newsRepository->list($userId, self::PAGINATE_LIMIT);
        $list->map(function ($item) use ($userId) {
            $item->source_date = Carbon::parse($item->source_date)->diffForHumans();
            $item->favorited = $this->newsRepository->favoriteExist($item->source_link_id, $userId) ? 1 : 0;
            $item->source_link = $item->web_archive ? 'https://web.archive.org/web/*/' . $item->source_link : $item->source_link;
            $item->favorited_label = 'Add to Favorites';
            if ($item->favorited) {
                $item->favorited_label = 'Remove from Favorites';
            }

            return $item;
        });

        return $list;
    }

    public function listFavourite(int $userId)
    {
        $list = $this->newsRepository->listFavourite($userId, self::PAGINATE_LIMIT);
        $list->map(function ($item) use ($userId) {
            $item->source_date = Carbon::parse($item->source_date)->diffForHumans();
            $item->favorited = $this->newsRepository->favoriteExist($item->source_link_id, $userId) ? 1 : 0;
            $item->source_link = $item->web_archive ? 'https://web.archive.org/web/*/' . $item->source_link : $item->source_link;
            $item->favorited_label = 'Add to Favorites';
            if ($item->favorited) {
                $item->favorited_label = 'Remove from Favorites';
            }

            return $item;
        });

        return $list;
    }

    /**
     * Get list of personalized tags
     *
     * @return collection
     */
    public function listTags(int $userId): object
    {
        return $this->tagsRepository->listTags($userId);
    }

    /**
     * list sources for user
     *
     * @return collection
     */
    public function listSources(int $userId): object
    {
        $sources = Sources::all();
        $userSources = UserSources::where('user_id', $userId)->get();

        # Add active state
        foreach ($sources as $sourceItem) {
            foreach ($userSources as $userItem) {
                if ($sourceItem->source_id == $userItem->source_id) {
                    $sourceItem->active = true;
                }
            }
        }

        return $sources;
    }


    /**
     * update sources for user
     *
     * @return array
     */
    public function updateSources(int $userId, int $sourceId, bool $active): array
    {
        if (!$active) {
            $userSourceRel = UserSources::where('source_id', '=', $sourceId)->where('user_id', '=', $userId);
            $userSourceRel->delete();
        } else {
            $userSourceRel = new UserSources;
            $userSourceRel->user_id = $userId;
            $userSourceRel->source_id = $sourceId;
            $userSourceRel->save();
        }

        return ['action' => 'success'];
    }

    /**
     * Favourite/Unfavorite
     *
     * @return collection
     */
    public function favourite(int $userId, int $sourceLinkId)
    {
        if ($this->newsRepository->favoriteExist($sourceLinkId, $userId)) {
            SourcesFavourites::where([
                'source_link_id' => $sourceLinkId,
                'user_id' => $userId
            ])->delete();
        } else {
            SourcesFavourites::create([
                'source_link_id' => $sourceLinkId,
                'user_id' => $userId
            ]);
        }
    }
}
