<?php

namespace App\Filament\Resources\LoanResource\RelationManagers;

use App\Filament\Resources\LoanResource\Widgets\LoanStats;
use App\Models\WeeklyRepayment;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UpcomingRepaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'upcomingRepayments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                Tables\Columns\TextColumn::make('#')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('MYR'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'warning',
                        'paid' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                Tables\Columns\TextColumn::make('due_date')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('pay')
                    ->action(fn (WeeklyRepayment $record) => $this->processRepayment($record))
                    ->button()
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->requiresConfirmation()
                    ->hidden(fn (WeeklyRepayment $record) => $record->status === 'paid'),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->emptyStateHeading('No upcoming repayments')
            ->defaultSort('due_date', 'asc');
    }

    public static function processRepayment($record)
    {
        $record->update([
            'status' => 'paid',
            'paid_at' => Carbon::now()
        ]);

        Notification::make() 
            ->title('Repayment processed successfully.')
            ->success()
            ->send();
    }
}
