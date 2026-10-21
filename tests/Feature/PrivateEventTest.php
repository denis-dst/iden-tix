<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Tenant;
use App\Models\TicketCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrivateEventTest extends TestCase
{
    use RefreshDatabase;

    protected User $organizer;
    protected Tenant $tenant;
    protected Event $publishedEvent;
    protected Event $privateEvent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Demo Organizer Tenant',
            'slug' => 'demo-organizer',
            'email' => 'organizer@example.com',
            'phone' => '081234567890',
            'address' => 'Jakarta',
            'is_active' => true,
        ]);

        Role::firstOrCreate(['name' => 'Penyedia Event', 'guard_name' => 'web']);

        $this->organizer = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        $this->organizer->assignRole('Penyedia Event');

        $this->publishedEvent = Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Konser Musik Terbuka 2026',
            'slug' => 'konser-musik-terbuka-2026',
            'type' => 'concert',
            'event_start_date' => Carbon::now()->addDays(5),
            'event_end_date' => Carbon::now()->addDays(5)->addHours(4),
            'venue' => 'Gelora Bung Karno',
            'city' => 'Jakarta',
            'status' => 'published',
            'is_free' => false,
            'security_code' => '123456',
            'purchase_flow' => 'evoucher',
        ]);

        TicketCategory::create([
            'event_id' => $this->publishedEvent->id,
            'tenant_id' => $this->tenant->id,
            'name' => 'VIP Regular',
            'price' => 150000,
            'quota' => 100,
            'is_active' => true,
        ]);

        $this->privateEvent = Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pertemuan Eksklusif Rahasia 2026',
            'slug' => 'rahasia-eksklusif-2026',
            'type' => 'seminar',
            'event_start_date' => Carbon::now()->addDays(10),
            'event_end_date' => Carbon::now()->addDays(10)->addHours(3),
            'venue' => 'Private Hall Room',
            'city' => 'Jakarta',
            'status' => 'private',
            'is_free' => false,
            'security_code' => '654321',
            'purchase_flow' => 'evoucher',
        ]);

        TicketCategory::create([
            'event_id' => $this->privateEvent->id,
            'tenant_id' => $this->tenant->id,
            'name' => 'Private Pass',
            'price' => 250000,
            'quota' => 50,
            'is_active' => true,
        ]);
    }

    public function test_homepage_does_not_show_private_event()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Konser Musik Terbuka 2026');
        $response->assertDontSee('Pertemuan Eksklusif Rahasia 2026');
    }

    public function test_tenant_public_profile_does_not_show_private_event()
    {
        $response = $this->get(route('tenant.events', $this->tenant->slug));

        $response->assertStatus(200);
        $response->assertSee('Konser Musik Terbuka 2026');
        $response->assertDontSee('Pertemuan Eksklusif Rahasia 2026');
    }

    public function test_private_event_is_accessible_via_direct_slug_link()
    {
        $response = $this->get(route('events.show', $this->privateEvent->slug));

        $response->assertStatus(200);
        $response->assertSee('Pertemuan Eksklusif Rahasia 2026');
        $response->assertSee('Private Pass');
    }

    public function test_draft_event_is_not_accessible_via_slug()
    {
        $draftEvent = Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Event Draft Rahasia Belum Siap',
            'slug' => 'draft-event-2026',
            'type' => 'concert',
            'event_start_date' => Carbon::now()->addDays(15),
            'event_end_date' => Carbon::now()->addDays(15)->addHours(3),
            'venue' => 'Draft Venue',
            'city' => 'Jakarta',
            'status' => 'draft',
            'is_free' => false,
            'security_code' => '112233',
            'purchase_flow' => 'evoucher',
        ]);

        $response = $this->get(route('events.show', $draftEvent->slug));
        $response->assertStatus(404);
    }

    public function test_private_event_checkout_is_accessible_and_validates_input()
    {
        // Should reach the checkout validation logic (not 404)
        $response = $this->post(route('checkout.process', $this->privateEvent->slug), []);

        // Validation fails because required fields are empty, confirming route and event are active
        $response->assertSessionHasErrors(['ticket_category_id', 'quantity']);
    }

    public function test_organizer_can_create_event_with_private_status()
    {
        $response = $this->actingAs($this->organizer)
            ->post(route('organizer.events.store'), [
                'name' => 'Event Internal Komite',
                'venue' => 'Kantor Pusat',
                'city' => 'Jakarta',
                'event_start_date' => Carbon::now()->addDays(20)->toDateTimeString(),
                'event_end_date' => Carbon::now()->addDays(20)->addHours(2)->toDateTimeString(),
                'status' => 'private',
                'security_code' => '999888',
                'purchase_flow' => 'evoucher',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('events', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Event Internal Komite',
            'status' => 'private',
        ]);
    }

    public function test_organizer_can_update_event_to_private_status()
    {
        $response = $this->actingAs($this->organizer)
            ->put(route('organizer.events.update', $this->publishedEvent->id), [
                'name' => $this->publishedEvent->name,
                'venue' => $this->publishedEvent->venue,
                'city' => $this->publishedEvent->city,
                'event_start_date' => $this->publishedEvent->event_start_date->toDateTimeString(),
                'event_end_date' => $this->publishedEvent->event_end_date->toDateTimeString(),
                'status' => 'private',
                'security_code' => $this->publishedEvent->security_code,
                'purchase_flow' => 'evoucher',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('events', [
            'id' => $this->publishedEvent->id,
            'status' => 'private',
        ]);
    }
}
