<?php

namespace NickKlein\News\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsSummary extends Model
{
    use HasFactory;

    protected $table = 'news_summary';
    protected $primaryKey = 'summary_id';

    public function tags()
    {
        return $this->hasOne('\App\Models\Tags', 'tag_id', 'tag_id');
    }
}
