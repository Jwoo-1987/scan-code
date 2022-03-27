<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'creat_time', 'update_time', 'code',
    ];

    protected $table = 'code';
    public $timestamps = false;
    protected $primaryKey = 'id';
}