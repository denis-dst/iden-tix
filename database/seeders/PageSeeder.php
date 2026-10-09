<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'faq',
                'title' => 'Pertanyaan Umum (FAQ)',
                'content' => '
                    <div class="space-y-6">
                        <p class="text-stone-300 leading-relaxed">Berikut adalah jawaban atas pertanyaan yang paling sering ditanyakan mengenai pemesanan tiket, pembayaran, e-voucher, serta layanan IdenTix.</p>
                        <h3 class="text-xl font-bold text-white">1. Bagaimana cara membeli tiket di IdenTix?</h3>
                        <p class="text-stone-400">Pilih event yang ingin Anda hadiri di halaman utama, tentukan kategori tiket dan jumlah tiket yang dibutuhkan, lalu klik "Beli Tiket" dan ikuti petunjuk pembayaran yang tersedia.</p>
                        
                        <h3 class="text-xl font-bold text-white">2. Metode pembayaran apa saja yang didukung?</h3>
                        <p class="text-stone-400">IdenTix mendukung berbagai metode pembayaran online tepercaya seperti Transfer Bank / Virtual Account, QRIS, E-Wallet (OVO, DANA, GoPay, ShopeePay), serta Kartu Kredit/Debit.</p>
                        
                        <h3 class="text-xl font-bold text-white">3. Kapan saya mendapatkan E-Voucher / Tiket?</h3>
                        <p class="text-stone-400">Setelah pembayaran terverifikasi otomatis oleh sistem, E-Voucher akan langsung dikirimkan ke email Anda dan dapat diunduh langsung dari halaman konfirmasi transaksi.</p>
                        
                        <h3 class="text-xl font-bold text-white">4. Bagaimana cara menggunakan E-Voucher saat di lokasi event?</h3>
                        <p class="text-stone-400">Tunjukkan QR Code yang tertera pada E-Voucher Anda di smartphone kepada petugas gate/loket penukaran gelang untuk dipindai.</p>
                    </div>
                '
            ],
            [
                'slug' => 'syarat-ketentuan',
                'title' => 'Syarat & Ketentuan',
                'content' => '
                    <div class="space-y-6">
                        <p class="text-stone-300 leading-relaxed">Selamat datang di IdenTix. Dengan mengakses atau menggunakan platform kami, Anda menyetujui untuk terikat oleh Syarat & Ketentuan berikut.</p>
                        
                        <h3 class="text-xl font-bold text-white">1. Ketentuan Umum Pembelian Tiket</h3>
                        <ul class="list-disc list-inside text-stone-400 space-y-2">
                            <li>Pembeli wajib memberikan informasi identitas yang akurat saat melakukan transaksi.</li>
                            <li>Setiap tiket yang dibeli memiliki kode unik yang hanya berlaku untuk 1 (satu) kali pemindaian atau penukaran.</li>
                            <li>Penyelenggara event berhak menolak akses jika ditemukan indikasi pemalsuan atau pemindaian berulang atas kode tiket yang sama.</li>
                        </ul>

                        <h3 class="text-xl font-bold text-white">2. Tanggung Jawab Pengguna</h3>
                        <p class="text-stone-400">Pengguna bertanggung jawab penuh atas kerahasiaan e-voucher dan kode QR tiket masing-masing. IdenTix tidak bertanggung jawab atas penyalahgunaan e-voucher akibat kelalaian pembeli membagikan kode tersebut kepada pihak ketiga.</p>
                        
                        <h3 class="text-xl font-bold text-white">3. Perubahan & Pembatalan Event</h3>
                        <p class="text-stone-400">Setiap perubahan jadwal, penundaan, atau pembatalan event merupakan tanggung jawab penuh dari Penyelenggara Event (Organizer). IdenTix bertindak sebagai penyedia teknologi tiket resmi.</p>
                    </div>
                '
            ],
            [
                'slug' => 'refund-policy',
                'title' => 'Refund Policy (Kebijakan Pengembalian Dana)',
                'content' => '
                    <div class="space-y-6">
                        <p class="text-stone-300 leading-relaxed">Kami di IdenTix berkomitmen menjaga kepercayaan dan kenyamanan Anda. Berikut kebijakan pengembalian dana (refund) yang berlaku pada platform kami.</p>
                        
                        <h3 class="text-xl font-bold text-white">1. Kondisi Pengembalian Dana</h3>
                        <ul class="list-disc list-inside text-stone-400 space-y-2">
                            <li><strong>Pembatalan Event Resmi:</strong> Jika event dibatalkan secara resmi oleh Penyelenggara, pengembalian dana penuh akan diproses sesuai pengumuman dari pihak Penyelenggara.</li>
                            <li><strong>Kesalahan Transaksi Ganda:</strong> Apabila akun Anda terpotong 2x untuk 1 pesanan yang sama akibat gangguan teknis payment gateway, dana berlebih akan dikembalikan.</li>
                        </ul>

                        <h3 class="text-xl font-bold text-white">2. Ketentuan Non-Refundable</h3>
                        <p class="text-stone-400">Tiket yang telah berhasil dibeli dan terkonfirmasi secara umum bersifat <strong>Final & Non-Refundable</strong> (tidak dapat dikembalikan/ditukar), kecuali terjadi pembatalan resmi dari Penyelenggara Event.</p>

                        <h3 class="text-xl font-bold text-white">3. Prosedur Pengajuan Refund</h3>
                        <p class="text-stone-400">Untuk mengajukan permohonan pengembalian dana, silakan hubungi tim Customer Support IdenTix melalui email <a href="mailto:virtusunity@gmail.com" class="text-orange-400 underline">virtusunity@gmail.com</a> atau WhatsApp <a href="https://wa.me/6283878537818" class="text-orange-400 underline">083878537818</a> dengan melampirkan Nomor Referensi Transaksi dan bukti pendukung.</p>
                    </div>
                '
            ],
            [
                'slug' => 'kontak',
                'title' => 'Kontak Usaha IdenTix',
                'content' => '
                    <div class="space-y-6">
                        <p class="text-stone-300 leading-relaxed">Apabila Anda memiliki pertanyaan, memerlukan bantuan seputar pembelian tiket, atau tertarik untuk berkerjasama dengan IdenTix, jangan ragu untuk menghubungi kami.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 my-8">
                            <div class="p-6 bg-white/5 border border-white/10 rounded-2xl">
                                <h4 class="font-bold text-white mb-2">Nama Usaha</h4>
                                <p class="text-stone-400">IdenTix</p>
                            </div>
                            <div class="p-6 bg-white/5 border border-white/10 rounded-2xl">
                                <h4 class="font-bold text-white mb-2">Email Official</h4>
                                <p class="text-stone-400"><a href="mailto:virtusunity@gmail.com" class="text-orange-400 hover:underline">virtusunity@gmail.com</a></p>
                            </div>
                            <div class="p-6 bg-white/5 border border-white/10 rounded-2xl">
                                <h4 class="font-bold text-white mb-2">Telepon / WA</h4>
                                <p class="text-stone-400"><a href="https://wa.me/6283878537818" class="text-orange-400 hover:underline">083878537818</a></p>
                            </div>
                        </div>

                        <h3 class="text-xl font-bold text-white">Alamat Kantor / Bisnis</h3>
                        <p class="text-stone-400 leading-relaxed">DUSUN MANDAH INDUK 00/001 MANDAH, NATAR, LAMPUNG SELATAN, LAMPUNG 35362</p>
                    </div>
                '
            ]
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }
}
