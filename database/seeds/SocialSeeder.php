<?php

use Illuminate\Database\Seeder;

class SocialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         DB::table('settings')->insert([
        	[
		    	'key' => 'FB_CLIENT_ID' ,
		    	'value' => '',
		    	'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s')
		    ],
            [
                'key' => 'FB_CLIENT_SECRET' ,
                'value' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'key' => 'FB_CALL_BACK' ,
                'value' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'key' => 'TWITTER_CLIENT_ID' ,
                'value' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
		    	'key' => 'TWITTER_CLIENT_SECRET' ,
		    	'value' => '',
		    	'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s')
		    ],
            [
                'key' => 'TWITTER_CALL_BACK' ,
                'value' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'key' => 'GOOGLE_CLIENT_ID' ,
                'value' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'key' => 'GOOGLE_CLIENT_SECRET' ,
                'value' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'key' => 'GOOGLE_CALL_BACK' ,
                'value' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
		]);
    }
}
