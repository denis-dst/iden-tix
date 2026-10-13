<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Tenant;
use App\Models\TicketCategory;
use App\Models\User;
use App\Models\WristbandTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WristbandExportDocxTest extends TestCase
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
            'name' => 'Kreatif Media Organizer',
            'slug' => 'kreatif-media',
            'email' => 'admin@kreatifmedia.com',
            'status' => 'active',
        ]);

        $this->user = User::create([
            'name' => 'Organizer Person',
            'email' => 'organizer@kreatifmedia.com',
            'password' => bcrypt('secret123'),
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);
        $this->user->assignRole('Penyedia Event');

        $this->event = Event::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Grand Concert 2026',
            'slug' => 'grand-concert-2026',
            'venue' => 'Stadion Utama Gelora Bung Karno',
            'city' => 'Jakarta Pusat',
            'event_start_date' => now()->addDays(5)->setHour(19)->setMinute(0),
            'event_end_date' => now()->addDays(5)->setHour(22)->setMinute(0),
            'gate_open_at' => now()->addDays(5)->setHour(17)->setMinute(0),
            'status' => 'published',
            'is_free' => false,
            'meta' => [
                'wristband_league_name' => 'MEGA CONCERT TOUR 2026',
            ],
        ]);

        $this->category = TicketCategory::create([
            'event_id' => $this->event->id,
            'tenant_id' => $this->tenant->id,
            'name' => 'VIP FRONT ROW',
            'price' => 750000,
            'quota' => 60,
            'hex_color' => '#E11D48',
        ]);
    }

    public function test_print_wristbands_view_with_f4_layout(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('organizer.categories.print-wristbands', [
                'category' => $this->category,
                'paper' => 'f4',
                'mode' => 'default',
                'count' => 15,
            ]));

        $response->assertStatus(200);
        $response->assertSee('VIP FRONT ROW');
        $response->assertSee('MEGA CONCERT TOUR 2026');
        $response->assertSee('215mm 330mm'); // F4 size
        $response->assertSee('page-f4');
        $response->assertSee('Export .docx (F4)');
    }

    public function test_print_wristbands_view_with_a3_layout(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('organizer.categories.print-wristbands', [
                'category' => $this->category,
                'paper' => 'a3',
                'mode' => 'default',
                'count' => 26,
            ]));

        $response->assertStatus(200);
        $response->assertSee('VIP FRONT ROW');
        $response->assertSee('480mm 310mm'); // A3+ landscape size
        $response->assertSee('page-a3');
        $response->assertSee('column-a3');
        $response->assertSee('Export .docx (A3)');
        $response->assertSee('Hemat Ruang (2 Kolom)');
    }

    public function test_export_docx_f4_downloads_valid_docx(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('organizer.categories.export-wristbands-docx', [
                'category' => $this->category,
                'paper' => 'f4',
                'mode' => 'default',
                'count' => 10,
            ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $contentDisposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('attachment;', $contentDisposition);
        $this->assertStringContainsString('.docx', $contentDisposition);
        $this->assertStringContainsString('F4', $contentDisposition);
    }

    public function test_export_docx_a3_downloads_valid_docx(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('organizer.categories.export-wristbands-docx', [
                'category' => $this->category,
                'paper' => 'a3',
                'mode' => 'default',
                'count' => 20,
            ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $contentDisposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('attachment;', $contentDisposition);
        $this->assertStringContainsString('.docx', $contentDisposition);
        $this->assertStringContainsString('A3', $contentDisposition);
    }

    public function test_export_docx_custom_mode_with_template(): void
    {
        $template = WristbandTemplate::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->category->id,
            'name' => 'Custom VIP Template',
            'mode' => 'custom',
            'columns_config' => WristbandTemplate::getDefaultColumns(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('organizer.categories.export-wristbands-docx', [
                'category' => $this->category,
                'paper' => 'a3',
                'mode' => 'custom',
                'count' => 5,
            ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->assertStringContainsString('Custom', $response->headers->get('content-disposition'));
    }

    public function test_unauthorized_user_cannot_export_wristbands(): void
    {
        $otherTenant = Tenant::create([
            'name' => 'Other Tenant',
            'slug' => 'other-tenant',
            'email' => 'other@tenant.com',
            'status' => 'active',
        ]);

        $otherUser = User::create([
            'name' => 'Intruder',
            'email' => 'other@other.com',
            'password' => bcrypt('password'),
            'tenant_id' => $otherTenant->id,
            'is_active' => true,
        ]);
        $otherUser->assignRole('Penyedia Event');

        $response = $this->actingAs($otherUser)
            ->get(route('organizer.categories.export-wristbands-docx', [
                'category' => $this->category,
                'paper' => 'a3',
            ]));

        $response->assertStatus(403);
    }

    public function test_export_docx_large_batch_f4_runs_fast_and_succeeds(): void
    {
        $startTime = microtime(true);

        $response = $this->actingAs($this->user)
            ->get(route('organizer.categories.export-wristbands-docx', [
                'category' => $this->category,
                'paper' => 'f4',
                'mode' => 'default',
                'start' => 1,
                'count' => 60,
            ]));

        $elapsed = microtime(true) - $startTime;

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->assertLessThan(10.0, $elapsed, "Large batch F4 export took {$elapsed}s, expected under 10s");
    }

    public function test_export_docx_large_batch_a3_runs_fast_and_succeeds(): void
    {
        $startTime = microtime(true);

        $response = $this->actingAs($this->user)
            ->get(route('organizer.categories.export-wristbands-docx', [
                'category' => $this->category,
                'paper' => 'a3',
                'mode' => 'default',
                'start' => 1,
                'count' => 52, // exactly 2 pages of A3+ (26 x 2)
            ]));

        $elapsed = microtime(true) - $startTime;

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->assertLessThan(10.0, $elapsed, "Large batch A3 export took {$elapsed}s, expected under 10s");
    }

    public function test_export_docx_custom_template_with_background_image(): void
    {
        // Create a temporary mock background image
        $tempBg = tempnam(sys_get_temp_dir(), 'bg_') . '.png';
        $bgImg = imagecreatetruecolor(1270, 130);
        $blue = imagecolorallocate($bgImg, 30, 64, 175);
        imagefilledrectangle($bgImg, 0, 0, 1270, 130, $blue);
        imagepng($bgImg, $tempBg);
        imagedestroy($bgImg);

        $template = WristbandTemplate::create([
            'tenant_id' => $this->tenant->id,
            'event_id' => $this->event->id,
            'ticket_category_id' => $this->category->id,
            'name' => 'Custom BG Template',
            'mode' => 'custom',
            'background_image' => $tempBg,
            'columns_config' => WristbandTemplate::getDefaultColumns(),
        ]);

        $startTime = microtime(true);

        $response = $this->actingAs($this->user)
            ->get(route('organizer.categories.export-wristbands-docx', [
                'category' => $this->category,
                'paper' => 'a3',
                'mode' => 'custom',
                'start' => 1,
                'count' => 26,
            ]));

        $elapsed = microtime(true) - $startTime;

        if (file_exists($tempBg)) {
            @unlink($tempBg);
        }

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->assertLessThan(8.0, $elapsed, "Custom BG template export took {$elapsed}s, expected under 8s");
    }
}
