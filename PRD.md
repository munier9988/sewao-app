# PRD.md — Product Requirements Document: Sewao (Production Web App)

- **Nama Produk:** Sewao — Platform Katalog & Manajemen Rental Elektronik
- **Target Rilis:** MVP Production (Timebox Sprint: 3,5 Jam / Selesai Pukul 16.00 WIB)
- **Tech Stack:** Laravel 11 (PHP 8.2+) + Blade + Tailwind CSS + Alpine.js + **MySQL**
- **Dokumen Gabungan:** System Specification + Anti-Slop UI/UX Design Spec + Sprint Task Plan

---

## BAGIAN 1: SPESIFIKASI SISTEM & ARSITEKTUR

### 1.1 Ringkasan & Batasan Waktu (Lean Production Scope)
**Sewao** adalah aplikasi web rental perangkat elektronik (iPhone, Android, Laptop/Komputer, dan Kamera) berbasis **MySQL** dengan pemisahan hak akses **3 Role Pengguna** (`admin`, `seller`, `pembeli`).

Mengingat waktu pengerjaan maksimal **3,5 jam**, arsitektur dirancang **ramping, monolitik, dan siap produksi** tanpa fitur berlebihan:
- Menggunakan autentikasi berbasis sesi bawaan framework (*login* & *logout* dengan proteksi *middleware* per role).
- Tidak menggunakan *payment gateway*, kalkulator denda otomatis, maupun unggah dokumen identitas (KTP) di dalam sistem.
- Interaksi transaksi antara **Pembeli** dan **Seller** berpusat pada pencatatan klaim sewa unit (`Pesanan`) yang terhubung langsung ke tautan **WhatsApp (`https://wa.me/...`)**.

---

### 1.2 Matriks Hak Akses (3 Role Pengguna)

| Fitur / Modul | Admin | Seller | Pembeli |
| :--- | :---: | :---: | :---: |
| Login & Logout | Ya | Ya | Ya |
| **Kelola User** (Tambah, Edit Role/Data, Hapus User) | **Ya (Penuh)** | Tidak | Tidak |
| **Kelola Kategori** (Tambah, Edit, Hapus Kategori) | Tidak | **Ya (Penuh)** | Tidak |
| **Kelola Produk** (Tambah, Edit, Hapus Unit, Stok, Foto, Unggulan) | Tidak | **Ya (Penuh)** | Tidak |
| **Atur Nomor WhatsApp Toko** | Tidak | **Ya** | Tidak |
| **Jelajah Katalog** (Cari, Filter Kategori, Urutkan) | Lihat Saja | Lihat Saja | **Ya** |
| **Favorit** (Simpan / Hapus Produk Favorit) | Tidak | Tidak | **Ya** |
| **Pesanan & Hubungi WhatsApp** (Miliki/Sewa Unit & Redirect WA) | Tidak | Lihat Daftar Masuk | **Ya** |

> **Catatan Aturan Bisnis:**
> 1. **Admin** berfokus penuh pada tata kelola pengguna (`users`).
> 2. **Seller** berfokus penuh pada tata kelola inventaris (`categories`, `products`, dan nomor WA penerimaan pesanan).
> 3. **Pembeli** hanya dapat menjelajahi katalog, menandai favorit, mengklaim/menyewa unit ke daftar miliknya (`Pesanan`), dan menghubungi Seller via WhatsApp.

---

### 1.3 Skema Database MySQL

Database menggunakan nama `sewao_db` dengan 5 tabel utama:

#### 1. Tabel `users`
| Kolom | Tipe Data MySQL | Keterangan & Constraint |
| :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED PK AUTO_INCREMENT` | Primary Key |
| `name` | `VARCHAR(100)` | Nama lengkap pengguna |
| `email` | `VARCHAR(120) UNIQUE` | Email untuk login |
| `password` | `VARCHAR(255)` | Password ter-hash (`bcrypt`) |
| `role` | `ENUM('admin', 'seller', 'pembeli')` | Default `'pembeli'` |
| `wa_phone` | `VARCHAR(20) NULLABLE` | Nomor WA (format `628xxx`, wajib untuk `seller`, opsional untuk `pembeli`) |
| `created_at` / `updated_at` | `TIMESTAMP` | Waktu pembuatan & pembaruan |

#### 2. Tabel `categories`
| Kolom | Tipe Data MySQL | Keterangan & Constraint |
| :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED PK AUTO_INCREMENT` | Primary Key |
| `name` | `VARCHAR(80) UNIQUE` | Nama kategori (misal: `iPhone & iOS`) |
| `slug` | `VARCHAR(100) UNIQUE` | Slug URL-friendly |
| `created_at` / `updated_at` | `TIMESTAMP` | Timestamp |

