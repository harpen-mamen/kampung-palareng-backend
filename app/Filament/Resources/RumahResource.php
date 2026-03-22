<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RumahResource\Pages;
use App\Models\Keluarga;
use App\Models\Rumah;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class RumahResource extends Resource
{
    protected static ?string $model = Rumah::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Data Rumah';

    protected static ?string $modelLabel = 'Rumah';

    protected static ?string $pluralModelLabel = 'Data Rumah';

    protected static ?string $slug = 'rumah';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    Select::make('keluarga_id')
                        ->label('Keluarga')
                        ->relationship('keluarga', 'kode_keluarga')
                        ->getOptionLabelFromRecordUsing(fn (Keluarga $record): string => "{$record->kode_keluarga} - {$record->nama_kepala_keluarga}")
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set): void {
                            $keluarga = Keluarga::find($state);
                            $rumah = Rumah::where('keluarga_id', $state)->first();

                            if (! $keluarga) {
                                return;
                            }

                            $set('alamat_singkat', $rumah?->alamat_singkat ?? $keluarga->alamat);
                            $set('lindongan', $rumah?->lindongan ?? $keluarga->lindongan);
                            $set('kategori_rumah', $rumah?->kategori_rumah ?? $keluarga->kategori_rumah);
                            $set('jumlah_penghuni', $rumah?->jumlah_penghuni ?? $keluarga->jumlah_anggota);

                            if ($rumah?->latitude) {
                                $set('latitude', $rumah->latitude);
                            }

                            if ($rumah?->longitude) {
                                $set('longitude', $rumah->longitude);
                            }

                            if ($rumah?->catatan_petugas) {
                                $set('catatan_petugas', $rumah->catatan_petugas);
                            }
                        })
                        ->required(),
                    Select::make('lindongan')
                        ->options([
                            'Lindongan 1' => 'Lindongan 1',
                            'Lindongan 2' => 'Lindongan 2',
                            'Lindongan 3' => 'Lindongan 3',
                            'Lindongan 4' => 'Lindongan 4',
                        ])
                        ->required(),
                    TextInput::make('alamat_singkat')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('kategori_rumah')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('latitude')
                        ->numeric()
                        ->required(),
                    TextInput::make('longitude')
                        ->numeric()
                        ->required(),
                    TextInput::make('jumlah_penghuni')
                        ->numeric()
                        ->required()
                        ->minValue(1),
                    FileUpload::make('foto_rumah')
                        ->image()
                        ->disk('public')
                        ->directory('rumah'),
                    Textarea::make('catatan_petugas')
                        ->rows(4)
                        ->columnSpan(2),
                    Placeholder::make('hint_koordinat')
                        ->label('Tips')
                        ->content('Pilih keluarga terlebih dahulu, lalu lengkapi koordinat rumah agar titik bisa muncul di peta.')
                        ->columnSpan(2),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('keluarga.kode_keluarga')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('keluarga.nama_kepala_keluarga')
                    ->label('Kepala Keluarga')
                    ->searchable(),
                TextColumn::make('lindongan')
                    ->badge(),
                TextColumn::make('jumlah_penghuni')
                    ->label('Penghuni')
                    ->sortable(),
                TextColumn::make('latitude')
                    ->limit(10),
                TextColumn::make('longitude')
                    ->limit(10),
                ImageColumn::make('foto_rumah')
                    ->disk('public')
                    ->square(),
            ])
            ->filters([
                SelectFilter::make('lindongan')
                    ->options([
                        'Lindongan 1' => 'Lindongan 1',
                        'Lindongan 2' => 'Lindongan 2',
                        'Lindongan 3' => 'Lindongan 3',
                        'Lindongan 4' => 'Lindongan 4',
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
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRumahs::route('/'),
            'create' => Pages\CreateRumah::route('/create'),
            'edit' => Pages\EditRumah::route('/{record}/edit'),
        ];
    }    
}
