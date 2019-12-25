<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Preference extends Model
{
	protected $table = 'preferences';
    protected $fillable = ['category', 'field', 'value'];
    public $timestamps = false;

    /*FOR CACHE - BELOW*/
    // public static function getAll()
    // {
    //     $data = Cache::get('preferences');
    //     if (empty($data)) {
    //         $data = parent::all();
    //         Cache::put('preferences', $data, 1440);
    //     }
    //     return $data;
    // }
}
