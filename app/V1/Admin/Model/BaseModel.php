<?php


namespace App\V1\Admin\Model;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();
class BaseModel
{
    protected static function removeEmpty(array $data = []){
        foreach ($data as $key => $item){
            if (is_null($item)){
                unset($data[$key]);
            }
        }

        return $data;
    }

    protected static function sortPageObject(Object $page){
        $result['data'] = $page->items();
        $result['per_page'] = $page->perPage();
        $result['last_page'] = $page->lastPage();
        $result['current_page'] = $page->currentPage();
        $result['count'] = $page->count();
        $result['total'] = $page->total();

        return $result;
    }

    public static function updateOrInsert(string $table,array $data,array $where = []){
        $sql = $where ?
            DB::table($table)->where($where)->update($data) :
            DB::table($table)->insertGetId($data);
        return ['code' => 0, 'id' => $sql, 'msg' => '数据库成功'];
    }

    public static function transactionOn(){
//        DB::beginTransaction();
    }

    public static function transactionSql($table, $data, $where = false)
    {// 启动事务
        try {
            //$sql_name = substr($sql_name, 0, 1) == '.' ? substr($sql_name, 1) : (config('database.prefix') . $sql_name);
            $sql = self::updateOrInsert($table,$data,$where);
            return $sql;
        } catch (\Exception $e) {
//            self::rollbackSql();
            return [
                'code' => 40000,
                'msg' => $e->getMessage(),
                //                'msg'=>'数据库错误',
            ];
        }
    }

    public static function commitSql()
    {
        //提交事务
        DB::commit();
        return true;
    }

    public static function rollbackSql()
    {
        //提交事务
        DB::rollback();
        return false;
    }
}
