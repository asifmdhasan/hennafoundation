<?php

namespace App\Filament\Resources\Media;

use App\Filament\Resources\Media\Pages\ManageMedia;
use App\Models\BlogPost;
use App\Models\Media;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Media';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Upload')
                    ->schema([
                        FileUpload::make('path')
                            ->label('Image file')
                            ->disk('public')
                            ->directory('media')
                            ->visibility('public')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                            ->maxSize(5120)
                            ->storeFileNamesIn('original_filename')
                            ->preventFilePathTampering()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->helperText('JPEG, PNG, WebP, or GIF; maximum 5 MB. Stored with a generated filename on the public disk.'),
                        Hidden::make('original_filename'),
                        TextInput::make('alt_text')
                            ->maxLength(255)
                            ->helperText('Describe the image for people using screen readers.'),
                        Textarea::make('caption')->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')->disk('public')->label('Preview'),
                TextColumn::make('original_filename')->label('File name')->searchable()->sortable(),
                TextColumn::make('mime_type')->label('Type')->toggleable(),
                TextColumn::make('size_bytes')->label('Size')->numeric()->sortable()
                    ->formatStateUsing(fn (?int $state): string => $state === null ? '—' : number_format($state / 1024, 1).' KB'),
                TextColumn::make('alt_text')->limit(40)->placeholder('No alt text'),
                TextColumn::make('uploader.name')->label('Uploaded by')->placeholder('—'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(fn (array $data, Media $record): array => static::storeUploadMetadata($data, $record)),
                DeleteAction::make()
                    ->disabled(fn (Media $record): bool => static::isReferenced($record))
                    ->tooltip('Remove this image from pages, posts, and projects before deleting it.')
                    ->after(fn (Media $record) => Storage::disk($record->disk)->delete($record->path)),
            ]);
    }

    public static function storeUploadMetadata(array $data, ?Media $existing = null): array
    {
        $path = $data['path'] ?? null;

        if (! is_string($path) || $path === '') {
            if ($existing !== null) {
                $data['path'] = $existing->path;
                $data['disk'] = $existing->disk;
                $data['original_filename'] = $existing->original_filename;
                $data['mime_type'] = $existing->mime_type;
                $data['size_bytes'] = $existing->size_bytes;
                $data['uploaded_by'] = $existing->uploaded_by;

                return $data;
            }

            throw ValidationException::withMessages([
                'path' => 'Please choose an image to upload.',
            ]);
        }

        if ($existing !== null && $path === $existing->path) {
            $data['disk'] = $existing->disk;
            $data['original_filename'] = $existing->original_filename;
            $data['mime_type'] = $existing->mime_type;
            $data['size_bytes'] = $existing->size_bytes;

            return $data;
        }

        if (! str_starts_with($path, 'media/')) {
            throw ValidationException::withMessages([
                'path' => 'Choose an uploaded file from the media upload field.',
            ]);
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            throw ValidationException::withMessages([
                'path' => 'The uploaded file could not be found. Please upload it again.',
            ]);
        }

        $data['disk'] = 'public';
        $data['mime_type'] = $disk->mimeType($path) ?? 'application/octet-stream';
        $data['size_bytes'] = $disk->size($path);

        $originalFilename = $data['original_filename'] ?? null;
        if (is_array($originalFilename)) {
            $originalFilename = reset($originalFilename) ?: null;
        }
        $data['original_filename'] = is_string($originalFilename) && $originalFilename !== ''
            ? $originalFilename
            : basename($path);
        $data['uploaded_by'] = $existing?->uploaded_by ?? auth()->id();

        return $data;
    }

    public static function isReferenced(Media $media): bool
    {
        return Page::query()->where('og_image_media_id', $media->getKey())->exists()
            || BlogPost::query()->where('og_image_media_id', $media->getKey())->exists()
            || $media->pages()->exists()
            || $media->projects()->exists()
            || $media->blogPosts()->exists();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMedia::route('/'),
        ];
    }
}
