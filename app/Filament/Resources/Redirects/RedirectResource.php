<?php

namespace App\Filament\Resources\Redirects;

use App\Filament\Resources\Redirects\Pages\ManageRedirects;
use App\Models\Redirect;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnLeft;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Legacy URL redirect')
                    ->columns(2)
                    ->description('Only add verified legitimate legacy URLs. Do not redirect suspected spam URLs to charity pages.')
                    ->schema([
                        TextInput::make('old_path')
                            ->label('Old path')
                            ->placeholder('/old-page/')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('new_path')
                            ->label('New path')
                            ->placeholder('/current-page/')
                            ->required()
                            ->maxLength(255),
                        Select::make('status_code')
                            ->options([
                                301 => '301 — Permanent',
                                302 => '302 — Temporary',
                                307 => '307 — Temporary (preserve method)',
                                308 => '308 — Permanent (preserve method)',
                            ])
                            ->in([301, 302, 307, 308])
                            ->default(301)
                            ->required(),
                        Toggle::make('is_active')->default(true)->required(),
                        Textarea::make('reason')->rows(3)->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('old_path')->searchable()->sortable(),
                TextColumn::make('new_path')->searchable(),
                TextColumn::make('status_code')->badge()->sortable(),
                IconColumn::make('is_active')->boolean()->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
                SelectFilter::make('status_code')->options([
                    301 => '301 — Permanent',
                    302 => '302 — Temporary',
                    307 => '307 — Temporary (preserve method)',
                    308 => '308 — Permanent (preserve method)',
                ]),
            ])
            ->defaultSort('old_path')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRedirects::route('/'),
        ];
    }
}
