<?php

namespace App\Filament\Resources\PartnershipApplications;

use App\Filament\Resources\PartnershipApplications\Pages\ManagePartnershipApplications;
use App\Models\PartnershipApplication;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class PartnershipApplicationResource extends Resource
{
    protected static ?string $model = PartnershipApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')->searchable()->sortable(),
                TextColumn::make('organisation_name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(self::statusOptions()),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()->schema([
                    TextEntry::make('full_name'),
                    TextEntry::make('organisation_name'),
                    TextEntry::make('phone'),
                    TextEntry::make('email'),
                    TextEntry::make('home_telephone'),
                    TextEntry::make('address_1'),
                    TextEntry::make('address_2')->placeholder('—'),
                    TextEntry::make('city'),
                    TextEntry::make('postal_code'),
                    TextEntry::make('status')->badge(),
                    TextEntry::make('created_at')->dateTime(),
                ]),
                EditAction::make()
                    ->schema([
                        Select::make('status')
                            ->options(self::statusOptions())
                            ->required()
                            ->rules(['required', Rule::in(PartnershipApplication::STATUSES)]),
                    ])
                    ->modalHeading('Update partnership application status'),
            ]);
    }

    private static function statusOptions(): array
    {
        return array_combine(PartnershipApplication::STATUSES, array_map(ucfirst(...), PartnershipApplication::STATUSES));
    }

    public static function getPages(): array
    {
        return ['index' => ManagePartnershipApplications::route('/')];
    }
}
