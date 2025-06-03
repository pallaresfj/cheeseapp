<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-swatch';
    protected static ?string $navigationGroup = 'Usuario';
    protected static ?string $label = 'Usuario';
    protected static ?string $pluralLabel = 'Usuarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Correo')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Select::make('role')
                    ->label('Rol')
                    ->options([
                        'soporte' => 'Soporte',
                        'admin' => 'Administrador',
                        'sucursal' => 'Sucursal',
                        'supplier' => 'Proveedor',
                        'customer' => 'Cliente',
                    ])
                    ->default('supplier')
                    ->native(false)
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255)
                    ->visible(fn (string $context): bool => $context === 'create'),
                Forms\Components\TextInput::make('identification')
                    ->label('Identificación')
                    ->maxLength(255),
                Forms\Components\TextInput::make('address')
                    ->label('Dirección')
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label('Verificado')
                    ->default(now())
                    ->visibleOn('edit'),
                FileUpload::make('avatar')
                    ->avatar(),
                Forms\Components\Select::make('roles')
                    ->label('Roles de acceso')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload()
                    ->native(false)
                    ->searchable(),
                Forms\Components\Toggle::make('status')
                    ->label('Activo')
                    ->inline(false)
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->extremePaginationLinks()
            ->striped()
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles de acceso')
                    ->badge()
                    ->separator(',')
                    ->sortable(),
            ])
            ->groups([
                Tables\Grouping\Group::make('role')
                    ->label('Rol')
                    ->getTitleFromRecordUsing(fn ($record) => match ($record->role) {
                        'soporte' => 'Soporte',
                        'admin' => 'Administrador',
                        'sucursal' => 'Sucursal',
                        'supplier' => 'Proveedor',
                        'customer' => 'Cliente',
                        default => ucfirst($record->role),
                    })
                    ->collapsible()
            ])
            ->defaultGroup('role')
            ->groupingSettingsHidden()
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Rol')
                    ->options([
                        'soporte' => 'Soporte',
                        'admin' => 'Administrador',
                        'sucursal' => 'Sucursal',
                        'supplier' => 'Proveedor',
                        'customer' => 'Cliente',
                    ])
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->tooltip('Editar')
                    ->iconSize('h-6 w-6'),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->tooltip('Borrar')
                    ->iconSize('h-6 w-6'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
