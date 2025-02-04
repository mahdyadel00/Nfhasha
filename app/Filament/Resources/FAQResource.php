<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FAQResource\Pages;
use App\Filament\Resources\FAQResource\RelationManagers;
use App\Models\FAQ;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;

class FAQResource extends Resource
{
    protected static ?string $model = FAQ::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    public static function getnavigationGroup(): string
    {
        return app()->getLocale() === 'en' ? 'APP Settings' : 'إعدادات التطبيق';
    }


    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'en' ? 'FAQs' : 'الأسئلة الشائعة';
    }

    public static function getpluralLabel(): string
    {
        return app()->getLocale() === 'en' ? 'FAQs' : 'الأسئلة الشائعة';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'en' ? 'FAQs' : 'الأسئلة الشائعة';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'en' ? 'FAQ' : 'سؤال شائع';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(app()->getLocale() === 'en' ? 'Question and Answer' : 'السؤال والإجابة')
                    ->schema([
                        Card::make()
                            ->schema([
                                TextInput::make('question:en')
                                    ->label(app()->getLocale() === 'en' ? 'Question (English)' : 'السؤال (بالإنجليزية)')
                                    ->placeholder(app()->getLocale() === 'en' ? 'Enter question in English' : 'أدخل السؤال بالإنجليزية')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('question:ar')
                                    ->label(app()->getLocale() === 'en' ? 'Question (Arabic)' : 'السؤال (بالعربية)')
                                    ->placeholder(app()->getLocale() === 'en' ? 'Enter question in Arabic' : 'أدخل السؤال بالعربية')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('answer:en')
                                    ->label(app()->getLocale() === 'en' ? 'Answer (English)' : 'الإجابة (بالإنجليزية)')
                                    ->placeholder(app()->getLocale() === 'en' ? 'Enter answer in English' : 'أدخل الإجابة بالإنجليزية')
                                    ->required()
                                    ->maxLength(500),

                                TextInput::make('answer:ar')
                                    ->label(app()->getLocale() === 'en' ? 'Answer (Arabic)' : 'الإجابة (بالعربية)')
                                    ->placeholder(app()->getLocale() === 'en' ? 'Enter answer in Arabic' : 'أدخل الإجابة بالعربية')
                                    ->required()
                                    ->maxLength(500),
                            ])
                            ->columns(2),
                    ]),

                Section::make(app()->getLocale() === 'en' ? 'Additional Settings' : 'إعدادات إضافية')
                    ->schema([
                        Toggle::make('is_active')
                            ->label(app()->getLocale() === 'en' ? 'Is Active' : 'حالة التنشيط')
                            ->default(true),

                        Toggle::make('to_providers')
                            ->label(app()->getLocale() === 'en' ? 'For Providers' : 'لمقدمي الخدمة')
                            ->default(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ToggleColumn::make('is_active')
                ->label(app()->getLocale() === 'en' ? 'Is Active' : 'حالة التنشيط'),
                Tables\Columns\ToggleColumn::make('to_providers')
                ->label(app()->getLocale() === 'en' ? 'For Providers' : 'لمقدمي الخدمة'),
                Tables\Columns\TextColumn::make('question:' . app()->getLocale())
                ->label(app()->getLocale() === 'en' ? 'Question' : 'السؤال'),
                Tables\Columns\TextColumn::make('answer:' . app()->getLocale())
                ->label(app()->getLocale() === 'en' ? 'Answer' : 'الإجابة'),
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
            'index' => Pages\ListFAQS::route('/'),
            'create' => Pages\CreateFAQ::route('/create'),
            'edit' => Pages\EditFAQ::route('/{record}/edit'),
        ];
    }
}
