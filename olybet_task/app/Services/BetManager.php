<?php

namespace App\Services;

use App\BalanceTransaction;
use App\Bet;
use App\BetSelection;
use App\Player;
use Illuminate\Support\Facades\DB;

class BetManager
{
    /** @var BetAmountCalculator */
    private $betAmountCalculator;

    public function __construct(BetAmountCalculator $betAmountCalculator)
    {
        $this->betAmountCalculator = $betAmountCalculator;
    }

    public function addBet(array $data): bool
    {
        try {
            DB::beginTransaction();
            $this->createBetModels($data);
            DB::commit();
            return true;
        } catch (\PDOException $e) {
            DB::rollBack();
            return false;
        }
    }

    public function createBetModels(array $data): void
    {
        $betAmount = $this->betAmountCalculator->calculateBetAmount($data);

        $player = Player::find($data['player_id']);
        if ($player === null) {
            $player = new Player();
            $player->id = $data['player_id'];

        }
        $amountBefore = $player->balance;
        $player->balance = $player->balance - $betAmount;
        $player->save();

        $this->createBalanceTransaction($data, $player, $amountBefore);
        $bet = $this->createBet($data);
        $this->createBetSelections($data, $bet);
    }

    private function createBalanceTransaction(array $data, Player $player, float $amountBefore): void
    {
        $balanceTransaction = new BalanceTransaction();
        $balanceTransaction->player_id = $player->id;
        $balanceTransaction->amount = $player->balance;
        $balanceTransaction->amount_before = $amountBefore;
        $balanceTransaction->save();

    }

    private function createBet(array $data): BET
    {
        $bet = new Bet();
        $bet->stake_amount = $data['stake_amount'];
        $bet->created_at = date("Y-m-d H:i:s");
        $bet->save();

        return $bet;
    }

    private function createBetSelections(array $data, Bet $bet): void
    {
        foreach ($data['selections'] as $selectionDatum) {
            $selection = new BetSelection();
            $selection->bet_id = $bet->id;
            $selection->selection_id = $selectionDatum['id'];
            $selection->odds = $selectionDatum['odds'];
            $selection->save();
        }
    }
}
