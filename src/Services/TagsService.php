<?php

namespace NickKlein\News\Services;

use NickKlein\News\Repositories\TagsRepository;
use Exception;

class TagsService
{
    private $repository;

    public function __construct(TagsRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * destroy tags for user
     *
     * @return collection
     */
    public function destroyUserTag(int $userId, string $tagName): array
    {
        $tag = $this->repository->findTag($tagName);
        if (empty($tag)) {
            return [];
        }

        $userTag = $this->repository->findUserTag($userId, $tag->tag_id);
        if (!$userTag->delete()) {
            return [];
        }

        return ['action' => 'success'];
    }

    // destroy tag for habit times
    public function destroyHabitTimesTag(int $userId, int $habitTimeId, string $tagName, LogsService $log): array
    {
        try {
            $tag = $this->repository->findTag($tagName);
            if (empty($tag)) {
                return [];
            }

            $habitTimesTag = $this->repository->findHabitTimesTag($habitTimeId, $tag->tag_id, $userId);
            if (!$habitTimesTag->delete()) {
                return [];
            }

            return ['action' => 'success'];
        } catch (Exception $e) {
            // Log the exception using the LogsService
            $log->handle("Error", "Exception occurred in destroyHabitTimesTag: " . $e->getMessage());
            return ['action' => 'error', 'message' => 'An error occurred while processing the request.'];
        }
    }
}
