<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialLinksResource\Pages;
use App\Filament\Resources\SocialLinksResource\RelationManagers;
use App\Models\SocialLinks;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SocialLinksResource extends Resource
{
    protected static ?string $model = SocialLinks::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $activeNavigationIcon = 'heroicon-o-chevron-double-right';
    protected static ?string $recordTitleAttribute = 'id';

    public static function getnavigationGroup(): string
    {
        return app()->getLocale() === 'en' ? 'APP Settings' : 'إعدادات التطبيق';
    }
    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Social Links' : 'روابط التواصل';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('facebook')
                    ->label('Facebook URL')
                    ->placeholder('https://www.facebook.com/your-page')
                    ->url()
                    ->required(),

                Forms\Components\TextInput::make('twitter')
                    ->label('Twitter URL')
                    ->placeholder('https://twitter.com/your-profile')
                    ->url()
                    ->required(),

                Forms\Components\TextInput::make('linkedin')
                    ->label('LinkedIn URL')
                    ->placeholder('https://www.linkedin.com/in/your-profile')
                    ->url()
                    ->required(),

                Forms\Components\TextInput::make('whatsapp')
                    ->label('WhatsApp Number')
                    ->placeholder('+201234567890')
                    ->tel()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),

                Tables\Columns\TextColumn::make('facebook')
                    ->label('Facebook')
                    ->url(fn($record) => $record->facebook)
                    ->limit(30) // اختياري: لجعل الرابط مختصرًا
                    ->openUrlInNewTab(), // يفتح الرابط في تبويب جديد

                Tables\Columns\TextColumn::make('twitter')
                    ->label('Twitter')
                    ->url(fn($record) => $record->twitter)
                    ->limit(30)
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('linkedin')
                    ->label('LinkedIn')
                    ->url(fn($record) => $record->linkedin)
                    ->limit(30)
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('whatsapp')
                    ->label('WhatsApp')
                    ->url(fn($record) => 'https://wa.me/' . str_replace(['+', ' '], '', $record->whatsapp))
                    ->limit(20)
                    ->color('success')
                    ->openUrlInNewTab(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
//                Tables\Actions\DeleteAction::make(),
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
            'index'     => Pages\ListSocialLinks::route('/'),
            'create'    => Pages\CreateSocialLinks::route('/create'),
            'edit'      => Pages\EditSocialLinks::route('/{record}/edit'),
        ];
    }
}
