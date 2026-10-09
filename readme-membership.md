# Panduan Fitur Membership Klub, Season Pass & Fan CRM IdenTix

Dokumentasi lengkap mengenai modul keanggotaan klub sepakbola (multi-tenant), pengelolaan basis suporter (Fan CRM), tiket terusan (Season Pass), sistem poin loyalitas, serta alokasi kuota komunitas (Korwil) pada platform IdenTix.

---

## 1. Ikhtisar & Standar Regulasi

Modul ini mentransformasi platform tiket IdenTix agar dapat digunakan oleh klub sepakbola profesional (tenant) sebagai database suporter terintegrasi, media pemasaran tiket musiman, dan pusat interaksi suporter.

### Standar Kepatuhan Single Fan Identity (PSSI / Liga)
Sistem ini dirancang sesuai prinsip identitas tunggal suporter:
* **1 Akun, 1 NIK Terverifikasi, Maksimal 1 Tiket per Kategori**: Mencegah praktik percaloan dan memenuhi standar keselamatan penonton di stadion.
* **Verifikasi KYC (Know Your Customer)**: Setiap suporter wajib melengkapi NIK 16 digit, nama sesuai KTP, nomor WhatsApp aktif, foto e-KTP, dan foto selfie wajah.
* **Kesiapan Integrasi Ekosistem**: Format data telah disesuaikan agar siap sinkronisasi dengan identitas liga (SobatLiga / Garuda ID) jika dibutuhkan.

---

## 2. Struktur Modul & Fitur Utama

### A. Database Suporter & Verifikasi KYC (Fan CRM)
* **Pendaftaran Member**: Suporter mendaftar akun dan terafiliasi dengan klub yang didukung.
* **Nomor Anggota Otomatis**: Sistem menerbitkan kode kartu anggota unik (contoh: `FAN-PRSKT-00123`).
* **Panel Verifikasi Admin Klub**:
  * Admin memeriksa dokumen foto KTP dan foto wajah.
  * Aksi **Setujui KYC**: Status berubah menjadi terverifikasi dan suporter otomatis menerima bonus +100 poin.
  * Aksi **Tolak KYC**: Admin menyertakan alasan penolakan (misalnya foto KTP buram) agar suporter dapat mengunggah ulang.
* **Filter & Pencarian**: Admin dapat memfilter suporter berdasarkan status KYC (Pending, Verified, Rejected, Unverified) atau mencari berdasarkan Nama, NIK, dan Nomor WhatsApp.

---

### B. Tingkatan Keanggotaan (Membership Tiers)
Klub dapat membuat tingkatan keanggotaan berjenjang (contoh: Free Supporter, Silver, Gold, VIP):
* **Harga & Masa Aktif**: Biaya membership per periode hari (misalnya 365 hari).
* **Hak Akses Awal (Early Access)**: Menentukan berapa jam lebih awal member tier tersebut dapat membeli tiket sebelum dibuka untuk penjualan umum (General Public Sale).
* **Diskon Tiket**: Persentase potongan harga tiket match kandang otomatis untuk member.
* **Daftar Benefit Dinamis**: Fasilitas tambahan seperti kartu fisik, diskon merchandise official, atau akses konten eksklusif.
* **Label & Warna Badge**: Kustomisasi warna identitas tier pada kartu digital.

---

### C. Tiket Terusan (Season Pass) & Mekanisme Klaim Matchday
Fitur penjualan tiket 1 musim penuh (Full Season) atau setengah musim (Half Season / Putaran):
* **Alokasi Kuota Match**: Paket mencakup kuota pertandingan kandang (misalnya 17 match).
* **Kategori Tribun & Nomor Kursi**: Dapat dikunci ke tribun tertentu (contoh: VIP Barat) atau fleksibel.
* **Jendela Waktu Klaim (H-X Hari)**:
  * Admin klub menentukan kapan link klaim tiket dibuka (default: H-5 hari sebelum kick-off).
  * Pada dashboard suporter, tombol **🎟 Klaim E-Ticket** akan aktif saat jendela klaim dibuka.
