<?php

namespace App\Models;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Grimzy\LaravelMysqlSpatial\Types\Point;

class AgentArea extends Model
{
    use SoftDeletes,SpatialTrait;

    protected $guarded=[];
    protected $spatialFields = [
        'area'
    ];
    protected $fillable = [
        'area','agent_id','minimum_cartons'
    ];
    public function agent(){
        return $this->belongsTo(Agent::class,'agent_id');
    }

    public function search($lat, $lng)
    {
        $point =  new Point($lat, $lng);
        $agent_area = AgentArea::contains('area',$point)->orderBy('id','desc')->first();
        if($agent_area == null)
            return null;
        return $agent_area;
    }

}
