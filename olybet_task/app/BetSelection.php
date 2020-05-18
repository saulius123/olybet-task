<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BetSelection extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    public function bet()
    {
        return $this->belongsTo(Bet::class);
    }
}
