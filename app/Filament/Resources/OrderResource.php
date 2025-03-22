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
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Model;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

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
                TextColumn::make('id')->label(__('رقم الطلب'))->sortable(),
                TextColumn::make('provider.name')->label(__('اسم المزود'))->sortable()->searchable(),
                TextColumn::make('user.name')->label(__('اسم المستخدم'))->sortable()->searchable(),
                TextColumn::make('expressService.name')->label(__('الخدمة السريعة'))->sortable(),
                TextColumn::make('city.name')->label(__('المدينة'))->sortable(),
                TextColumn::make('pickUpTruck.name')->label(__('شاحنة النقل'))->sortable(),
                BadgeColumn::make('status')
                    ->label(__('الحالة'))
                    ->colors([
                        'pending' => 'warning',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'completed' => 'primary',
                        'canceled' => 'gray',
                        'sent' => 'info',
                        'refunded' => 'purple',
                        'paid' => 'green',
                    ]),
                TextColumn::make('payment_method')->label(__('طريقة الدفع'))->sortable(),
                TextColumn::make('from_lat')->label(__('إحداثيات الانطلاق (خط العرض)'))->copyable(),
                TextColumn::make('from_long')->label(__('إحداثيات الانطلاق (خط الطول)'))->copyable(),
                TextColumn::make('to_lat')->label(__('إحداثيات الوجهة (خط العرض)'))->copyable(),
                TextColumn::make('to_long')->label(__('إحداثيات الوجهة (خط الطول)'))->copyable(),
                TextColumn::make('total_cost')->label(__('التكلفة الإجمالية'))->sortable()->money('SAR'),
                TextColumn::make('note')->label(__('ملاحظات'))->limit(30),
                TextColumn::make('address')->label(__('عنوان الاستلام'))->limit(30),
                TextColumn::make('address_to')->label(__('عنوان الوجهة'))->limit(30),
                TextColumn::make('reason')
                    ->label(__('Reason'))
                    ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : $state)
                    ->limit(50)
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('حالة الطلب'))
                    ->options([
                        'pending' => __('معلق'),
                        'accepted' => __('مقبول'),
                        'rejected' => __('مرفوض'),
                        'completed' => __('مكتمل'),
                        'canceled' => __('ملغي'),
                        'sent' => __('تم الإرسال'),
                        'refunded' => __('تم الاسترداد'),
                        'paid' => __('مدفوع'),
                    ]),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()->label(__('حذف')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label(__('حذف المحدد')),
                ]),
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
