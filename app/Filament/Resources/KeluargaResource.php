<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KeluargaResource\Pages;
use App\Models\Keluarga;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class KeluargaResource extends Resource
{
    protected static ?string $model = Keluarga::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Data Keluarga';

    protected static ?string $modelLabel = 'Keluarga';

    protected static ?string $pluralModelLabel = 'Data Keluarga';

    protected static ?string $slug = 'keluarga';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('kode_keluarga')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    TextInput::make('nama_kepala_keluarga')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('alamat')
                        ->required()
                        ->rows(3)
                        ->columnSpan(2),
                    Select::make('lindongan')
                        ->options([
                            'Lindongan 1' => 'Lindongan 1',
                            'Lindongan 2' => 'Lindongan 2',
                            'Lindongan 3' => 'Lindongan 3',
                            'Lindongan 4' => 'Lindongan 4',
                        ])
                        ->required(),
                    TextInput::make('jumlah_anggota')
                        ->numeric()
                        ->required()
                        ->minValue(1),
                    TextInput::make('status_ekonomi')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('pekerjaan_utama')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('kategori_rumah')
                        ->required()
                        ->maxLength(255),
                    Toggle::make('status_dtks')
                        ->label('Status DTKS')
                        ->inline(false),
                    Textarea::make('catatan_petugas')
                        ->rows(4)
                        ->columnSpan(2),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_keluarga')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_kepala_keluarga')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('lindongan')
                    ->colors(['primary']),
                TextColumn::make('jumlah_anggota')
                    ->label('Anggota')
                    ->sortable(),
                TextColumn::make('status_ekonomi')
                    ->searchable(),
                TextColumn::make('pekerjaan_utama')
                    ->toggleable(),
                IconColumn::make('status_dtks')
                    ->label('DTKS')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->since()
                    ->label('Diperbarui'),
            ])
            ->filters([
                SelectFilter::make('lindongan')
                    ->options([
                        'Lindongan 1' => 'Lindongan 1',
                        'Lindongan 2' => 'Lindongan 2',
                        'Lindongan 3' => 'Lindongan 3',
                        'Lindongan 4' => 'Lindongan 4',
                    ]),
                SelectFilter::make('status_dtks')
                    ->options([
                        '1' => 'DTKS',
                        '0' => 'Non DTKS',
                    ])
                    ->query(function ($query, array $data) {
                        if (($data['value'] ?? null) === null || $data['value'] === '') {
                            return $query;
                        }

                        return $query->where('status_dtks', $data['value']);
                    }),
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
            'index' => Pages\ListKeluargas::route('/'),
            'create' => Pages\CreateKeluarga::route('/create'),
            'edit' => Pages\EditKeluarga::route('/{record}/edit'),
        ];
    }    
}
