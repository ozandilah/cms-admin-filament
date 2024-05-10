<?php

namespace App\Filament\Widgets;

use App\Models\Listing;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{

    // Buat fungsi karena akan digunakan beberapa kali
    // ini adalah helper
    private function getPercentage(int $from, int $to){
        return $to - $from / ($to + $from / 2) * 100;
    }


    protected function getStats(): array
    {
        // menghitung listing dalam satu bulan ini
        $newListing = Listing::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at',Carbon::now()->year)->count();

        // menghitung jumlah transaction dalam satu bulan in
        $transactions = Transaction::whereStatus('approved')->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);

        // menambahkan statistik lebih detail pada transaction untuk melihat kenaikan dan penurunan bulan ini dibandingkan bulan lalu

        // variable untuk perbandingan
        // subMonth(untuk pengurangan 1 bulan, jika  pengen ditambah tinggal di masukkan angkanya saja)
        $prevTransactions = Transaction::whereStatus('approved')->whereMonth('created_at', Carbon::now()->subMonth()->month)->whereYear('created_at', Carbon::now()->subMonth()->year);

        $transactionPercentage = $this->getPercentage($prevTransactions->count(), $transactions->count());

        $revenuePercentage = $this->getPercentage($prevTransactions->sum('total_price'), $transactions->sum('total_price'));


        return [
            Stat::make('New Listing of the month', $newListing),
            Stat::make('Transaction of the month', $transactions->count())
            ->description($transactionPercentage > 0 ? "{$transactionPercentage}% increased" : "{$transactionPercentage}% decreased")
            ->descriptionIcon($transactionPercentage > 0 ? "heroicon-m-arrow-trending-up" : "heroicon-m-arrow-trending-down")
            ->color($transactionPercentage > 0 ? "success" : "danger"),
            Stat::make('Revenue of the month', \Number::currency($transactions->sum('total_price'),'USD'))
            ->description($revenuePercentage > 0 ? "{$revenuePercentage}% increased" : "{$revenuePercentage}% decreased")
            ->descriptionIcon($revenuePercentage > 0 ? "heroicon-m-arrow-trending-up" : "heroicon-m-arrow-trending-down")
            ->color($revenuePercentage > 0 ? "success" : "danger")
        ];
    }
}