* **Penerbitan Tiket Resmi**:
  * Saat diklaim, sistem menerbitkan record tiket resmi lengkap dengan QR code unik.
  * **Kompatibilitas Gate**: Jika event diatur untuk pemindaian langsung di pintu masuk, tiket dapat langsung dipindai oleh turnstile scanner (GateController). Jika event diatur wajib penukaran fisik, tiket masuk ke alur penukaran gelang (Redemption).

---

### D. Sistem Poin Loyalitas & Gamifikasi
Suporter dapat mengumpulkan poin reward yang dapat digunakan sebagai potongan harga tiket:
* **Perolehan Poin (Earn)**:
  1. *Bonus Verifikasi KYC*: +100 Poin saat disetujui.
  2. *Tebak Skor Pertandingan (Score Predictor)*: Suporter menebak skor matchday kandang sebelum kick-off. Admin menginput skor akhir, dan sistem otomatis mendistribusikan poin ke semua pemenang.
  3. *Kuis Trivia Klub*: Menjawab kuis sejarah dan pemain klub untuk mendapatkan poin.
  4. *Transaksi Tiket & Membership*: Poin reward otomatis dari nilai belanja.
* **Buku Besar Poin (Ledger Audit Trail)**: Setiap mutasi masuk dan keluar tercatat dengan saldo akhir yang transparan.

---

### E. Manajemen Komunitas Suporter (Korwil) & Izin NIK
Mendukung koordinasi pembelian tiket rombongan bersama basis suporter:
* **Pendaftaran Korwil**: Klub mendaftarkan koordinator wilayah suporter resmi.
* **Alokasi Kuota Matchday**: Klub menentukan kuota tribun khusus rombongan korwil.
* **Persetujuan NIK (Consent Mechanism)**:
  * Saat korwil mendaftarkan daftar NIK anggotanya, pemilik NIK menerima notifikasi di dashboard portal suporter.
  * Suporter dapat memilih **Setujui Rombongan** (kuotanya diproses korwil) atau **Beli Mandiri** (menolak alokasi korwil untuk memesan sendiri). Hal ini mencegah pencatutan NIK tanpa izin pemilik.

---

### F. Kartu Keanggotaan Digital (Digital Fan Card)
Tampilan kartu suporter interaktif di dashboard:
* Menampilkan logo klub, nama suporter, nomor anggota resmi, NIK yang disamarkan demi privasi, badge tier, dan total saldo poin aktif.
* Menampilkan status verifikasi KYC dan tautan cepat ke jadwal pertandingan kandang.

---

## 3. Struktur Database (Tabel Baru)

Semua penambahan tabel bersifat modular dan tidak mengubah struktur tabel tiket atau transaksi eksisting (100% backward compatible):

1. **`membership_tiers`**: Kategori tingkatan member klub, harga, masa aktif, benefit, dan persentase diskon.
2. **`tenant_members`**: Profil suporter, NIK, data KYC KTP/wajah, saldo poin, dan relasi tier klub.
3. **`season_passes`**: Data tiket terusan suporter, kuota match, dan konfigurasi hari buka klaim.
4. **`season_pass_claims`**: Catatan klaim e-ticket pertandingan yang terhubung ke model tiket standar.
5. **`fan_points_ledger`**: Buku besar mutasi perolehan dan penukaran poin suporter.
6. **`match_predictions`**: Data tebak skor pertandingan suporter.
7. **`fan_quizzes` & `fan_quiz_participants`**: Kuis klub dan rekaman partisipasi suporter.
8. **`korwils`**: Data basis suporter resmi dan koordinator wilayah.
9. **`korwil_allocations`**: Penetapan kuota matchday untuk rombongan korwil.
10. **`korwil_member_consents`**: Status persetujuan NIK anggota rombongan korwil.

---

## 4. Daftar Rute Aplikasi (Endpoints)

### Rute Backoffice Klub (Organizer)
Akses melalui menu navigasi **Klub & Suporter** di panel organizer:

