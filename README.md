# KaryaLokal.id

Marketplace kerajinan lokal dengan identitas digital untuk setiap karya. Pembeli dapat mengenal perajin dan proses pembuatan lewat Craft Story yang dibuka dari QR unik.

## Fitur

- Katalog dengan pencarian, kategori, rentang harga, jenis pemenuhan, dan sorting.
- Pendaftaran customer dan perajin dengan lima role: customer, artisan, admin, curator, dan customer service.
- Pengajuan produk dan tahapan proses dari dashboard perajin.
- Antrean kurator, checklist verifikasi, keputusan review, serta penerbitan Craft ID dan QR SVG.
- Keranjang sesi/akun, gift message, checkout, snapshot harga, validasi stok, dan histori order.
- Digital Craft Story publik dengan pencatatan scan QR.
- Tiket bantuan dengan bukti lampiran dan status penyelesaian customer service.
- Chat customer-perajin yang terhubung dengan produk, dengan inbox dan riwayat pesan.

Payment gateway, pelacakan kurir, login Google, chat real-time, referral, loyalty, dan refund otomatis belum terhubung.

## Pembayaran Manual

Customer mengunggah bukti pada detail pesanan; Admin memeriksanya melalui menu **Verifikasi pembayaran**. Pada `APP_ENV=local`, checkout memakai nomor rekening/VA simulasi dan aset `public/images/karyalokal-qris.jpeg`. Kode QR tersebut hanya untuk simulasi dan tidak menerima dana.

```dotenv
KARYALOKAL_PAYMENT_SIMULATION=true
KARYALOKAL_BCA_ACCOUNT=
KARYALOKAL_MANDIRI_ACCOUNT=
KARYALOKAL_BNI_ACCOUNT=
KARYALOKAL_BRI_ACCOUNT=
KARYALOKAL_BCA_VA=
KARYALOKAL_MANDIRI_VA=
KARYALOKAL_BNI_VA=
KARYALOKAL_BRI_VA=
KARYALOKAL_QRIS_IMAGE=
```

Nomor lokal yang tampil: BCA `888000112233`, Mandiri `889000223344`, BNI `880000334455`, BRI `887000445566`; virtual account BCA `88001 90001 123456`, Mandiri `89508 90002 234567`, BNI `98881 90003 345678`, BRI `77777 90004 456789`. Batasnya 24 jam untuk transfer bank, 2 jam untuk VA, dan 30 menit untuk QRIS.

Checkout menampilkan hitung mundur dari deadline yang tersimpan pada order: 24 jam untuk transfer bank, 2 jam untuk VA, dan 30 menit untuk QRIS. Setelah lewat batas, bukti tidak dapat diunggah dan stok order dipulihkan.

Untuk produksi, set `KARYALOKAL_PAYMENT_SIMULATION=false`, isi rekening/VA merchant yang valid, dan sediakan QRIS resmi. `KARYALOKAL_QRIS_IMAGE` adalah path file relatif terhadap `public`, misalnya `images/qris-merchant.png`. Jalankan `php artisan config:clear` setelah mengubah konfigurasi.

## Data Operasional Lokal

`RoleFeatureDataSeeder` menambah 50 produk, order pending, tiket, percakapan, scan QR, wishlist, dan cart menggunakan akun role yang sudah ada. Seeder ini idempotent, hanya berjalan pada `APP_ENV=local`, tidak membuat akun/role baru, dan order tetap pending agar tidak masuk ke rekap penjualan terverifikasi. Jalankan dengan `php artisan db:seed --class=RoleFeatureDataSeeder`.

## Menjalankan Lokal

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve
```

Buat database MySQL bernama `karyalokal_db` dan atur koneksi di `.env` sebelum migration. Migration `2026_10_01_000300_remove_legacy_ptsl_data` menghapus tabel dan notifikasi PTSL lama secara permanen. Backup database terlebih dahulu sebelum menjalankannya pada database yang berisi data.

## Akun Berdasarkan Role

Seeder menyiapkan akun lokal berikut, masing-masing untuk role yang berbeda:

- Admin: `admin@karyalokal.local`
- Kurator: `kurator@karyalokal.local`
- Customer Service: `cs@karyalokal.local`
- Perajin: `siti@karyalokal.local`
- Pelanggan: `pembeli@karyalokal.local`

Password akun lokal tersebut adalah `password`; gunakan hanya untuk pengembangan lokal.

## Test

```powershell
php artisan test
```
