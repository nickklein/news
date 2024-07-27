<?php

namespace NickKlein\News\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sources extends Model
{
    use HasFactory;

    protected $table = 'sources';
    protected $primaryKey = 'source_id';
}
