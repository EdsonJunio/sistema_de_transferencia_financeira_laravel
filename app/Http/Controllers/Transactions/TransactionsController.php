<?php

namespace App\Http\Controllers\Transactions;

use App\Exceptions\IdleServiceException;
use App\Exceptions\InvalidDataProviderException;
use App\Exceptions\NoMoneyAtTheMomentException;
use App\Exceptions\TransactionDeniedException;
use App\Http\Controllers\Controller;
use App\Repositories\Transaction\TransactionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionsController extends Controller
{
    private TransactionRepository $repository;

    public function __construct(TransactionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function postTransaction(Request $request)
    {
        $this->validateRequest($request);

        $fields = $request->only('provider', 'payee_id', 'amount');

        try {
            $result = $this->repository->handle($fields);
            return response()->json($result);
        } catch (InvalidDataProviderException|NoMoneyAtTheMomentException $exception) {
            return $this->handleClientException($exception);
        } catch (TransactionDeniedException|IdleServiceException $exception) {
            return $this->handleUnauthorizedException($exception);
        } catch (\Exception $exception) {
            return $this->handleServerException($exception);
        }
    }

    private function validateRequest(Request $request): void
    {
        $this->validate($request, [
            'provider' => 'required|in:users,merchants',
            'payee_id' => 'required',
            'amount' => 'required|numeric',
        ]);
    }

    private function handleClientException(\Exception $exception)
    {
        return response()->json(['errors' => ['main' => $exception->getMessage()]], $exception->getCode());
    }

    private function handleUnauthorizedException(\Exception $exception)
    {
        return response()->json(['errors' => ['main' => $exception->getMessage()]], 401);
    }

    private function handleServerException(\Exception $exception)
    {
        Log::critical('Transaction failed: ', ['message' => $exception->getMessage()]);
        return response()->json(['errors' => ['main' => 'An unexpected error occurred. Please try again later.']], 500);
    }
}
