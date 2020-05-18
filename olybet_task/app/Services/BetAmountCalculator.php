<?php

namespace App\Services;

class BetAmountCalculator
{
    public function calculateBetAmount($data): float
    {
        $globalOdds = 1;

        foreach ($data['selections'] as $selection) {
            $globalOdds = $globalOdds * $selection['odds'] ;
        }

        return $globalOdds * $data['stake_amount'];
    }
}
