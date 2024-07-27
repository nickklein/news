<?php

namespace NickKlein\News\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsTracking extends Model
{
    use HasFactory;

    protected $table = 'news_tracking';
}
