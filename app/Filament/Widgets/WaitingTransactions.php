<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class WaitingTransactions extends BaseWidget
{
    protected static ?int $sort=3;
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()->whereStatus('waiting')
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable()->weight(FontWeight::Bold),
                Tables\Columns\TextColumn::make('listing.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_days')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('USD')
                    ->sortable()
                    ->weight(FontWeight::Bold),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn(string $state): string => match($state) {
                    'waiting'=>'gray',
                    'approved'=>'info',
                    'canceled'=>'danger',
                }),
            ]) ->actions([
                Action::make('approved')
                        ->button()
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function(Transaction $transaction){
                            Transaction::find($transaction->id)->update(
                                [
                                    'status'=>'approved'
                                ]
                                );
                                Notification::make()
                                    ->title('Transaction Approved!')
                                    ->body('Transaction has been approved successfully')
                                    ->success()
                                    ->icon('heroicon-o-check')
                                    ->actions([
                                    ])
                                    ->send();
                        })
                        ->hidden(fn(Transaction $transaction)=> $transaction-> status !== 'waiting' )
            ]);
    }
}
