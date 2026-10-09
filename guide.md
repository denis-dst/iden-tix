# Panduan Pengaturan Kredensial Doku API & Webhook

Berikut adalah panduan lengkap mengenai cara menempatkan kredensial Doku API yang Anda miliki ke dalam sistem IdenTix.

---

## 1. Konfigurasi File `.env`
Buka file [**.env**](file:///d:/laragon/www/iden-tix/.env) pada root project dan atur variabel berikut (biasanya di baris **86** ke bawah):

```ini
DOKU_CLIENT_ID=masukkan_client_id_anda
DOKU_SHARED_KEY=masukkan_active_secret_key_anda
DOKU_IS_PRODUCTION=false # Ubah ke true jika beralih ke lingkungan Live / Production
DOKU_API_URL=https://api-sandbox.doku.com # Gunakan https://api.doku.com untuk production
```

### Keterangan Pengisian:
* **Client ID** ➡️ Salin dan tempelkan ke kolom `DOKU_CLIENT_ID`.
* **API Keys Active Secret Key / API Key** ➡️ Salin dan tempelkan ke kolom `DOKU_SHARED_KEY`. Kunci ini digunakan untuk kalkulasi signature HMAC-SHA256 untuk memvalidasi transaksi secara aman.
* **Doku API URL** ➡️ URL Endpoint API Doku. Secara default menggunakan sandbox (`https://api-sandbox.doku.com`).

---

## 2. Kunci Publik (Doku Public Key & Merchant Public Key)
* **Status**: **Tidak Perlu Dimasukkan ke `.env`**.
* **Penjelasan**: Layanan pembayaran Doku Checkout / Payment Link yang diintegrasikan pada aplikasi ini (melalui [**DokuService**](file:///d:/laragon/www/iden-tix/app/Services/DokuService.php)) menggunakan algoritma symmetric signature HMAC-SHA256. Skema ini hanya membutuhkan kombinasi **Client ID** dan **Shared/Secret Key**. Kunci Publik asimetris (`.pem`) tidak diperlukan untuk integrasi standard ini.

---

## 3. Konfigurasi "Snap Token URL" (Notification / Webhook URL)
Kolom **"Snap Token URL"** (Notification/Webhook URL) di Dashboard Doku Merchant Portal digunakan oleh server Doku untuk mengirim notifikasi (callback) setiap kali ada pembayaran yang sukses dari pelanggan.

Isi kolom tersebut dengan URL Endpoint aplikasi Anda:

* **Lingkungan Pengembangan (Lokal)**: 
  * `http://iden-tix.test/doku/notification`
  * *Catatan*: Doku memerlukan domain publik agar bisa mengirim callback ke komputer lokal Anda. Anda dapat menggunakan tool tunnel seperti **Ngrok** atau **Expose** untuk meneruskan request ke komputer lokal Anda (contoh URL: `https://subdomain.ngrok-free.app/doku/notification`).
* **Lingkungan Production (Live)**: 
  * `https://domain-anda.com/doku/notification`

### Arsitektur Penanganan Notifikasi di Aplikasi:
* **Route**: `/doku/notification` (Metode POST, didefinisikan di [**routes/web.php**](file:///d:/laragon/www/iden-tix/routes/web.php)).
* **Middleware**: CSRF Verification dikecualikan untuk route ini di [**bootstrap/app.php**](file:///d:/laragon/www/iden-tix/bootstrap/app.php).
* **Controller Handler**: Method `handleDokuNotification` di [**PublicEventController.php**](file:///d:/laragon/www/iden-tix/app/Http/Controllers/PublicEventController.php) akan menerima data dari Doku, memverifikasi keaslian signature, dan menandai transaksi sebagai lunas (`paid`) serta menerbitkan tiket otomatis secara realtime.
