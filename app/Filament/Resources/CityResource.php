<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CityResource\Pages;
use App\Filament\Resources\CityResource\RelationManagers;
use App\Models\City;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $recordTitleAttribute = 'name:en';

    protected static ?string $activeNavigationIcon = 'heroicon-o-chevron-double-right';

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Cities' : 'المدن';
    }

    public static function getPluralLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Cities' : 'المدن';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Cities' : 'المدن';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'en' ? 'City' : 'مدينة';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('is_active')
                    ->label(app()->getLocale() === 'en' ? 'Active' : 'مفعّلة')
                    ->default(true),

                Forms\Components\Section::make(app()->getLocale() === 'en' ? 'Translations' : 'الترجمات')
                    ->schema([
                        Forms\Components\TextInput::make('name:ar')
                            ->label(app()->getLocale() === 'en' ? 'Name in Arabic' : 'الاسم بالعربية')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('name:en')
                            ->label(app()->getLocale() === 'en' ? 'Name in English' : 'الاسم بالإنجليزية')
                            ->required()
                            ->maxLength(255),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label(app()->getLocale() === 'en' ? 'Active' : 'مفعّلة'),

                Tables\Columns\BadgeColumn::make('districts_count')
                    ->label(app()->getLocale() === 'en' ? 'Districts' : 'الأحياء')
                    ->counts('districts'),

                Tables\Columns\TextColumn::make('name')
                    ->label(app()->getLocale() === 'en' ? 'Name' : 'الاسم')
                    ->formatStateUsing(fn($state, $record) => $record->translate(app()->getLocale())?->name)
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->whereTranslationLike('name', "%{$search}%", app()->getLocale());
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('الحالة')
                    ->options([
                        true => 'مفعّلة',
                        false => 'غير مفعّلة',
                    ]),

                Tables\Filters\Filter::make('search_by_name')
                    ->label(app()->getLocale() === 'en' ? 'Search by Name' : 'البحث بالاسم')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label(app()->getLocale() === 'en' ? 'Name' : 'الاسم')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['name'])) {
                            $query->whereTranslationLike('name', "%{$data['name']}%", app()->getLocale());
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record) {
                        if ($record->districts()->exists()) {
                            throw new \Exception('لا يمكن حذف هذه المدينة لأنها تحتوي على أحياء مرتبطة بها.');
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
            // يمكن إضافة العلاقات هنا
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCities::route('/'),
            'create' => Pages\CreateCity::route('/create'),
            'edit' => Pages\EditCity::route('/{record}/edit'),
        ];
    }
}
