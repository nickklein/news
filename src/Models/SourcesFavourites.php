<?php

namespace NickKlein\News\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SourcesFavourites extends Model
{
    use HasFactory;

    protected $table = 'sources_favorites';
    protected $primaryKey = 'sources_favorites_id';
    public $timestamps = false;

    public $fillable = [
        'source_link_id',
        'user_id',
    ];
}
