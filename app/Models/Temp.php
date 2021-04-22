<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Temp extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'table',
        'query',
        'bindings',
        'output'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];
}
