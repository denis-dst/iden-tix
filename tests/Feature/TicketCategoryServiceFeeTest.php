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

class TicketCategoryServiceFeeTest extends TestCase
{
    use RefreshDatabase;

    protected User $organizer;
    protected Tenant $tenant;
    protected Event $event;

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

        $this->event = Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Konser Musik Eksklusif 2026',
            'slug' => 'konser-musik-eksklusif-2026',
            'type' => 'concert',
            'event_start_date' => Carbon::now()->addDays(5),
            'event_end_date' => Carbon::now()->addDays(5)->addHours(4),
            'venue' => 'Jakarta Convention Center',
            'city' => 'Jakarta',
            'status' => 'published',
            'is_free' => false,
            'security_code' => '123456',
            'purchase_flow' => 'evoucher',
            'max_tickets_per_transaction' => 5,
        ]);
    }

    public function test_organizer_can_create_ticket_category_with_service_fee()
    {
        $response = $this->actingAs($this->organizer)
            ->post(route('organizer.events.categories.store', $this->event), [
                'name' => 'VIP Gold',
                'price' => 150000,
                'service_fee' => 7500,
                'quota' => 100,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('ticket_categories', [
            'event_id' => $this->event->id,
            'name' => 'VIP Gold',
            'price' => 150000,
            'service_fee' => 7500,
            'quota' => 100,
        ]);
    }

    public function test_organizer_can_update_ticket_category_service_fee()
    {
        $category = TicketCategory::create([
            'event_id' => $this->event->id,
            'tenant_id' => $this->tenant->id,
            'name' => 'Early Bird',
            'price' => 100000,
            'service_fee' => 3000,
            'quota' => 50,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->organizer)
            ->put(route('organizer.events.categories.update', [$this->event, $category]), [
                'name' => 'Early Bird (Updated)',
                'price' => 100000,
                'service_fee' => 5000,
                'quota' => 50,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('ticket_categories', [
            'id' => $category->id,
            'name' => 'Early Bird (Updated)',
            'price' => 100000,
            'service_fee' => 5000,
        ]);
    }

    public function test_free_event_forces_service_fee_to_zero()
    {
        $freeEvent = Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Seminar Gratis 2026',
            'slug' => 'seminar-gratis-2026',
            'type' => 'seminar',
            'event_start_date' => Carbon::now()->addDays(7),
            'event_end_date' => Carbon::now()->addDays(7)->addHours(2),
            'venue' => 'Auditorium',
            'city' => 'Jakarta',
            'status' => 'published',
            'is_free' => true,
            'security_code' => '999888',
            'purchase_flow' => 'evoucher',
        ]);

        $response = $this->actingAs($this->organizer)
            ->post(route('organizer.events.categories.store', $freeEvent), [
                'name' => 'Peserta Umum',
                'price' => 0,
                'service_fee' => 5000, // Should be forced to 0
                'quota' => 200,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('ticket_categories', [
            'event_id' => $freeEvent->id,
            'name' => 'Peserta Umum',
            'price' => 0,
            'service_fee' => 0,
        ]);
    }

    public function test_checkout_calculates_total_including_service_fee()
    {
        $category = TicketCategory::create([
            'event_id' => $this->event->id,
            'tenant_id' => $this->tenant->id,
            'name' => 'Festival Reguler',
            'price' => 50000,
            'service_fee' => 2500,
            'quota' => 100,
            'is_active' => true,
        ]);

        $wagoMock = \Mockery::mock(\App\Services\WagoService::class);
        $wagoMock->shouldReceive('createPayment')
            ->andReturn([
                'success' => true,
                'payment_url' => 'https://pay.wago-id.web.id/test',
            ]);
        $this->app->instance(\App\Services\WagoService::class, $wagoMock);

        // Purchase 2 tickets: (50000 + 2500) * 2 = 105000
        $response = $this->postJson(route('checkout.process', $this->event->slug), [
            'ticket_category_id' => $category->id,
            'quantity' => 2,
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567899',
            'nik' => '1234567890123456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('transactions', [
            'event_id' => $this->event->id,
            'ticket_category_id' => $category->id,
            'quantity' => 2,
            'service_fee' => 5000,
            'total_amount' => 105000,
            'customer_name' => 'Budi Santoso',
        ]);
    }

    public function test_pos_cash_calculates_total_including_service_fee()
    {
        $category = TicketCategory::create([
            'event_id' => $this->event->id,
            'tenant_id' => $this->tenant->id,
            'name' => 'On The Spot',
            'price' => 75000,
            'service_fee' => 5000,
            'quota' => 100,
            'is_active' => true,
        ]);

        // Purchase 2 tickets via POS Tunai: (75000 + 5000) * 2 = 160000
        $response = $this->actingAs($this->organizer)
            ->post(route('organizer.pos.store', $this->event), [
                'ticket_category_id' => $category->id,
                'quantity' => 2,
                'customer_name' => 'Joko Widodo',
                'customer_email' => 'joko@example.com',
                'customer_phone' => '081987654321',
                'payment_method' => 'Tunai',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('transactions', [
            'event_id' => $this->event->id,
            'ticket_category_id' => $category->id,
            'quantity' => 2,
            'service_fee' => 10000,
            'total_amount' => 160000,
            'payment_status' => 'paid',
            'channel' => 'pos',
        ]);
    }
}
