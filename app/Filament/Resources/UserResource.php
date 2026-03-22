<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Sistem';

    protected static ?string $navigationLabel = 'Pengguna Admin';

    protected static ?string $slug = 'pengguna';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    TextInput::make('phone')
                        ->maxLength(50),
                    Select::make('lindongan')
                        ->options([
                            'Lindongan 1' => 'Lindongan 1',
                            'Lindongan 2' => 'Lindongan 2',
                            'Lindongan 3' => 'Lindongan 3',
                            'Lindongan 4' => 'Lindongan 4',
                        ]),
                    TextInput::make('alamat')
                        ->maxLength(255)
                        ->columnSpan(2),
                    Select::make('role')
                        ->options([
                            'super_admin' => 'Super Admin',
                            'operator' => 'Operator',
                            'verifikator' => 'Verifikator',
                            'pimpinan' => 'Pimpinan',
                            'warga' => 'Warga',
                        ])
                        ->required(),
                    TextInput::make('password')
                        ->password()
                        ->maxLength(255)
                        ->required(fn (string $context): bool => $context === 'create')
                        ->dehydrated(fn ($state): bool => filled($state))
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->sortable(),
                BadgeColumn::make('role')
                    ->colors([
                        'danger' => 'super_admin',
                        'primary' => 'operator',
                        'warning' => 'verifikator',
                        'success' => 'pimpinan',
                        'secondary' => 'warga',
                    ]),
                TextColumn::make('lindongan'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'operator' => 'Operator',
                        'verifikator' => 'Verifikator',
                        'pimpinan' => 'Pimpinan',
                        'warga' => 'Warga',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'super_admin';
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->role === 'super_admin';
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->role === 'super_admin';
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->role === 'super_admin';
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
