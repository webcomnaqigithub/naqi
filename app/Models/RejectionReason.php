<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RejectionReason extends Model
{
    //
    protected $table = 'rejectionReasons';
    protected $fillable = ['id','arabicReason','englishReason','status','created_at','updated_at'];
    protected $appends=['reason'];

    public function getReasonAttribute(){
        if(app()->getLocale()=='ar'){
            return $this->arabicReason;

        }
        return $this->englishReason;
    }
}
