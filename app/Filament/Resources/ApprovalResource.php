<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApprovalResource\Pages;
use App\Filament\Resources\ApprovalResource\RelationManagers;
use App\Models\Loan;
use App\Models\WeeklyRepayment;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApprovalResource extends Resource
{
    protected static ?string $model = Loan::class;

    protected static ?string $modelLabel = 'Approvals';

    protected static ?string $navigationLabel = 'Approvals';

    protected static ?string $slug = 'approvals';

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    // Temporary workaround to control viewing
    public static function canViewAny(): bool
    {
        return str_starts_with(auth()->user()->email, 'admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                ->schema([
                    Forms\Components\TextInput::make('user.name')->disabled(),
                    Forms\Components\TextInput::make('user.email')->disabled(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('#')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('user.name'),
                Tables\Columns\TextColumn::make('user.email'),
                Tables\Columns\TextColumn::make('amount_applied')
                    ->label('Amount Applied')
                    ->money('MYR'),
                Tables\Columns\TextColumn::make('term')
                    ->label('Loan Term')
                    ->suffix(' months'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Applied At')
                    ->dateTime(), 
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->action(fn (Loan $record) => ApprovalResource::approveLoan($record))
                    ->button()
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('reject')
                    ->action(fn (Loan $record) => $record->update(['status' => 'rejected']))
                    ->button()
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function approveLoan($record)
    {
        $record->update(['status' => 'approved']);

        $weeks = ceil((float) $record->term * 4.33);
        $weeklyAmount = round((float) $record->amount_applied / ((float) $record->term * 4.33), 2);

        $amountApplied = $record->amount_applied;

        $dueDate = Carbon::now();

        while($weeks > 0) {
            if($amountApplied > $weeklyAmount) {
                $amountApplied -= $weeklyAmount;
            } else {
                $weeklyAmount = $amountApplied;
            }

            WeeklyRepayment::create([
                'loan_id' => $record->id,
                'amount' => $weeklyAmount,
                'status' => 'unpaid',
                'due_date' => $dueDate,
            ]);

            $dueDate = $dueDate->addDays(7);
            $weeks--;
        }

        Notification::make() 
            ->title('Loan Successfully Approved')
            ->success()
            ->send();

    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApprovals::route('/'),
            // 'create' => Pages\CreateApproval::route('/create'),
            // 'edit' => Pages\EditApproval::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'pending-approval');
    }
}
