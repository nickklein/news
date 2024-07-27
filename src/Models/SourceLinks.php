<?php

namespace NickKlein\News\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SourceLinks extends Model
{
    use HasFactory;

    protected $table = 'source_links';
    protected $primaryKey = 'source_link_id';
}
