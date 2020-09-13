<?php

use Illuminate\Database\Seeder;

class V6Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(Schema::hasTable('settings')) {

         	DB::table('settings')->insert([
	    		[
                    'key' => 'referral_earnings',
                    'value' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                [
                    'key' => 'referrer_earnings',
                    'value' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
			]);
    	}
    }
}
