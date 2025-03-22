<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Orders' : 'الطلبات';
    }

    public static function getNavigationGroup(): string
{
    return app()->getLocale() === 'en' ? 'Order Management' : 'إدارة الطلبات';
}



    public static function getPluralLabel(): string
    {
        return __('Orders');
    }

    public static function getModelLabel(): string
    {
        return __('Order');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->label(__('User ID'))
                    ->required(),

                Forms\Components\TextInput::make('provider_id')
                    ->label(__('Provider ID'))
                    ->nullable(),

                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending'    => __('Pending'),
                        'accepted'   => __('Accepted'),
                        'rejected'   => __('Rejected'),
                        'completed'  => __('Completed'),
                        'canceled'   => __('Canceled'),
                        'sent'       => __('Sent'),
                        'refunded'   => __('Refunded'),
                        'paid'       => __('Paid'),
                    ])
                    ->required(),

                Forms\Components\Select::make('payment_method')
                    ->label(__('Payment Method'))
                    ->options([
                        'Online'     => __('Online'),
                        'Cash'       => __('Cash'),
                        'Wallet'     => __('Wallet'),
                        'Visa'       => __('Visa'),
                        'Mastercard' => __('Mastercard'),
                        'Mada'       => __('Mada'),
                        'ApplePay'   => __('Apple Pay'),
                    ])
                    ->nullable(),

                Forms\Components\TextInput::make('total_cost')
                    ->label(__('Total Cost'))
                    ->numeric()
                    ->required(),

                Forms\Components\Textarea::make('details')
                    ->label(__('Details'))
                    ->nullable(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('ID'))
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label(__('User Name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('provider.name')
                    ->label(__('Provider Name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label(__('Payment Method'))
                    ->sortable(),

                TextColumn::make('total_cost')
                    ->label(__('Total Cost'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending'    => __('Pending'),
                        'accepted'   => __('Accepted'),
                        'rejected'   => __('Rejected'),
                        'completed'  => __('Completed'),
                        'canceled'   => __('Canceled'),
                        'sent'       => __('Sent'),
                        'refunded'   => __('Refunded'),
                        'paid'       => __('Paid'),
                    ]),

                SelectFilter::make('payment_method')
                    ->label(__('Payment Method'))
                    ->options([
                        'Online'     => __('Online'),
                        'Cash'       => __('Cash'),
                        'Wallet'     => __('Wallet'),
                        'Visa'       => __('Visa'),
                        'Mastercard' => __('Mastercard'),
                        'Mada'       => __('Mada'),
                        'ApplePay'   => __('Apple Pay'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading(__('Delete Confirmation'))
                    ->modalDescription(__('Are you sure you want to delete this order?')),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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
            'index'  => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
