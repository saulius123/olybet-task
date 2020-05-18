<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BalanceTransaction extends Model
{
    /**
     * @var string
     */
    protected $table = 'balance_transaction';

    /**
     * @var bool
     */
    public $timestamps = false;

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
