<?php

namespace NickKlein\News\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use NickKlein\Tags\Models\Tags;

class NewsSummary extends Model
{
    use HasFactory;

    protected $table = 'news_summary';
    protected $primaryKey = 'summary_id';

    public function tags()
    {
        return $this->hasOne(Tags::class, 'tag_id', 'tag_id');
    }
}
