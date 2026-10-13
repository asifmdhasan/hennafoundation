<?php

namespace App\Filament\Resources\BlogPosts;

use App\Filament\Resources\BlogPosts\Pages\ManageBlogPosts;
use App\Models\BlogPost;
use App\Models\Media;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
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

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Post content')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
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
                            ->helperText('You can edit this URL. Existing published URLs are not changed when the title changes.'),
                        Textarea::make('excerpt')->rows(3)->columnSpanFull(),
                        FileUpload::make('featured_image_path')
                            ->label('Featured image')
                            ->image()
                            ->disk('public')
                            ->directory('blog')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->imageEditor()
                            ->helperText('Upload the image shown on the public blog card. Maximum 5 MB.')
                            ->columnSpanFull(),
                        RichEditor::make('content')->columnSpanFull(),
                    ]),
                Section::make('Publishing and organization')
                    ->columns(2)
                    ->schema([
                        Select::make('author_id')
                            ->label('Author')
                            ->relationship('author', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        Select::make('status')
                            ->options(['draft' => 'Draft', 'published' => 'Published'])
                            ->in(['draft', 'published'])
                            ->default('draft')
                            ->helperText('Only Published posts appear publicly. A future publish date keeps the post hidden until then.')
                            ->required(),
                        DateTimePicker::make('published_at')
                            ->timezone('Asia/Dhaka')
                            ->nullable(),
                    ]),
                Section::make('Search engine and social sharing')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')->maxLength(255),
                        TextInput::make('canonical_url')->url()->maxLength(255),
                        Textarea::make('meta_description')->rows(3),
                        TextInput::make('og_title')->maxLength(255),
                        Textarea::make('og_description')->rows(3),
                        Select::make('og_image_media_id')
                            ->label('Open Graph image')
                            ->relationship('ogImage', 'original_filename')
                            ->getOptionLabelFromRecordUsing(fn (Media $record): string => $record->original_filename)
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('author.name')->label('Author')->sortable()->searchable()->placeholder('—'),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('published_at')->dateTime(timezone: 'Asia/Dhaka')->sortable()->placeholder('Not scheduled'),
                TextColumn::make('created_at')->dateTime(timezone: 'Asia/Dhaka')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published']),
                SelectFilter::make('author_id')->label('Author')->relationship('author', 'name')->searchable()->preload(),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => ManageBlogPosts::route('/'),
        ];
    }
}
