<?php

namespace App\V1\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsersCollect extends Model
{
    protected $table = 'users_collects';

    public function article(){
        return $this->belongsTo('App\V1\App\Models\Articles');
    }

    //
    use SoftDeletes;
}