<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BeritaResource\Pages;
use App\Models\Berita;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class BeritaResource extends Resource
{
    protected static ?string $model = Berita::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Program & Publikasi';

    protected static ?string $navigationLabel = 'Berita';

    protected static ?string $slug = 'berita';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('judul')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),
                    Placeholder::make('slug_preview')
                        ->label('Slug')
                        ->content(fn (?Berita $record, callable $get) => $record?->slug ?? \Illuminate\Support\Str::slug($get('judul') ?? ''))
                        ->columnSpan(2),
                    TextInput::make('kategori')
                        ->required()
                        ->maxLength(255),
                    Select::make('status_publish')
                        ->options([
                            'draft' => 'Draft',
                            'publish' => 'Publish',
                        ])
                        ->required(),
                    Textarea::make('ringkasan')
                        ->required()
                        ->rows(3)
                        ->columnSpan(2),
                    Textarea::make('isi')
                        ->required()
                        ->rows(10)
                        ->columnSpan(2),
                    FileUpload::make('gambar')
                        ->image()
                        ->disk('public')
                        ->directory('berita')
                        ->columnSpan(2),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('gambar')
                    ->disk('public')
                    ->square(),
                TextColumn::make('judul')
                    ->searchable()
                    ->limit(45),
                BadgeColumn::make('kategori'),
                BadgeColumn::make('status_publish')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'publish',
                    ]),
                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('kategori'),
                SelectFilter::make('status_publish')
                    ->options([
                        'draft' => 'Draft',
                        'publish' => 'Publish',
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
            'index' => Pages\ListBeritas::route('/'),
            'create' => Pages\CreateBerita::route('/create'),
            'edit' => Pages\EditBerita::route('/{record}/edit'),
        ];
    }    
}
