<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleBrandResource\Pages;
use App\Models\VehicleBrand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VehicleBrandResource extends Resource
{
    protected static ?string $model = VehicleBrand::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Management';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->required()
                ->label(__('messages.title')) // استخدام الترجمة
                ->placeholder(__('messages.enter_title')), // استخدام الترجمة
            Forms\Components\Toggle::make('status')
                ->label(__('messages.active')) // استخدام الترجمة
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->label(__('messages.id')), // استخدام الترجمة
            Tables\Columns\TextColumn::make('title')
                ->label(__('messages.title')) // استخدام الترجمة
                ->searchable(),
            Tables\Columns\BooleanColumn::make('status')->label(__('messages.active')), // استخدام الترجمة
        ])
        ->actions([
            Tables\Actions\EditAction::make(), // زر التعديل
            Tables\Actions\DeleteAction::make(), // زر الحذف
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicleBrands::route('/'),
            'create' => Pages\CreateVehicleBrand::route('/create'),
            'edit' => Pages\EditVehicleBrand::route('/{record}/edit'),
        ];
    }
}
