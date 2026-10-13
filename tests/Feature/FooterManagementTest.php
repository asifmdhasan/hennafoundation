<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageFooter;
use App\Filament\Pages\ManageHomepage;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class FooterManagementTest extends TestCase
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

    public function test_footer_page_is_registered_and_homepage_editor_keeps_only_its_first_seven_sections(): void
    {
        $this->actingAs($this->admin)->get('/admin/footer')->assertOk();

        Livewire::actingAs($this->admin)->test(ManageHomepage::class)
            ->assertSchemaComponentExists('home_hero_title')
            ->assertSchemaComponentExists('home_blog_title')
            ->assertSchemaComponentDoesNotExist('footer_links')
            ->assertSchemaComponentDoesNotExist('contact_items');

        Livewire::actingAs($this->admin)->test(ManageFooter::class)
            ->assertSchemaComponentExists('footer_links')
            ->assertSchemaComponentExists('contact_items')
            ->assertSchemaComponentExists('social_items')
            ->assertSchemaComponentExists('footer_text');
    }

    public function test_footer_editor_saves_repeatable_links_contacts_and_social_profiles(): void
    {
        Livewire::actingAs($this->admin)->test(ManageFooter::class)
            ->set('data.footer_links', [['label' => 'Our work', 'url' => '/projects/']])
            ->set('data.contact_items', [[
                'type' => 'Website',
                'label' => 'Website',
                'value' => 'www.hennafoundation.org.uk',
                'url' => 'https://hennafoundation.org.uk/',
                'icon_media_id' => '',
            ]])
            ->set('data.social_items', [[
                'platform' => 'Facebook',
                'url' => 'https://facebook.com/hennafoundation',
                'icon_media_id' => '',
            ]])
            ->set('data.footer_text', '<p>Editable <strong>footer</strong> description.</p>')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Our work', json_decode(WebsiteSetting::query()->where('key', 'footer_links')->firstOrFail()->value, true)[0]['label']);
        $this->assertSame('Facebook', json_decode(WebsiteSetting::query()->where('key', 'footer_social_items')->firstOrFail()->value, true)[0]['platform']);
        $this->assertSame('https://hennafoundation.org.uk/', WebsiteSetting::query()->where('key', 'site_website_url')->firstOrFail()->value);
        $this->assertSame('<p>Editable <strong>footer</strong> description.</p>', WebsiteSetting::query()->where('key', 'footer_text')->firstOrFail()->value);
    }
}
