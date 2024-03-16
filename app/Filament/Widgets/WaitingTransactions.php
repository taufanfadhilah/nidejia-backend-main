<?php

namespace App\Filament\Widgets;

use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Transaction;

class WaitingTransactions extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = '6';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()->whereStatus('waiting')
            )
            ->columns([
                Tables\Columns\TextColumn::make('User.name'),
                Tables\Columns\TextColumn::make('Listing.title'),
                Tables\Columns\TextColumn::make('total_price')->money('USD'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'waiting' => 'gray',
                        'approved' => 'info',
                        'canceled' => 'danger',
                    })
            ])->actions([
                    Action::make('approve')
                        ->button()
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Transaction $transaction) {
                            Transaction::find($transaction->id)->update([
                                'status' => 'approved'
                            ]);
                            Notification::make()->success()->title('Transaction Approved!')->body('Transaction has been approved successfully')->icon('heroicon-o-check')->send()->toDatabase();
                        })
                        ->hidden(fn(Transaction $transaction) => $transaction->status !== 'waiting')
                ]);
    }
}
