<?php

namespace App\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Jobs\Job;

use App\User, App\Notification;

use Log; 

class SaveNotification extends Job implements ShouldQueue
{    
    use InteractsWithQueue, SerializesModels;

    protected $data;

    /**
    * The number of times the job may attempted.
    *
    * @var int 
    */
    public $tries =2;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $data = $this->data;

            \Log::info("Notification Inside".print_r($data,true));

            User::where('is_verified', USER_EMAIL_VERIFIED)->chunk(100, function($users) use($data) {
                Log::info("Count of users ".count($users));
                
                foreach ($users as $key => $value) {
                    
                    $notification =  Notification::where('admin_video_id', $data['video_id'])
                        ->where('user_id', $value->id)
                        ->first();

                    $notification_details = $notification ? $notification : new Notification;

                    $notification_details->user_id = $value->id;

                    $notification_details->admin_video_id = $data['video_id'];

                    $notification_details->type = $data['type'];  // Future use

                    $notification_details->link_id = $data['video_id']; // Future use

                    $notification_details->status = 0;

                    $notification_details->save();

                }

            });

        } catch(Exception $e) {

            Log::info("Notification Save Job - ERROR".print_r($e->getMessage(), true));
        }
        
    }
}