| Metode | URL Route | Nama Rute | Fungsi |
| :--- | :--- | :--- | :--- |
| `GET` | `/organizer/membership/tiers` | `organizer.membership.tiers.index` | Daftar tier keanggotaan |
| `GET` | `/organizer/membership/tiers/create` | `organizer.membership.tiers.create` | Form tambah tier |
| `POST` | `/organizer/membership/tiers` | `organizer.membership.tiers.store` | Simpan tier baru |
| `GET` | `/organizer/membership/tiers/{tier}/edit` | `organizer.membership.tiers.edit` | Form edit tier |
| `PUT` | `/organizer/membership/tiers/{tier}` | `organizer.membership.tiers.update` | Perbarui tier |
| `DELETE` | `/organizer/membership/tiers/{tier}` | `organizer.membership.tiers.destroy` | Hapus tier |
| `GET` | `/organizer/membership/members` | `organizer.membership.members.index` | Database suporter & status KYC |
| `GET` | `/organizer/membership/members/{member}` | `organizer.membership.members.show` | Detail suporter & dokumen KYC |
| `POST` | `/organizer/membership/members/{member}/verify-kyc` | `organizer.membership.members.verify-kyc` | Setujui atau tolak KYC |
| `GET` | `/organizer/season-passes` | `organizer.season-passes.index` | Daftar tiket terusan terbit |
| `GET` | `/organizer/season-passes/create` | `organizer.season-passes.create` | Form penerbitan season pass |
| `POST` | `/organizer/season-passes` | `organizer.season-passes.store` | Simpan season pass |
| `GET` | `/organizer/gamification` | `organizer.gamification.index` | Manajemen tebak skor & kuis |
| `POST` | `/organizer/gamification/matches/{event}/score` | `organizer.gamification.update-score` | Input skor akhir & bagi poin |
| `POST` | `/organizer/gamification/quizzes` | `organizer.gamification.store-quiz` | Terbitkan kuis baru |
| `GET` | `/organizer/korwil` | `organizer.korwil.index` | Manajemen basis korwil & alokasi |
| `POST` | `/organizer/korwil/store-korwil` | `organizer.korwil.store-korwil` | Tambah korwil baru |
| `POST` | `/organizer/korwil/store-allocation` | `organizer.korwil.store-allocation` | Set alokasi kuota match |

---

### Rute Portal Suporter (Fan Zone)
Akses melalui menu **Fan Zone & Kartu Member** di header dan sidebar:

| Metode | URL Route | Nama Rute | Fungsi |
| :--- | :--- | :--- | :--- |
| `GET` | `/fan/dashboard` | `fan.dashboard` | Dashboard utama & kartu digital suporter |
| `GET` | `/fan/kyc` | `fan.kyc` | Form pengisian NIK & upload foto KTP/wajah |
| `POST` | `/fan/kyc` | `fan.kyc.submit` | Kirim data verifikasi identitas |
| `GET` | `/fan/season-pass` | `fan.season-pass` | Daftar tiket terusan & klaim matchday |
| `POST` | `/fan/season-pass/{event}/{seasonPass}/claim` | `fan.season-pass.claim` | Klaim e-ticket pertandingan (H-X hari) |
| `GET` | `/fan/game-zone` | `fan.game-zone` | Halaman tebak skor & kuis trivia |
| `POST` | `/fan/game-zone/predict/{event}` | `fan.game-zone.predict` | Kirim tebakan skor pertandingan |
| `POST` | `/fan/game-zone/quiz/{quiz}` | `fan.game-zone.quiz` | Kirim jawaban kuis trivia |
| `POST` | `/fan/korwil-consent/{consent}/respond` | `fan.korwil-consent.respond` | Konfirmasi izin NIK rombongan korwil |

---

## 5. Panduan Operasional Klub

### Langkah Menyiapkan Musim Baru (Season Setup):
1. **Atur Tier Membership**: Buat kategori tier seperti Silver dan Gold dengan masa aktif 365 hari dan persentase diskon tiket.
2. **Buat Pertandingan Kandang**: Pada menu Events, buat jadwal match dan aktifkan opsi **Allow Season Pass** serta tentukan parameter **H-X hari buka klaim tiket** (contoh: 5 hari sebelum match).
3. **Terbitkan Paket Season Pass**: Buka menu Season Pass untuk menerbitkan paket terusan suporter.
4. **Verifikasi Data KYC Suporter**: Periksa foto KTP suporter secara berkala pada menu Database Suporter agar suporter dapat bertransaksi dengan lancar.
5. **Aktifkan Tebak Skor**: Setiap sebelum kick-off, umumkan mini game tebak skor untuk memeriahkan hari pertandingan dan tingkatkan loyalitas suporter.
