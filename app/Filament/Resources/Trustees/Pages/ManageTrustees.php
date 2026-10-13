<?php

namespace App\Filament\Resources\Trustees\Pages;

use App\Filament\Resources\Trustees\TrusteeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTrustees extends ManageRecords
{
    protected static string $resource = TrusteeResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
