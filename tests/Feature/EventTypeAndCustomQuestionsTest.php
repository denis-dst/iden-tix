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

class EventTypeAndCustomQuestionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $organizer;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Test Organizer Tenant',
            'slug' => 'test-organizer-tenant',
            'email' => 'tenant@example.com',
            'phone' => '081234567890',
            'address' => 'Jakarta',
            'is_active' => true,
        ]);

        Role::firstOrCreate(['name' => 'Penyedia Event', 'guard_name' => 'web']);

        $this->organizer = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        $this->organizer->assignRole('Penyedia Event');
    }

    public function test_event_has_default_type_and_helpers(): void
    {
        $event = Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Konser Musik Rock',
            'slug' => 'konser-musik-rock',
            'type' => 'concert',
            'event_start_date' => Carbon::now()->addDays(5),
            'event_end_date' => Carbon::now()->addDays(5)->addHours(4),
            'venue' => 'Stadion Madya',
            'city' => 'Jakarta',
            'status' => 'published',
        ]);

        $this->assertEquals('concert', $event->type);
        $this->assertEquals('Konser Musik', $event->type_label);
        $this->assertEquals('🎵', $event->type_icon);

        $sportEvent = Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pertandingan Sepak Bola',
            'slug' => 'pertandingan-sepak-bola',
            'type' => 'sport',
            'event_start_date' => Carbon::now()->addDays(10),
            'event_end_date' => Carbon::now()->addDays(10)->addHours(3),
            'venue' => 'GBK',
            'city' => 'Jakarta',
            'status' => 'published',
        ]);

        $this->assertEquals('sport', $sportEvent->type);
        $this->assertEquals('Olahraga & Pertandingan', $sportEvent->type_label);
        $this->assertEquals('⚽', $sportEvent->type_icon);
    }

    public function test_organizer_can_create_event_with_type_and_custom_fields(): void
    {
        $customFields = [
            [
                'id' => 'ukuran_jersey',
                'label' => 'Ukuran Jersey Peserta',
                'type' => 'select',
                'options' => ['S', 'M', 'L', 'XL'],
                'is_required' => true,
            ],
            [
                'id' => 'golongan_darah',
                'label' => 'Golongan Darah',
                'type' => 'text',
                'options' => [],
                'is_required' => false,
            ],
        ];

        $response = $this->actingAs($this->organizer)
            ->post(route('organizer.events.store'), [
                'name' => 'Lomba Lari Marathon 10K',
                'type' => 'sport',
                'event_start_date' => Carbon::now()->addDays(7)->format('Y-m-d\TH:i'),
                'event_end_date' => Carbon::now()->addDays(7)->addHours(5)->format('Y-m-d\TH:i'),
                'venue' => 'Kawasan Sudirman',
                'city' => 'Jakarta',
                'status' => 'published',
                'purchase_flow' => 'redeem',
                'categories' => [
                    [
                        'name' => 'Umum',
                        'price' => 150000,
                        'quota' => 100,
                    ]
                ],
                'custom_fields_json' => json_encode($customFields),
            ]);

        $event = Event::where('name', 'Lomba Lari Marathon 10K')->first();
        $this->assertNotNull($event);
        $response->assertRedirect(route('organizer.events.edit', $event->id));
        $this->assertEquals('sport', $event->type);
        $this->assertTrue($event->umroh_question_enabled);

        $configuredFields = $event->getCustomFields();
        $this->assertCount(2, $configuredFields);
        $this->assertEquals('ukuran_jersey', $configuredFields[0]['id']);
        $this->assertEquals('Ukuran Jersey Peserta', $configuredFields[0]['label']);
        $this->assertEquals('select', $configuredFields[0]['type']);
        $this->assertTrue($configuredFields[0]['is_required']);
    }

    public function test_paid_checkout_validates_required_custom_fields(): void
    {
        $customFields = [
            [
                'id' => 'ukuran_baju',
                'label' => 'Ukuran Baju',
                'type' => 'select',
                'options' => ['M', 'L'],
                'is_required' => true,
            ]
        ];

        $event = Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Konser Rock Paid',
            'slug' => 'konser-rock-paid',
            'type' => 'concert',
            'event_start_date' => Carbon::now()->addDays(5),
            'event_end_date' => Carbon::now()->addDays(5)->addHours(4),
            'venue' => 'JCC',
            'city' => 'Jakarta',
            'status' => 'published',
            'is_free' => false,
            'meta' => [
                'custom_fields' => $customFields,
            ],
        ]);

        $category = TicketCategory::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $event->id,
            'name' => 'VIP',
            'price' => 500000,
            'quota' => 50,
        ]);

        // Attempt checkout without required custom field answer
        $response = $this->postJson(route('checkout.process', $event->slug), [
            'ticket_category_id' => $category->id,
            'quantity' => 1,
            'nik' => '1234567890123456',
            'name' => 'John Doe',
            'phone' => '08123456789',
            'email' => 'john@example.com',
            'custom_answers' => [],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
        $this->assertStringContainsString('Ukuran Baju', $response->json('message'));
    }

    public function test_free_checkout_saves_custom_answers(): void
    {
        $customFields = [
            [
                'id' => 'institusi',
                'label' => 'Nama Sekolah / Institusi',
                'type' => 'text',
                'is_required' => true,
            ]
        ];

        $event = Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Seminar Pendidikan Gratis',
            'slug' => 'seminar-pendidikan-gratis',
            'type' => 'seminar',
            'event_start_date' => Carbon::now()->addDays(3),
            'event_end_date' => Carbon::now()->addDays(3)->addHours(3),
            'venue' => 'Aula Kampus',
            'city' => 'Bandung',
            'status' => 'published',
            'is_free' => true,
            'meta' => [
                'custom_fields' => $customFields,
                'registration_proofs' => [],
                'proof_ig_required' => false,
                'proof_review_required' => false,
            ],
        ]);

        $category = TicketCategory::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $event->id,
            'name' => 'Peserta Umum',
            'price' => 0,
            'quota' => 100,
        ]);

        $attendees = [
            [
                'name' => 'Budi Santoso',
                'gender' => 'ikhwan',
                'custom_answers' => [
                    'institusi' => 'Universitas Indonesia',
                ],
            ]
        ];

        $response = $this->postJson(route('checkout.process', $event->slug), [
            'ticket_category_id' => $category->id,
            'quantity' => 1,
            'phone' => '081298765432',
            'email' => 'budi@example.com',
            'attendees' => json_encode($attendees),
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $ticket = $event->tickets()->first();
        $this->assertNotNull($ticket);
        $visitorData = is_array($ticket->visitor_data) ? $ticket->visitor_data : json_decode($ticket->visitor_data, true);
        $this->assertEquals('Budi Santoso', $visitorData['name']);
        $this->assertEquals('Universitas Indonesia', $visitorData['custom_answers']['institusi']);
    }

    public function test_organizer_can_set_and_update_attendee_gender_type(): void
    {
        $response = $this->actingAs($this->organizer)
            ->post(route('organizer.events.store'), [
                'name' => 'Lomba Kucing Sehat',
                'type' => 'sport',
                'event_start_date' => Carbon::now()->addDays(10)->format('Y-m-d\TH:i'),
                'event_end_date' => Carbon::now()->addDays(10)->addHours(4)->format('Y-m-d\TH:i'),
                'venue' => 'Mall Taman Anggrek',
                'city' => 'Jakarta',
                'status' => 'published',
                'purchase_flow' => 'redeem',
                'attendee_gender_type' => 'disabled',
                'categories' => [
                    [
                        'name' => 'Kategori Kucing Domestik',
                        'price' => 50000,
                        'quota' => 30,
                    ]
                ],
            ]);

        $event = Event::where('name', 'Lomba Kucing Sehat')->first();
        $this->assertNotNull($event);
        $this->assertEquals('disabled', $event->getAttendeeGenderSetting());

        // Update to general
        $this->actingAs($this->organizer)
            ->put(route('organizer.events.update', $event->id), [
                'name' => 'Lomba Kucing Sehat Updated',
                'type' => 'sport',
                'venue' => 'Mall Taman Anggrek',
                'city' => 'Jakarta',
                'security_code' => $event->security_code,
                'event_start_date' => Carbon::now()->addDays(10)->format('Y-m-d\TH:i'),
                'event_end_date' => Carbon::now()->addDays(10)->addHours(4)->format('Y-m-d\TH:i'),
                'status' => 'published',
                'purchase_flow' => 'redeem',
                'attendee_gender_type' => 'general',
            ]);

        $event->refresh();
        $this->assertEquals('general', $event->getAttendeeGenderSetting());
    }

    public function test_free_checkout_with_disabled_gender_setting_succeeds_without_gender(): void
    {
        $event = Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Kontes Hewan Gratis',
            'slug' => 'kontes-hewan-gratis',
            'type' => 'competition',
            'event_start_date' => Carbon::now()->addDays(4),
            'event_end_date' => Carbon::now()->addDays(4)->addHours(4),
            'venue' => 'Parkir Timur',
            'city' => 'Jakarta',
            'status' => 'published',
            'is_free' => true,
            'meta' => [
                'attendee_gender_type' => 'disabled',
                'custom_fields' => [],
                'registration_proofs' => [],
                'proof_ig_required' => false,
                'proof_review_required' => false,
            ],
        ]);

        $category = TicketCategory::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $event->id,
            'name' => 'Peserta Kontes',
            'price' => 0,
            'quota' => 50,
        ]);

        $attendees = [
            [
                'name' => 'Ahmad Komunitas',
                'gender' => null, // Gender is disabled, so left null
            ]
        ];

        $response = $this->postJson(route('checkout.process', $event->slug), [
            'ticket_category_id' => $category->id,
            'quantity' => 1,
            'phone' => '081234567890',
            'email' => 'ahmad@example.com',
            'attendees' => json_encode($attendees),
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $ticket = $event->tickets()->first();
        $this->assertNotNull($ticket);
        $visitorData = is_array($ticket->visitor_data) ? $ticket->visitor_data : json_decode($ticket->visitor_data, true);
        $this->assertEquals('Ahmad Komunitas', $visitorData['name']);
        $this->assertNull($visitorData['gender']);
    }

    public function test_free_checkout_with_general_gender_setting_accepts_laki_laki_and_perempuan(): void
    {
        $event = Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Festival Musik Gratis',
            'slug' => 'festival-musik-gratis',
            'type' => 'festival',
            'event_start_date' => Carbon::now()->addDays(5),
            'event_end_date' => Carbon::now()->addDays(5)->addHours(6),
            'venue' => 'Lapangan Banteng',
            'city' => 'Jakarta',
            'status' => 'published',
            'is_free' => true,
            'meta' => [
                'attendee_gender_type' => 'general',
                'custom_fields' => [],
                'registration_proofs' => [],
                'proof_ig_required' => false,
                'proof_review_required' => false,
            ],
        ]);

        $category = TicketCategory::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $event->id,
            'name' => 'Tiket Festival',
            'price' => 0,
            'quota' => 100,
        ]);

        $attendees = [
            [
                'name' => 'Rina Nose',
                'gender' => 'perempuan',
            ]
        ];

        $response = $this->postJson(route('checkout.process', $event->slug), [
            'ticket_category_id' => $category->id,
            'quantity' => 1,
            'phone' => '081234567899',
            'email' => 'rina@example.com',
            'attendees' => json_encode($attendees),
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $ticket = $event->tickets()->first();
        $this->assertNotNull($ticket);
        $visitorData = is_array($ticket->visitor_data) ? $ticket->visitor_data : json_decode($ticket->visitor_data, true);
        $this->assertEquals('perempuan', $visitorData['gender']);
    }

    public function test_gate_checkin_returns_formatted_custom_fields(): void
    {
        $customFields = [
            [
                'id' => 'ukuran_baju',
                'label' => 'Ukuran Baju',
                'type' => 'select',
                'options' => ['S', 'M', 'L'],
                'is_required' => true,
            ]
        ];

        $event = Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Fun Run 5K',
            'slug' => 'fun-run-5k',
            'type' => 'sport',
            'event_start_date' => Carbon::now()->addDays(1),
            'event_end_date' => Carbon::now()->addDays(1)->addHours(3),
            'venue' => 'Monas',
            'city' => 'Jakarta',
            'status' => 'published',
            'meta' => [
                'attendee_gender_type' => 'general',
                'custom_fields' => $customFields,
            ],
        ]);

        $category = TicketCategory::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $event->id,
            'name' => 'Runner',
            'price' => 100000,
            'quota' => 100,
        ]);

        $tx = \App\Models\Transaction::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $event->id,
            'ticket_category_id' => $category->id,
            'quantity' => 1,
            'reference_no' => 'TX-GATE-01',
            'customer_name' => 'Doni Salman',
            'customer_email' => 'doni@example.com',
            'customer_phone' => '081234567890',
            'total_amount' => 100000,
            'payment_status' => 'paid',
            'paid_at' => now(),
            'channel' => 'online',
        ]);

        $ticket = \App\Models\Ticket::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $event->id,
            'transaction_id' => $tx->id,
            'ticket_category_id' => $category->id,
            'ticket_code' => 'GTX-TESTGATE01',
            'status' => 'sold',
            'visitor_data' => [
                'name' => 'Doni Salman',
                'gender' => 'laki-laki',
                'custom_answers' => [
                    'ukuran_baju' => 'L',
                ],
            ]
        ]);

        $response = $this->actingAs($this->organizer)
            ->postJson(route('organizer.gate.process'), [
                'ticket_code' => 'GTX-TESTGATE01',
                'event_id' => $event->id,
                'mode' => 'IN',
                'gate_name' => 'Main Gate',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertNotEmpty($response->json('custom_fields'));
        $this->assertEquals('Doni Salman', $response->json('customer'));
        $this->assertEquals('GTX-TESTGATE01', $response->json('ticket_code'));
    }

    public function test_operational_report_export_maps_custom_fields_dynamically(): void
    {
        $rows = collect([
            [
                'ticket_code' => 'GTX-COMMUNITY01',
                'reference_no' => 'TX-COMM-01',
                'name' => 'Siti Nurhaliza',
                'gender' => 'perempuan',
                'phone' => '081234567891',
                'email' => 'siti@example.com',
                'custom_fields' => [
                    [
                        'id' => 'komunitas',
                        'label' => 'Nama Komunitas',
                        'value' => 'Indo Runner',
                    ]
                ],
                'event_name' => 'Gathering Komunitas',
                'category_name' => 'Member',
                'status' => 'sold',
                'first_checkin_at' => null,
            ]
        ]);

        $export = new \App\Exports\OperationalReportExport($rows);
        $headings = $export->headings();
        $this->assertContains('Nama Komunitas', $headings);
        $this->assertContains('Kode Tiket', $headings);

        $collection = $export->collection();
        $firstRecord = $collection->first();
        $this->assertContains('GTX-COMMUNITY01', $firstRecord);
        $this->assertContains('Indo Runner', $firstRecord);
    }
}
