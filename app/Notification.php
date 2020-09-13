<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Jobs\SaveNotification;

class Notification extends Model
{
    //

    public static function save_notification($video_id, $type = "") {

        $data['video_id'] = $video_id;

        $data['type'] = $type;

        dispatch(new SaveNotification($data));

    }


    public function adminVideo() {
        return $this->hasOne('App\AdminVideo', 'id', 'admin_video_id');
    }
}
