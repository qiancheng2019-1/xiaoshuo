<?php


namespace App\V1\Admin\Model;


class UserModel extends BaseModel
{
    public function postUser(){

    }

    public static function getUserOne($where = []){
        $result = DB::table('user')
            ->where($where)
            ->first();
        return $result;
    }
}
