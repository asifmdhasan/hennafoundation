<?php

namespace App\Filament\Resources\PartnershipApplications\Pages;

use App\Filament\Resources\PartnershipApplications\PartnershipApplicationResource;
use Filament\Resources\Pages\ManageRecords;

class ManagePartnershipApplications extends ManageRecords
{
    protected static string $resource = PartnershipApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
