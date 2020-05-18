<?php

namespace App\Validators;


use App\Services\BetAmountCalculator;
use Illuminate\Http\Request;

class BetValidator
{
    private const REQUIRED_GLOBAL_FIELDS = ['player_id', 'stake_amount', 'selections'];
    private const REQUIRED_SELECTION_FIELDS = ['id', 'odds'];
    private const MIN_STAKE_AMOUNT = 0.3;
    private const MAX_STAKE_AMOUNT = 10000;
    private const MIN_SELECTIONS = 1;
    private const MAX_SELECTIONS = 20;
    private const MIN_ODDS = 1;
    private const MAX_ODDS = 10000;
    private const MAX_WIN_AMOUNT = 20000;

    /** @var array */
    private $globalErrors = [];

    /** @var array */
    private $selectionErrors = [];

    /** @var BetAmountCalculator */
    private $betAmountCalculator;

    public function __construct(BetAmountCalculator $betAmountCalculator)
    {
        $this->betAmountCalculator = $betAmountCalculator;
    }

    public function validateRequest(Request $request): array
    {
        $data = $request->toArray();

        $valid = $this->validateStructure($data);

        if ($valid === false) {
            return $this->getErrors();
        }

        $this->validateGlobalFields($data);

        return $this->getErrors();
    }

    private function validateStructure(array $data): bool
    {
        $code = 1;
        $message = 'Betslip structure mismatch';
        foreach ($data as $key => $value) {
            if (!in_array($key, self::REQUIRED_GLOBAL_FIELDS)) {
                $this->addGlobalError($message, $code);
                return false;
            }
        }
        foreach ($data['selections'] as $selection) {
            foreach ($selection as $fieldKey => $fieldVal) {
                if (!in_array($fieldKey, self::REQUIRED_SELECTION_FIELDS)) {
                    $this->addGlobalError($message, $code);
                    return false;
                }
            }
        }

        return true;
    }

    public function getErrors(): array
    {
        if (count($this->globalErrors) === 0 && count($this->selectionErrors) === 0) {
            return [];
        }
        $errors['errors'] = $this->globalErrors;

        $errors['selections'] = [];

        foreach ($this->selectionErrors as $id => $selectionErrors) {
            $errors['selections'][] = [
                'id' => $id,
                'errors' => $selectionErrors
            ];
        }
        return $errors;
    }

    public function addGlobalError(string $message, int $code): void
    {
        $this->globalErrors[] = ['code' => $code, 'message' => $message];
    }

    private function addSelectionsError(string $message, int $code, int $id): void
    {
        $this->selectionErrors[$id][] = ['code' => $code, 'message' => $message];
    }

    private function validateGlobalFields(array $data): void
    {
        $this->validateStakeAmounts($data);
        $this->validateSelectionNumber($data);
        $this->validateSelections($data);
        $this->validateWinAmount($data);
    }

    private function validateStakeAmounts(array $data): void
    {
        if ($data['stake_amount'] < self::MIN_STAKE_AMOUNT) {
            $this->addGlobalError(sprintf('Minimum stake amount is %s', self::MIN_STAKE_AMOUNT), 2);
            return;
        }

        if ($data['stake_amount'] > self::MAX_STAKE_AMOUNT) {
            $this->addGlobalError(sprintf('Maximum stake amount is %s', self::MAX_STAKE_AMOUNT), 3);
        }
    }

    private function validateSelectionNumber(array $data): void
    {
        if (count($data['selections']) < self::MIN_SELECTIONS) {
            $this->addGlobalError(sprintf('Minimum number of selections is %s', self::MIN_SELECTIONS), 4);
            return;
        }
        if (count($data['selections']) > self::MAX_SELECTIONS) {
            $this->addGlobalError(sprintf('Maximum number of selections is %s', self::MAX_SELECTIONS), 5);
        }
    }

    private function validateSelections(array $data): void
    {
        foreach ($data['selections'] as $key => $selection) {
            if ($selection['odds'] < self::MIN_ODDS) {
                $this->addSelectionsError(sprintf('Minimum odds are %s', self::MIN_ODDS), 6, $selection['id']);
                return;
            }
            if ($selection['odds'] > self::MAX_ODDS) {
                $this->addSelectionsError(sprintf('Maximum odds are %s', self::MAX_ODDS), 7, $selection['id']);
            }

            $selectionToCompare = $data['selections'];
            unset($selectionToCompare[$key]);
            if ($this->selectionHasDuplicates($selection, $selectionToCompare)) {
                $this->addSelectionsError('Duplicate selection found', 8, $selection['id']);
            }
        }
    }

    private function selectionHasDuplicates($selection, $selectionsToCompare): bool
    {
        foreach ($selectionsToCompare as $selectionToCompare) {
            if ($selectionToCompare['id'] === $selection['id']) {
                return true;
            }
        }
        return false;
    }

    private function validateWinAmount(array $data): void
    {
        $amount = $this->betAmountCalculator->calculateBetAmount($data);
        if ($amount > self::MAX_WIN_AMOUNT) {
            $this->addGlobalError(sprintf('Maximum win amount is %s', self::MAX_WIN_AMOUNT), 9);
        }
    }
}
