<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrganizerEventStatusFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Penyedia Event']);

        $this->tenant = Tenant::create([
            'name' => 'Konser Nusantara',
            'slug' => 'konser-nusantara',
            'email' => 'admin@konsernusantara.com',
            'status' => 'active',
        ]);

        $this->user = User::create([
            'name' => 'Organizer Lead',
            'email' => 'lead@konsernusantara.com',
            'password' => bcrypt('password123'),
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);
        $this->user->assignRole('Penyedia Event');

        // Create events with different statuses
        Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Rock Fest 2026',
            'slug' => 'rock-fest-2026',
            'venue' => 'Gelora Bung Karno',
            'city' => 'Jakarta',
            'event_start_date' => now()->addDays(10),
            'event_end_date' => now()->addDays(11),
            'status' => 'published',
            'is_free' => false,
        ]);

        Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Jazz Night Special',
            'slug' => 'jazz-night-special',
            'venue' => 'Balai Sarbini',
            'city' => 'Jakarta',
            'event_start_date' => now()->addDays(20),
            'event_end_date' => now()->addDays(20),
            'status' => 'draft',
            'is_free' => false,
        ]);

        Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Indie Fest Cancelled Event',
            'slug' => 'indie-fest-cancelled',
            'venue' => 'Lapangan Renon',
            'city' => 'Denpasar',
            'event_start_date' => now()->addDays(30),
            'event_end_date' => now()->addDays(30),
            'status' => 'cancelled',
            'is_free' => false,
        ]);
    }

    public function test_organizer_events_index_shows_all_statuses_by_default(): void
    {
        $response = $this->actingAs($this->user)->get(route('organizer.events.index'));

        $response->assertStatus(200);
        $response->assertSee('Rock Fest 2026');
        $response->assertSee('Jazz Night Special');
        $response->assertSee('Indie Fest Cancelled Event');
        $response->assertSee('Total Event');
        $response->assertSee('Semua Status');
    }

    public function test_filter_by_published_status(): void
    {
        $response = $this->actingAs($this->user)->get(route('organizer.events.index', ['status' => 'published']));

        $response->assertStatus(200);
        $response->assertSee('Rock Fest 2026');
        $response->assertDontSee('Jazz Night Special');
        $response->assertDontSee('Indie Fest Cancelled Event');
    }

    public function test_filter_by_draft_status(): void
    {
        $response = $this->actingAs($this->user)->get(route('organizer.events.index', ['status' => 'draft']));

        $response->assertStatus(200);
        $response->assertSee('Jazz Night Special');
        $response->assertDontSee('Rock Fest 2026');
        $response->assertDontSee('Indie Fest Cancelled Event');
    }

    public function test_filter_by_cancelled_status(): void
    {
        $response = $this->actingAs($this->user)->get(route('organizer.events.index', ['status' => 'cancelled']));

        $response->assertStatus(200);
        $response->assertSee('Indie Fest Cancelled Event');
        $response->assertDontSee('Rock Fest 2026');
        $response->assertDontSee('Jazz Night Special');
    }

    public function test_filter_by_keyword_and_status(): void
    {
        $response = $this->actingAs($this->user)->get(route('organizer.events.index', [
            'q' => 'Rock',
            'status' => 'published',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Rock Fest 2026');
        $response->assertDontSee('Jazz Night Special');
    }

    public function test_filter_returns_empty_state_with_reset_button(): void
    {
        $response = $this->actingAs($this->user)->get(route('organizer.events.index', [
            'q' => 'NonExistentEventName123',
            'status' => 'published',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Tidak ada event yang sesuai filter');
        $response->assertSee('Reset Filter');
    }
}
