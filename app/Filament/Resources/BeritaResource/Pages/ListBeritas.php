<?php

namespace App\Filament\Resources\BeritaResource\Pages;

use App\Filament\Resources\BeritaResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBeritas extends ListRecords
{
    protected static string $resource = BeritaResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
