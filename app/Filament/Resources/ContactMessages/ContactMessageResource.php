<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\ManageContactMessages;
use App\Models\ContactMessage;
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

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('subject')->searchable()->limit(60),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(self::statusOptions()),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()->schema([
                    TextEntry::make('name'),
                    TextEntry::make('email'),
                    TextEntry::make('subject'),
                    TextEntry::make('message')->columnSpanFull(),
                    TextEntry::make('status')->badge(),
                    TextEntry::make('created_at')->dateTime(),
                ]),
                EditAction::make()
                    ->schema([
                        Select::make('status')
                            ->options(self::statusOptions())
                            ->required()
                            ->rules(['required', Rule::in(ContactMessage::STATUSES)]),
                    ])
                    ->modalHeading('Update message status'),
            ]);
    }

    private static function statusOptions(): array
    {
        return array_combine(ContactMessage::STATUSES, array_map(ucfirst(...), ContactMessage::STATUSES));
    }

    public static function getPages(): array
    {
        return ['index' => ManageContactMessages::route('/')];
    }
}
