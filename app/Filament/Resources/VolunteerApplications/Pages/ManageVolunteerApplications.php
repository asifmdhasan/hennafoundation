<?php

namespace App\Filament\Resources\VolunteerApplications\Pages;

use App\Filament\Resources\VolunteerApplications\VolunteerApplicationResource;
use Filament\Resources\Pages\ManageRecords;

class ManageVolunteerApplications extends ManageRecords
{
    protected static string $resource = VolunteerApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
