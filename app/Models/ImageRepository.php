<?php

namespace App\Models;

use DB;
use Log;

class ImageRepository {

    public static function dropzonePreloadedImage($filename) {
        return [
            'name'      => 'image',
            'size'      => '100',
            'id'        => '1',
            'status'    => 'Dropzone.ADDED',
	        'path'      => asset('/img/user_avatars/' . $filename),
            'filename'  => $filename
        ];
    }


}