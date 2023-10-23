<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoanResource\Pages;
use App\Filament\Resources\LoanResource\RelationManagers;
use App\Filament\Resources\LoanResource\Widgets\LoanStats;
use App\Models\Loan;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // Temporary workaround to control viewing
    public static function canViewAny(): bool
    {
        return str_starts_with(auth()->user()->email, 'customer');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                ->schema([
                    Hidden::make('user_id'),
                    Hidden::make('status'),
                    TextInput::make('amount_applied')
                        ->label('Amount Required')
                        ->currencyMask(thousandSeparator: ',',decimalSeparator: '.',precision: 2)
                        ->numeric()
                        ->minValue(1)
                        ->suffix('MYR')
                        ->required()
                        ->live(),
                    TextInput::make('term')
                        ->label('Loan Term')
                        ->numeric()
                        ->mask('99')
                        ->minValue(1)
                        ->maxValue(60)
                        ->suffix('months')
                        ->required()
                        ->live(),
                    Placeholder::make('Estimated Repayment:')
                        ->content(function (Get $get): string {
                            $weeklyRepayment = !empty($get('amount_applied')) && !empty($get('term')) ? number_format((float) $get('amount_applied') / ((float) $get('term') * 4.33), 2) : '0.00';
                            return $weeklyRepayment .' MYR/week';
                        })
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),
                TextColumn::make('amount_applied')
                    ->label('Amount Applied')
                    ->money('MYR'),
                TextColumn::make('term')
                    ->label('Loan Term')
                    ->suffix(' months'),
                TextColumn::make('created_at')
                    ->label('Applied At')
                    ->dateTime(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending-approval' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => str_replace('-', ' ', ucwords($state))),              
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
    
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Additional Details')
                ->schema([
                    InfolistGrid::make(2)
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'draft' => 'gray',
                                'pending-approval' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                            })
                            ->formatStateUsing(fn (string $state): string => str_replace('-', ' ', ucwords($state))),
                        TextEntry::make('created_at')
                            ->label('Applied At')
                            ->date(),
                    ])
                ])
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UpcomingRepaymentsRelationManager::class,
            RelationManagers\RepaymentHistoryRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            LoanStats::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoans::route('/'),
            'create' => Pages\CreateLoan::route('/create'),
            'view' => Pages\ViewLoan::route('/{record}'),
            // 'edit' => Pages\EditLoan::route('/{record}/edit'),
        ];
    }   
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereBelongsTo(auth()->user());
    }
}
