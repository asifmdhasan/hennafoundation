<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Page;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::beginTransaction();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    protected function tearDown(): void
    {
        DB::rollBack();

        parent::tearDown();
    }

    public function test_about_us_page_loads_the_published_database_page_and_its_seo_fields(): void
    {
        Page::query()->where('slug', 'about-us')->update([
            'title' => 'About Henna Foundation from the CMS',
            'content' => '<p>Content entered through Filament.</p>',
            'excerpt' => 'CMS supplied introduction.',
            'status' => 'published',
            'published_at' => now(),
            'meta_title' => 'About Us SEO title',
            'meta_description' => 'About Us SEO description.',
            'canonical_url' => 'https://example.org/about-us/',
        ]);

        $this->get('/about-us/')
            ->assertOk()
            ->assertViewIs('pages.about-us')
            ->assertSee('About Henna Foundation from the CMS')
            ->assertSee('Content entered through Filament.')
            ->assertSee('<title>About Us SEO title</title>', false)
            ->assertSee('<meta name="description" content="About Us SEO description.">', false)
            ->assertSee('<link rel="canonical" href="https://example.org/about-us/">', false);
    }

    public function test_unpublished_about_us_page_is_not_public(): void
    {
        Page::query()->where('slug', 'about-us')->update([
            'title' => 'Hidden About Page',
            'content' => '<p>Private draft page.</p>',
            'status' => 'draft',
        ]);

        $this->get('/about-us/')->assertNotFound();
    }

    public function test_projects_page_lists_seeded_published_projects_in_sort_order(): void
    {
        $this->get('/projects/')
            ->assertOk()
            ->assertViewIs('pages.projects')
            ->assertSeeInOrder(['Breast Cancer Project', 'Women Empowerment', 'Climate Resilience'])
            ->assertSee('<title>Projects | Henna Foundation</title>', false)
            ->assertSee('<link rel="canonical" href="'.url('/projects/').'">', false);
    }

    public function test_projects_page_excludes_draft_and_future_projects(): void
    {
        Project::query()->create([
            'title' => 'Private Draft Project',
            'slug' => 'private-draft-project',
            'status' => 'draft',
            'sort_order' => 0,
        ]);
        Project::query()->create([
            'title' => 'Future Project',
            'slug' => 'future-project',
            'status' => 'published',
            'published_at' => now()->addDay(),
            'sort_order' => 0,
        ]);

        $this->get('/projects/')
            ->assertOk()
            ->assertDontSee('Private Draft Project')
            ->assertDontSee('Future Project');
    }

    public function test_project_edits_and_unpublishing_are_reflected_on_the_listing(): void
    {
        $project = Project::query()->where('slug', 'breast-cancer-project')->firstOrFail();
        $this->get('/projects/')->assertSee('Breast Cancer Project');

        $project->update([
            'title' => 'Updated CMS Project Name',
            'summary' => 'Updated CMS project description.',
        ]);

        $this->get('/projects/')
            ->assertSee('Updated CMS Project Name')
            ->assertSee('Updated CMS project description.')
            ->assertDontSee('Breast Cancer Project');

        $project->update(['status' => 'draft']);
        $this->get('/projects/')->assertDontSee('Updated CMS Project Name');

        $project->delete();
        $this->get('/projects/')->assertDontSee('Updated CMS Project Name');
    }

    public function test_project_featured_media_uses_local_media_relationship_and_not_wordpress_urls(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('projects/local-featured.jpg', 'test image');

        $project = Project::query()->where('slug', 'breast-cancer-project')->firstOrFail();
        $media = Media::query()->create([
            'disk' => 'public',
            'path' => 'projects/local-featured.jpg',
            'original_filename' => 'local-featured.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 10,
            'alt_text' => 'Uploaded project image',
        ]);
        $project->mediaAttachments()->attach($media->id, ['role' => 'featured', 'sort_order' => 0]);

        $response = $this->get('/projects/')->assertOk();

        $response->assertSee(Storage::disk('public')->url('projects/local-featured.jpg'), false)
            ->assertSee('alt="Uploaded project image"', false)
            ->assertDontSee('/wp-content/')
            ->assertDontSee('wp.com');
    }

    public function test_projects_page_handles_an_empty_published_project_list(): void
    {
        Project::query()->delete();

        $this->get('/projects/')
            ->assertOk()
            ->assertSee('Project details will be available soon.')
            ->assertDontSee('Breast Cancer Project');
    }

    public function test_about_us_page_handles_blank_seeded_content_without_inventing_replacement_copy(): void
    {
        Page::query()->where('slug', 'about-us')->update([
            'status' => 'published',
            'published_at' => now(),
            'excerpt' => '',
            'content' => '',
        ]);

        $this->get('/about-us/')
            ->assertOk()
            ->assertSee('About Us')
            ->assertDontSee('Our mission is');
    }

    public function test_admin_login_remains_accessible(): void
    {
        $this->seed(\Database\Seeders\AdminUserSeeder::class);
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('password123', $admin->password));
        $this->get('/admin/login')->assertOk();
        $this->actingAs($admin)->get('/admin')->assertOk();
    }
}
