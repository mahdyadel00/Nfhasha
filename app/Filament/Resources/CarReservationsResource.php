<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarReservationsResource\Pages;
use App\Filament\Resources\CarReservationsResource\RelationManagers;
use App\Models\CarReservations;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CarReservationsResource extends Resource
{
    protected static ?string $model = CarReservations::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $activeNavigationIcon = 'heroicon-o-chevron-double-right';
    protected static ?string $recordTitleAttribute = 'name:en';

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Car Reservations' : 'حجوزات السيارات';
    }

    public static function getPluralLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Car Reservations' : 'حجوزات السيارات';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Car Reservation' : 'حجز السيارة';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('city_id')
                    ->label(app()->getLocale() === 'en' ? 'City' : 'المدينة')
                    ->options(\App\Models\City::all()->pluck('name', 'id')->filter())
                    ->required()
                    ->searchable()
                    ->placeholder(app()->getLocale() === 'en' ? 'Select City' : 'اختر المدينة')
                    ->helperText(app()->getLocale() === 'en' ? 'Choose the associated city' : 'اختر المدينة المرتبطة')
                    ->extraAttributes(['class' => 'p-3 bg-white border border-gray-300 rounded-md shadow-sm']),
                Forms\Components\Select::make('user_vehicle_id')
                    ->label(app()->getLocale() === 'en' ? 'Vehicle' : 'المركبة')
                    ->options(\App\Models\UserVehicle::all()->pluck('letters', 'id')->filter())
                    ->required()
                    ->searchable()
                    ->placeholder(app()->getLocale() === 'en' ? 'Select Vehicle' : 'اختر المركبة')
                    ->helperText(app()->getLocale() === 'en' ? 'Choose the associated vehicle' : 'اختر المركبة المرتبطة')
                    ->extraAttributes(['class' => 'p-3 bg-white border border-gray-300 rounded-md shadow-sm']),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('inspection_side')
                            ->label(app()->getLocale() === 'en' ? 'Inspection Side' : 'جانب الفحص')
                            ->options([
                                'all'   => app()->getLocale() === 'en' ? 'All' : 'الكل',
                                'front' => app()->getLocale() === 'en' ? 'Front' : 'الأمام',
                                'back'  => app()->getLocale() === 'en' ? 'Back' : 'الخلف',
                                'sides' => app()->getLocale() === 'en' ? 'Sides' : 'الجوانب',
                                'left'  => app()->getLocale() === 'en' ? 'Left' : 'اليسار',
                            ])
                            ->required()
                            ->searchable()
                            ->placeholder(app()->getLocale() === 'en' ? 'Select Inspection Side' : 'اختر جانب الفحص')
                            ->helperText(app()->getLocale() === 'en' ? 'Choose the inspection side' : 'اختر جانب الفحص')
                            ->extraAttributes(['class' => 'p-3 bg-white border border-gray-300 rounded-md shadow-sm']),
                        Forms\Components\DatePicker::make('date')
                            ->label(app()->getLocale() === 'en' ? 'Date' : 'التاريخ')
                            ->required()
                            ->extraAttributes(['class' => 'p-3 bg-white border border-gray-300 rounded-md shadow-sm']),

                        Forms\Components\TimePicker::make('time')
                            ->label(app()->getLocale() === 'en' ? 'Time' : 'الوقت')
                            ->required()
                            ->extraAttributes(['class' => 'p-3 bg-white border border-gray-300 rounded-md shadow-sm']),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('city_id')
                    ->label(app()->getLocale() === 'en' ? 'City' : 'المدينة'),
                Tables\Columns\TextColumn::make('user_vehicle_id')
                    ->label(app()->getLocale() === 'en' ? 'Vehicle' : 'المركبة'),
                Tables\Columns\TextColumn::make('inspection_side')
                    ->label(app()->getLocale() === 'en' ? 'Inspection Side' : 'جانب الفحص'),
                Tables\Columns\TextColumn::make('date')
                    ->label(app()->getLocale() === 'en' ? 'Date' : 'التاريخ'),
                Tables\Columns\TextColumn::make('time')
                    ->label(app()->getLocale() === 'en' ? 'Time' : 'الوقت'),
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
            'index'     => Pages\ListCarReservations::route('/'),
            'create'    => Pages\CreateCarReservations::route('/create'),
            'edit'      => Pages\EditCarReservations::route('/{record}/edit'),
        ];
    }
}
