<div class="hf-media-picker-field">
    @php
        $currentIds = collect($multiple ? ($state ?? []) : [$state])->filter()->map(fn ($id) => (int) $id)->all();
        $currentMedia = \App\Models\Media::query()->whereIn('id', $currentIds)->get();
    @endphp

    <div class="hf-media-picker-current">
        @forelse ($currentMedia as $media)
            <img src="{{ $this->imageUrl($media) }}" alt="{{ $media->alt_text ?: $media->original_filename }}">
        @empty
            <span>No image selected</span>
        @endforelse
        <div class="hf-media-picker-current-actions">
            <button type="button" wire:click="open">{{ $currentMedia->isEmpty() ? 'Select image' : 'Change image'.($multiple ? 's' : '') }}</button>
            @if ($currentMedia->isNotEmpty())
                <button type="button" class="hf-media-picker-remove" wire:click="removeSelection">Remove</button>
            @endif
        </div>
    </div>

    @if ($isOpen)
        <div class="hf-media-picker-backdrop" wire:key="media-picker-modal-{{ $statePath }}" role="presentation">
            <section class="hf-media-picker-modal" role="dialog" aria-modal="true" aria-labelledby="hf-media-picker-title">
                <header class="hf-media-picker-header">
                    <h2 id="hf-media-picker-title">Insert Media</h2>
                    <button type="button" aria-label="Close media picker" wire:click="close">×</button>
                </header>

                <nav class="hf-media-picker-tabs" aria-label="Media picker tabs">
                    <button type="button" @class(['is-active' => $activeTab === 'library']) wire:click="$set('activeTab', 'library')">Media Library</button>
                    <button type="button" @class(['is-active' => $activeTab === 'upload']) wire:click="$set('activeTab', 'upload')">Upload Files</button>
                </nav>

                @if ($activeTab === 'library')
                    <div class="hf-media-picker-library">
                        <label class="hf-media-picker-search">
                            <span class="sr-only">Search media</span>
                            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search media by filename, title, or description">
                        </label>
                        <div class="hf-media-picker-grid">
                            @forelse ($this->libraryMedia as $media)
                                @php($isSelected = in_array($media->id, $selectedMediaIds, true))
                                <button type="button" @class(['hf-media-picker-item', 'is-selected' => $isSelected]) wire:click="toggleSelection({{ $media->id }})" aria-pressed="{{ $isSelected ? 'true' : 'false' }}">
                                    @if (\Illuminate\Support\Facades\Storage::disk($media->disk)->exists($media->path))
                                        <img src="{{ $this->imageUrl($media) }}" alt="{{ $media->alt_text ?: $media->original_filename }}">
                                    @else
                                        <span class="hf-media-picker-missing">Preview unavailable</span>
                                    @endif
                                    <span class="hf-media-picker-name">{{ $media->caption ?: $media->original_filename }}</span>
                                    @if ($isSelected)<span class="hf-media-picker-check" aria-label="Selected">✓</span>@endif
                                </button>
                            @empty
                                <p class="hf-media-picker-empty">No images found. Use Upload Files to add images.</p>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div class="hf-media-picker-upload">
                        <label for="hf-media-picker-upload-{{ md5($statePath) }}">Choose one or more images</label>
                        <input id="hf-media-picker-upload-{{ md5($statePath) }}" type="file" wire:model="uploads" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
                        <p>JPEG, PNG, WebP, or GIF. Maximum 5 MB per image.</p>

                        @error('uploads')<p class="hf-media-picker-error">{{ $message }}</p>@enderror
                        @error('uploads.*')<p class="hf-media-picker-error">{{ $message }}</p>@enderror

                        @if ($uploads)
                            <ul class="hf-media-picker-upload-list">
                                @foreach ($uploads as $upload)
                                    <li>
                                        <img src="{{ $upload->temporaryUrl() }}" alt="">
                                        <span>{{ $upload->getClientOriginalName() }}</span>
                                        <span wire:loading wire:target="uploads">Uploading…</span>
                                    </li>
                                @endforeach
                            </ul>
                            <button type="button" wire:click="uploadSelectedFiles" wire:loading.attr="disabled" wire:target="uploadSelectedFiles">Upload to Media Library</button>
                        @endif

                        @if ($uploadResults)
                            <ul class="hf-media-picker-upload-results">
                                @foreach ($uploadResults as $result)
                                    <li>{{ $result['name'] }} — {{ $result['message'] }}</li>
                                @endforeach
                            </ul>
                            <p>Uploaded images are now selected. Click Select to assign them.</p>
                        @endif
                    </div>
                @endif

                <footer class="hf-media-picker-footer">
                    <span>{{ count($selectedMediaIds) }} selected</span>
                    <div>
                        <button type="button" class="hf-media-picker-cancel" wire:click="close">Cancel</button>
                        <button type="button" wire:click="select" @disabled(! count($selectedMediaIds))>Select</button>
                    </div>
                </footer>
            </section>
        </div>
    @endif

    @once
    <style>
        .hf-media-picker-current{display:flex;align-items:center;gap:12px;flex-wrap:wrap;min-height:42px}.hf-media-picker-current>img{width:76px;height:56px;object-fit:contain;background:#f3f4f6;border:1px solid #d1d5db;border-radius:6px}.hf-media-picker-current>span{color:#6b7280;font-size:.875rem}.hf-media-picker-current-actions{display:flex;gap:8px}.hf-media-picker-current-actions button,.hf-media-picker-upload>button,.hf-media-picker-footer button{border:0;border-radius:6px;background:#be123c;color:white;padding:9px 14px;font-weight:600;cursor:pointer}.hf-media-picker-current-actions .hf-media-picker-remove,.hf-media-picker-footer .hf-media-picker-cancel{background:#e5e7eb;color:#111827}.hf-media-picker-backdrop{position:fixed;inset:0;z-index:1000;background:rgba(17,24,39,.62);display:grid;place-items:center;padding:24px}.hf-media-picker-modal{display:flex;flex-direction:column;width:min(980px,100%);max-height:min(820px,90vh);background:#fff;color:#111827;border-radius:12px;box-shadow:0 20px 70px #0005;overflow:hidden}.hf-media-picker-header,.hf-media-picker-footer{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e5e7eb}.hf-media-picker-header h2{margin:0;font-size:1.2rem}.hf-media-picker-header button{border:0;background:transparent;font-size:1.7rem;cursor:pointer}.hf-media-picker-tabs{display:flex;gap:20px;padding:0 20px;border-bottom:1px solid #e5e7eb}.hf-media-picker-tabs button{padding:14px 4px;border:0;border-bottom:2px solid transparent;background:transparent;cursor:pointer}.hf-media-picker-tabs button.is-active{color:#be123c;border-color:#be123c;font-weight:700}.hf-media-picker-library,.hf-media-picker-upload{padding:18px 20px;overflow:auto;flex:1}.hf-media-picker-search input{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:7px}.hf-media-picker-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(145px,1fr));gap:12px;margin-top:16px}.hf-media-picker-item{position:relative;display:flex;flex-direction:column;gap:8px;min-width:0;padding:8px;border:2px solid #e5e7eb;border-radius:8px;background:white;text-align:left;cursor:pointer}.hf-media-picker-item.is-selected{border-color:#be123c;box-shadow:0 0 0 2px #be123c33}.hf-media-picker-item>img,.hf-media-picker-missing{display:grid;place-items:center;width:100%;aspect-ratio:1.35/1;object-fit:contain;background:#f3f4f6;border-radius:4px}.hf-media-picker-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.8rem}.hf-media-picker-check{position:absolute;right:12px;top:12px;display:grid;place-items:center;width:24px;height:24px;border-radius:50%;background:#be123c;color:white}.hf-media-picker-empty{grid-column:1/-1;padding:24px;text-align:center;color:#6b7280}.hf-media-picker-upload{display:flex;flex-direction:column;gap:12px}.hf-media-picker-upload input{max-width:100%}.hf-media-picker-upload p{margin:0;color:#6b7280;font-size:.875rem}.hf-media-picker-upload-list,.hf-media-picker-upload-results{display:grid;gap:8px;padding:0;list-style:none}.hf-media-picker-upload-list li{display:flex;align-items:center;gap:12px}.hf-media-picker-upload-list img{width:64px;height:48px;object-fit:contain;background:#f3f4f6}.hf-media-picker-error{color:#b91c1c!important}.hf-media-picker-footer{border-top:1px solid #e5e7eb;border-bottom:0}.hf-media-picker-footer>div{display:flex;gap:8px}.hf-media-picker-footer button:disabled{opacity:.5;cursor:not-allowed}@media(max-width:640px){.hf-media-picker-backdrop{padding:8px}.hf-media-picker-modal{max-height:94vh}.hf-media-picker-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    </style>
    @endonce
</div>
