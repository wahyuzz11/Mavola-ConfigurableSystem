<?php
namespace App\Services;


use App\Models\Account;


class AccountingService{

    public function assetAccountsAreEmpty(array $codes = ['1101', '1102', '1103']): bool
    {
        return ! Account::whereIn('code', $codes)
            ->whereHas('journalEntriesDetails', fn ($q) => $q->where(
                fn ($q) => $q->where('debit', '>', 0)->orWhere('credit', '>', 0)
            ))
            ->exists();
    }


}