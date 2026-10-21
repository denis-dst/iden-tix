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

class EVoucherTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected User $organizer;
    protected Tenant $tenant;
    protected Event $event;
    protected TicketCategory $category;
    protected Transaction $transaction;
    protected Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Persatuan Sepakbola Indonesia',
            'slug' => 'psi-tenant',
            'email' => 'promotor@example.com',
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
            'name' => 'FIFA ASEAN CUP 2026: INDONESIA VS MALAYSIA',
            'slug' => 'indonesia-vs-malaysia-2026',
            'type' => 'sport',
            'home_team_name' => 'INDONESIA',
            'away_team_name' => 'MALAYSIA',
            'event_start_date' => Carbon::parse('2026-09-28 20:00:00'),
            'event_end_date' => Carbon::parse('2026-09-28 22:30:00'),
            'gate_open_at' => Carbon::parse('2026-09-28 17:00:00'),
            'venue' => 'Stadion Utama Gelora Bung Karno',
            'city' => 'Jakarta',
            'status' => 'published',
            'is_free' => false,
            'purchase_flow' => 'redeem',
            'meta' => [
                'custom_fields' => [
                    [
                        'id' => 'ukuran_jersey',
                        'label' => 'Ukuran Jersey',
                        'type' => 'select',
                        'options' => ['S', 'M', 'L', 'XL'],
                        'is_required' => true,
                    ],
                    [
                        'id' => 'nomor_member',
                        'label' => 'Nomor Anggota Suporter',
                        'type' => 'text',
                        'is_required' => false,
                    ],
                ],
            ],
        ]);

        $this->category = TicketCategory::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'name' => 'VIP TIMUR',
            'price' => 150000,
            'quota' => 500,
            'sold_count' => 1,
        ]);

        $gate = \App\Models\Gate::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'name' => 'GATE 3',
        ]);
        $this->category->gates()->attach($gate->id);

        $this->transaction = Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->category->id,
            'reference_no' => 'IDTX-768122293',
            'customer_name' => 'Brian Arisowo',
            'customer_email' => 'brian@example.com',
            'customer_phone' => '081234567890',
            'quantity' => 1,
            'total_amount' => 150000,
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'paid_at' => Carbon::now(),
        ]);

        $this->ticket = Ticket::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'transaction_id' => $this->transaction->id,
            'ticket_category_id' => $this->category->id,
            'ticket_code' => 'IDTX-9876543210',
            'status' => 'sold',
            'visitor_data' => [
                'name' => 'Brian Arisowo',
                'phone' => '081234567890',
                'custom_answers' => [
                    'ukuran_jersey' => 'XL',
                    'nomor_member' => 'GARUDA-007',
                ],
            ],
        ]);
    }

    public function test_evoucher_view_renders_new_template_with_custom_questions(): void
    {
        $response = $this->get(route('tickets.view', $this->ticket->ticket_code));

        $response->assertStatus(200);

        // Branding
        $response->assertSee('WWW.IDEN-TIX.COM');
        $response->assertSee('IDENTIX');
        $response->assertSee('TICKETING & FAN ENGAGEMENT', false);

        // Event & Category Details
        $response->assertSee('VIP TIMUR');
        $response->assertSee('GATE 3');
        $response->assertSee('INDONESIA VS MALAYSIA');
        $response->assertSee('Brian Arisowo');
        $response->assertSee('IDTX-768122293');
        $response->assertSee('IDTX-9876543210');
        $response->assertSee('Stadion Utama Gelora Bung Karno');
        $response->assertSee('TIKET HANYA DAPAT DIGUNAKAN 1X');

        // Custom Questions and Answers
        $response->assertSee('Ukuran Jersey');
        $response->assertSee('XL');
        $response->assertSee('Nomor Anggota Suporter');
        $response->assertSee('GARUDA-007');

        // Terms & Promotor Section
        $response->assertSee('SYARAT DAN KETENTUAN');
        $response->assertSee('ORGANIZED BY :');
        $response->assertSee('Persatuan Sepakbola Indonesia');
    }

    public function test_organizer_evoucher_print_view_renders_same_template(): void
    {
        $response = $this->get(route('evoucher.public', $this->transaction->reference_no));

        $response->assertStatus(200);
        $response->assertSee('WWW.IDEN-TIX.COM');
        $response->assertSee('IDENTIX');
        $response->assertSee('VIP TIMUR');
        $response->assertSee('Ukuran Jersey');
        $response->assertSee('XL');
        $response->assertSee('TIKET HANYA DAPAT DIGUNAKAN 1X');
    }

    public function test_evoucher_hides_questions_with_show_in_evoucher_false(): void
    {
        // Set nomor_member show_in_evoucher = false
        $meta = $this->event->meta;
        $meta['custom_fields'][1]['show_in_evoucher'] = false;
        $this->event->meta = $meta;
        $this->event->save();

        $response = $this->get(route('tickets.view', $this->ticket->ticket_code));
        $response->assertStatus(200);

        // Ukuran Jersey should be visible
        $response->assertSee('Ukuran Jersey');
        $response->assertSee('XL');

        // Nomor Anggota Suporter should be hidden
        $response->assertDontSee('Nomor Anggota Suporter');
        $response->assertDontSee('GARUDA-007');
    }

    public function test_evoucher_extracts_answers_from_payment_reference_when_ticket_visitor_data_is_null(): void
    {
        // Simulate a transaction where ticket visitor_data is empty but transaction has payment_reference with custom_answers
        $tx = Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->category->id,
            'reference_no' => 'IDTX-PAYREFTEST',
            'customer_name' => 'Doni Salman',
            'customer_email' => 'doni@example.com',
            'customer_phone' => '081122334455',
            'quantity' => 1,
            'total_amount' => 150000,
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'payment_reference' => json_encode([
                'custom_answers' => [
                    'ukuran_jersey' => 'M',
                    'nomor_member' => 'MEMBER-889',
                ]
            ]),
            'paid_at' => Carbon::now(),
        ]);

        $ticket = Ticket::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'transaction_id' => $tx->id,
            'ticket_category_id' => $this->category->id,
            'ticket_code' => '9918273645',
            'status' => 'sold',
            'visitor_data' => null, // empty visitor data
        ]);

        $response = $this->get(route('tickets.view', $ticket->ticket_code));
        $response->assertStatus(200);

        // Answers should be extracted per question from payment_reference
        $response->assertSee('Ukuran Jersey');
        $response->assertSee('M');
        $response->assertSee('Nomor Anggota Suporter');
        $response->assertSee('MEMBER-889');
    }

    public function test_evoucher_does_not_duplicate_phone_number_when_phone_custom_field_is_configured(): void
    {
        $this->event->update([
            'meta' => [
                'custom_fields' => [
                    [
                        'id' => 'no_hp_wa',
                        'label' => 'Nomor Telpon/WA',
                        'type' => 'phone',
                        'show_in_evoucher' => true,
                    ],
                ],
            ],
        ]);

        $this->ticket->update([
            'visitor_data' => [
                'name' => 'Brian Arisowo',
                'phone' => '081234567890',
                'custom_answers' => [
                    'no_hp_wa' => '081234567890',
                ],
            ],
        ]);

        $response = $this->get(route('tickets.view', $this->ticket->ticket_code));
        $response->assertStatus(200);

        $content = $response->getContent();
        // Count how many times the phone number 081234567890 appears in the rendered HTML
        $count = substr_count($content, '081234567890');
        $this->assertEquals(1, $count, 'Phone number must appear exactly once, never duplicated.');
        $response->assertSee('Nomor Telpon/WA');
        $response->assertDontSee('NO. TELEPON / WHATSAPP');
    }

    public function test_evoucher_hides_phone_when_phone_custom_field_has_show_in_evoucher_false(): void
    {
        $this->event->update([
            'meta' => [
                'custom_fields' => [
                    [
                        'id' => 'no_hp_wa',
                        'label' => 'Nomor Telpon/WA',
                        'type' => 'phone',
                        'show_in_evoucher' => false,
                    ],
                ],
            ],
        ]);

        $this->ticket->update([
            'visitor_data' => [
                'name' => 'Brian Arisowo',
                'phone' => '081234567890',
                'custom_answers' => [
                    'no_hp_wa' => '081234567890',
                ],
            ],
        ]);

        $response = $this->get(route('tickets.view', $this->ticket->ticket_code));
        $response->assertStatus(200);

        // Phone number should be completely hidden since organizer set show_in_evoucher to false
        $response->assertDontSee('Nomor Telpon/WA');
        $response->assertDontSee('NO. TELEPON / WHATSAPP');
        $response->assertDontSee('081234567890');
    }

    public function test_evoucher_hides_gate_box_when_no_gate_is_configured(): void
    {
        // Detach gates from category and ensure no event gates exist
        $this->category->gates()->detach();
        $this->event->gates()->delete();

        $response = $this->get(route('tickets.view', $this->ticket->ticket_code));
        $response->assertStatus(200);

        // Kategori should be visible
        $response->assertSee('KATEGORI');
        $response->assertSee('VIP TIMUR');

        // GATE box should NOT be rendered and should not fallback to GATE UTAMA
        $response->assertDontSee('GATE UTAMA');
        $response->assertDontSee('<div class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">GATE</div>', false);
    }

    public function test_evoucher_renders_waktu_acara_instead_of_penukaran_when_purchase_flow_is_not_redeem(): void
    {
        // Set event purchase_flow to evoucher (no penukaran)
        $this->event->update([
            'purchase_flow' => 'evoucher',
        ]);

        $response = $this->get(route('tickets.view', $this->ticket->ticket_code));
        $response->assertStatus(200);

        // Should NOT show WAKTU MULAI PENUKARAN
        $response->assertDontSee('WAKTU MULAI PENUKARAN:');
        $response->assertDontSee('PENUKARAN WRISTBAND');

        // Should show WAKTU ACARA and DIRECT SCAN AT GATE
        $response->assertSee('WAKTU ACARA:');
        $response->assertSee('DIRECT SCAN AT GATE');
    }

    public function test_evoucher_renders_transaction_purchase_date_and_time(): void
    {
        $paidTime = Carbon::parse('2026-10-21 14:35:00');
        $this->transaction->update([
            'paid_at' => $paidTime,
        ]);

        $response = $this->get(route('tickets.view', $this->ticket->ticket_code));
        $response->assertStatus(200);

        $response->assertSee('WAKTU TRANSAKSI PEMBELIAN');
        $response->assertSee('14:35 WIB');
    }

    public function test_evoucher_uses_identix_logo_from_settings_when_configured(): void
    {
        \App\Models\Setting::updateOrCreate(
            ['key' => 'app_logo'],
            ['value' => 'settings/test-identix-logo.webp', 'group' => 'appearance']
        );

        $response = $this->get(route('tickets.view', $this->ticket->ticket_code));
        $response->assertStatus(200);

        // Header and side strip should render the logo from settings
        $response->assertSee('settings/test-identix-logo.webp');
    }
}


