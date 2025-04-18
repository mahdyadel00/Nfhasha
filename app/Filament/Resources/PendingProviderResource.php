<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PendingProviderResource\Pages;
use App\Filament\Resources\PendingProviderResource\RelationManagers;
use App\Models\Provider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Actions\ViewAction;

class PendingProviderResource extends Resource
{
    protected static ?string $model = Provider::class;


    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function getnavigationGroup(): string
    {
        return app()->getLocale() === 'en' ? 'Providers' : 'موفري الخدمة';
    }


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', false)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('is_active', false)->count() > 0 ? 'danger' : 'success';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Pending Providerss' : 'بإنتظار الموافقة';
    }

    public static function getpluralLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Pending Providerss' : 'بإنتظار الموافقة';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Pending Providerss' : 'بإنتظار الموافقة';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // ضيف هنا العناصر اللي محتاجها في الفورم لو فيه
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('messages.provider_name'))
                    ->searchable(),

                TextColumn::make('city.name')
                    ->label(__('messages.city')),

                TextColumn::make('district.name')
                    ->label(__('messages.district')),

                TextColumn::make('type')
                    ->label(__('messages.type'))
                    ->formatStateUsing(fn($state) => $state === 'center' ? __('center') : __('individual')),

                BooleanColumn::make('mechanical')
                    ->label(__('messages.mechanical')),

                BooleanColumn::make('plumber')
                    ->label(__('messages.plumber')),

                BooleanColumn::make('electrical')
                    ->label(__('messages.electrical')),

                BooleanColumn::make('puncture')
                    ->label(__('messages.puncture')),

                BooleanColumn::make('battery')
                    ->label(__('messages.battery')),

                BooleanColumn::make('pickup')
                    ->label(__('messages.pickup_service')),

                BooleanColumn::make('is_active')
                    ->label(__('messages.active')),
            ])
            ->actions([
                ViewAction::make('view_documents')
                    ->label(__('messages.view_documents'))
                    ->icon('heroicon-o-document'),

                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->label(__('messages.approve'))
                    ->modalHeading(__('approve_provider'))
                    ->modalDescription(__('approve_confirmation'))
                    ->modalSubmitActionLabel(__('approve'))
                    ->modalCancelActionLabel(__('cancel'))
                    ->requiresConfirmation()
                    ->action(fn(Provider $provider) => $provider->update(['is_active' => true])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                // حذف المستخدم المرتبط
                                if ($record->user) {
                                    $record->user->delete();
                                }
                                // حذف السجل
                                $record->delete();
                            }
                        }),
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
            'index' => Pages\ListPendingProviders::route('/'),
            'create' => Pages\CreatePendingProvider::route('/create'),
            'edit' => Pages\EditPendingProvider::route('/{record}/edit'),
        ];
    }
}