#### 3. Tabel `products`
| Kolom | Tipe Data MySQL | Keterangan & Constraint |
| :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED PK AUTO_INCREMENT` | Primary Key |
| `category_id` | `BIGINT UNSIGNED FK` | Relasi ke `categories.id` (`ON DELETE RESTRICT`) |
| `seller_id` | `BIGINT UNSIGNED FK` | Relasi ke `users.id` (`role = seller`) |
| `name` | `VARCHAR(150)` | Nama perangkat elektronik |
| `price_per_day` | `INT UNSIGNED` | Tarif sewa per hari dalam Rupiah (`>= 0`) |
| `stock` | `INT UNSIGNED` | Jumlah unit tersedia / ready (`>= 0`) |
| `description` | `TEXT NULLABLE` | Spesifikasi & kelengkapan unit |
| `image_url` | `VARCHAR(255) NULLABLE` | URL gambar atau path lokal (fallback ke SVG placeholder jika kosong) |
| `is_featured` | `BOOLEAN` | Default `false` (Badge `★ Favorit`) |
| `created_at` / `updated_at` | `TIMESTAMP` | Timestamp |

#### 4. Tabel `favorites` (Wishlist Pembeli)
| Kolom | Tipe Data MySQL | Keterangan & Constraint |
| :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED PK AUTO_INCREMENT` | Primary Key |
| `user_id` | `BIGINT UNSIGNED FK` | Relasi ke `users.id` (`ON DELETE CASCADE`) |
| `product_id` | `BIGINT UNSIGNED FK` | Relasi ke `products.id` (`ON DELETE CASCADE`) |
| `created_at` / `updated_at` | `TIMESTAMP` | Unique composite (`user_id`, `product_id`) |

#### 5. Tabel `orders` (Daftar Produk Dimiliki / Disewa Pembeli)
| Kolom | Tipe Data MySQL | Keterangan & Constraint |
| :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED PK AUTO_INCREMENT` | Primary Key |
| `order_code` | `VARCHAR(30) UNIQUE` | Kode unik transaksi (misal: `SW-20261116-001`) |
| `buyer_id` | `BIGINT UNSIGNED FK` | Relasi ke `users.id` (`role = pembeli`) |
| `product_id` | `BIGINT UNSIGNED FK` | Relasi ke `products.id` |
| `duration_days` | `INT UNSIGNED` | Durasi sewa (default `1` hari) |
| `total_price` | `INT UNSIGNED` | `price_per_day * duration_days` |
| `status` | `ENUM('menunggu_wa', 'disewa', 'selesai')` | Default `'menunggu_wa'` saat klik Sewa via WA |
| `created_at` / `updated_at` | `TIMESTAMP` | Timestamp |

---

### 1.4 Data Dummy Wajib (`DatabaseSeeder`)
Saat perintah `php artisan migrate:fresh --seed` dijalankan, sistem otomatis mengisi data berikut:
1. **3 Akun Demo Utama** (Password semua akun: `password123`):
   - **Admin:** `admin@sewao.id` (`role: admin`, Nama: `Admin Sewao`)
   - **Seller:** `seller@sewao.id` (`role: seller`, Nama: `Toko Sewao Pusat`, `wa_phone: 6281200000000`)
   - **Pembeli:** `pembeli@sewao.id` (`role: pembeli`, Nama: `Budi Penyewa`, `wa_phone: 6285700000000`)
   - Tambahan 2 akun pembeli dummy agar tabel Kelola User Admin langsung terlihat realistis.
2. **4 Kategori Elektronik:**
   - `iPhone & iOS`
   - `Android`
   - `Komputer & Laptop`
   - `Kamera & Multimedia`
3. **8 Produk Elektronik Dummy:**
   - *iPhone 15 Pro 256GB Natural* (`Rp 250.000/hari`, Stok: `4`, Unggulan: `Ya`)
   - *iPhone 13 128GB Midnight* (`Rp 140.000/hari`, Stok: `6`, Unggulan: `Tidak`)
   - *Samsung Galaxy S24 Ultra* (`Rp 260.000/hari`, Stok: `3`, Unggulan: `Ya`)
   - *Xiaomi 14T Pro Leica 512GB* (`Rp 165.000/hari`, Stok: `5`, Unggulan: `Tidak`)
   - *MacBook Air M2 13-inch 16GB* (`Rp 220.000/hari`, Stok: `3`, Unggulan: `Ya`)
   - *ASUS ROG Zephyrus G14 RTX* (`Rp 275.000/hari`, Stok: `2`, Unggulan: `Tidak`)
   - *Sony A7 III + Lensa 28-70mm* (`Rp 280.000/hari`, Stok: `3`, Unggulan: `Ya`)
   - *DJI Osmo Pocket 3 Creator* (`Rp 185.000/hari`, Stok: `0`, Unggulan: `Tidak` — untuk menguji status habis)
4. **2 Data Dummy `orders` & 2 Data Dummy `favorites`** untuk akun `pembeli@sewao.id` agar tab **Pesanan** dan **Favorit** langsung terisi saat diuji.

---

### 1.5 Alur Bisnis Utama
1. **Alur Sewa & Hubungi WhatsApp (Pembeli):**
   - Pembeli membuka halaman **Jelajah**, memilih perangkat dengan `stock > 0`, lalu menekan tombol **"Sewa via WhatsApp"**.
   - Sistem memproses `POST /orders` yang menyimpan record baru ke tabel `orders` milik pembeli dengan status `menunggu_wa` (sehingga produk langsung muncul di tab **Pesanan** sebagai unit yang dimiliki/disewa pembeli), kemudian langsung mengarahkan browser ke URL `https://wa.me/{seller.wa_phone}?text={pesan_terformat}`.
   - Jika pengunjung belum login saat menekan tombol sewa, sistem mengarahkan ke halaman `/login` terlebih dahulu.
