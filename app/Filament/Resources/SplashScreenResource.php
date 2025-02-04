<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SplashScreenResource\Pages;
use App\Filament\Resources\SplashScreenResource\RelationManagers;
use App\Models\SplashScreen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SplashScreenResource extends Resource
{
    protected static ?string $model = SplashScreen::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';


    protected static ?string $activeNavigationIcon = 'heroicon-o-chevron-double-right';

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Splash Screens' : 'شاشات البداية';
    }

    public static function getpluralLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Splash Screens' : 'شاشات البداية';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Splash Screens' : 'شاشات البداية';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Splash Screen' : 'شاشة بداية';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Section::make(app()->getLocale() === 'en' ? 'General Information' : 'المعلومات العامة')
                    ->schema([
                        \Filament\Forms\Components\FileUpload::make('image')
                            ->label(app()->getLocale() === 'en' ? 'Splash Screen Image' : 'صورة الشاشة الترحيبية')
                            ->directory('splash_screens')
                            ->required()
                            ->image()
                            ->imagePreviewHeight('200px'),

                        \Filament\Forms\Components\Toggle::make('is_active')
                            ->label(app()->getLocale() === 'en' ? 'Active' : 'مفعّلة')
                            ->default(true),
                    ])
                    ->columns(2),

                \Filament\Forms\Components\Section::make(app()->getLocale() === 'en' ? 'Translations' : 'الترجمات')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('title:ar')
                            ->label(app()->getLocale() === 'en' ? 'Title in Arabic' : 'العنوان بالعربية')
                            ->required()
                            ->maxLength(255),

                        \Filament\Forms\Components\TextInput::make('title:en')
                            ->label(app()->getLocale() === 'en' ? 'Title in English' : 'العنوان بالإنجليزية')
                            ->required()
                            ->maxLength(255),

                        \Filament\Forms\Components\Textarea::make('description:ar')
                            ->label(app()->getLocale() === 'en' ? 'Description in Arabic' : 'الوصف بالعربية')
                            ->required()
                            ->rows(3),

                        \Filament\Forms\Components\Textarea::make('description:en')
                            ->label(app()->getLocale() === 'en' ? 'Description in English' : 'الوصف بالإنجليزية')
                            ->required()
                            ->rows(3),
                    ])
                    ->columns(2),
            ]);
    }




    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label(app()->getLocale() === 'en' ? 'Image' : 'الصورة')
                    ->rounded(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label(app()->getLocale() === 'en' ? 'Active' : 'نشط')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(app()->getLocale() === 'en' ? 'Created At' : 'تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(app()->getLocale() === 'en' ? 'Updated At' : 'تاريخ التعديل')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListSplashScreens::route('/'),
            'create' => Pages\CreateSplashScreen::route('/create'),
            'edit' => Pages\EditSplashScreen::route('/{record}/edit'),
        ];
    }
}
