<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Project;
use App\Models\User;
use App\Models\WebsiteSetting;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicHomepageTest extends TestCase
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

    public function test_homepage_returns_success_and_uses_the_home_controller_route(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertViewIs('pages.home')
            ->assertSee('GUARDIANS OF GIVING')
            ->assertSee('Creating Awareness, Driving Change');

        $this->assertSame('App\\Http\\Controllers\\HomeController', (string) (\Illuminate\Support\Facades\Route::getRoutes()->match(\Illuminate\Http\Request::create('/'))->getActionName()));
    }

    public function test_seeded_admin_can_access_the_admin_panel(): void
    {
        $this->seed(\Database\Seeders\AdminUserSeeder::class);
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('password123', $admin->password));
        $this->assertTrue($admin->canAccessPanel(Filament::getPanel('admin')));
        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_deleting_a_project_removes_it_from_the_homepage(): void
    {
        $project = Project::query()->create([
            'title' => 'Removable CMS Project',
            'slug' => 'removable-cms-project',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->get('/')->assertSee('Removable CMS Project');
        $project->delete();
        $this->get('/')->assertDontSee('Removable CMS Project');
    }

    public function test_database_website_settings_control_homepage_copy(): void
    {
        WebsiteSetting::query()->where('key', 'home_hero_title')->update(['value' => 'A database-controlled headline']);

        $this->get('/')->assertOk()->assertSee('A database-controlled headline');
    }

    public function test_published_blog_posts_appear_and_drafts_do_not(): void
    {
        BlogPost::query()->create([
            'title' => 'Verified Published Story', 'slug' => 'verified-published-story',
            'excerpt' => 'Visible story excerpt.', 'content' => '<p>Story content</p>',
            'status' => 'published', 'published_at' => now(),
        ]);
        BlogPost::query()->create([
            'title' => 'Draft Story Should Stay Hidden', 'slug' => 'draft-story-hidden',
            'content' => '<p>Draft content</p>', 'status' => 'draft',
        ]);

        $this->get('/blog/')->assertOk()
            ->assertSee('Verified Published Story')
            ->assertDontSee('Draft Story Should Stay Hidden');
        $this->get('/')->assertSee('Verified Published Story')->assertDontSee('Draft Story Should Stay Hidden');
    }

    public function test_homepage_handles_empty_cms_content_without_fallback_projects(): void
    {
        Project::query()->delete();
        BlogPost::query()->delete();
        Page::query()->whereIn('slug', ['home', 'pink-chai-day-2025'])->delete();

        $this->get('/')->assertOk()
            ->assertSee('Project details will be available soon.')
            ->assertSee('New stories will be shared here soon.')
            ->assertDontSee('Breast Cancer Project')
            ->assertDontSee('Pink Chai Day 2025');
    }

    public function test_database_seeder_does_not_import_blog_posts_or_unverified_redirects(): void
    {
        $this->assertSame(0, BlogPost::query()->count());
        $this->assertSame(0, \App\Models\Redirect::query()->count());
        $this->assertSame(0, \App\Models\Category::query()->count());
    }

    public function test_only_currently_published_projects_are_rendered(): void
    {
        Project::query()->create([
            'title' => 'Published Test Project',
            'slug' => 'published-test-project',
            'summary' => 'This published project should be visible.',
            'status' => 'published',
            'sort_order' => 1,
        ]);

        Project::query()->create([
            'title' => 'Draft Test Project',
            'slug' => 'draft-test-project',
            'summary' => 'This draft project must stay private.',
            'status' => 'draft',
            'sort_order' => 2,
        ]);

        Project::query()->create([
            'title' => 'Scheduled Test Project',
            'slug' => 'scheduled-test-project',
            'summary' => 'This future project must stay private.',
            'status' => 'published',
            'published_at' => now()->addDay(),
            'sort_order' => 3,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Published Test Project')
            ->assertDontSee('Draft Test Project')
            ->assertDontSee('Scheduled Test Project');
    }

    public function test_homepage_queries_only_published_blog_posts(): void
    {
        BlogPost::query()->create([
            'title' => 'Unapproved Blog Fixture',
            'slug' => 'unapproved-blog-fixture',
            'content' => 'This content must never appear on the homepage.',
            'status' => 'draft',
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->get('/')->assertOk();
        $queryLog = DB::getQueryLog();

        DB::disableQueryLog();

        $response->assertDontSee('Unapproved Blog Fixture')
            ->assertDontSee('This content must never appear on the homepage.');

        $blogPostQueries = collect($queryLog)
            ->pluck('query')
            ->filter(fn (string $query) => str_contains(strtolower($query), 'blog_posts'));

        $this->assertNotCount(0, $blogPostQueries);
    }

    public function test_homepage_navigation_renders_expected_destinations(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('href="'.url('/about-us').'/"', false)
            ->assertSee('href="'.url('/projects').'/"', false)
            ->assertSee('href="'.url('/blog').'/"', false)
            ->assertSee('href="'.url('/contact-us').'/"', false)
            ->assertSee('href="'.url('/donate').'/"', false)
            ->assertSee('aria-expanded="false"', false);
    }

    public function test_filament_admin_login_remains_available(): void
    {
        $this->get('/admin/login')->assertOk();
    }
}
