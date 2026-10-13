<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Gate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GateLogoutButtonTest extends TestCase
{
    use RefreshDatabase;

    protected User $gateUser;
    protected Tenant $tenant;
    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Petugas Gate']);

        $this->tenant = Tenant::create([
            'name' => 'Konser Nusantara',
            'slug' => 'konser-nusantara',
            'email' => 'admin@konsernusantara.com',
            'status' => 'active',
        ]);

        $this->gateUser = User::create([
            'name' => 'Petugas Gate 1',
            'email' => 'gate@konsernusantara.com',
            'password' => bcrypt('password123'),
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);
        $this->gateUser->assignRole('Petugas Gate');

        $this->event = Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pesta Musik 2026',
            'slug' => 'pesta-musik-2026',
            'venue' => 'Stadion Utama',
            'city' => 'Jakarta',
            'event_start_date' => now()->addDays(5),
            'event_end_date' => now()->addDays(6),
            'status' => 'published',
            'is_free' => false,
            'security_code' => '123456',
        ]);
    }

    public function test_gate_index_displays_logout_action(): void
    {
        $response = $this->actingAs($this->gateUser)->get(route('organizer.gate.index'));

        $response->assertStatus(200);
        $response->assertSee(route('logout'));
        $response->assertSee('Keluar Akun');
    }

    public function test_gate_verify_form_displays_logout_action(): void
    {
        $response = $this->actingAs($this->gateUser)->get(route('organizer.gate.verify', $this->event));

        $response->assertStatus(200);
        $response->assertSee(route('logout'));
        $response->assertSee('Keluar');
    }

    public function test_gate_setup_form_displays_logout_action(): void
    {
        Gate::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'name' => 'Gate A',
            'type' => 'in',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->gateUser)
            ->withSession(['gate_event_id' => $this->event->id])
            ->get(route('organizer.gate.setup', $this->event));

        $response->assertStatus(200);
        $response->assertSee(route('logout'));
        $response->assertSee('Keluar');
    }

    public function test_gate_scan_displays_logout_action(): void
    {
        $response = $this->actingAs($this->gateUser)
            ->withSession([
                'gate_event_id' => $this->event->id,
                'gate_name' => 'Main Gate A',
            ])
            ->get(route('organizer.gate.scan', $this->event));

        $response->assertStatus(200);
        $response->assertSee(route('logout'));
        $response->assertSee('Keluar');
    }
}
