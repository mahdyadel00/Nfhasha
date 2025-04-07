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
            ->modifyQueryUsing(fn(Builder $query) => $query->where('is_active', false))
            ->columns([
                TextColumn::make('user.name')->label(__('Provider Name'))->sortable()->searchable(),
                TextColumn::make('city.name')->label(__('City'))->sortable()->searchable(),
                TextColumn::make('district.name')->label(__('District'))->sortable()->searchable(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->formatStateUsing(fn($state) => $state === 'center' ? __('Center') : __('Individual'))
                    ->sortable(),
                BooleanColumn::make('mechanical')->label(__('Mechanical')),
                BooleanColumn::make('plumber')->label(__('Plumber')),
                BooleanColumn::make('electrical')->label(__('Electrical')),
                BooleanColumn::make('puncture')->label(__('Puncture')),
                BooleanColumn::make('battery')->label(__('Battery')),
                BooleanColumn::make('pickup')->label(__('Pickup Service')),
                BooleanColumn::make('is_active')->label(__('Active'))->sortable(),
            ])
            ->actions([
                ViewAction::make('View Documents')->label(__('View Documents'))->icon('heroicon-o-document'),
                Action::make('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->label(__('Approve'))
                    ->modalHeading(__('Approve Provider'))
                    ->modalDescription(__('Are you sure you want to approve this provider?'))
                    ->modalSubmitActionLabel(__('Approve'))
                    ->modalCancelActionLabel(__('Cancel'))
                    ->requiresConfirmation()
                    ->action(fn(Provider $provider) => $provider->update(['is_active' => true])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
