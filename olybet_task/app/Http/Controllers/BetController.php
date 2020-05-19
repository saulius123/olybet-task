<?php

namespace App\Http\Controllers;

use App\Services\BetManager;
use App\Validators\BetValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BetController extends Controller
{
    /** @var BetManager  */
    private $betManager;

    /** @var BetValidator */
    private $betValidator;

    public function __construct(BetValidator $betValidator, BetManager $betManager)
    {
        $this->betManager = $betManager;
        $this->betValidator = $betValidator;
    }

    public function store(Request $request): ?JsonResponse
    {

        $errors = $this->betValidator->validateRequest($request);

        if (count($errors) > 0) {
            return response()->json($errors, 400);
        }

        $success = $this->betManager->addBet($request->toArray());

        if ($success === false) {
            $this->betValidator->addGlobalError('Unknown error', 0);
            return response()->json($this->betValidator->getErrors(), 400);
        }
    }
}
