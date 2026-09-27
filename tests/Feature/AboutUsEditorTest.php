<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageAboutUs;
use App\Models\Media;
use App\Models\Page;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AboutUsEditorTest extends TestCase
{
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        DB::beginTransaction();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
    }

    protected function tearDown(): void
    {
        DB::rollBack();

        parent::tearDown();
    }

    public function test_about_us_editor_is_in_the_admin_panel_and_requires_authentication(): void
    {
        $this->get('/admin/about-us-editor')->assertRedirect('/admin/login');
        $this->actingAs($this->admin)->get('/admin/about-us-editor')->assertOk();

        Livewire::actingAs($this->admin)
            ->test(ManageAboutUs::class)
            ->assertSchemaComponentExists('title')
            ->assertSchemaComponentExists('hero_image_media_id')
            ->assertSchemaComponentExists('trustees')
            ->assertSchemaComponentExists('goals')
            ->assertSchemaComponentExists('plan_items')
            ->assertSchemaComponentExists('meta_title')
            ->assertSee('About Us Editor');
    }

    public function test_editor_saves_page_content_and_can_update_it_again(): void
    {
        $editor = Livewire::actingAs($this->admin)->test(ManageAboutUs::class);

        $editor
            ->set('data.title', 'About Our Foundation')
            ->set('data.inspiration_heading', 'A revised introduction')
            ->set('data.inspiration_paragraphs', [['paragraph' => 'A saved and updated introduction.']])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pages', ['slug' => 'about-us', 'title' => 'About Our Foundation']);
        $this->assertSame(
            'A revised introduction',
            WebsiteSetting::query()->where('key', 'about_inspiration_heading')->value('value'),
        );

        $editor
            ->set('data.inspiration_heading', 'The latest introduction')
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(
            'The latest introduction',
            WebsiteSetting::query()->where('key', 'about_inspiration_heading')->value('value'),
        );
    }

    public function test_trustees_goals_plan_visibility_and_media_selection_persist(): void
    {
        $media = Media::query()->firstOrFail();

        Livewire::actingAs($this->admin)
            ->test(ManageAboutUs::class)
            ->set('data.hero_image_media_id', $media->id)
            ->set('data.patron_image_media_id', $media->id)
            ->set('data.inspiration_image_ids', [$media->id])
            ->set('data.show_patron', false)
            ->set('data.trustees', [
                ['name' => 'Second in display order', 'is_visible' => true],
                ['name' => 'Hidden trustee', 'is_visible' => false],
                ['name' => 'Third in display order', 'is_visible' => true],
            ])
            ->set('data.goals', [
                ['icon' => '', 'title' => 'Second goal', 'description' => 'Second goal text.', 'is_visible' => true],
                ['icon' => '', 'title' => 'Hidden goal', 'description' => 'Hidden goal text.', 'is_visible' => false],
                ['icon' => '', 'title' => 'Third goal', 'description' => 'Third goal text.', 'is_visible' => true],
            ])
            ->set('data.plan_items', [
                ['title' => 'Second plan item', 'description' => '', 'is_visible' => true],
                ['title' => 'Hidden plan item', 'description' => '', 'is_visible' => false],
                ['title' => 'Third plan item', 'description' => '', 'is_visible' => true],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($media->id, Page::query()->where('slug', 'about-us')->value('og_image_media_id'));
        $this->assertSame((string) $media->id, WebsiteSetting::query()->where('key', 'about_patron_image_media_id')->value('value'));
        $this->assertSame(
            [$media->id],
            Page::query()->where('slug', 'about-us')->firstOrFail()->mediaAttachments()
                ->wherePivot('role', 'about-inspiration')->orderByPivot('sort_order')->pluck('media.id')->all(),
        );
        $this->assertSame('0', WebsiteSetting::query()->where('key', 'about_show_patron')->value('value'));

        $trustees = json_decode(WebsiteSetting::query()->where('key', 'about_trustees')->value('value'), true);
        $goals = json_decode(WebsiteSetting::query()->where('key', 'about_goals')->value('value'), true);
        $plan = json_decode(WebsiteSetting::query()->where('key', 'about_plan_items')->value('value'), true);

        $this->assertSame(['Second in display order', 'Hidden trustee', 'Third in display order'], array_column($trustees, 'name'));
        $this->assertSame([0, 1, 2], array_column($trustees, 'sort_order'));
        $this->assertSame(['Second goal', 'Hidden goal', 'Third goal'], array_column($goals, 'title'));
        $this->assertSame(['Second plan item', 'Hidden plan item', 'Third plan item'], array_column($plan, 'title'));
    }

    public function test_public_about_page_renders_database_content_in_admin_order_and_hides_inactive_items(): void
    {
        $this->putSetting('about_show_patron', '0');
        $this->putSetting('about_trustees', json_encode([
            ['name' => 'Trustee B', 'is_visible' => true, 'sort_order' => 1],
            ['name' => 'Hidden Trustee', 'is_visible' => false, 'sort_order' => 2],
            ['name' => 'Trustee A', 'is_visible' => true, 'sort_order' => 0],
        ]));
        $this->putSetting('about_goals', json_encode([
            ['title' => 'Goal B', 'description' => 'Goal B description.', 'is_visible' => true, 'sort_order' => 1],
            ['title' => 'Hidden Goal', 'description' => 'Hidden goal description.', 'is_visible' => false, 'sort_order' => 2],
            ['title' => 'Goal A', 'description' => 'Goal A description.', 'is_visible' => true, 'sort_order' => 0],
        ]));
        $this->putSetting('about_plan_items', json_encode([
            ['title' => 'Plan B', 'is_visible' => true, 'sort_order' => 1],
            ['title' => 'Hidden Plan', 'is_visible' => false, 'sort_order' => 2],
            ['title' => 'Plan A', 'is_visible' => true, 'sort_order' => 0],
        ]));
        $this->putSetting('about_inspiration_heading', '<script>unsafe()</script>CMS introduction');

        $this->get('/about-us/')
            ->assertOk()
            ->assertSee('The Board of Trustees')
            ->assertSeeInOrder(['Trustee A', 'Trustee B'])
            ->assertDontSee('Hidden Trustee')
            ->assertSeeInOrder(['Goal A', 'Goal B'])
            ->assertDontSee('Hidden Goal')
            ->assertSeeInOrder(['Plan A', 'Plan B'])
            ->assertDontSee('Hidden Plan')
            ->assertDontSee('Ifty Islam')
            ->assertSee('&lt;script&gt;unsafe()&lt;/script&gt;CMS introduction', false)
            ->assertDontSee('<script>unsafe()</script>CMS introduction', false);
    }

    public function test_about_page_obeys_publishing_rules_and_handles_optional_content_safely(): void
    {
        Page::query()->where('slug', 'about-us')->update(['status' => 'draft']);
        $this->get('/about-us/')->assertNotFound();

        Page::query()->where('slug', 'about-us')->update([
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);
        $this->get('/about-us/')->assertNotFound();

        Page::query()->where('slug', 'about-us')->update(['published_at' => now()->subMinute()]);
        $this->putSetting('about_show_hero', '0');
        $this->putSetting('about_show_inspiration', '0');
        $this->putSetting('about_show_patron', '0');
        $this->putSetting('about_show_trustees', '0');
        $this->putSetting('about_show_goals', '0');
        $this->putSetting('about_show_heart', '0');
        $this->putSetting('about_show_plan', '0');
        $this->putSetting('about_show_charity_purpose', '0');

        $this->get('/about-us/')->assertOk()->assertSee('About Us')->assertDontSee('Ifty Islam');
    }

    public function test_seeded_about_us_content_uses_current_copy_and_local_media(): void
    {
        $response = $this->get('/about-us/')
            ->assertOk()
            ->assertSee('The inspiration behind Henna Foundation')
            ->assertSee('Ifty Islam')
            ->assertSee('The Board of Trustees')
            ->assertSee('Fariha Ema Aniqa Chowdhury')
            ->assertSee('Building Foundations for a Better Tomorrow')
            ->assertSee('Plan for Next 1-3 Years')
            ->assertSee('Registered Charity Number')
            ->assertDontSee('/wp-content/');

        $page = Page::query()->where('slug', 'about-us')->firstOrFail();
        $imageUrls = $page->mediaAttachments()
            ->wherePivot('role', 'about-inspiration')
            ->orderByPivot('sort_order')
            ->get()
            ->map(fn (Media $media): string => \Illuminate\Support\Facades\Storage::disk($media->disk)->url($media->path));

        foreach ($imageUrls as $imageUrl) {
            $response->assertSee($imageUrl, false);
        }
    }

    private function putSetting(string $key, string $value): void
    {
        WebsiteSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => 'about-us'],
        );
    }
}
