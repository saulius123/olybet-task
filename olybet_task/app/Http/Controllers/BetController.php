<?php

namespace App\Http\Controllers;

use App\Services\BetAmountCalculator;
use App\Validators\BetValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BetController extends Controller
{
    /** @var BetValidator */
    private $betValidator;

    public function __construct(BetValidator $betValidator)
    {
        $this->betValidator = $betValidator;
    }

    public function store(Request $request): ?JsonResponse
    {

        $errors = $this->betValidator->validateRequest($request);

        if (count($errors) > 0) {
            return response()->json([$errors], 400);
        }



        return null;
    }
}
