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
              //name
                Card::make('Name')
                    ->schema([
                        TextInput::make('name:ar')
                            ->label(app()->getLocale() === 'en' ? 'Name (Arabic)' : 'الاسم (بالعربية)')
                            ->placeholder(app()->getLocale() === 'en' ? 'Enter the name in Arabic' : 'أدخل الاسم بالعربية')
                            ->required(),

                        TextInput::make('name:en')
                            ->label(app()->getLocale() === 'en' ? 'Name (English)' : 'الاسم (بالإنجليزية)')
                            ->placeholder(app()->getLocale() === 'en' ? 'Enter the name in English' : 'أدخل الاسم بالإنجليزية')
                            ->required(),
                    ]),
             //description
                Card::make('Description')
                    ->schema([
                        Textarea::make('description:ar')
                            ->label(app()->getLocale() === 'en' ? 'Description (Arabic)' : 'الوصف (بالعربية)')
                            ->placeholder(app()->getLocale() === 'en' ? 'Enter the description in Arabic' : 'أدخل الوصف بالعربية')
                            ->required(),

                        Textarea::make('description:en')
                            ->label(app()->getLocale() === 'en' ? 'Description (English)' : 'الوصف (بالإنجليزية)')
                            ->placeholder(app()->getLocale() === 'en' ? 'Enter the description in English' : 'أدخل الوصف بالإنجليزية')
                            ->required(),
                    ]),
                Card::make('Price')
                    ->schema([
                        TextInput::make('price')
                            ->label(app()->getLocale() === 'en' ? 'Price' : 'السعر')
                            ->placeholder(app()->getLocale() === 'en' ? 'Enter the price' : 'أدخل السعر')
                            ->required(),
                    ]),
                //vat
                Card::make('VAT')
                    ->schema([
                        TextInput::make('vat')
                            ->label(app()->getLocale() === 'en' ? 'VAT' : 'الضريبة')
                            ->placeholder(app()->getLocale() === 'en' ? 'Enter the VAT' : 'أدخل الضريبة')
                            ->required(),
                    ]),
                //status
                Card::make('Status')
                    ->schema([
                        Toggle::make('status')
                            ->label(app()->getLocale() === 'en' ? 'Status' : 'الحالة')
                            ->default(true),
                    ]),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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

                //price
                Tables\Columns\TextColumn::make('price')
                    ->label(app()->getLocale() === 'en' ? 'Price' : 'السعر')
                    ->sortable()
                    ->searchable(),

                //vat
                Tables\Columns\TextColumn::make('vat')
                    ->label(app()->getLocale() === 'en' ? 'VAT' : 'الضريبة')
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
//                Tables\Actions\DeleteAction::make(),
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
            'index'     => Pages\ListServices::route('/'),
            'create'    => Pages\CreateService::route('/create'),
            'edit'      => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
