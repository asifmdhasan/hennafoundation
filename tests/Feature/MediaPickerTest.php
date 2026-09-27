<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageAboutUs;
use App\Filament\Pages\ManageHomepage;
use App\Livewire\MediaPicker;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MediaPickerTest extends TestCase
{
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        DB::beginTransaction();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        DB::rollBack();

        parent::tearDown();
    }

    public function test_picker_opens_to_existing_media_library_and_can_select_one_image(): void
    {
        $media = $this->makeMedia('picker-single.png');

        Livewire::actingAs($this->admin)
            ->test(MediaPicker::class, ['statePath' => 'data.hero_image_id'])
            ->call('open')
            ->assertSet('activeTab', 'library')
            ->assertSee('picker-single.png')
            ->call('toggleSelection', $media->id)
            ->assertSet('selectedMediaIds', [$media->id])
            ->call('select')
            ->assertDispatched('media-picker-selected', statePath: 'data.hero_image_id', mediaIds: [$media->id], multiple: false);
    }

    public function test_picker_supports_ordered_multi_selection_and_deselection(): void
    {
        $first = $this->makeMedia('first.png');
        $second = $this->makeMedia('second.png');

        Livewire::actingAs($this->admin)
            ->test(MediaPicker::class, ['statePath' => 'data.gallery_media_ids', 'multiple' => true])
            ->call('open')
            ->call('toggleSelection', $second->id)
            ->call('toggleSelection', $first->id)
            ->assertSet('selectedMediaIds', [$second->id, $first->id])
            ->call('toggleSelection', $second->id)
            ->assertSet('selectedMediaIds', [$first->id])
            ->call('toggleSelection', $second->id)
            ->call('select')
            ->assertDispatched('media-picker-selected', statePath: 'data.gallery_media_ids', mediaIds: [$first->id, $second->id], multiple: true);
    }

    public function test_search_matches_filename_alt_text_and_caption(): void
    {
        $media = $this->makeMedia('community-image.png', 'community class', 'Classroom visit');

        Livewire::actingAs($this->admin)
            ->test(MediaPicker::class, ['statePath' => 'data.hero_image_id'])
            ->call('open')
            ->set('search', 'Classroom')
            ->assertSee('community-image.png')
            ->assertDontSee('unrelated.png');
    }

    public function test_one_or_multiple_uploads_create_media_records_and_can_be_selected_immediately(): void
    {
        $one = UploadedFile::fake()->image('new-one.png', 24, 24);

        $picker = Livewire::actingAs($this->admin)
            ->test(MediaPicker::class, ['statePath' => 'data.hero_image_id'])
            ->call('open')
            ->set('activeTab', 'upload')
            ->set('uploads', [$one])
            ->call('uploadSelectedFiles')
            ->assertHasNoErrors()
            ->assertSet('activeTab', 'library');

        $uploadedOne = Media::query()->where('original_filename', 'new-one.png')->firstOrFail();
        Storage::disk('public')->assertExists($uploadedOne->path);
        $picker->assertSee('new-one.png')->call('select')->assertDispatched('media-picker-selected', mediaIds: [$uploadedOne->id]);

        $picker = Livewire::actingAs($this->admin)
            ->test(MediaPicker::class, ['statePath' => 'data.inspiration_image_ids', 'multiple' => true])
            ->call('open')
            ->set('activeTab', 'upload')
            ->set('uploads', [
                UploadedFile::fake()->image('new-two.webp', 24, 24),
                UploadedFile::fake()->image('new-three.png', 25, 24),
            ])
            ->call('uploadSelectedFiles')
            ->assertHasNoErrors()
            ->assertSet('activeTab', 'library');

        $uploadedMany = Media::query()->whereIn('original_filename', ['new-two.webp', 'new-three.png'])->orderBy('id')->get();
        $this->assertCount(2, $uploadedMany);
        foreach ($uploadedMany as $media) {
            Storage::disk('public')->assertExists($media->path);
        }
        $picker->call('select')->assertDispatched('media-picker-selected', mediaIds: $picker->get('selectedMediaIds'), multiple: true);
    }

    public function test_invalid_upload_is_rejected_without_creating_media(): void
    {
        Livewire::actingAs($this->admin)
            ->test(MediaPicker::class, ['statePath' => 'data.hero_image_id'])
            ->set('uploads', [UploadedFile::fake()->create('not-an-image.txt', 5, 'text/plain')])
            ->call('uploadSelectedFiles')
            ->assertSet('uploadResults.0.success', false)
            ->assertSet('uploadResults.0.name', 'not-an-image.txt');

        $this->assertDatabaseMissing('media', ['original_filename' => 'not-an-image.txt']);
    }

    public function test_valid_uploads_succeed_while_invalid_files_show_per_file_errors(): void
    {
        Livewire::actingAs($this->admin)
            ->test(MediaPicker::class, ['statePath' => 'data.gallery_media_ids', 'multiple' => true])
            ->set('uploads', [
                UploadedFile::fake()->image('valid.png', 26, 26),
                UploadedFile::fake()->create('invalid.txt', 5, 'text/plain'),
            ])
            ->call('uploadSelectedFiles')
            ->assertSet('uploadResults.0.success', false)
            ->assertSet('uploadResults.1.success', true)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('media', ['original_filename' => 'valid.png']);
        $this->assertDatabaseMissing('media', ['original_filename' => 'invalid.txt']);
    }

    public function test_identical_upload_content_reuses_the_existing_media_record(): void
    {
        $firstFile = UploadedFile::fake()->image('original.png', 29, 29);
        $sameImage = file_get_contents($firstFile->getRealPath());
        $copyFile = UploadedFile::fake()->createWithContent('copy.png', $sameImage);

        Livewire::actingAs($this->admin)
            ->test(MediaPicker::class, ['statePath' => 'data.hero_image_id'])
            ->set('uploads', [$firstFile])
            ->call('uploadSelectedFiles')
            ->assertHasNoErrors();

        $recordCount = Media::query()->count();

        Livewire::actingAs($this->admin)
            ->test(MediaPicker::class, ['statePath' => 'data.hero_image_id'])
            ->set('uploads', [$copyFile])
            ->call('uploadSelectedFiles')
            ->assertSet('uploadResults.0.message', 'Already in Media Library; reused existing image.');

        $this->assertSame($recordCount, Media::query()->count());
    }

    public function test_homepage_and_about_editors_use_single_and_multiple_picker_fields(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ManageHomepage::class)
            ->assertSchemaComponentExists('hero_image_id')
            ->assertSchemaComponentExists('gallery_media_ids')
            ->assertSee('Select image');

        Livewire::actingAs($this->admin)
            ->test(ManageAboutUs::class)
            ->assertSchemaComponentExists('hero_image_media_id')
            ->assertSchemaComponentExists('inspiration_image_ids')
            ->assertSee('Select image');
    }

    public function test_editor_receives_picker_selection_into_nested_form_state(): void
    {
        $media = $this->makeMedia('nested-field.png');

        Livewire::actingAs($this->admin)
            ->test(ManageHomepage::class)
            ->call('applyMediaPickerSelection', 'data.home_actions.0.image_media_id', [$media->id], false)
            ->assertSet('data.home_actions.0.image_media_id', $media->id);
    }

    private function makeMedia(string $filename, ?string $alt = null, ?string $caption = null): Media
    {
        $path = 'media/'.uniqid().'-'.$filename;
        Storage::disk('public')->put($path, 'fake-image-bytes-'.$filename);

        return Media::query()->create([
            'disk' => 'public',
            'path' => $path,
            'original_filename' => $filename,
            'mime_type' => 'image/png',
            'size_bytes' => Storage::disk('public')->size($path),
            'alt_text' => $alt,
            'caption' => $caption,
            'uploaded_by' => $this->admin->id,
        ]);
    }
}
