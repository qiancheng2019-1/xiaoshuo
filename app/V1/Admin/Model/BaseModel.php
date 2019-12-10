<?php


namespace App\V1\Admin\Model;

use Illuminate\Support\Facades\DB;

DB::beginTransaction();

class BaseModel
{
    public static function transactionSql($sql_name, $data, $where = false)
    {// 启动事务
        try {
            //$sql_name = substr($sql_name, 0, 1) == '.' ? substr($sql_name, 1) : (config('database.prefix') . $sql_name);
            $sql = $where ?
                DB::table($sql_name)->where($where)->update($data) :
                DB::table($sql_name)->insertGetId($data);
            return ['code' => 1, 'id' => $sql, 'msg' => '数据库成功'];
        } catch (\Exception $e) {
            return [
                'code' => 0,
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
