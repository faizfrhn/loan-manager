<?php

namespace App\Filament\Resources\LoanResource\Widgets;

use App\Filament\Resources\LoanResource\Pages\ViewLoan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;


class LoanStats extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        $paidAmount = round(floatval($this->record->repaymentHistory()->sum('amount')) / 100, precision: 2);

        return [
            Stat::make('Balance', \Filament\Support\format_money($this->record->amount_applied - $paidAmount, 'MYR')),
            Stat::make('Amount Applied', \Filament\Support\format_money($this->record->amount_applied, 'MYR')),
            Stat::make('Loan Term', $this->record->term . ' month(s)'),
        ];
    }
}
