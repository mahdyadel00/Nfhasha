<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CyPeriodicResource\Pages;
use App\Filament\Resources\CyPeriodicResource\RelationManagers;
use App\Models\City;
use App\Models\CyPeriodic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CyPeriodicResource extends Resource
{
    protected static ?string $model = CyPeriodic::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';


    public static function getnavigationGroup(): string
    {
        return app()->getLocale() === 'en' ? 'Services' : 'الخدمات';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Periodic Examination' : 'الفحص الدوري';
    }

    public static function getpluralLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Periodic Examination' : 'الفحص الدوري';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Periodic Examination' : 'الفحص الدوري';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'en' ? ' Periodic Examination Type' : 'نوع الفحص الدوري';
    }

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Section::make()
                ->label(app()->getLocale() === 'en' ? 'City Selection' : 'اختيار المدينة')
                ->schema([
                    Forms\Components\Select::make('city_id')
                        ->label(app()->getLocale() === 'en' ? 'City' : 'المدينة')
                        ->options(\App\Models\City::all()->pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->placeholder(app()->getLocale() === 'en' ? 'Select City' : 'اختر المدينة')
                        ->helperText(app()->getLocale() === 'en' ? 'Choose the associated city' : 'اختر المدينة المرتبطة')
                        ->extraAttributes(['class' => 'p-3 bg-white border border-gray-300 rounded-md shadow-sm']),
                ]),

            Forms\Components\Section::make()
                ->label(app()->getLocale() === 'en' ? 'Title Information' : 'معلومات العنوان')
                ->schema([
                    Forms\Components\TextInput::make('title:ar')
                        ->label(app()->getLocale() === 'en' ? 'Title (Arabic)' : 'العنوان (العربية)')
                        ->required()
                        ->maxLength(150)
                        ->extraAttributes(['class' => 'p-3 bg-white border border-gray-300 rounded-md shadow-sm']),

                    Forms\Components\TextInput::make('title:en')
                        ->label(app()->getLocale() === 'en' ? 'Title (English)' : 'العنوان (الإنجليزية)')
                        ->required()
                        ->maxLength(150)
                        ->extraAttributes(['class' => 'p-3 bg-white border border-gray-300 rounded-md shadow-sm']),
                ]),

            Forms\Components\Section::make()
                ->label(app()->getLocale() === 'en' ? 'Price Details' : 'تفاصيل السعر')
                ->schema([
                    Forms\Components\TextInput::make('price')
                        ->label(app()->getLocale() === 'en' ? 'Price' : 'السعر')
                        ->required()
                        ->numeric()
                        ->prefix(app()->getLocale() === 'en' ? 'SAR' : 'ريال')
                        ->step(0.01)
                        ->extraAttributes(['class' => 'p-3 bg-white border border-gray-300 rounded-md shadow-sm']),
                ]),

            Forms\Components\Section::make()
                ->label(app()->getLocale() === 'en' ? 'Status' : 'الحالة')
                ->schema([
                    Forms\Components\Toggle::make('status')
                        ->label(app()->getLocale() === 'en' ? 'Status' : 'الحالة')
                        ->default(true),
                ]),
        ]);
}



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(app()->getLocale() === 'en' ? 'Title' : 'العنوان')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city.name')
                    ->label(app()->getLocale() === 'en' ? 'City' : 'المدينة')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(app()->getLocale() === 'en' ? 'Price' : 'السعر')
                    ->suffix(app()->getLocale() === 'en' ? ' SAR' : ' ريال')
                    ->searchable(),
                Tables\Columns\IconColumn::make('status')
                    ->boolean(),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListCyPeriodics::route('/'),
            'create' => Pages\CreateCyPeriodic::route('/create'),
            'edit' => Pages\EditCyPeriodic::route('/{record}/edit'),
        ];
    }
}
