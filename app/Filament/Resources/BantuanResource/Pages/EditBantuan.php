<?php

namespace App\Filament\Resources\BantuanResource\Pages;

use App\Filament\Resources\BantuanResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBantuan extends EditRecord
{
    protected static string $resource = BantuanResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
