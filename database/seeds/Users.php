<?php

use Illuminate\Database\Seeder;

class Users extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        DB::table('users')->insert([
            'nickname' => 'Crazypeak',
            'username' => 'Crazypeak',
            'password' => bcrypt('qweqwe'),
            'api_token' => Hash::make(Str::random(60)),
            'group_id' => 1,
        ]);

        DB::table('users')->insert([
            'nickname' => 'tang',
            'username' => 'tang',
            'password' => bcrypt('qweqwe'),
            'api_token' => Hash::make(Str::random(60)),
            'group_id' => 1,
        ]);
    }
}
