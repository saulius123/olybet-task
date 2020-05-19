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

    public function addBet(array $data): ?array
    {
        try {
            DB::beginTransaction();
            $createdModels = $this->createBetModels($data);
            DB::commit();
            return $createdModels;
        } catch (\PDOException $e) {
            DB::rollBack();
            return null;
        }
    }

    public function createBetModels(array $data): array
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

        $balanceTransaction = $this->createBalanceTransaction($data, $player, $amountBefore);
        $bet = $this->createBet($data);
        $betSelections = $this->createBetSelections($data, $bet);

        return [
            'player' => $player,
            'balanceTransaction' => $balanceTransaction,
            'bet' => $bet,
            'betSelections' => $betSelections
        ];
    }

    private function createBalanceTransaction(array $data, Player $player, float $amountBefore): BalanceTransaction
    {
        $balanceTransaction = new BalanceTransaction();
        $balanceTransaction->player_id = $player->id;
        $balanceTransaction->amount = $player->balance;
        $balanceTransaction->amount_before = $amountBefore;
        $balanceTransaction->save();

        return $balanceTransaction;
    }

    private function createBet(array $data): Bet
    {
        $bet = new Bet();
        $bet->stake_amount = $data['stake_amount'];
        $bet->created_at = date("Y-m-d H:i:s");
        $bet->save();

        return $bet;
    }

    private function createBetSelections(array $data, Bet $bet): array
    {
        $selections = [];
        foreach ($data['selections'] as $selectionDatum) {
            $selection = new BetSelection();
            $selection->bet_id = $bet->id;
            $selection->selection_id = $selectionDatum['id'];
            $selection->odds = $selectionDatum['odds'];
            $selection->save();

            $selections[] = $selection;
        }

        return $selections;
    }
}
