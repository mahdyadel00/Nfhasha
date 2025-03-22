<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpressServiceResource\Pages;
use App\Models\ExpressService;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExpressServiceResource extends Resource
{
    protected static ?string $model = ExpressService::class;

    protected static ?string $navigationIcon        = 'heroicon-o-rectangle-stack';
    protected static ?string $activeNavigationIcon  = 'heroicon-o-chevron-double-right';
    protected static ?string $recordTitleAttribute  = 'name:en';

    public static function getnavigationGroup(): string
    {
        return app()->getLocale() === 'en' ? 'Services' : 'الخدمات';
    }
    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Express Services' : 'الخدمات السريعة';
    }

    public static function getPluralLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Express Services' : 'الخدمات السريعة';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Express Service' : 'الخدمة السريعة';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(app()->getLocale() === 'en' ? 'Translations' : 'الترجمات')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name:en')
                            ->label(app()->getLocale() === 'en' ? 'Name (English)' : 'الاسم (الإنجليزية)')
                            ->required()
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('name:ar')
                            ->label(app()->getLocale() === 'en' ? 'Name (Arabic)' : 'الاسم (العربية)')
                            ->required()
                            ->columnSpan(2),
                    ])->columnSpan(2),

                Forms\Components\Select::make('type')
                    ->label(app()->getLocale() === 'en' ? 'Type' : 'النوع')
                    ->options([
                        'open_locks'                => app()->getLocale() === 'en' ? 'Open Locks' : 'فتح الأقفال',
                        'battery'                   => app()->getLocale() === 'en' ? 'Battery' : 'بطارية',
                        'fuel'                      => app()->getLocale() === 'en' ? 'Fuel' : 'وقود',
                        'puncture'                  => app()->getLocale() === 'en' ? 'Puncture' : 'تعشيق',
                        'tow_truck'                 => app()->getLocale() === 'en' ? 'Tow Truck' : 'سحب السيارة',
                        'periodic_inspections'      => app()->getLocale() === 'en' ? 'Periodic Inspections' : 'الفحص الدوري',
                        'comprehensive_inspections' => app()->getLocale() === 'en' ? 'Comprehensive Inspections' : 'الفحص الشامل',
                        'maintenance'               => app()->getLocale() === 'en' ? 'Maintenance' : 'الصيانة',
                        'car_reservations'          => app()->getLocale() === 'en' ? 'Car Reservations' : 'حواجز السيارات',
                    ])
                    ->required()
                    ->columnSpan(2),

                Forms\Components\TextInput::make('price')
                    ->label(app()->getLocale() === 'en' ? 'Price' : 'السعر')
                    ->required()
                    ->numeric()
                    ->columnSpan(2),

                Forms\Components\TextInput::make('tax')
                    ->label(app()->getLocale() === 'en' ? 'Tax (%)' : 'الضريبة (%)')
                    ->required()
                    ->numeric()
                    ->columnSpan(2),

                Forms\Components\Toggle::make('is_active')
                    ->label(app()->getLocale() === 'en' ? 'Active' : 'مفعّلة')
                    ->default(true)
                    ->columnSpan(2),
            ])
            ->columns(1);
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

                TextColumn::make('type')
                    ->label(app()->getLocale() === 'en' ? 'Type' : 'النوع'),

                TextColumn::make('price')
                    ->label(app()->getLocale() === 'en' ? 'Price' : 'السعر')
                    ->sortable(),

                TextColumn::make('vat')
                    ->label(app()->getLocale() === 'en' ? 'VAT (%)' : 'الضريبة (%)')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' %')

            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(app()->getLocale() === 'en' ? 'Type' : 'النوع')
                    ->options([
                        'open_locks'                => app()->getLocale() === 'en' ? 'Open Locks' : 'فتح الأقفال',
                        'battery'                   => app()->getLocale() === 'en' ? 'Battery' : 'بطارية',
                        'fuel'                      => app()->getLocale() === 'en' ? 'Fuel' : 'وقود',
                        'puncture'                  => app()->getLocale() === 'en' ? 'Puncture' : 'تعشيق',
                        'tow_truck'                 => app()->getLocale() === 'en' ? 'Tow Truck' : 'سحب السيارة',
                        'periodic_inspections'      => app()->getLocale() === 'en' ? 'Periodic Inspections' : 'الفحص الدوري',
                        'comprehensive_inspections' => app()->getLocale() === 'en' ? 'Comprehensive Inspections' : 'الفحص الشامل',
                        'maintenance'               => app()->getLocale() === 'en' ? 'Maintenance' : 'الصيانة',
                        'car_reservations'          => app()->getLocale() === 'en' ? 'Car Reservations' : 'حواجز السيارات',
                    ]),

                SelectFilter::make('is_active')
                    ->label('الحالة')
                    ->options([
                        true    => app()->getLocale() === 'en' ? 'Active' : 'مفعّلة',
                        false   => app()->getLocale() === 'en' ? 'Inactive' : 'غير مفعّلة',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading(__('Delete Confirmation'))
                    ->modalDescription(app()->getLocale() === 'en'
                        ? 'Are you sure you want to delete this record?'
                        : 'هل أنت متأكد أنك تريد حذف هذا السجل؟'
                    ),
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
            'index'     => Pages\ListExpressServices::route('/'),
            'create'    => Pages\CreateExpressService::route('/create'),
            'edit'      => Pages\EditExpressService::route('/{record}/edit'),
        ];
    }
}