2. **Alur Favorit (Pembeli):**
   - Pembeli menekan ikon hati/bookmark pada kartu produk di **Jelajah** (`POST /favorites/toggle/{product}`) untuk menyimpan atau menghapus dari tab **Favorit**.
3. **Alur Back-Office (Seller & Admin):**
   - Setelah login, **Admin** diarahkan ke `/admin/users` (layout **Left Sidebar**).
   - Setelah login, **Seller** diarahkan ke `/seller/products` (layout **Left Sidebar**).
   - Nomor WhatsApp Seller dapat diperbarui langsung dari dashboard Seller dan otomatis tervalidasi ke format `628xxx`.

---

## BAGIAN 2: SPESIFIKASI DESAIN ANTARMUKA (ANTI-SLOP UI/UX)

### 2.1 Prinsip Desain "Tactile Hardware & Warm Amber" (Anti-Slop)
Menghindari tampilan template AI generik (tanpa gradasi ungu/biru pasaran, tanpa bayangan kabur berlebihan, tanpa sudut terlalu bulat seperti mainan):
- **Estetika Industrial-Editorial:** Terinspirasi dari katalog perangkat keras modern dengan kartu berbingkai tegas (`1.5px solid`), latar krem hangat (`#FFFDF5`), permukaan putih bersih (`#FFFFFF`), dan aksen kuning kunyit/amber berenergi tinggi (`#FACC15`).
- **Kontras Tinggi (WCAG AA/AAA):** Teks di atas elemen kuning selalu menggunakan cokelat espresso gelap (`#422006`) atau arang pekat (`#1C1917`), bukan teks putih pudar.
- **Tipografi Berkarakter:**
  - **Judul & Angka KPI:** `Bricolage Grotesque` (Bold/ExtraBold, *tight tracking* `-0.02em`).
  - **Teks UI & Deskripsi:** `Plus Jakarta Sans` (Regular/Medium/SemiBold).
  - **Harga, Kode Order, & Spesifikasi Teknis:** `JetBrains Mono` (memberikan presisi teknis khas katalog elektronik).

### 2.2 Design Tokens (Palet Warna)
- `--bg-canvas`: `#FFFDF5` (Krem Gading Hangat)
- `--bg-surface`: `#FFFFFF` (Putih Bersih)
- `--bg-subtle`: `#FEF9C3` (Kuning Lembut)
- `--accent-yellow`: `#FACC15` (Kuning Utama Sewao)
- `--accent-yellow-hover`: `#EAB308`
- `--accent-amber-dark`: `#B45309` (Teks harga & indikator aktif)
- `--text-primary`: `#1C1917`
- `--text-on-yellow`: `#422006`
- `--text-muted`: `#57534E`
- `--wa-primary`: `#15803D` (Hijau WhatsApp kontras tinggi)
- `--border-crisp`: `#E7E5E4` / `#FDE68A`

---

### 2.3 Arsitektur Navigasi Berdasarkan Peran

#### A. Navigasi Pembeli (Top Sticky Navbar — 3 Menu Utama)
Navbar atas bersih dan fokus dengan tepat **3 pilihan menu utama** di tengah/kanan:
1. **`Jelajah`** — Katalog perangkat, hero banner ringkas, pencarian real-time, pill filter kategori, sorting, dan grid kartu produk.
2. **`Pesanan`** — Daftar unit yang disewa/dimiliki pembeli beserta kode transaksi, tarif, status (`Menunggu Konfirmasi WA` / `Aktif Disewa`), dan tombol chat ulang WhatsApp.
3. **`Favorit`** — Daftar unit perangkat yang disimpan pembeli.
*(Di ujung kanan navbar terdapat info nama akun pembeli & tombol `Keluar`, atau tombol `Masuk` bagi tamu).*

