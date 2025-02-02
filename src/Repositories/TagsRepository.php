<?php

namespace NickKlein\News\Repositories;

use App\Models\HabitTime;
use App\Models\HabitTimesTag;
use App\Models\Tags;
use App\Models\UserTags;
use Illuminate\Database\Eloquent\Collection;

class TagsRepository
{
    /**
     * Find Tag
     *
     * @return collection
     */
    public function findTag(string $tag)
    {
        return Tags::where('tag_name', $tag)->first();
    }

    /**
     * Find Tag
     *
     * @return collection
     */
    public function findUserTag(int $userId, int $tagId)
    {
        return UserTags::where('tag_id', $tagId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * create tags for user
     *
     * @return collection
     */
    public function createUserTag(int $userId, string $tagName)
    {
        // Find Tag through repo
        $tags = Tags::firstOrCreate([
            'tag_name' => $tagName
        ]);

        return UserTags::create([
            'tag_id' => $tags->tag_id,
            'user_id' => $userId,
        ]);
    }

    /**
     * Get list of personalized tags
     *
     * @return collection
     */
    public function listTags(int $userId)
    {
        return UserTags::get()
            ->load('tag')
            ->where('user_id', $userId);
    }

    /**
     * create tags for habits
     *
     * @return collection
     */
    public function createHabitTimeTag(int $userId, int $habitTimeId, string $tagName)
    {
        // Find Tag through repo
        $tags = Tags::firstOrCreate([
            'tag_name' => $tagName
        ]);

        // Bail if habit time doesn't belon to user
        if (!$this->isOwnedByUser($habitTimeId, $userId)) {
            return new Collection([]);
        }

        return HabitTimesTag::create([
            'habit_time_id' => $habitTimeId,
            'tag_id' => $tags->tag_id,
        ]);
    }

    /**
     * Check if habit times is owned by an user
     * @todo middleware?policy?
     *
     * @param integer $habitTimeId
     * @param integer $userId
     * @return boolean
     */
    public function isOwnedByUser(int $habitTimeId, int $userId): bool
    {
        return HabitTime::where('id', $habitTimeId)->where('user_id', $userId)->exists();
    }

    /**
     * Find Habit Times Tag
     *
     * @param integer $habitTimeId
     * @param integer $tagId
     * @return HabitTimesTag
     */
    public function findHabitTimesTag(int $habitTimeId, int $tagId, int $userId): HabitTimesTag
    {
        return HabitTimesTag::where('habit_time_id', $habitTimeId)
            ->join('habit_times', 'habit_times.id', '=', 'habit_times_tags.id')
            ->where('habit_times.user_id', $userId)
            ->where('tag_id', $tagId)
            ->first();
    }

    /**
     * Get list of personalized tags
     *
     * @return collection
     */
    public function listHabitTimesTags(int $habitTimeId, int $userId)
    {
        return Tags::whereHas('habitTimes', function ($query) use ($habitTimeId, $userId) {
            $query->where('habit_times.id', $habitTimeId)
                ->where('habit_times.user_id', $userId);
        })->pluck('tag_name');
    }
}
