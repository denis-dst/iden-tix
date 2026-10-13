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

class OrganizerTransactionSalesFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;
    protected Event $event;
    protected TicketCategory $category;

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

        $this->event = Event::create([
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

        $this->category = TicketCategory::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'name' => 'VIP Regular',
            'price' => 250000,
            'quota' => 100,
            'sold_count' => 2,
            'is_active' => true,
        ]);
    }

    public function test_organizer_transactions_page_only_shows_online_and_direct_sales(): void
    {
        // 1. Online Sale
        $onlineTx = Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->category->id,
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
            'event_id' => $this->event->id,
            'transaction_id' => $onlineTx->id,
            'ticket_category_id' => $this->category->id,
            'ticket_code' => 'GTX-ONLINE-001',
            'status' => 'sold',
        ]);

        // 2. Direct POS Sale
        $posTx = Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->category->id,
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
            'event_id' => $this->event->id,
            'transaction_id' => $posTx->id,
            'ticket_category_id' => $this->category->id,
            'ticket_code' => 'GTX-POS-001',
            'status' => 'sold',
        ]);

        // 3. Gate Wristband Scan (Synthetic Transaction from gate scan)
        $gateWbTx = Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->category->id,
            'reference_no' => 'WB-' . $this->category->id . '-1',
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
            'event_id' => $this->event->id,
            'transaction_id' => $gateWbTx->id,
            'ticket_category_id' => $this->category->id,
            'ticket_code' => 'GTX-WB-GATE001',
            'wristband_qr' => 'WB-C' . $this->category->id . '-0001',
            'status' => 'redeemed',
        ]);

        // 4. Offline Dummy Stock
        $dummyStockTx = Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->category->id,
            'reference_no' => 'STOCK-999',
            'customer_name' => 'OFFLINE STOCK',
            'customer_email' => 'wristband@gentix-apps.com',
            'customer_phone' => '-',
            'quantity' => 1,
            'total_amount' => 250000,
            'payment_status' => 'paid',
            'payment_method' => 'OFFLINE STOCK',
            'paid_at' => now(),
        ]);

        // Send request to organizer transactions page
        $response = $this->actingAs($this->user)->get(route('organizer.transactions.index'));

        $response->assertStatus(200);

        // Assert legitimate sales are visible
        $response->assertSee('INV-ONLINE-001');
        $response->assertSee('Budi Santoso');
        $response->assertSee('POS-20261013-ABC123');
        $response->assertSee('Siti Nurhaliza');

        // Assert gate scan and dummy stock are NOT visible
        $response->assertDontSee('WB-' . $this->category->id . '-1');
        $response->assertDontSee('Gelang Fisik #1 (VIP Regular)');
        $response->assertDontSee('STOCK-999');
        $response->assertDontSee('OFFLINE STOCK');
    }

    public function test_organizer_transactions_channel_filtering(): void
    {
        // 1. Online Sale
        Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->category->id,
            'reference_no' => 'INV-ONLINE-999',
            'customer_name' => 'Online Buyer',
            'customer_email' => 'buyer@example.com',
            'customer_phone' => '08123456789',
            'quantity' => 1,
            'total_amount' => 250000,
            'payment_status' => 'paid',
            'payment_method' => 'QRIS',
            'channel' => 'online',
            'paid_at' => now(),
        ]);

        // 2. Direct POS Sale
        Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->category->id,
            'reference_no' => 'POS-20261013-POS999',
            'customer_name' => 'POS Buyer',
            'customer_email' => 'posbuyer@example.com',
            'customer_phone' => '08987654321',
            'quantity' => 1,
            'total_amount' => 250000,
            'payment_status' => 'paid',
            'payment_method' => 'Tunai',
            'channel' => 'pos',
            'paid_at' => now(),
        ]);

        // Filter channel=online
        $onlineResponse = $this->actingAs($this->user)->get(route('organizer.transactions.index', ['channel' => 'online']));
        $onlineResponse->assertStatus(200);
        $onlineResponse->assertSee('INV-ONLINE-999');
        $onlineResponse->assertDontSee('POS-20261013-POS999');

        // Filter channel=pos
        $posResponse = $this->actingAs($this->user)->get(route('organizer.transactions.index', ['channel' => 'pos']));
        $posResponse->assertStatus(200);
        $posResponse->assertSee('POS-20261013-POS999');
        $posResponse->assertDontSee('INV-ONLINE-999');
    }
}
