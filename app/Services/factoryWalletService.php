<?php

namespace App\Services;

class factoryWalletService
{
    /**
     * @param int|string $totalEarning
     * @param int|string $pendingWithdraw
     * @return int[]|string[]
     */
    public function getfactoryWalletData(int|string $totalEarning, int|string $pendingWithdraw):array
    {
        return [
            'total_earning' => $totalEarning,
            'pending_withdraw' =>$pendingWithdraw,
        ];
    }
}