#### B. Navigasi Admin & Seller (Left Sidebar Navbar)
Halaman dashboard Admin dan Seller menggunakan **Left Sidebar Navbar tetap (`width: 250px` di desktop, *slide-over drawer* di mobile)**:
- **Menu Left Sidebar untuk `Admin`:**
  - `👥 Kelola Pengguna` (Halaman utama Admin: statistik user + tabel CRUD user)
  - `🌐 Lihat Katalog Publik` (Membuka tampilan storefront)
  - `🚪 Keluar` (Di bagian bawah sidebar)
- **Menu Left Sidebar untuk `Seller`:**
  - `📦 Kelola Produk` (Statistik unit + CRUD perangkat elektronik)
  - `🏷️ Kelola Kategori` (CRUD kategori perangkat)
  - `📱 Pengaturan WhatsApp` (Form ubah nomor WA penerimaan pesanan)
  - `🌐 Lihat Katalog Publik`
  - `🚪 Keluar`

---

### 2.4 Wireframe Teks

#### 1) Tampilan Pembeli — Top Navbar (`Jelajah` | `Pesanan` | `Favorit`)
```text
+------------------------------------------------------------------------------------+
| [LOGO] 📸 Sewao            ( Jelajah )   ( Pesanan )   ( Favorit )     [Budi | Out] |
+------------------------------------------------------------------------------------+
|                                                                                    |
|  +------------------------------------------------------------------------------+  |
|  | [HERO - Latar Kuning Lembut #FEF9C3, Border #FDE68A]                         |  |
|  | Butuh Gadget Dadakan? Sewa Aja di Sewao!                   [ 📱 💻 📸 🎧 ]   |  |
|  | Unit terawat, siap pakai, booking langsung ke WhatsApp.                      |  |
|  +------------------------------------------------------------------------------+  |
|                                                                                    |
|  [ 🔍 Cari iPhone, MacBook, Sony A7...              ]   [ Urutkan: Favorit Dulu v ] |
|  [ Semua ]  [ iPhone & iOS ]  [ Android ]  [ Komputer & Laptop ]  [ Kamera ]       |
|                                                                                    |
|  +--------------------+  +--------------------+  +--------------------+            |
|  | [★ Favorit]   [♡]  |  |               [♥]  |  | [★ Favorit]   [♡]  |            |
|  |   [Gambar / SVG]   |  |   [Gambar / SVG]   |  |   [Gambar / SVG]   |            |
|  |     [iPhone & iOS] |  |          [Android] |  | [Komputer & Laptop]|            |
|  +--------------------+  +--------------------+  +--------------------+            |
|  | iPhone 15 Pro 256  |  | Samsung S24 Ultra  |  | MacBook Air M2 16G |            |
|  | ProRAW 48MP, BH96% |  | Zoom 10x, S-Pen    |  | M2 Chip, Include bag |            |
|  | ------------------ |  | ------------------ |  | ------------------ |            |
|  | Rp 250.000/hr  [4] |  | Rp 260.000/hr  [3] |  | Rp 220.000/hr  [3] |            |
|  | [ Sewa WhatsApp  ] |  | [ Sewa WhatsApp  ] |  | [ Sewa WhatsApp  ] |            |
|  +--------------------+  +--------------------+  +--------------------+            |
+------------------------------------------------------------------------------------+
```

#### 2) Tampilan Admin & Seller — Left Sidebar Layout
```text
+--------------------+---------------------------------------------------------------+
| 📸 Sewao           |  PANEL PENGELOLA (ADMIN / SELLER)             [Halo, Admin]   |
| Back-Office        +---------------------------------------------------------------+
| ------------------ |                                                               |
|                    |  +-------------------+ +-------------------+ +--------------+ |
| > Kelola Pengguna  |  | Total Pengguna: 5 | | Admin: 1          | | Pembeli: 3   | |
|   (atau Produk &   |  +-------------------+ +-------------------+ +--------------+ |
|    Kategori utk    |                                                               |
|    Seller)         |  +---------------------------------------------------------+  |
|                    |  | Daftar Data & Tombol [+ Tambah Baru]                    |  |
| ------------------ |  | ------------------------------------------------------- |  |
| 🌐 Lihat Katalog   |  | Nama / Unit        | Role / Kategori | Detail   | Aksi  |  |
|                    |  | -------------------+-----------------+----------+------ |  |
|                    |  | Admin Sewao        | admin           | admin@.. |[E][H] |  |
|                    |  | Toko Sewao Pusat   | seller          | 62812... |[E][H] |  |
|                    |  | Budi Penyewa       | pembeli         | pembeli@ |[E][H] |  |
| ------------------ |  +---------------------------------------------------------+  |
| 🚪 Keluar          |                                                               |
+--------------------+---------------------------------------------------------------+
```
