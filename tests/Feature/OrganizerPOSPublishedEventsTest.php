<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Tenant;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrganizerPOSPublishedEventsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;
    protected Event $publishedEvent;
    protected Event $draftEvent;
    protected TicketCategory $publishedCategory;

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

        $this->publishedEvent = Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Published Rock Fest',
            'slug' => 'published-rock-fest',
            'venue' => 'Gelora Bung Karno',
            'city' => 'Jakarta',
            'event_start_date' => now()->addDays(10),
            'event_end_date' => now()->addDays(11),
            'status' => 'published',
            'is_free' => false,
        ]);

        $this->publishedCategory = TicketCategory::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->publishedEvent->id,
            'name' => 'VIP Regular',
            'price' => 150000,
            'quota' => 100,
            'is_active' => true,
        ]);

        $this->draftEvent = Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Draft Jazz Night',
            'slug' => 'draft-jazz-night',
            'venue' => 'Balai Sarbini',
            'city' => 'Jakarta',
            'event_start_date' => now()->addDays(20),
            'event_end_date' => now()->addDays(21),
            'status' => 'draft',
            'is_free' => false,
        ]);
    }

    public function test_pos_index_only_displays_published_events(): void
    {
        $response = $this->actingAs($this->user)->get(route('organizer.pos.index'));

        $response->assertStatus(200);
        $response->assertSee('Published Rock Fest');
        $response->assertDontSee('Draft Jazz Night');
    }

    public function test_pos_create_blocks_non_published_event(): void
    {
        $response = $this->actingAs($this->user)->get(route('organizer.pos.create', $this->draftEvent));

        $response->assertRedirect(route('organizer.pos.index'));
        $response->assertSessionHas('error');
    }

    public function test_pos_create_allows_published_event(): void
    {
        $response = $this->actingAs($this->user)->get(route('organizer.pos.create', $this->publishedEvent));

        $response->assertStatus(200);
        $response->assertSee('Published Rock Fest');
    }
}
