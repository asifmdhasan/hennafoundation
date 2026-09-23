<?php

namespace Tests\Feature;

use App\Filament\Resources\BlogPosts\Pages\ManageBlogPosts;
use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Filament\Resources\Media\Pages\ManageMedia;
use App\Filament\Resources\Pages\Pages\ManagePages;
use App\Filament\Resources\Projects\Pages\ManageProjects;
use App\Filament\Resources\Redirects\Pages\ManageRedirects;
use App\Filament\Resources\WebsiteSettings\Pages\ManageWebsiteSettings;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Media;
use App\Models\Page;
use App\Models\Project;
use App\Models\Redirect;
use App\Models\User;
use App\Models\WebsiteSetting;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentCoreResourcesTest extends TestCase
{
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        DB::beginTransaction();

        $this->admin = User::query()->create([
            'name' => 'Temporary Resource Test Admin',
            'email' => 'resource-test-'.uniqid().'@example.test',
            'password' => 'temporary-test-password',
        ]);
    }

    protected function tearDown(): void
    {
        DB::rollBack();

        parent::tearDown();
    }

    public function test_core_resource_pages_and_create_forms_render_for_an_authorized_user(): void
    {
        $resources = [
            ['/admin/pages', ManagePages::class, 'title'],
            ['/admin/projects', ManageProjects::class, 'title'],
            ['/admin/blog-posts', ManageBlogPosts::class, 'title'],
            ['/admin/categories', ManageCategories::class, 'name'],
            ['/admin/media', ManageMedia::class, 'path'],
            ['/admin/website-settings', ManageWebsiteSettings::class, 'key'],
            ['/admin/redirects', ManageRedirects::class, 'old_path'],
        ];

        foreach ($resources as [$url, $component, $requiredField]) {
            $this->actingAs($this->admin)->get($url)->assertOk();

            Livewire::actingAs($this->admin)
                ->test($component)
                ->mountAction('create')
                ->assertActionMounted('create')
                ->assertSchemaComponentExists($requiredField);
        }
    }

    public function test_page_edit_and_delete_actions_open_and_required_fields_are_validated(): void
    {
        $page = Page::query()->create([
            'title' => 'Temporary Page for Resource Test',
            'slug' => 'temporary-page-'.uniqid(),
            'content' => '<p>Temporary test content.</p>',
            'status' => 'draft',
        ]);

        $component = Livewire::actingAs($this->admin)->test(ManagePages::class);

        $component
            ->mountAction(TestAction::make('edit')->table($page))
            ->assertActionMounted(TestAction::make('edit')->table($page))
            ->assertSchemaComponentExists('title');

        $component
            ->unmountAction()
            ->mountAction(TestAction::make('delete')->table($page))
            ->assertActionMounted(TestAction::make('delete')->table($page));

        Livewire::actingAs($this->admin)
            ->test(ManagePages::class)
            ->mountAction('create')
            ->fillForm(['title' => '', 'slug' => '', 'content' => ''])
            ->callMountedAction()
            ->assertHasFormErrors([
                'title' => 'required',
                'slug' => 'required',
            ]);
    }

    public function test_edit_and_delete_actions_open_for_the_other_core_resources(): void
    {
        $records = [
            [ManageProjects::class, Project::query()->create([
                'title' => 'Temporary Project',
                'slug' => 'temporary-project-'.uniqid(),
                'status' => 'draft',
            ])],
            [ManageBlogPosts::class, BlogPost::query()->create([
                'title' => 'Temporary Blog Post',
                'slug' => 'temporary-blog-post-'.uniqid(),
                'content' => '<p>Temporary test content.</p>',
                'status' => 'draft',
                'author_id' => $this->admin->id,
            ])],
            [ManageCategories::class, Category::query()->create([
                'name' => 'Temporary Category',
                'slug' => 'temporary-category-'.uniqid(),
            ])],
            [ManageMedia::class, Media::query()->create([
                'disk' => 'public',
                'path' => 'media/nonexistent-test-image.png',
                'original_filename' => 'test-image.png',
                'mime_type' => 'image/png',
                'size_bytes' => 0,
                'uploaded_by' => $this->admin->id,
            ])],
            [ManageWebsiteSettings::class, WebsiteSetting::query()->create([
                'key' => 'resource-test-setting-'.uniqid(),
                'value' => 'Temporary Test Site',
                'group' => 'general',
            ])],
            [ManageRedirects::class, Redirect::query()->create([
                'old_path' => '/temporary-old-'.uniqid().'/',
                'new_path' => '/temporary-new/',
                'status_code' => 301,
                'is_active' => true,
            ])],
        ];

        foreach ($records as [$componentClass, $record]) {
            $component = Livewire::actingAs($this->admin)->test($componentClass);

            $component
                ->mountAction(TestAction::make('edit')->table($record))
                ->assertActionMounted(TestAction::make('edit')->table($record));

            $component
                ->unmountAction()
                ->mountAction(TestAction::make('delete')->table($record))
                ->assertActionMounted(TestAction::make('delete')->table($record));
        }
    }

    public function test_media_upload_saves_a_generated_file_and_server_side_metadata(): void
    {
        Storage::fake('public');

        Livewire::actingAs($this->admin)
            ->test(ManageMedia::class)
            ->callAction('create', [
                'path' => UploadedFile::fake()->image('henna-logo.png', 32, 32),
                'alt_text' => 'Test logo',
                'caption' => 'Temporary media upload test',
            ])
            ->assertHasNoFormErrors();

        $media = Media::query()->where('caption', 'Temporary media upload test')->firstOrFail();

        $this->assertSame('public', $media->disk);
        $this->assertSame('henna-logo.png', $media->original_filename);
        $this->assertSame('image/png', $media->mime_type);
        $this->assertGreaterThan(0, $media->size_bytes);
        $this->assertSame($this->admin->id, $media->uploaded_by);
        Storage::disk('public')->assertExists($media->path);

        Storage::disk('public')->delete($media->path);
    }
}
