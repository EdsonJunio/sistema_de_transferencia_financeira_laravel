<?php

namespace App\Observers;

use App\Models\Merchant;

class MerchantObserver
{
    public function created(Merchant $merchant)
    {
        $merchant->wallet()->create([
            'balance' => 0
        ]);
    }
}
