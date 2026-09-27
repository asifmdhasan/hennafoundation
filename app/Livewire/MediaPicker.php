<?php

namespace App\Livewire;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class MediaPicker extends Component
{
    use WithFileUploads;

    public string $statePath;

    public bool $multiple = false;

    public mixed $state = null;

    public bool $isOpen = false;

    public string $activeTab = 'library';

    public string $search = '';

    public array $selectedMediaIds = [];

    public array $uploads = [];

    public array $uploadResults = [];

    public function mount(string $statePath, bool $multiple = false, mixed $state = null): void
    {
        abort_unless(auth()->check(), 403);
        abort_unless(str_starts_with($statePath, 'data.'), 403);

        $this->statePath = $statePath;
        $this->multiple = $multiple;
        $this->state = $state;
    }

    public function open(): void
    {
        abort_unless(auth()->check(), 403);

        $this->selectedMediaIds = collect($this->multiple ? ($this->state ?? []) : [$this->state])
            ->filter(fn ($id): bool => is_numeric($id) && Media::query()->whereKey((int) $id)->exists())
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();
        $this->activeTab = 'library';
        $this->search = '';
        $this->uploadResults = [];
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->uploads = [];
    }

    public function toggleSelection(int $mediaId): void
    {
        abort_unless(Media::query()->whereKey($mediaId)->exists(), 404);

        if (! $this->multiple) {
            $this->selectedMediaIds = [$mediaId];

            return;
        }

        $this->selectedMediaIds = in_array($mediaId, $this->selectedMediaIds, true)
            ? array_values(array_diff($this->selectedMediaIds, [$mediaId]))
            : [...$this->selectedMediaIds, $mediaId];
    }

    public function select(): void
    {
        abort_unless(auth()->check(), 403);

        $ids = collect($this->selectedMediaIds)->map(fn ($id): int => (int) $id)->unique()->values();
        $validIds = Media::query()->whereIn('id', $ids)->pluck('id')->map(fn ($id): int => (int) $id)->all();

        if (count($validIds) !== $ids->count()) {
            throw ValidationException::withMessages(['selectedMediaIds' => 'Please select images from the Media Library.']);
        }

        $this->dispatch('media-picker-selected', statePath: $this->statePath, mediaIds: $this->multiple ? $validIds : array_slice($validIds, 0, 1), multiple: $this->multiple);
        $this->state = $this->multiple ? $validIds : ($validIds[0] ?? null);
        $this->isOpen = false;
    }

    public function removeSelection(): void
    {
        $this->selectedMediaIds = [];
        $this->state = $this->multiple ? [] : null;
        $this->dispatch('media-picker-selected', statePath: $this->statePath, mediaIds: [], multiple: $this->multiple);
    }

    public function uploadSelectedFiles(): void
    {
        abort_unless(auth()->check(), 403);

        if (count($this->uploads) < 1) {
            throw ValidationException::withMessages(['uploads' => 'Choose at least one image to upload.']);
        }

        $createdIds = [];
        $validUploads = [];

        foreach ($this->uploads as $upload) {
            $validator = Validator::make(['upload' => $upload], [
                'upload' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            ], [
                'upload.image' => 'This file is not a valid image.',
                'upload.mimes' => 'Use a JPEG, PNG, WebP, or GIF image.',
                'upload.max' => 'This image must be 5 MB or smaller.',
            ]);

            if ($validator->fails()) {
                $this->uploadResults[] = [
                    'name' => method_exists($upload, 'getClientOriginalName') ? $upload->getClientOriginalName() : 'Selected file',
                    'success' => false,
                    'message' => $validator->errors()->first('upload'),
                ];
                continue;
            }

            $validUploads[] = $upload;
        }

        foreach ($validUploads as $upload) {
            $size = $upload->getSize();
            $hash = hash_file('sha256', $upload->getRealPath());

            $existing = Media::query()
                ->where('size_bytes', $size)
                ->get()
                ->first(function (Media $media) use ($hash): bool {
                    $disk = Storage::disk($media->disk);

                    return $disk->exists($media->path)
                        && hash_file('sha256', $disk->path($media->path)) === $hash;
                });

            if ($existing) {
                $media = $existing;
                $this->uploadResults[] = ['name' => $upload->getClientOriginalName(), 'success' => true, 'message' => 'Already in Media Library; reused existing image.'];
            } else {
                $path = $upload->store('media', 'public');
                $media = Media::query()->create([
                    'disk' => 'public',
                    'path' => $path,
                    'original_filename' => $upload->getClientOriginalName(),
                    'mime_type' => Storage::disk('public')->mimeType($path) ?? $upload->getMimeType(),
                    'size_bytes' => $size,
                    'uploaded_by' => auth()->id(),
                ]);
                $this->uploadResults[] = ['name' => $upload->getClientOriginalName(), 'success' => true, 'message' => 'Uploaded successfully.'];
            }

            $createdIds[] = $media->getKey();
        }

        $this->uploads = [];
        $this->search = '';
        $this->activeTab = 'library';
        if ($createdIds !== []) {
            $this->selectedMediaIds = $this->multiple
                ? array_values(array_unique([...$this->selectedMediaIds, ...$createdIds]))
                : [end($createdIds)];
        }
    }

    public function getLibraryMediaProperty()
    {
        return Media::query()
            ->when(filled($this->search), function ($query): void {
                $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $this->search).'%';
                $query->where(function ($query) use ($term): void {
                    $query->where('original_filename', 'like', $term)
                        ->orWhere('alt_text', 'like', $term)
                        ->orWhere('caption', 'like', $term);
                });
            })
            ->latest('created_at')
            ->get();
    }

    public function imageUrl(Media $media): string
    {
        return Storage::disk($media->disk)->url($media->path);
    }

    public function render()
    {
        return view('livewire.media-picker');
    }
}
