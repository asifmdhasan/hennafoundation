<?php

namespace App\Filament\Resources\Trustees;

use App\Filament\Resources\Trustees\Pages\ManageTrustees;
use App\Models\Media;
use App\Models\Trustee;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class TrusteeResource extends Resource
{
    protected static ?string $model = Trustee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Trustees';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Trustee profile')->columns(2)->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                        if (blank($get('slug'))) {
                            $set('slug', Str::slug($state ?? ''));
                        }
                    }),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Profile URL, for example trustee/tasnim-maisha-chowdhury.'),
                TextInput::make('designation')->maxLength(255),
                Select::make('image_media_id')
                    ->label('Profile photo')
                    ->relationship('image', 'original_filename')
                    ->getOptionLabelFromRecordUsing(fn (Media $record): string => $record->original_filename)
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Choose the profile photo from Content → Media.'),
                Textarea::make('content')
                    ->label('Trustee description')
                    ->rows(14)
                    ->extraInputAttributes(['data-tinymce' => 'trustee-description'])
                    ->columnSpanFull(),
            ]),
            Section::make('Profile links')->columns(2)->collapsed()->schema([
                TextInput::make('facebook_url')->label('Facebook')->url()->maxLength(2048),
                TextInput::make('twitter_url')->label('Twitter / X')->url()->maxLength(2048),
                TextInput::make('linkedin_url')->label('LinkedIn')->url()->maxLength(2048),
                TextInput::make('instagram_url')->label('Instagram')->url()->maxLength(2048),
                TextInput::make('other_profile_url')->label('Other profile')->url()->maxLength(2048),
            ]),
            Section::make('Publishing')->columns(3)->schema([
                Select::make('status')
                    ->options(['draft' => 'Draft', 'published' => 'Published'])
                    ->in(['draft', 'published'])
                    ->default('draft')
                    ->required(),
                TextInput::make('sort_order')->numeric()->integer()->default(0)->required(),
                DateTimePicker::make('published_at')->nullable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('designation')->searchable()->placeholder('—'),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('sort_order')->numeric()->sortable(),
                TextColumn::make('published_at')->dateTime()->sortable()->placeholder('Not scheduled'),
            ])
            ->filters([SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published'])])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageTrustees::route('/')];
    }
}
