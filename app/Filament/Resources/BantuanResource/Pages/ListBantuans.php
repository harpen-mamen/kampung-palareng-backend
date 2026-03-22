<?php

namespace App\Filament\Resources\BantuanResource\Pages;

use App\Filament\Resources\BantuanResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBantuans extends ListRecords
{
    protected static string $resource = BantuanResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
