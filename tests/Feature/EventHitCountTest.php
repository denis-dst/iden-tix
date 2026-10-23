<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Tenant;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventHitCountTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;
    protected Event $event;
    protected TicketCategory $ticketCategory;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Penyedia Event']);

        $this->tenant = Tenant::create([
            'name' => 'Karnaval Musik',
            'slug' => 'karnaval-musik',
            'email' => 'admin@karnavalmusik.com',
            'status' => 'active',
        ]);

        $this->user = User::create([
            'name' => 'Owner Musik',
            'email' => 'owner@karnavalmusik.com',
            'password' => bcrypt('secret123'),
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);
        $this->user->assignRole('Penyedia Event');

        $this->event = Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Konser Harmoni Semesta',
            'slug' => 'konser-harmoni-semesta',
            'venue' => 'Stadion Madya',
            'city' => 'Jakarta',
            'event_start_date' => now()->addDays(5),
            'event_end_date' => now()->addDays(6),
            'status' => 'published',
            'is_free' => false,
            'views_count' => 0,
        ]);

        $this->ticketCategory = TicketCategory::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'name' => 'Festival VIP',
            'price' => 150000,
            'quota' => 50,
            'sold_count' => 0,
            'is_active' => true,
        ]);
    }

    public function test_public_event_page_increments_views_count(): void
    {
        $this->assertEquals(0, $this->event->fresh()->views_count);

        $response = $this->get(route('events.show', $this->event->slug));
        $response->assertStatus(200);

        $this->assertEquals(1, $this->event->fresh()->views_count);
        $this->assertEquals(1, $this->event->fresh()->hit_count);
    }

    public function test_rapid_reloads_in_same_session_respect_cooldown(): void
    {
        $this->get(route('events.show', $this->event->slug))->assertStatus(200);
        $this->assertEquals(1, $this->event->fresh()->views_count);

        // Immediate second visit in same session
        $this->get(route('events.show', $this->event->slug))->assertStatus(200);
        $this->assertEquals(1, $this->event->fresh()->views_count);
    }

    public function test_organizer_dashboard_displays_event_hit_count(): void
    {
        $this->event->update(['views_count' => 842]);

        $response = $this->actingAs($this->user)->get(route('organizer.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Total Hit Publik');
        $response->assertSee('Hit Publik');
        $response->assertSee('842');
        $response->assertSee(route('events.show', $this->event->slug));
    }

    public function test_organizer_events_index_displays_event_hit_count(): void
    {
        $this->event->update(['views_count' => 1250]);

        $response = $this->actingAs($this->user)->get(route('organizer.events.index'));

        $response->assertStatus(200);
        $response->assertSee('1.250 Hits');
    }
}
