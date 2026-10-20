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

class OrganizerReportRevenueSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;
    protected Event $event;
    protected TicketCategory $categoryA;
    protected TicketCategory $categoryB;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Penyedia Event']);

        $this->tenant = Tenant::create([
            'name' => 'Festival Mandiri',
            'slug' => 'festival-mandiri',
            'email' => 'admin@festivalmandiri.com',
            'status' => 'active',
        ]);

        $this->user = User::create([
            'name' => 'Finance Lead',
            'email' => 'finance@festivalmandiri.com',
            'password' => bcrypt('password123'),
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);
        $this->user->assignRole('Penyedia Event');

        $this->event = Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Festival Musik Semesta 2026',
            'slug' => 'festival-musik-semesta-2026',
            'venue' => 'Stadion Utama',
            'city' => 'Bandung',
            'event_start_date' => now()->addDays(5),
            'event_end_date' => now()->addDays(6),
            'status' => 'published',
            'is_free' => false,
        ]);

        $this->categoryA = TicketCategory::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'name' => 'VIP Access',
            'price' => 100000,
            'quota' => 50,
            'is_active' => true,
            'hex_color' => '#f59e0b',
        ]);

        $this->categoryB = TicketCategory::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'name' => 'Regular',
            'price' => 50000,
            'quota' => 200,
            'is_active' => true,
            'hex_color' => '#3b82f6',
        ]);
    }

    public function test_revenue_in_reports_matches_transactions_sales_and_excludes_wristband_logs(): void
    {
        // 1. Genuine Paid Online Transaction (Cat A - 2 VIP tickets = 200,000)
        $txOnline = Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->categoryA->id,
            'quantity' => 2,
            'reference_no' => 'INV-ONLINE-001',
            'customer_name' => 'Andi Pratama',
            'customer_email' => 'andi@gmail.com',
            'customer_phone' => '081234567890',
            'total_amount' => 200000,
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'channel' => 'web',
            'paid_at' => now(),
        ]);

        Ticket::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->categoryA->id,
            'transaction_id' => $txOnline->id,
            'ticket_code' => 'TICK-VIP-001',
            'status' => 'sold',
        ]);
        Ticket::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->categoryA->id,
            'transaction_id' => $txOnline->id,
            'ticket_code' => 'TICK-VIP-002',
            'status' => 'redeemed',
        ]);

        // 2. Genuine Paid POS Transaction (Cat B - 1 Regular ticket = 50,000)
        $txPos = Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->categoryB->id,
            'quantity' => 1,
            'reference_no' => 'POS-20261020-XYZ',
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@gmail.com',
            'customer_phone' => '081298765432',
            'total_amount' => 50000,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'channel' => 'pos',
            'paid_at' => now(),
        ]);

        Ticket::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->categoryB->id,
            'transaction_id' => $txPos->id,
            'ticket_code' => 'TICK-REG-001',
            'status' => 'sold',
        ]);

        // 3. Gate Physical Wristband Scan (Not genuine sale - should be excluded from revenue)
        $txWristband = Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->categoryA->id,
            'quantity' => 1,
            'reference_no' => 'WB-99-101',
            'customer_name' => 'Gelang Fisik #101',
            'customer_email' => 'wristband@gentix-apps.com',
            'customer_phone' => '08000000000',
            'total_amount' => 100000,
            'payment_status' => 'paid',
            'payment_method' => 'OFFLINE_WRISTBAND',
            'channel' => 'gate',
            'paid_at' => now(),
        ]);

        Ticket::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->categoryA->id,
            'transaction_id' => $txWristband->id,
            'ticket_code' => 'TICK-WB-001',
            'status' => 'redeemed',
        ]);

        // 4. Offline Stock Placeholder (Should also be excluded)
        Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->categoryB->id,
            'quantity' => 5,
            'reference_no' => 'STOCK-20261020-001',
            'customer_name' => 'OFFLINE STOCK',
            'customer_email' => 'stock@gentix-apps.com',
            'customer_phone' => '08000000001',
            'total_amount' => 250000,
            'payment_status' => 'paid',
            'payment_method' => 'OFFLINE STOCK',
            'channel' => 'offline',
            'paid_at' => now(),
        ]);

        // Expected genuine sales revenue: 200,000 + 50,000 = 250,000
        // Expected genuine sold tickets count: 3 (2 VIP + 1 Regular)

        // Test Laporan Penjualan (/organizer/transactions)
        $responseTransactions = $this->actingAs($this->user)->get(route('organizer.transactions.index'));
        $responseTransactions->assertStatus(200);
        $responseTransactions->assertViewHas('totalRevenue', 250000.0);
        $responseTransactions->assertViewHas('totalSold', 3);

        // Test Laporan Event (/organizer/reports)
        $responseReports = $this->actingAs($this->user)->get(route('organizer.reports.index'));
        $responseReports->assertStatus(200);

        $reportRows = $responseReports->viewData('reportRows');
        $this->assertCount(1, $reportRows);

        $firstReport = $reportRows->first();
        // Event level revenue must match 250,000
        $this->assertEquals(250000.0, $firstReport['revenue']);
        $this->assertEquals(3, $firstReport['sold_count']);
        $this->assertEquals(2, $firstReport['paid_transactions_count']);

        // Category level revenue
        $categories = $firstReport['categories']->keyBy('id');
        $this->assertEquals(200000.0, $categories[$this->categoryA->id]['revenue']);
        $this->assertEquals(2, $categories[$this->categoryA->id]['sold_count']);
        $this->assertEquals(50000.0, $categories[$this->categoryB->id]['revenue']);
        $this->assertEquals(1, $categories[$this->categoryB->id]['sold_count']);

        // Totals array in view
        $totals = $responseReports->viewData('totals');
        $this->assertEquals(250000.0, $totals['revenue']);
        $this->assertEquals(3, $totals['sold']);
        $this->assertEquals(2, $totals['paid_transactions']);
    }

    public function test_organizer_reports_only_lists_published_events(): void
    {
        // Create draft event
        Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Draft Concert 2026',
            'slug' => 'draft-concert-2026',
            'venue' => 'Mini Hall',
            'city' => 'Bandung',
            'event_start_date' => now()->addDays(20),
            'event_end_date' => now()->addDays(21),
            'status' => 'draft',
            'is_free' => false,
        ]);

        $response = $this->actingAs($this->user)->get(route('organizer.reports.index'));
        $response->assertStatus(200);

        $events = $response->viewData('events');
        $this->assertCount(1, $events);
        $this->assertEquals('published', $events->first()->status);
        $this->assertEquals($this->event->id, $events->first()->id);

        $eventOptions = $response->viewData('eventOptions');
        $this->assertCount(1, $eventOptions);
        $this->assertEquals($this->event->id, $eventOptions->first()->id);
    }
}

