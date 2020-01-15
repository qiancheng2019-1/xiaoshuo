<?php

namespace App\Api\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsersCollect extends Model
{
    protected $table = 'users_collects';

    public function article(){
        return $this->belongsTo('App\Api\Models\Articles');
    }

    //
    use SoftDeletes;
}
