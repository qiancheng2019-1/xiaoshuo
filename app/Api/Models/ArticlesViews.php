<?php

namespace App\Api\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArticlesViews extends Model
{
    protected $table = 'articles_views';
    protected $primaryKey = 'article_id';
    public $timestamps = false;
}
