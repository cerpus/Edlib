<?php

namespace Tests\Seeds;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestProgressSeeder extends Seeder
{
    public function run()
    {
        $this->h5pContentUserData();
    }

    public function h5pContentUserData()
    {
        DB::table('h5p_contents_user_data')->insert([
            [
                'content_id' => 1,
                'user_id' => 1,
                'sub_content_id' => 0,
                'data' => '{\"progress\":15.641,\"answers\":[{\"answers\":[2,0]}]}',
                'data_id' => 'state',
                'preload' => 1,
                'invalidate' => 1,
                'updated_at' => Carbon::now(),
                'context' => null,
            ],
            [
                'content_id' => 1,
                'user_id' => 2,
                'sub_content_id' => 0,
                'data' => '{\"progress\":3,\"answers\":[null,[null],null,[{\"answers\":[[{\"x\":1.8610421836228288,\"y\":10.66997518610422,\"dz\":0}]]}]],\"answered\":[false,false,false,true]}',
                'data_id' => 'state',
                'preload' => 1,
                'invalidate' => 1,
                'updated_at' => Carbon::now(),
                'context' => null,
            ],
            [
                'content_id' => 2,
                'user_id' => 1,
                'sub_content_id' => 0,
                'data' => '[\'hello\', \'everyone\']',
                'data_id' => 'state',
                'preload' => 1,
                'invalidate' => 1,
                'updated_at' => Carbon::now(),
                'context' => null,
            ],
            [
                'content_id' => 2,
                'user_id' => 1,
                'sub_content_id' => 0,
                'data' => '[\'hello\', \'there\']',
                'data_id' => 'state',
                'preload' => 1,
                'invalidate' => 1,
                'updated_at' => Carbon::now(),
                'context' => 'context_1',
            ],
            [
                'content_id' => 2,
                'user_id' => 2,
                'sub_content_id' => 0,
                'data' => '[\'hola\', \'amigo\']',
                'data_id' => 'state',
                'preload' => 1,
                'invalidate' => 1,
                'updated_at' => Carbon::now(),
                'context' => 'context_2',
            ],
        ]);
    }
}
