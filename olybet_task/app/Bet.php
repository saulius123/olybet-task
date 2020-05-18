<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    /**
     * @var string
     */
    protected $table = 'bet';

    /**
     * @var bool
     */
    public $timestamps = false;
}
