<?php


namespace App\V1\App\Models;


use Illuminate\Support\Facades\DB;

class Ad extends IndexModel {
    public static function getList()
    {
        $data = [];
        $list = DB::table('ad')->select([
            'key',
            'name',
            'value'])->get();
        foreach ($list as $item) {
            $data[$item->key] = [
                'key'     => $item->key,
                'name'    => $item->name,
                'js_code' => $item->value];
        }
        return $data;
    }
}
