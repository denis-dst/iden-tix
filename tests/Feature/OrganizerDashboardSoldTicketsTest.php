<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrganizerDashboardSoldTicketsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;
    protected Event $publishedEvent;
    protected Event $draftEvent;
    protected TicketCategory $publishedCategory;
    protected TicketCategory $draftCategory;

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

        // 1. Published Event
        $this->publishedEvent = Event::create([
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

        $this->publishedCategory = TicketCategory::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->publishedEvent->id,
            'name' => 'VIP Regular',
            'price' => 250000,
            'quota' => 100,
            'sold_count' => 2,
            'is_active' => true,
        ]);

        // 2. Draft Event
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

        $this->draftCategory = TicketCategory::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->draftEvent->id,
            'name' => 'Regular Draft',
            'price' => 100000,
            'quota' => 500,
            'sold_count' => 1,
            'is_active' => true,
        ]);
    }

    public function test_organizer_dashboard_sold_tickets_matches_transactions_statistics(): void
    {
        // 1. Valid Online Sale for Published Event
        $onlineTx = Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->publishedEvent->id,
            'ticket_category_id' => $this->publishedCategory->id,
            'reference_no' => 'INV-ONLINE-001',
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@example.com',
            'customer_phone' => '08123456789',
            'quantity' => 1,
            'total_amount' => 250000,
            'payment_status' => 'paid',
            'payment_method' => 'QRIS (WAGO)',
            'channel' => 'online',
            'paid_at' => now(),
        ]);

        Ticket::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->publishedEvent->id,
            'transaction_id' => $onlineTx->id,
            'ticket_category_id' => $this->publishedCategory->id,
            'ticket_code' => 'GTX-ONLINE-001',
            'status' => 'sold',
        ]);

        // 2. Valid POS Direct Sale for Published Event
        $posTx = Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->publishedEvent->id,
            'ticket_category_id' => $this->publishedCategory->id,
            'reference_no' => 'POS-20261013-ABC123',
            'customer_name' => 'Siti Nurhaliza',
            'customer_email' => 'siti@example.com',
            'customer_phone' => '08987654321',
            'quantity' => 1,
            'total_amount' => 250000,
            'payment_status' => 'paid',
            'payment_method' => 'Tunai',
            'channel' => 'pos',
            'paid_at' => now(),
        ]);

        Ticket::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->publishedEvent->id,
            'transaction_id' => $posTx->id,
            'ticket_category_id' => $this->publishedCategory->id,
            'ticket_code' => 'GTX-POS-001',
            'status' => 'sold',
        ]);

        // 3. Gate Wristband Scan (Synthetic, NOT a genuine sales transaction)
        $gateWbTx = Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->publishedEvent->id,
            'ticket_category_id' => $this->publishedCategory->id,
            'reference_no' => 'WB-' . $this->publishedCategory->id . '-1',
            'customer_name' => 'Gelang Fisik #1 (VIP Regular)',
            'customer_email' => 'wristband@gentix-apps.com',
            'customer_phone' => '-',
            'quantity' => 1,
            'total_amount' => 250000,
            'payment_status' => 'paid',
            'payment_method' => 'OFFLINE_WRISTBAND',
            'paid_at' => now(),
        ]);

        Ticket::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->publishedEvent->id,
            'transaction_id' => $gateWbTx->id,
            'ticket_category_id' => $this->publishedCategory->id,
            'ticket_code' => 'GTX-WB-GATE001',
            'wristband_qr' => 'WB-C' . $this->publishedCategory->id . '-0001',
            'status' => 'redeemed',
        ]);

        // 4. Voided (Cancelled/Refunded) Ticket
        $voidTx = Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->publishedEvent->id,
            'ticket_category_id' => $this->publishedCategory->id,
            'reference_no' => 'INV-VOID-001',
            'customer_name' => 'Cancelled Buyer',
            'customer_email' => 'void@example.com',
            'customer_phone' => '0811111111',
            'quantity' => 0,
            'total_amount' => 0,
            'payment_status' => 'refunded',
            'channel' => 'online',
        ]);

        Ticket::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->publishedEvent->id,
            'transaction_id' => $voidTx->id,
            'ticket_category_id' => $this->publishedCategory->id,
            'ticket_code' => 'GTX-VOID-001',
            'status' => 'void',
        ]);

        // 5. Ticket on Draft Event (Should NOT count in published stats)
        $draftTx = Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->draftEvent->id,
            'ticket_category_id' => $this->draftCategory->id,
            'reference_no' => 'INV-DRAFT-001',
            'customer_name' => 'Draft Buyer',
            'customer_email' => 'draft@example.com',
            'customer_phone' => '0822222222',
            'quantity' => 1,
            'total_amount' => 100000,
            'payment_status' => 'paid',
            'channel' => 'online',
        ]);

        Ticket::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->draftEvent->id,
            'transaction_id' => $draftTx->id,
            'ticket_category_id' => $this->draftCategory->id,
            'ticket_code' => 'GTX-DRAFT-001',
            'status' => 'sold',
        ]);

        // Get transactions page to check source-of-truth totalSold
        $transactionsResponse = $this->actingAs($this->user)->get(route('organizer.transactions.index'));
        $transactionsResponse->assertStatus(200);
        $expectedTotalSold = $transactionsResponse->viewData('totalSold');

        // Total sold in organizer/transactions must be 2 (online + pos)
        $this->assertEquals(2, $expectedTotalSold);

        // Get dashboard page
        $dashboardResponse = $this->actingAs($this->user)->get(route('organizer.dashboard'));
        $dashboardResponse->assertStatus(200);

        // Total tickets sold on dashboard must match organizer/transactions exactly
        $dashboardSold = $dashboardResponse->viewData('totalTicketsSold');
        $this->assertEquals($expectedTotalSold, $dashboardSold);
        $this->assertEquals(2, $dashboardSold);

        // Events list in dashboard should have accurate tickets_count (excluding void & wristbands)
        $events = $dashboardResponse->viewData('events');
        $publishedEventInList = $events->firstWhere('id', $this->publishedEvent->id);
        $this->assertEquals(2, $publishedEventInList->tickets_count);
    }

    public function test_organizer_dashboard_sold_tickets_with_event_filter(): void
    {
        // Add 1 valid sale to published event
        $tx = Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->publishedEvent->id,
            'ticket_category_id' => $this->publishedCategory->id,
            'reference_no' => 'INV-ONLINE-FILTER',
            'customer_name' => 'Filter Buyer',
            'customer_email' => 'filter@example.com',
            'customer_phone' => '08123456789',
            'quantity' => 1,
            'total_amount' => 250000,
            'payment_status' => 'paid',
            'channel' => 'online',
        ]);

        Ticket::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->publishedEvent->id,
            'transaction_id' => $tx->id,
            'ticket_category_id' => $this->publishedCategory->id,
            'ticket_code' => 'GTX-FILTER-001',
            'status' => 'sold',
        ]);

        // Filter by published event
        $txResponse = $this->actingAs($this->user)->get(route('organizer.transactions.index', [
            'event_id' => $this->publishedEvent->id,
        ]));
        $dashResponse = $this->actingAs($this->user)->get(route('organizer.dashboard', [
            'event_id' => $this->publishedEvent->id,
        ]));

        $this->assertEquals($txResponse->viewData('totalSold'), $dashResponse->viewData('totalTicketsSold'));
        $this->assertEquals(1, $dashResponse->viewData('totalTicketsSold'));

        // Filter by draft event (should be 0, matching organizer/transactions)
        $dashDraftResponse = $this->actingAs($this->user)->get(route('organizer.dashboard', [
            'event_id' => $this->draftEvent->id,
        ]));
        $this->assertEquals(0, $dashDraftResponse->viewData('totalTicketsSold'));
    }
}
