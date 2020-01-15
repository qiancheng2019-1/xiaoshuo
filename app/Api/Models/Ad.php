<?php


namespace App\Api\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Ad extends IndexModel
{
    protected $table = 'ad';
    protected $fillable = ['views_num','click_num'];

    public static function getList(string $keyword,string $ip)
    {
        $cache_key = md5('ad.'.$keyword);
        $data = Cache::get($cache_key);
        if (!$data){
            //cache
            $list = self::query()->where('key','like',$keyword.'_%')->get();
            foreach ($list as &$item) {
                $data[$item->key] = ['key' => $item->key, 'name' => $item->name, 'code' => $item->value];
            }
            Cache::forever($cache_key,$data);
        }

        //ad_log
        $date = date('Y-m-d H:i:s');
        DB::table('ad_log')->insert(['key'=>$keyword ?: 'all','ip'=>$ip,'method'=>'GET','created_at'=>$date,'updated_at'=>$date]);
        return $data;
    }

    public static function addLog(string $key,string $ip){
        $cache_key = md5('ad.'.explode('_',$key)[0]);
        if (Cache::get($cache_key))
            return $date = date('Y-m-d H:i:s') and DB::table('ad_log')->insert(['key'=>$key ?: 'all','ip'=>$ip,'method'=>'PUT','created_at'=>$date,'updated_at'=>$date]);
        return false;
    }
}
