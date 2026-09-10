<?php

namespace App\Restaurant;

use App\BusinessLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResFloor extends Model
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    public function location()
    {
        return $this->belongsTo(BusinessLocation::class, 'location_id');
    }

    public function tables()
    {
        return $this->hasMany(ResTable::class, 'floor_id');
    }
}
