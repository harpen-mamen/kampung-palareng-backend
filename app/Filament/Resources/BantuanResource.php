<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BantuanResource\Pages;
use App\Models\Bantuan;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class BantuanResource extends Resource
{
    protected static ?string $model = Bantuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'Program & Publikasi';

    protected static ?string $navigationLabel = 'Bantuan';

    protected static ?string $slug = 'bantuan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('nama_bantuan')->required()->maxLength(255),
                    TextInput::make('kategori')->required()->maxLength(255),
                    TextInput::make('sumber')->required()->maxLength(255),
                    TextInput::make('periode')->required()->maxLength(255),
                    TextInput::make('status')->required()->maxLength(255),
                    Textarea::make('deskripsi')->rows(4)->columnSpan(2),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_bantuan')->searchable()->sortable(),
                BadgeColumn::make('kategori'),
                TextColumn::make('sumber')->searchable(),
                TextColumn::make('periode'),
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'aktif',
                        'secondary' => 'arsip',
                    ]),
            ])
            ->filters([
                SelectFilter::make('kategori'),
                SelectFilter::make('status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListBantuans::route('/'),
            'create' => Pages\CreateBantuan::route('/create'),
            'edit' => Pages\EditBantuan::route('/{record}/edit'),
        ];
    }    
}
