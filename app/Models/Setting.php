<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;


class Setting extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at',
   ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Add a settings value
     *
     * @param $key
     * @param $val
     * @param string $type
     * @return bool
     */
    public static function add($key, $val)
    {
        if ( self::has($key) ) {
            return self::set($key, $val);
        }

        return self::create(['name' => $key, 'val' => $val]) ? $val : false;
    }

    /**
     * Set a value for setting
     *
     * @param $key
     * @param $val
     * @return bool
     */
    public static function set($key, $val)
    {
        if ( $setting = self::getAllSettings()->where('name', $key)->first() ) {
            return $setting->update([
                'name' => $key,
                'val' => $val]) ? $val : false;
        }

        return self::add($key, $val);
    }

    /**
     * Get a settings value
     *
     * @param $key
     * @param null $default
     * @return bool|int|mixed
     */
    public static function get($name)
    {
        $record = self::where('name',$name)->first();
        return $record;
    }
    /**
     * Get all the settings
     *
     * @return mixed
     */
    public static function getAllSettings()
    {
        return self::all();
    }

    /**
     * Remove a setting
     *
     * @param $key
     * @return bool
     */
    public static function remove($key)
    {
        if( self::has($key) ) {
            return self::whereName($key)->delete();
        }

        return false;
    }

     /**
     * Check if setting exists
     *
     * @param $key
     * @return bool
     */
    public static function has($key)
    {
        return (boolean) self::where('name', $key)->count();
    }

    public static function key($type)
    {
        return self::where('name', $type)->first();
    }

    public static function valueOf($type , $default = null)
    {
        return (isset(self::key($type)->value)) ? self::key($type)->value : $default;
    }

}
