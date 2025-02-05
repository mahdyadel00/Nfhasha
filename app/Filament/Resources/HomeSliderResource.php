<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeSliderResource\Pages;
use App\Models\HomeSlider;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;

class HomeSliderResource extends Resource
{
    protected static ?string $model = HomeSlider::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $activeNavigationIcon = 'heroicon-o-chevron-double-right';



    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Home Sliders' : 'سلايدرات الصفحة الرئيسية';
    }

    public static function getpluralLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Home Sliders' : 'سلايدرات الصفحة الرئيسية';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Home Sliders' : 'سلايدرات الصفحة الرئيسية';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Home Slider' : 'سلايدر';
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('cover')
                ->label(app()->getLocale() === 'en' ? 'Cover' : 'صورة الغلاف')
                ->directory('home_sliders')
                ->required()
                ->image(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_url')
                    ->label(app()->getLocale() === 'en' ? 'Cover' : 'صورة الغلاف')
                    ->circular(),
            ])
            ->filters([
                //
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
            'index'     => Pages\ListHomeSliders::route('/'),
            'create'    => Pages\CreateHomeSlider::route('/create'),
            'edit'      => Pages\EditHomeSlider::route('/{record}/edit'),
        ];
    }
}
