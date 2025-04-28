<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserVehicleResource\Pages;
use App\Models\UserVehicle;
use App\Models\VehicleType;
use App\Models\VehicleModel;
use App\Models\VehicleBrand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;

class UserVehicleResource extends Resource
{
    protected static ?string $model = UserVehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function getNavigationLabel(): string
    {
        return __('مركبات المستخدمين');
    }

    public static function getModelLabel(): string
    {
        return __('مركبة المستخدم');
    }

    public static function getPluralModelLabel(): string
    {
        return __('مركبات المستخدمين');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label(__('المستخدم'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('vehicle_type_id')
                    ->label(__('نوع المركبة'))
                    ->options(
                        VehicleType::active()
                            ->get()
                            ->pluck('title', 'id')
                    )
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('vehicle_model_id')
                    ->label(__('طراز المركبة'))
                    ->relationship('vehicleModel', 'title')
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('vehicle_brand_id')
                    ->label(__('العلامة التجارية'))
                    ->relationship('vehicleBrand', 'name')
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('vehicle_manufacture_year_id')
                    ->label(__('سنة التصنيع'))
                    ->relationship('vehicleManufactureYear', 'year')
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('letters_ar')
                    ->label(__('الحروف بالعربية'))
                    ->maxLength(255)
                    ->required(),

                Forms\Components\TextInput::make('numbers_ar')
                    ->label(__('الأرقام بالعربية'))
                    ->maxLength(255)
                    ->required(),

                Forms\Components\TextInput::make('letters_en')
                    ->label(__('الحروف بالإنجليزية'))
                    ->maxLength(255)
                    ->required(),

                Forms\Components\TextInput::make('numbers_en')
                    ->label(__('الأرقام بالإنجليزية'))
                    ->maxLength(255)
                    ->required(),

                Forms\Components\DatePicker::make('checkup_date')
                    ->label(__('تاريخ الفحص'))
                    ->nullable(),

                Forms\Components\TextInput::make('color')
                    ->label(__('اللون'))
                    ->maxLength(50)
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('المستخدم'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('type.title')
                    ->label(__('نوع المركبة'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('model.title')
                    ->label(__('طراز المركبة'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('brand.title')
                    ->label(__('العلامة التجارية'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('letters_ar')
                    ->label(__('الحروف بالعربية'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('numbers_ar')
                    ->label(__('الأرقام بالعربية'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('letters_en')
                    ->label(__('الحروف بالإنجليزية'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('numbers_en')
                    ->label(__('الأرقام بالإنجليزية'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('color')
                    ->label(__('اللون'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('checkup_date')
                    ->label(__('تاريخ الفحص'))
                    ->date(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('تاريخ الإنشاء'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('تاريخ التحديث'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // فلاتر للبحث والتصفية
                SelectFilter::make('user_id')
                    ->label(__('تصفية حسب المستخدم'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('checkup_date')
                    ->label(__('تصفية حسب تاريخ الفحص'))
                    ->form([
                        DatePicker::make('from_date')
                            ->label(__('من تاريخ')),
                        DatePicker::make('until_date')
                            ->label(__('إلى تاريخ')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('checkup_date', '>=', $date),
                            )
                            ->when(
                                $data['until_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('checkup_date', '<=', $date),
                            );
                    }),

                Filter::make('created_at')
                    ->label(__('تصفية حسب تاريخ الإنشاء'))
                    ->form([
                        DatePicker::make('from_date')
                            ->label(__('من تاريخ')),
                        DatePicker::make('until_date')
                            ->label(__('إلى تاريخ')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(__('عرض'))
                    ->icon('heroicon-o-eye'),

                Tables\Actions\EditAction::make()
                    ->label(__('تعديل'))
                    ->icon('heroicon-o-pencil')
                    ->modalHeading(__('تعديل بيانات المركبة'))
                    ->modalSubmitActionLabel(__('حفظ التغييرات')),

                Tables\Actions\DeleteAction::make()
                    ->label(__('حذف'))
                    ->icon('heroicon-o-trash')
                    ->modalHeading(__('حذف المركبة'))
                    ->modalDescription(__('هل أنت متأكد من رغبتك في حذف هذه المركبة؟ لا يمكن التراجع عن هذا الإجراء.'))
                    ->modalSubmitActionLabel(__('نعم، احذف المركبة'))
                    ->modalCancelActionLabel(__('إلغاء')),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label(__('حذف جماعي'))
                    ->icon('heroicon-o-trash')
                    ->modalHeading(__('حذف المركبات المحددة'))
                    ->modalDescription(__('هل أنت متأكد من رغبتك في حذف المركبات المحددة؟ لا يمكن التراجع عن هذا الإجراء.'))
                    ->modalSubmitActionLabel(__('نعم، احذف المركبات المحددة'))
                    ->modalCancelActionLabel(__('إلغاء')),

                Tables\Actions\BulkAction::make('export')
                    ->label(__('تصدير المحدد'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function ($records) {
                        // أكواد التصدير تأتي هنا
                        // يمكنك استخدام $records للوصول للسجلات المحددة
                    })
                    ->requiresConfirmation(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // يمكنك إضافة العلاقات هنا إذا كنت بحاجة إليها
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserVehicles::route('/'),
            'create' => Pages\CreateUserVehicle::route('/create'),
            'edit' => Pages\EditUserVehicle::route('/{record}/edit'),
        ];
    }
}
