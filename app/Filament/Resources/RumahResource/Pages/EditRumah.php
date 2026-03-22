<?php

namespace App\Filament\Resources\RumahResource\Pages;

use App\Filament\Resources\RumahResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRumah extends EditRecord
{
    protected static string $resource = RumahResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
