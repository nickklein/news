<?php

namespace NickKlein\News\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSources extends Model
{
    use HasFactory;

    protected $table = 'user_sources';
    protected $primaryKey = 'user_sources_id';
    public $timestamps = false;
}
