<?php

namespace App\Filament\Resources\PengumumanResource\Pages;

use App\Filament\Resources\PengumumanResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengumuman extends EditRecord
{
    protected static string $resource = PengumumanResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
