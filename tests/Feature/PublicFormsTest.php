<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\PartnershipApplication;
use App\Models\User;
use App\Models\VolunteerApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PublicFormsTest extends TestCase
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

    public function test_contact_form_page_and_valid_submission_store_a_new_message(): void
    {
        $this->get('/contact-us/')->assertOk()->assertSee('Contact Us')->assertSee('name="_token"', false);

        $this->post('/contact-us/', [
            'name' => 'Asha Rahman',
            'email' => 'asha@example.com',
            'subject' => 'Question about volunteering',
            'message' => 'Please contact me about volunteering.',
            'status' => 'resolved',
        ])->assertRedirect(route('contact-us.create'));

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Asha Rahman',
            'email' => 'asha@example.com',
            'subject' => 'Question about volunteering',
            'status' => 'new',
        ]);
        $this->get('/contact-us/')->assertSee('Your message has been received.');
    }

    public function test_contact_form_rejects_invalid_email_and_missing_required_fields(): void
    {
        $this->from('/contact-us/')->post('/contact-us/', [
            'name' => 'Asha',
            'email' => 'not-an-email',
            'subject' => 'Subject',
            'message' => 'Message',
        ])->assertRedirect('/contact-us/')->assertSessionHasErrors(['email']);

        $this->from('/contact-us/')->post('/contact-us/', [])->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_contact_submission_route_keeps_web_csrf_and_throttling_middleware(): void
    {
        $route = Route::getRoutes()->match(Request::create('/contact-us/', 'POST'));
        $middleware = $route->gatherMiddleware();

        $this->assertContains('web', $middleware);
        $this->assertContains('throttle:contact-submissions', $middleware);
    }

    public function test_volunteer_page_and_valid_submission_allow_empty_address_two(): void
    {
        $this->get('/volunteer-registration/')->assertOk()->assertSee('Home Telephone');

        $this->post('/volunteer-registration/', $this->volunteerPayload(['address_2' => '']))
            ->assertRedirect(route('volunteer-registration.create'));

        $this->assertDatabaseHas('volunteer_applications', [
            'full_name' => 'Samira Begum',
            'email' => 'samira@example.com',
            'address_2' => null,
            'status' => 'pending',
        ]);
    }

    public function test_volunteer_form_rejects_missing_required_fields_and_does_not_insert(): void
    {
        $this->from('/volunteer-registration/')->post('/volunteer-registration/', [
            'address_2' => '',
        ])->assertSessionHasErrors([
            'full_name', 'phone', 'email', 'home_telephone', 'address_1', 'city', 'postal_code',
        ]);

        $this->assertDatabaseCount('volunteer_applications', 0);
    }

    public function test_partnership_page_and_valid_submission_store_pending_application(): void
    {
        $this->get('/partnership-registration/')->assertOk()->assertSee('Organisation Name');

        $this->post('/partnership-registration/', $this->partnershipPayload(['address_2' => '']))
            ->assertRedirect(route('partnership-registration.create'));

        $this->assertDatabaseHas('partnership_applications', [
            'full_name' => 'Nadia Ali',
            'organisation_name' => 'Community Group',
            'address_2' => null,
            'status' => 'pending',
        ]);
    }

    public function test_partnership_form_requires_organisation_name(): void
    {
        $payload = $this->partnershipPayload();
        unset($payload['organisation_name']);

        $this->from('/partnership-registration/')->post('/partnership-registration/', $payload)
            ->assertSessionHasErrors(['organisation_name']);

        $this->assertDatabaseCount('partnership_applications', 0);
    }

    public function test_invalid_input_does_not_create_submissions_and_public_markup_escapes_input(): void
    {
        $this->from('/contact-us/')->post('/contact-us/', [
            'name' => '<script>alert(1)</script>',
            'email' => 'invalid',
            'subject' => 'Subject',
            'message' => 'Message',
        ])->assertSessionHasErrors(['email'])->assertDontSee('<script>alert(1)</script>', false);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_admin_can_open_all_submission_resources_and_submitted_html_is_escaped(): void
    {
        $this->seed(\Database\Seeders\AdminUserSeeder::class);
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $maliciousName = '<script>alert(1)</script>';

        ContactMessage::query()->create([
            'name' => $maliciousName,
            'email' => 'contact@example.com',
            'subject' => 'A message',
            'message' => '<script>unsafe content</script>',
            'status' => 'new',
        ]);

        $this->actingAs($admin);
        $this->get('/admin/contact-messages')->assertOk()->assertDontSee($maliciousName, false);
        $this->get('/admin/volunteer-applications')->assertOk();
        $this->get('/admin/partnership-applications')->assertOk();

        $this->assertTrue(Hash::check('password123', $admin->password));
    }

    public function test_all_public_forms_retain_csrf_protection_through_web_middleware(): void
    {
        foreach ([
            ['/contact-us/', 'contact-submissions'],
            ['/volunteer-registration/', 'volunteer-applications'],
            ['/partnership-registration/', 'partnership-applications'],
        ] as [$path, $limiter]) {
            $route = Route::getRoutes()->match(Request::create($path, 'POST'));
            $middleware = $route->gatherMiddleware();

            $this->assertContains('web', $middleware);
            $this->assertContains('throttle:'.$limiter, $middleware);
        }
    }

    private function volunteerPayload(array $overrides = []): array
    {
        return array_replace([
            'full_name' => 'Samira Begum',
            'phone' => '+44 7700 900123',
            'email' => 'samira@example.com',
            'home_telephone' => '01903 555 111',
            'address_1' => '1 High Street',
            'address_2' => 'Flat 2',
            'city' => 'Worthing',
            'postal_code' => 'BN11 1AA',
        ], $overrides);
    }

    private function partnershipPayload(array $overrides = []): array
    {
        return array_replace([
            'full_name' => 'Nadia Ali',
            'organisation_name' => 'Community Group',
            'phone' => '+44 7700 900456',
            'email' => 'nadia@example.com',
            'home_telephone' => '01903 555 222',
            'address_1' => '2 High Street',
            'address_2' => 'Suite 4',
            'city' => 'Worthing',
            'postal_code' => 'BN11 2BB',
        ], $overrides);
    }
}
