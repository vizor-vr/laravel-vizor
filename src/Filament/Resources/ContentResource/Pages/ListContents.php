<?php

namespace Vizor\Laravel\Filament\Resources\ContentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Vizor\Laravel\Filament\Resources\ContentResource;

class ListContents extends ListRecords
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
