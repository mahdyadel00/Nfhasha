<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\FileUpload;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';


    public static function getnavigationGroup(): string
    {
        return app()->getLocale() === 'en' ? 'Services' : 'الخدمات';
    }
    

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Services' : 'الخدمات';
    }

    public static function getpluralLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Services' : 'الخدمات';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Services' : 'الخدمات';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Service' : 'خدمة';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        FileUpload::make('cover')
                            ->label(app()->getLocale() === 'en' ? 'Cover Image' : 'صورة الغلاف')
                            ->image()
                            ->required()
                            ->directory('service_covers')
                            ->maxSize(2048)
                            ->helperText(app()->getLocale() === 'en' ? 'Upload the cover image of the service.' : 'قم برفع صورة الغلاف الخاصة بالخدمة.'),

                        Tabs::make('Translations')
                            ->tabs([
                                Tab::make(app()->getLocale() === 'en' ? 'Arabic' : 'عربي')
                                    ->schema([
                                        TextInput::make('name:ar')
                                            ->label(app()->getLocale() === 'en' ? 'Name (Arabic)' : 'الاسم (بالعربية)')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder(app()->getLocale() === 'en' ? 'Enter name in Arabic' : 'أدخل الاسم بالعربية'),

                                        Textarea::make('description:ar')
                                            ->label(app()->getLocale() === 'en' ? 'Description (Arabic)' : 'الوصف (بالعربية)')
                                            ->required()
                                            ->placeholder(app()->getLocale() === 'en' ? 'Enter description in Arabic' : 'أدخل الوصف بالعربية'),

                                        Textarea::make('instructions:ar')
                                            ->label(app()->getLocale() === 'en' ? 'Instructions (Arabic)' : 'التعليمات (بالعربية)')
                                            ->required()
                                            ->placeholder(app()->getLocale() === 'en' ? 'Enter instructions in Arabic' : 'أدخل نص التعليمات بالعربية'),
                                    ]),

                                Tab::make(app()->getLocale() === 'en' ? 'English' : 'إنجليزي')
                                    ->schema([
                                        TextInput::make('name:en')
                                            ->label(app()->getLocale() === 'en' ? 'Name (English)' : 'الاسم (بالإنجليزية)')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder(app()->getLocale() === 'en' ? 'Enter name in English' : 'أدخل الاسم بالإنجليزية'),

                                        Textarea::make('description:en')
                                            ->label(app()->getLocale() === 'en' ? 'Description (English)' : 'الوصف (بالإنجليزية)')
                                            ->required()
                                            ->placeholder(app()->getLocale() === 'en' ? 'Enter description in English' : 'أدخل الوصف بالإنجليزية'),

                                        Textarea::make('instructions:en')
                                            ->label(app()->getLocale() === 'en' ? 'Instructions (English)' : 'التعليمات (بالإنجليزية)')
                                            ->required()
                                            ->placeholder(app()->getLocale() === 'en' ? 'Enter instructions in English' : 'أدخل نص التعليمات بالإنجليزية'),
                                    ]),
                            ]),
                    ])
                    ->columns(2),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover')
                    ->label(app()->getLocale() === 'en' ? 'Cover Image' : 'صورة الغلاف')
                    ->circular(),

                Tables\Columns\ToggleColumn::make('status')
                    ->label(app()->getLocale() === 'en' ? 'Status' : 'الحالة'),

                Tables\Columns\TextColumn::make('name:ar')
                    ->label(app()->getLocale() === 'en' ? 'Name (Arabic)' : 'الاسم (بالعربية)')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name:en')
                    ->label(app()->getLocale() === 'en' ? 'Name (English)' : 'الاسم (بالإنجليزية)')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(app()->getLocale() === 'en' ? 'Created At' : 'تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(app()->getLocale() === 'en' ? 'Updated At' : 'آخر تحديث')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
