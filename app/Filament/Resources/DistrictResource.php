<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistrictResource\Pages;
use App\Filament\Resources\DistrictResource\RelationManagers;
use App\Models\District;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DistrictResource extends Resource
{
    protected static ?string $model = District::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $activeNavigationIcon = 'heroicon-o-chevron-double-right';
    protected static ?string $recordTitleAttribute = 'name:en';

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Districts' : 'الأحياء';
    }

    public static function getPluralLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Districts' : 'الأحياء';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'en' ? 'District' : 'حي';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('city_id')
                    ->label(app()->getLocale() === 'en' ? 'City' : 'المدينة')
                    ->options(\App\Models\City::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->placeholder(app()->getLocale() === 'en' ? 'Select City' : 'اختر المدينة')
                    ->helperText(app()->getLocale() === 'en' ? 'Choose the associated city' : 'اختر المدينة المرتبطة')
                    ->columnSpan(2),

                Forms\Components\Toggle::make('is_active')
                    ->label(app()->getLocale() === 'en' ? 'Active' : 'مفعّلة')
                    ->default(true)
                    ->columnSpan(1)
                    ->helperText(app()->getLocale() === 'en' ? 'Is this district active?' : 'هل هذا الحي مفعّل؟')
                ,

                Forms\Components\Section::make(app()->getLocale() === 'en' ? 'Translations' : 'الترجمات')
                    ->schema([
                        Forms\Components\TextInput::make('name:ar')
                            ->label(app()->getLocale() === 'en' ? 'Name in Arabic' : 'الاسم بالعربية')
                            ->required()
                            ->maxLength(255)
                            ->helperText(app()->getLocale() === 'en' ? 'Enter the name in Arabic' : 'أدخل الاسم باللغة العربية')
                        ,

                        Forms\Components\TextInput::make('name:en')
                            ->label(app()->getLocale() === 'en' ? 'Name in English' : 'الاسم بالإنجليزية')
                            ->required()
                            ->maxLength(255)
                            ->helperText(app()->getLocale() === 'en' ? 'Enter the name in English' : 'أدخل الاسم باللغة الإنجليزية')
                        ,
                    ])
                    ->columnSpan(2),
            ])
            ->columns(2);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label(app()->getLocale() === 'en' ? 'Active' : 'مفعّلة'),

                Tables\Columns\TextColumn::make('name:ar')
                    ->label(app()->getLocale() === 'en' ? 'Name in Arabic' : 'الاسم بالعربية')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name:en')
                    ->label(app()->getLocale() === 'en' ? 'Name in English' : 'الاسم بالإنجليزية')
                    ->searchable(),

                TextColumn::make('city.name')
                    ->label(app()->getLocale() === 'en' ? 'City' : 'المدينة')
                    ->searchable(),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record) {
                        if ($record->city->districts()->exists()) {
                            throw new \Exception('لا يمكن حذف هذا الحي لأنه مرتبط بمدينة.');
                        }
                        $record->translations()->delete();
                    }),
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
            'index' => Pages\ListDistricts::route('/'),
            'create' => Pages\CreateDistrict::route('/create'),
            'edit' => Pages\EditDistrict::route('/{record}/edit'),
        ];
    }
}
