<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PickUpTruckResource\Pages;
use App\Filament\Resources\PickUpTruckResource\RelationManagers;
use App\Models\PickUpTruck;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class   PickUpTruckResource extends Resource
{
    protected static ?string $model = PickUpTruck::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';


    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Pick Up Trucks' : 'السطحات';
    }

    public static function getpluralLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Pick Up Trucks' : 'السطحات';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Pick Up Trucks' : 'السطحات';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Pick Up Truck' : 'سطحة';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image')
                    ->label(app()->getLocale() === 'en' ? 'Image' : 'صورة السطحة')
                    ->directory('pickup_trucks')
                    ->required()
                    ->image(),

                Forms\Components\TextInput::make('price')
                    ->label(app()->getLocale() === 'en' ? 'Price' : 'سعر السطحة')
                    ->required()
                    ->numeric()
                    ->prefix(app()->getLocale() === 'en' ? 'SAR' : 'ريال'),

                Forms\Components\Section::make(app()->getLocale() === 'en' ? 'Translations' : 'الترجمات')
                    ->schema([
                        Forms\Components\TextInput::make('name:ar')
                            ->label('الاسم بالعربية')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('name:en')
                            ->label('الاسم بالإنجليزية')
                            ->required()
                            ->maxLength(255),
                    ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label(app()->getLocale() === 'en' ? 'Image' : 'الصورة'),

                Tables\Columns\TextColumn::make('name')
                    ->label(app()->getLocale() === 'en' ? 'Name' : 'الاسم')
                    ->getStateUsing(fn ($record) => $record->translate(app()->getLocale())->name ?? '-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->label(app()->getLocale() === 'en' ? 'Price' : 'السعر')
                    ->suffix(app()->getLocale() === 'en' ? ' SAR' : ' ريال')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('price_range')
                    ->form([
                        Forms\Components\TextInput::make('min_price')
                            ->numeric()
                            ->label(app()->getLocale() === 'en' ? 'Minimum Price' : 'الحد الأدني للسعر')
                            ->placeholder(app()->getLocale() === 'en' ? 'Minimum Price' : 'الحد الأدني للسعر'),
                        Forms\Components\TextInput::make('max_price')
                            ->numeric()
                            ->label(app()->getLocale() === 'en' ? 'Maximum Price' : 'الحد الأعلي للسعر')
                            ->placeholder(app()->getLocale() === 'en' ? 'Maximum Price' : 'الحد الأعلي للسعر'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['min_price']) && !empty($data['max_price'])) {
                            return $query->whereBetween('price', [$data['min_price'], $data['max_price']]);
                        } elseif (!empty($data['min_price'])) {
                            return $query->where('price', '>=', $data['min_price']);
                        } elseif (!empty($data['max_price'])) {
                            return $query->where('price', '<=', $data['max_price']);
                        }

                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index'         => Pages\ListPickUpTrucks::route('/'),
            'create'        => Pages\CreatePickUpTruck::route('/create'),
            'edit'          => Pages\EditPickUpTruck::route('/{record}/edit'),
        ];
    }
}
