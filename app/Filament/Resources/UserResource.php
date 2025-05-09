<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $activeNavigationIcon = 'heroicon-o-chevron-double-right';

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Users' : 'المستخدمين';
    }

    public static function getPluralLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Users' : 'المستخدمين';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Users' : 'المستخدمين';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'en' ? 'User' : 'مستخدم';
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(app()->getLocale() === 'en' ? 'Name' : 'الاسم')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label(app()->getLocale() === 'en' ? 'Email' : 'البريد الإلكتروني')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->rule(function ($livewire) {
                        return (new Unique('users', 'email'))
                            ->where(function ($query) use ($livewire) {
                                if ($livewire->record) {
                                    $query->where('id', '!=', $livewire->record->id);
                                }
                                return $query;
                            });
                    }),

                Forms\Components\TextInput::make('phone')
                    ->label(app()->getLocale() === 'en' ? 'Phone' : 'الهاتف')
                    ->tel()
                    ->nullable()
                    ->maxLength(15),

                Forms\Components\Select::make('role')
                    ->label(app()->getLocale() === 'en' ? 'Role' : 'الدور')
                    ->options([
                        'admin' => app()->getLocale() === 'en' ? 'Admin' : 'مدير',
                        'user' => app()->getLocale() === 'en' ? 'User' : 'مستخدم',
                        'provider' => app()->getLocale() === 'en' ? 'Provider' : 'مزود',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('password')
                    ->label(app()->getLocale() === 'en' ? 'Password' : 'كلمة المرور')
                    ->password()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->minLength(8),

                Forms\Components\FileUpload::make('profile_picture')
                    ->label(app()->getLocale() === 'en' ? 'Profile Picture' : 'الصورة الشخصية')
                    ->image()
                    ->nullable(),

                Forms\Components\TextInput::make('invitation_code')
                    ->label(app()->getLocale() === 'en' ? 'Invitation Code' : 'كود الدعوة')
                    ->nullable()
                    ->maxLength(255),

                Forms\Components\TextInput::make('balance')
                    ->label(app()->getLocale() === 'en' ? 'Balance' : 'المحفظة')
                    ->numeric()
                    ->default(0)
                    ->suffix(app()->getLocale() === 'en' ? ' SAR' : ' ريال'),

                Forms\Components\Textarea::make('address')
                    ->label(app()->getLocale() === 'en' ? 'Address' : 'العنوان')
                    ->nullable()
                    ->maxLength(255),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_picture')
                    ->label(app()->getLocale() === 'en' ? 'Profile Picture' : 'الصورة الشخصية')
                    ->circular(),

                Tables\Columns\BadgeColumn::make('role')
                    ->label(app()->getLocale() === 'en' ? 'Role' : 'الدور')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(app()->getLocale() === 'en' ? 'Name' : 'الاسم')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label(app()->getLocale() === 'en' ? 'Email' : 'البريد الإلكتروني')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label(app()->getLocale() === 'en' ? 'Phone' : 'الهاتف')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\BooleanColumn::make('email_verified_at')
                    ->label(app()->getLocale() === 'en' ? 'Account Verified' : 'الحساب موثق')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\TextColumn::make('password')
                    ->label('Password')
                    ->hidden(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(app()->getLocale() === 'en' ? 'Created At' : 'تاريخ الإنضمام')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(app()->getLocale() === 'en' ? 'Updated At' : 'تاريخ التحديث')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('invitation_code')
                    ->label(app()->getLocale() === 'en' ? 'Invitation Code' : 'كود الدعوة')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('balance')
                    ->label(app()->getLocale() === 'en' ? 'Balance' : 'المحفظة')
                    ->sortable()
                    ->suffix(app()->getLocale() === 'en' ? ' SAR' : ' ريال'),

                Tables\Columns\TextColumn::make('address')
                    ->label(app()->getLocale() === 'en' ? 'Address' : 'العنوان')
                    ->getStateUsing(function ($record) {
                        return $record->address ?? '--';
                    })
                    ->badge(function ($record) {
                        return $record->address ? false : true;
                    })
                    ->color(function ($record) {
                        return $record->address ? '' : 'danger';
                    })
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label(app()->getLocale() === 'en' ? 'Filter by Role' : 'فلتر حسب الدور')
                    ->options([
                        'admin' => app()->getLocale() === 'en' ? 'Admin' : 'مدير',
                        'user' => app()->getLocale() === 'en' ? 'User' : 'مستخدم',
                        'provider' => app()->getLocale() === 'en' ? 'Provider' : 'مزود',
                    ]),

                TernaryFilter::make('email_verified_at')
                    ->label(app()->getLocale() === 'en' ? 'Filter by Verification' : 'فلتر حسب تأكيد الحساب')
                    ->nullable()
                    ->placeholder(app()->getLocale() === 'en' ? 'All Users' : 'جميع المستخدمين')
                    ->trueLabel(app()->getLocale() === 'en' ? 'Verified users' : 'المستخدمين الموثقين')
                    ->falseLabel(app()->getLocale() === 'en' ? 'Unverified users' : 'المستخدمين غير الموثقين'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->tooltip('Edit'),

                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->tooltip('Delete'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->color('danger'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}