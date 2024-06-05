<?php

namespace App\Repositories\Transaction;

use App\Events\SendNotification;
use App\Exceptions\IdleServiceException;
use App\Exceptions\InvalidDataProviderException;
use App\Exceptions\NoMoneyAtTheMomentException;
use App\Exceptions\TransactionDeniedException;
use App\Models\Merchant;
use App\Models\Transactions\Transaction;
use App\Models\Transactions\Wallet;
use App\Models\User;
use App\Services\MockyService;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionRepository
{
    public function handle(array $data): Transaction
    {
        $this->authorizeTransaction();

        $payee = $this->retrievePayee($data);
        $this->ensurePayeeExists($payee);

        $payerWallet = $this->getPayerWallet($data['provider']);
        $this->ensureSufficientBalance($payerWallet, $data['amount']);
        $this->ensureServiceIsAvailable();

        return $this->processTransaction($payee, $data);
    }

    private function authorizeTransaction(): void
    {
        if (!$this->canUserTransfer()) {
            throw new TransactionDeniedException('Merchant is not authorized to make transactions', 404);
        }
    }

    private function canUserTransfer(): bool
    {
        if (Auth::guard('users')->check()) {
            return true;
        } elseif (Auth::guard('merchants')->check()) {
            return false;
        } else {
            throw new InvalidDataProviderException('Invalid provider', 422);
        }
    }

    private function getPayerWallet(string $provider): Wallet
    {
        return Auth::guard($provider)->user()->wallet;
    }

    private function ensureSufficientBalance(Wallet $wallet, float $amount): void
    {
        if (!$this->hasSufficientBalance($wallet, $amount)) {
            throw new NoMoneyAtTheMomentException('You do not have enough funds to transfer', 422);
        }
    }

    private function hasSufficientBalance(Wallet $wallet, float $amount): bool
    {
        return $wallet->balance >= $amount;
    }

    private function ensureServiceIsAvailable(): void
    {
        if (!$this->isServiceAvailable()) {
            throw new IdleServiceException('Service is not responding. Try again later');
        }
    }

    private function isServiceAvailable(): bool
    {
        $service = app(MockyService::class)->authorizetTransaction();
        return $service['message'] == 'Autorizado';
    }

    private function ensurePayeeExists($payee): void
    {
        if (!$payee) {
            throw new InvalidDataProviderException('User Not Found', 422);
        }
    }

    private function retrievePayee(array $data)
    {
        try {
            $model = $this->getProviderModel($data['provider']);
            return $model->find($data['payee_id']);
        } catch (Exception $e) {
            return null;
        }
    }

    private function getProviderModel(string $provider): Authenticatable
    {
        return match ($provider) {
            'users' => new User(),
            'merchants' => new Merchant(),
            default => throw new InvalidDataProviderException('Invalid provider', 422),
        };
    }

    private function processTransaction($payee, array $data): Transaction
    {
        $transactionData = $this->prepareTransactionData($payee, $data);

        return DB::transaction(function () use ($transactionData) {
            $transaction = Transaction::create($transactionData);

            $transaction->walletPayer->withdraw($transactionData['amount']);
            $transaction->walletPayee->deposit($transactionData['amount']);

            event(new SendNotification($transaction));

            return $transaction;
        });
    }

    private function prepareTransactionData($payee, array $data): array
    {
        $payerWallet = Auth::guard($data['provider'])->user()->wallet;

        return [
            'payer_wallet_id' => $payerWallet->id,
            'payee_wallet_id' => $payee->wallet->id,
            'amount' => $data['amount'],
        ];
    }
}
