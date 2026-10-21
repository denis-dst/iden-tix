<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NumericTicketCodeTest extends TestCase
{
    use RefreshDatabase;

    protected User $organizer;
    protected Tenant $tenant;
    protected Event $event;
    protected TicketCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Identix Music Festival',
            'slug' => 'identix-music-festival',
            'email' => 'fest@example.com',
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
            'name' => 'Live Concert 2026',
            'slug' => 'live-concert-2026',
            'type' => 'concert',
            'event_start_date' => Carbon::now()->addDays(3),
            'event_end_date' => Carbon::now()->addDays(3)->addHours(5),
            'venue' => 'Gelora Bung Karno',
            'city' => 'Jakarta',
            'status' => 'published',
            'is_free' => false,
            'security_code' => '654321',
            'purchase_flow' => 'evoucher',
        ]);

        $this->category = TicketCategory::create([
            'event_id' => $this->event->id,
            'tenant_id' => $this->tenant->id,
            'name' => 'VIP Tribune',
            'price' => 500000,
            'quota' => 100,
            'is_active' => true,
        ]);
    }

    public function test_ticket_code_generator_produces_pure_numeric_digits(): void
    {
        $code = Ticket::generateTicketCode();

        // Must be exactly 10 characters long
        $this->assertEquals(10, strlen($code));

        // Must contain ONLY digits (0-9), no alphabet, no hyphens, no prefixes
        $this->assertMatchesRegularExpression('/^[1-9][0-9]{9}$/', $code);
        $this->assertTrue(ctype_digit($code));
    }

    public function test_ticket_model_boot_automatically_assigns_numeric_code_when_empty(): void
    {
        $transaction = Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->category->id,
            'quantity' => 1,
            'reference_no' => 'TX-TEST-NUM-01',
            'customer_name' => 'Ahmad',
            'customer_email' => 'ahmad@example.com',
            'customer_phone' => '081234567890',
            'total_amount' => 500000,
            'payment_status' => 'paid',
            'payment_method' => 'WAGO',
        ]);

        $ticket = Ticket::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'transaction_id' => $transaction->id,
            'ticket_category_id' => $this->category->id,
            'status' => 'sold',
        ]);

        $this->assertNotEmpty($ticket->ticket_code);
        $this->assertMatchesRegularExpression('/^[1-9][0-9]{9}$/', $ticket->ticket_code);
        $this->assertTrue(ctype_digit($ticket->ticket_code));
    }

    public function test_pos_generates_tickets_with_pure_numeric_ticket_code(): void
    {
        $response = $this->actingAs($this->organizer)
            ->post(route('organizer.pos.store', $this->event), [
                'ticket_category_id' => $this->category->id,
                'quantity' => 2,
                'customer_name' => 'Bambang Sudiro',
                'customer_email' => 'bambang@example.com',
                'customer_phone' => '081299998888',
                'payment_method' => 'Tunai',
                'cash_received' => 1000000,
            ]);

        $response->assertSessionHasNoErrors();

        $tickets = Ticket::where('event_id', $this->event->id)->get();
        $this->assertCount(2, $tickets);

        foreach ($tickets as $t) {
            $this->assertMatchesRegularExpression('/^[1-9][0-9]{9}$/', $t->ticket_code);
            $this->assertTrue(ctype_digit($t->ticket_code));
        }
    }

    public function test_gate_scanner_and_manual_input_accepts_numeric_code(): void
    {
        $numericCode = Ticket::generateTicketCode();

        $transaction = Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->category->id,
            'quantity' => 1,
            'reference_no' => 'TX-TEST-GATE-01',
            'customer_name' => 'Siti Aminah',
            'customer_email' => 'siti@example.com',
            'customer_phone' => '081233334444',
            'total_amount' => 500000,
            'payment_status' => 'paid',
            'payment_method' => 'WAGO',
        ]);

        $ticket = Ticket::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'transaction_id' => $transaction->id,
            'ticket_category_id' => $this->category->id,
            'ticket_code' => $numericCode,
            'status' => 'sold',
        ]);

        // 1. Direct raw numeric entry (scanner reading QR or manual digits input)
        $response1 = $this->actingAs($this->organizer)
            ->postJson(route('organizer.gate.process'), [
                'ticket_code' => $numericCode,
                'mode' => 'IN',
                'gate_name' => 'Pintu Barat',
                'event_id' => $this->event->id,
            ]);

        $response1->assertStatus(200);
        $this->assertTrue($response1->json('success'));
        $this->assertEquals($numericCode, $response1->json('ticket_code'));

        // 2. Formatted with dashes/spaces from operator manual entry
        $formattedCode = substr($numericCode, 0, 3) . '-' . substr($numericCode, 3, 3) . '-' . substr($numericCode, 6);
        $ticket->gateLogs()->delete(); // Reset logs to allow scan check

        $response2 = $this->actingAs($this->organizer)
            ->postJson(route('organizer.gate.process'), [
                'ticket_code' => $formattedCode,
                'mode' => 'IN',
                'gate_name' => 'Pintu Barat',
                'event_id' => $this->event->id,
            ]);

        $response2->assertStatus(200);
        $this->assertTrue($response2->json('success'));
        $this->assertEquals($numericCode, $response2->json('ticket_code'));
    }

    public function test_redeem_check_and_process_accepts_numeric_code(): void
    {
        $numericCode = Ticket::generateTicketCode();

        $transaction = Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->category->id,
            'quantity' => 1,
            'reference_no' => 'TX-TEST-REDEEM-01',
            'customer_name' => 'Rian Hidayat',
            'customer_email' => 'rian@example.com',
            'customer_phone' => '081277778888',
            'total_amount' => 500000,
            'payment_status' => 'paid',
            'payment_method' => 'WAGO',
        ]);

        $ticket = Ticket::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'transaction_id' => $transaction->id,
            'ticket_category_id' => $this->category->id,
            'ticket_code' => $numericCode,
            'status' => 'sold',
        ]);

        // Check endpoint
        $checkResponse = $this->actingAs($this->organizer)
            ->postJson(route('organizer.redeem.check'), [
                'ticket_code' => $numericCode,
                'event_id' => $this->event->id,
            ]);

        $checkResponse->assertStatus(200);
        $this->assertTrue($checkResponse->json('success'));
        $this->assertEquals('Rian Hidayat', $checkResponse->json('customer_name'));

        // Formatted code check
        $formattedCode = substr($numericCode, 0, 3) . '-' . substr($numericCode, 3, 3) . '-' . substr($numericCode, 6);
        $formattedCheck = $this->actingAs($this->organizer)
            ->postJson(route('organizer.redeem.check'), [
                'ticket_code' => $formattedCode,
                'event_id' => $this->event->id,
            ]);
        $formattedCheck->assertStatus(200);
        $this->assertTrue($formattedCheck->json('success'));
        $this->assertEquals('Rian Hidayat', $formattedCheck->json('customer_name'));

        // Redeem process endpoint
        $fakeBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY44YAAAAASUVORK5CYII=';
        $redeemResponse = $this->actingAs($this->organizer)
            ->postJson(route('organizer.redeem.process'), [
                'ticket_code' => $numericCode,
                'photo' => $fakeBase64,
                'event_id' => $this->event->id,
            ]);

        $redeemResponse->assertStatus(200);
        $this->assertTrue($redeemResponse->json('success'));
        $this->assertEquals('redeemed', $ticket->fresh()->status);
    }
}
