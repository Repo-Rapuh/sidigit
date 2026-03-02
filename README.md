# sidigit

## Modul Produksi
- URL modul internal:
  - `/productions` (Kanban gabungan Desain + Produksi)
  - `/productions/history` (Riwayat job produksi)
- Konsep: 1 `production_job` untuk setiap `order_item` (bukan per invoice/order header).
- Trigger pembuatan job:
  - saat status order menjadi `desain` => job tahap `desain`
  - saat status order menjadi `produksi` => job tahap `produksi`
- Akses menu: `Transaksi -> Produksi` (board kanban gabungan) dan `Riwayat Produksi`.
- Parent menu `Produksi` sekarang mengarah ke `productions.index` (bukan `#`), tetap bisa expand submenu.

### Flow Produksi (Per Item)
- `antrian -> in_progress -> qc -> siap_diambil`
- Jika QC gagal: `qc -> in_progress` (kembali ke Produksi).
- Setiap transisi disimpan ke `production_job_logs` untuk jejak audit.

### Model Kanban
- Board digabung menjadi satu halaman.
- Tiap card adalah `1 item order`.
- Ada mekanisme `Ambil Task` (claim) dan `Lepas`.
- Card bisa dipindahkan antar kolom dengan drag-and-drop untuk transisi status.
- Kolom board: `Antrian -> Desain -> Produksi -> QC -> Siap Diambil`.
- Tahap `Desain` bersifat opsional: task bisa langsung dari `Antrian` ke `Produksi` (bypass desain).
- Tahap `Finishing` tidak dipisah sebagai kolom; aktivitas finishing dianggap bagian dari tahap `Produksi`.
- Tahap `Selesai` tidak digunakan pada board; perpindahan produksi langsung ke `QC`.
- Perubahan status di board mengikuti flow yang sama untuk menjaga konsistensi operasional.
- Kolom board dibuat lebih lebar agar informasi card lebih mudah dibaca pada monitor desktop tanpa mengorbankan kerapian.
- View board berbasis role operasional:
  - user role `Desainer` fokus menampilkan task tahap desain.
  - user role `Operator` fokus menampilkan task tahap produksi.
  - owner/superadmin/manager tetap melihat semua task.
- Kartu kanban diperkaya informasi: deadline + countdown, bahan, ukuran, prioritas, dan status claim PIC.
- Prioritas task otomatis (`Urgent`, `Today`, `Normal`) dan urutan card mengutamakan prioritas + deadline terdekat.
- Aksi per state disederhanakan:
  - `Antrian`: `Ambil Task` lalu tombol start tunggal (`Mulai Desain`/`Mulai Produksi`) sesuai tahap.
  - `Desain`: `Lanjut Produksi`.
  - `Produksi`: `Kirim QC`.
- Ditambahkan modal **Detail Task Produksi** untuk melihat spek lengkap (produk, qty, bahan, ukuran, finishing, deadline, catatan) + placeholder lampiran file.
- Perbaikan stabilitas: render modal **Detail Task Produksi** dibuat null-safe agar halaman `/productions` tidak error 500 saat `taskDetail` belum terisi (state awal Livewire).

### Riwayat Produksi
- Halaman `/productions/history` tetap menggunakan list/tabel riwayat produksi.
- Aksi `Riwayat` pada tiap baris menampilkan popup detail dengan tampilan card timeline (inspirasi TailAdmin Logistics):
  - menampilkan `Tracking ID`, badge status terkini, dan urutan event produksi per item.
  - setiap event menampilkan pelaku perubahan (user) untuk tracing, bukan hanya role.
- Ukuran modal riwayat diperkecil agar lebih proporsional, dengan tipografi timeline yang lebih compact dan area isi scrollable.
- Styling popup riwayat diperhalus: ukuran jam diperkecil, angka memakai `tabular-nums`, dan spacing timeline dirapikan agar lebih nyaman dibaca.
- Tipografi popup disetel ulang agar lebih proporsional: hierarki ukuran teks `tracking/date/title/subtitle/time` dibuat lebih seimbang dan mudah dipindai.

### Sinkronisasi Status Order
- Sinkron status order dihitung dari seluruh job item produksi (tahap desain + produksi).
- Jika masih ada item yang berada di tahap `desain`, status order tetap `desain`.
- Jika seluruh item `siap_diambil` -> order otomatis `siap`.
- Jika seluruh item sudah masuk `qc`/`siap_diambil` -> order otomatis `qc`.
- Selain kondisi di atas (tidak ada item di desain) -> order otomatis `produksi`.
- Saat sinkron status dari board membuat order masuk ke `produksi`, sistem otomatis sinkron pergerakan stok order menjadi `stock out` (ref_type `order`) agar pemakaian bahan tercatat.

### Assignment Role
- Auto-assign berbasis status order:
  - status order `desain` -> role `Desainer`
  - status order `produksi` -> role `Operator`
- User dengan role terkait dapat mengambil task tanpa assign manual satu-per-satu.
- Assign manual tetap tersedia sebagai override operasional.

### Permission Produksi
- `production.view`
- `production.edit`
- `production.assign`
- `production.qc`

### Preset Role Produksi & Stok
- `RolePermissionSeeder` menambahkan preset akses role berikut:
  - `Desainer`: menu `Transaksi -> Produksi` dan `Stok` beserta seluruh submenu.
  - `Operator`: menu `Transaksi -> Produksi` dan `Stok` beserta seluruh submenu.
- Permission default:
  - `Desainer`: `production.view`, `production.edit`, lalu seluruh permission stok:
    - `stock-in.(view|create|edit|delete)`
    - `stock-out.(view|create|edit|delete)`
    - `stock-opname.(view|create|edit|delete)`
    - `stock-balance.view`, `stock-reservation.view`
  - `Operator`: sama seperti `Desainer` + `production.qc`.

### Catatan Simplifikasi
- Tidak ada scheduling mesin/jam produksi.
- Tidak ada splitting job ke multi-step internal.
- Tidak ada kapasitas planning otomatis.

## Dockerized workflows

### Local development
- Copy `.env.example` to `.env` and adjust anything you need for development (defaults target PHP 8.4 on the official PHP FPM image).
- Use `make help` to discover common Docker targets.
- Start the stack with `make up` (or `docker compose -f docker-compose.local.yml up --build`).
- Install PHP dependencies inside the container: `make composer-install` (or `docker compose -f docker-compose.local.yml exec app composer install`).
- Run database migrations: `docker compose -f docker-compose.local.yml exec app php artisan migrate`.
- Handle frontend assets on the host machine: run `npm install` and `npm run dev` locally to keep Vite watching for changes.
- Your API is available on `http://localhost:8080` and the host-side Vite server runs on `http://localhost:5173`.
- Point the `AWS_*` values in `.env` at your existing MinIO/S3 endpoint (the compose file defaults to `http://host.docker.internal:9000`; on Linux set this to the accessible host or network alias).

### Production image & deployment
- Review `docker/.env.production` and replace the placeholder values with your real production secrets before building.
- Build the images with `docker compose -f docker-compose.prod.yml build`.
- Push them to your registry (for example `docker tag sidigit-app:latest registry.example.com/sidigit-app:latest`).
- Run the stack where you deploy: `docker compose -f docker-compose.prod.yml up -d`.
- After the containers start, run `docker compose -f docker-compose.prod.yml exec app php artisan key:generate --force` (first run), `php artisan migrate --force`, and `php artisan storage:link` if you need public storage.
- Make sure `docker/.env.production` references the correct external MinIO/S3 endpoint and credentials before deploying.

## Modul Struk Thermal
- Ditambahkan modul struk thermal untuk order pada route: `/orders/{order}/receipt` (`orders.receipt`).
- Akses route struk diproteksi permission `order.view` melalui mapping explicit di `config/route_permissions.php`.
- Struk bisa dibuka publik internal aplikasi (halaman preview) atau langsung cetak thermal via query `?print=1`.
- Opsi ukuran thermal didukung via query `paper`:
  - `paper=80` (default)
  - `paper=58`
- Contoh: `/orders/{order}/receipt?payment_id=10&paper=58&print=1`
- Jika order punya beberapa pembayaran, struk bisa dipilih per pembayaran melalui parameter `payment_id`.
- Integrasi tombol:
  - daftar order: aksi `Print Struk` (muncul jika order sudah punya pembayaran),
  - halaman order (edit/lihat): tombol `Print Struk`,
  - halaman input pembayaran: tombol `Print Struk Terakhir`.
- Layout struk didesain untuk kertas thermal 80mm di `resources/views/admin/orders/receipt.blade.php`.
## Paket Aplikasi (Rencana Modular)
### Matriks Paket (Saran)
| Modul/Fitur | Silver (Rp200.000) | Gold (Rp300.000) | Platinum (Rp350.000) |
|---|---|---|---|
| Dashboard | ✅ | ✅ | ✅ |
| Customer | ✅ | ✅ | ✅ |
| Order + Quotation + Invoice | ✅ | ✅ | ✅ |
| Input Pembayaran | ✅ | ✅ | ✅ |
| Tracking Order Publik | ✅ | ✅ | ✅ |
| Stok (in/out/opname/saldo) | ❌ | ✅ | ✅ |
| Pengeluaran (bahan & umum) | ❌ | ✅ | ✅ |
| Laporan (sales & expense) | ❌ | ✅ | ✅ |
| Akuntansi (COA + Jurnal Umum) | ❌ | ✅ | ✅ |
| Audit Logs | ❌ | ❌ | ✅ |
| Multi Branch | ❌ | ❌ | ✅ |
| RBAC lanjutan (role/permission/menu custom) | ❌ | ❌ | ✅ |
| Laporan cabang | ❌ | ❌ | ✅ |

### Skema Praktis (Dikelola Superadmin Internal)
- Akun `superadmin` dipegang internal (tidak diberikan ke klien).
- Klien hanya menerima menu dan permission sesuai paket aktif.
- Akses final = user punya permission + fitur paket aktif.
- Konsep data yang disarankan:
  - `features`
  - `packages` (silver/gold/platinum)
  - `package_feature` (default fitur per paket)
  - `client_feature_overrides` (opsional ON/OFF khusus per klien)
- Implementasi teknis bertahap:
  - fase 1: kontrol menu + route middleware berbasis fitur
  - fase 2: guard fitur di service/action agar tidak bisa bypass API/livewire

## Blueprint Integrasi Web Publik
### Target Arsitektur
- `sidigit-core` (aplikasi ini): backoffice POS/ERP, sumber data utama order, pembayaran, stok, produksi, akuntansi.
- `sidigit-web` (project terpisah): website company profile + storefront ecommerce (public traffic).
- Integrasi antar app menggunakan API internal tenant-aware (bukan akses DB langsung antar project).

### Kenapa Dipisah
- Isolasi beban traffic publik agar tidak mengganggu operasional kasir/backoffice.
- SEO, caching, dan UX web publik bisa dioptimasi tanpa risiko regression ke modul internal.
- Deployment storefront dapat lebih sering tanpa menyentuh release cycle core.
- Batas keamanan lebih jelas: admin/backoffice tidak diekspos ke permukaan publik.

### Batas Tanggung Jawab Data
1. Master data produk, harga, stok, status order tetap authoritative di `sidigit-core`.
2. `sidigit-web` hanya menyimpan cache/read model untuk kebutuhan tampilan katalog.
3. Pembuatan order final tetap melalui API `sidigit-core`.
4. Tracking order publik membaca status dari `sidigit-core` (token tracking terenkripsi).

### Domain dan Routing Tenant
1. Backoffice: `app.client.com` atau `client.posklien.com`.
2. Website profile: `client.com`.
3. Storefront ecommerce: `shop.client.com` atau path `client.com/shop`.
4. Tenant mapping direkomendasikan via tabel/domain mapping di `sidigit-web`, dengan `tenant_key` yang sama dengan `sidigit-core`.

### Kontrak API Minimum (`sidigit-core`)
1. `GET /api/public/profile` untuk data company profile (about, contact, lokasi, logo).
2. `GET /api/public/catalog/products` untuk list produk aktif yang boleh dijual online.
3. `GET /api/public/catalog/products/{slug}` untuk detail produk, harga, opsi bahan/finishing.
4. `POST /api/public/checkout/quotes` untuk simulasi harga (opsional, sebelum checkout).
5. `POST /api/public/checkout/orders` untuk membuat order dari web ke status awal (`draft`/`quotation` sesuai flow).
6. `GET /api/public/checkout/orders/{public_token}` untuk melihat status checkout customer.
7. `GET /api/public/tracking/{token}` untuk tracking produksi/pesanan publik.

### API Contract (Tanpa Versioning URL)
- Prinsip endpoint publik: tidak memakai prefix versi seperti `/v1`.
- Base URL:
  - production: `https://{domain-backoffice}`
  - staging: `https://staging.{domain-backoffice}`
- Prefix API publik: `/api/public/*`

#### Format Response Standar
```json
{
  "success": true,
  "message": "OK",
  "data": {},
  "meta": {}
}
```

#### Format Error Standar
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "field_name": ["Pesan error"]
  },
  "code": "VALIDATION_ERROR"
}
```

#### 1) GET `/api/public/profile`
- Tujuan: data website company profile tenant.
- Auth: public (rate-limited).
- Query opsional: `tenant_key` (jika tidak pakai domain mapping).
- Contoh response `data`:
```json
{
  "company_name": "Motekarindo Printing",
  "tagline": "Digital Printing Cepat",
  "description": "Layanan cetak untuk bisnis dan personal.",
  "logo_url": "https://cdn.example.com/logo.png",
  "phone": "08123456789",
  "email": "hello@motekarindo.com",
  "address": "Jl. Contoh No. 1",
  "maps_url": "https://maps.google.com/...",
  "socials": {
    "instagram": "https://instagram.com/motekarindo",
    "whatsapp": "https://wa.me/628123456789"
  }
}
```

#### 2) GET `/api/public/catalog/products`
- Tujuan: list produk yang publish ke web.
- Auth: public (rate-limited).
- Query:
  - `search` (string)
  - `category` (slug/id)
  - `page` (int)
  - `per_page` (int)
  - `sort` (`latest|name|price_low|price_high`)
- Contoh item:
```json
{
  "id": 10,
  "slug": "banner-flexy-280",
  "name": "Banner Flexy China 280",
  "category": "Banner",
  "thumbnail_url": "https://cdn.example.com/p/banner.jpg",
  "price_from": 25000,
  "unit_label": "m2",
  "is_service": false,
  "is_available": true
}
```

#### 3) GET `/api/public/catalog/products/{slug}`
- Tujuan: detail produk untuk halaman detail + configurator.
- Auth: public (rate-limited).
- Contoh response `data`:
```json
{
  "id": 10,
  "slug": "banner-flexy-280",
  "name": "Banner Flexy China 280",
  "description": "Cocok untuk indoor/outdoor.",
  "images": [
    "https://cdn.example.com/p/banner-1.jpg"
  ],
  "price": {
    "base_price": 25000,
    "unit_label": "m2",
    "minimum_billable_area_m2": 1
  },
  "materials": [
    {
      "id": 3,
      "name": "Flexy China 280 GSM"
    }
  ],
  "finishes": [
    {
      "id": 2,
      "name": "Mata Ayam"
    }
  ],
  "rules": {
    "requires_material": true,
    "supports_dimension_cm": true
  }
}
```

#### 4) POST `/api/public/checkout/quotes`
- Tujuan: simulasi harga sebelum order.
- Auth: signed request per tenant.
- Header wajib:
  - `X-Tenant-Key`
  - `X-Timestamp` (epoch detik)
  - `X-Signature` (HMAC SHA-256 dari `timestamp + body`)
- Body minimal:
```json
{
  "items": [
    {
      "product_id": 10,
      "qty": 1,
      "material_id": 3,
      "panjang_cm": 100,
      "lebar_cm": 100,
      "finish_ids": [2]
    }
  ],
  "discount": 0
}
```
- Contoh response `data`:
```json
{
  "subtotal": 25000,
  "discount": 0,
  "grand_total": 25000,
  "lines": [
    {
      "item_index": 0,
      "area_m2": 1,
      "price_per_m2": 25000,
      "line_total": 25000
    }
  ]
}
```

#### 5) POST `/api/public/checkout/orders`
- Tujuan: membuat order dari web (masuk pipeline backoffice).
- Auth: signed request per tenant (header sama seperti endpoint quotes).
- Body minimal:
```json
{
  "customer": {
    "name": "Budi",
    "phone": "08123456789",
    "email": "budi@gmail.com"
  },
  "source": "web",
  "items": [
    {
      "product_id": 10,
      "qty": 1,
      "material_id": 3,
      "panjang_cm": 100,
      "lebar_cm": 100
    }
  ],
  "notes": "Mohon cepat"
}
```
- Contoh response `data`:
```json
{
  "order_id": 123,
  "order_no": "ORD-20260228-0001",
  "status": "quotation",
  "grand_total": 25000,
  "public_token": "ord_pub_xxx",
  "tracking_url": "https://app.client.com/track/order/xxxxx"
}
```

#### 6) GET `/api/public/checkout/orders/{public_token}`
- Tujuan: cek status order dari proses checkout web.
- Auth: token-based (path token), rate-limited.
- Contoh response `data`:
```json
{
  "order_no": "ORD-20260228-0001",
  "status": "produksi",
  "payment_status": "partial",
  "grand_total": 25000,
  "paid_total": 10000,
  "remaining_total": 15000
}
```

#### 7) GET `/api/public/tracking/{token}`
- Tujuan: tracking progres produksi/order publik.
- Auth: token-based (path token), rate-limited.
- Contoh response `data`:
```json
{
  "order_no": "ORD-20260228-0001",
  "status": "produksi",
  "updated_at": "2026-02-28 20:00:00",
  "timeline": [
    {
      "status": "quotation",
      "label": "Quotation",
      "time": "2026-02-28 09:00:00"
    },
    {
      "status": "approval",
      "label": "Disetujui",
      "time": "2026-02-28 10:00:00"
    },
    {
      "status": "produksi",
      "label": "Produksi",
      "time": "2026-02-28 13:00:00"
    }
  ]
}
```

### Auth dan Security
1. Public read endpoint pakai rate limit + IP throttling.
2. Endpoint create order pakai signed API key per tenant (`X-Tenant-Key`, `X-Signature`, timestamp).
3. Semua token order publik harus opaque/encrypted, tidak pernah expose ID integer.
4. Webhook pembayaran (jika ada) masuk ke `sidigit-core`, lalu status disinkronkan kembali ke web.

### Feature Gate Paket
1. `web.company_profile` untuk fitur website profile.
2. `web.ecommerce` untuk katalog + checkout.
3. `web.ecommerce.payment_gateway` untuk integrasi pembayaran online.
4. Gate dicek di menu admin, route API publik, dan service action checkout.

### Alur Sinkronisasi Data
1. Perubahan produk/harga/stok di `sidigit-core` memicu job publish ke endpoint sync `sidigit-web`.
2. `sidigit-web` menyimpan cache katalog terindeks cepat (read optimized).
3. Checkout di `sidigit-web` memanggil API order di `sidigit-core`.
4. Perubahan status order di `sidigit-core` otomatis tercermin pada halaman tracking publik.

### Tahap Implementasi Praktis
1. Tahap 1: company profile public (tanpa checkout), aktifkan `web.company_profile`.
2. Tahap 2: katalog produk + inquiry/quotation request, aktifkan `web.ecommerce`.
3. Tahap 3: checkout penuh + pembayaran online + notifikasi order.
4. Tahap 4: optimasi SEO, analytics, dan campaign landing per tenant.

## Role Owner (Override)
- Role sistem utama diganti dari `Administrator` menjadi `Superadmin`.
- Seeder default membuat akun superadmin:
  - `name`: `Superadmin User`
  - `username`: `superadmin`
  - `email`: `superadmin@gmail.com`
  - `password`: `password`
- Ditambahkan role baru: `Owner` (slug otomatis: `owner`).
- Tujuan role `Owner`: hak akses operasional tertinggi di sisi klien tanpa memberikan akun `superadmin`.
- Seeder default membuat akun:
  - `name`: `owner`
  - `username`: `owner`
  - `email`: `owner@gmail.com`
  - `password`: `password`
- Seeder default juga membuat akun operasional berikut (otomatis terhubung ke data karyawan via `employee_id`):
  - `desainer` (`desainer@gmail.com`) dengan role `Desainer`
  - `operator` (`operator@gmail.com`) dengan role `Operator`
  - password default: `password`
- Permission override workflow yang ditambahkan:
  - `workflow.override.status`
  - `workflow.override.actor`
  - `workflow.override.locked-order`
- `Owner` mendapatkan seluruh menu + permission tenant melalui `RolePermissionSeeder`.
- Penugasan role `Owner` dibatasi:
  - hanya akun dengan role `superadmin` yang dapat menetapkan role `Owner` pada form user.
  - non-superadmin tidak melihat opsi role `Owner` pada form user.
- Untuk order yang statusnya sudah terkunci (`approval` ke atas), user dengan permission `workflow.override.locked-order` dapat tetap melakukan edit penuh order.

## Feature Gate
- Ditambahkan infrastruktur feature gate dasar untuk klasifikasi paket:
  - `config/feature_gate.php`
  - `App\Support\FeatureGate`
  - middleware `App\Http\Middleware\EnsureFeatureEnabled`
  - alias middleware route: `route.feature`
- Cara pakai di route:
  - contoh: `Route::get('/orders', ...)->middleware('route.feature:orders');`
- Cara menambah feature baru:
  1. Tambahkan key pada `config/feature_gate.php` bagian `features`.
  2. (Opsional) Atur override role di `role_overrides`.
  3. Pasang middleware `route.feature:{nama_feature}` pada route yang relevan.
- Bypass default:
  - role slug pada `bypass_role_slugs` (default: `superadmin`) selalu lolos pengecekan feature gate.

## Order
- Tracking order publik menggunakan URL: `/track/order/{id_order_encrypted}`.
- Link tracking bersifat public dan memakai token terenkripsi (`OrderTrackingToken`), bukan ID mentah.
- Akses link tracking dipindahkan ke Daftar Order kolom **Tracking** (aksi `Lihat` dan `Salin Link`) agar header halaman detail order tetap ringkas.
- Aksi **Salin Link** menampilkan toast sukses.
- Untuk environment `http` (non-HTTPS), salin link tetap dicoba otomatis via fallback `execCommand('copy')`; prompt manual hanya muncul jika browser menolak semua metode copy.

## Dashboard
- Dashboard `/dashboard` dirapikan menjadi command center operasional dengan data real-time (bukan dummy card).
- Data dashboard dipindahkan ke `DashboardController` + `DashboardService` agar query/aggregasi tetap modular.
- Filter periode ditambahkan:
  - `Hari Ini`
  - `7 Hari`
  - `Bulan Ini`
  - `Custom` (`from`/`to`)
- KPI utama:
  - order hari ini
  - omzet hari ini
  - piutang aktif
  - job produksi aktif
  - order overdue
  - jumlah bahan menipis
- Section operasional:
  - pipeline order per status
  - tabel **Perlu Tindakan Sekarang**
  - snapshot produksi per kolom
  - daftar job produksi urgent
  - top piutang
  - stok menipis
  - aktivitas terbaru (order status, pembayaran, produksi)
- Quick action di header dashboard ditambahkan untuk akses cepat ke:
  - tambah order
  - daftar order
  - board produksi
  - reservasi stok
  - laporan sales
  - overview akuntansi

## Storage Upload
- Semua upload file operasional (logo cabang, QRIS, foto karyawan) sekarang menggunakan disk terpusat `filesystems.upload_disk`.
- Disk upload diatur melalui env `UPLOAD_DISK` (default mengikuti `FILESYSTEM_DISK`).
- Titik upload yang sudah mengikuti setting ini:
  - `BranchService` (logo + QRIS)
  - `EmployeeService` (foto)
  - preview media di form/list employee, form branch, sidebar logo, invoice, dan invoice-pdf.
- Struktur object key upload sekarang berbasis cabang:
  - `{branch_id}/branches/logos/{uuid}.{ext}`
  - `{branch_id}/branches/qris/{uuid}.{ext}`
  - `{branch_id}/employees/photos/{uuid}.{ext}`
  - (siap dipakai) `{branch_id}/orders/attachments/{order_id}/{uuid}.{ext}`
- Ditambahkan util path terpusat:
  - `App\Support\BranchContext` untuk resolve cabang aktif.
  - `App\Support\UploadPath` untuk generate key object konsisten.
- Ditambahkan helper `App\Support\UploadStorage::disk()`:
  - jika konfigurasi S3 belum lengkap (key/secret/bucket kosong), sistem otomatis fallback ke `public` agar aplikasi tidak error.
  - `UploadStorage::deletionDisks()` untuk cleanup kompatibilitas data lama (disk upload aktif + `public`).
- Ditambahkan validasi kuota upload terpusat via `App\Services\UploadQuotaService`:
  - upload logo cabang, QRIS, dan foto karyawan otomatis ditolak jika melebihi `UPLOAD_QUOTA_BYTES`.
  - kuota dihitung global level klien (akumulasi semua cabang), bukan kuota per cabang.
  - skenario replace file tetap diperhitungkan (ukuran file lama dikompensasi dulu), jadi update file tetap bisa selama total akhir masih dalam batas kuota.
  - pesan error upload menampilkan ringkasan kuota, sisa, dan ukuran file agar user tahu penyebab gagal.
- Command migrasi path lama ke struktur branch:
  - simulasi: `php artisan uploads:migrate-branch-prefix --dry-run`
  - eksekusi: `php artisan uploads:migrate-branch-prefix`
- Untuk S3 compatible (contoh NevaCloud), isi variabel:
  - `UPLOAD_DISK=s3`
  - `AWS_ACCESS_KEY_ID`
  - `AWS_SECRET_ACCESS_KEY`
  - `AWS_BUCKET`
  - `AWS_ENDPOINT`
  - `AWS_DEFAULT_REGION`

## File Manager
- Ditambahkan modul **File Manager** untuk kelola asset upload di object storage.
- URL: `/file-manager` (menu: `Settings -> File Manager`).
- Scope listing dibatasi prefix cabang aktif: `/{branch_id}/...`.
- Fitur inti:
  - list file per cabang + filter folder + search path/nama.
  - preview image, lihat file, download file, salin URL signed, dan hapus file.
  - preview list menggunakan thumbnail terkompresi (`/file-manager/thumbnail`), bukan file asli, untuk menekan bandwidth browser.
  - pagination 20/50/100.
  - optimasi performa: metadata file dihitung hanya untuk item pada halaman aktif (bukan seluruh list file).
  - cache listing/folder/storage details untuk mengurangi beban scan object storage berulang.
  - panel **Storage Details**: used storage, quota/progress bar, dan komposisi tipe file.
  - warna progress quota bertingkat: hijau (`<70%`), kuning (`70-89%`), merah (`>=90%`).
  - perbaikan UI: tombol `Salin URL` dibuat `nowrap` agar tetap satu baris.
  - catatan: thumbnail preview membutuhkan ekstensi PHP GD.
- Quota untuk panel diatur lewat env:
  - `UPLOAD_QUOTA_BYTES` (0 = tanpa batas)
  - progress kuota menggunakan total pemakaian global klien lintas cabang.
  - filter cabang tetap berlaku untuk list file dan ringkasan usage cabang aktif (sementara progress bar kuota tetap global).
  - `FILE_MANAGER_CACHE_TTL_SECONDS` (default `60`)
- Permission baru:
  - `file-manager.view`
  - `file-manager.delete`
- Mapping route permission:
  - `file-manager.index` -> `file-manager.view`

## UI Sidebar
- Sidebar sekarang memakai map icon SVG bergaya TailAdmin dari `config/menu.php`.
- Kompatibilitas icon lama tetap aman: nilai menu `bi bi-*` otomatis dikonversi ke SVG, jadi icon tetap muncul tanpa ubah data menu lama.
- Resolver icon memiliki fallback default sehingga menu tanpa mapping tidak tampil kosong.
- Icon `Profile` dan `Logout` pada blok **Account** diganti ke SVG agar konsisten dengan gaya TailAdmin.
- Versi cache sidebar dinaikkan ke `v3` agar data menu lama tidak mengunci icon kosong.
- Form **Tambah/Edit Menu** sekarang mendukung input icon manual (`icon`) dan pilihan preset icon key.
- Nilai icon dari form diprioritaskan (jika tidak ada mapping) agar custom class/key yang diinput user tetap tersimpan dan dirender.
- Styling sidebar dirapikan agar lebih mirip TailAdmin: ikon tanpa kotak border, state aktif/inaktif lebih clean, dan indent submenu lebih proporsional.
- Styling **submenu** ikut disamakan: panel dropdown diberi spacing yang konsisten, item aktif/nonaktif mengikuti tone TailAdmin, dan area klik item dibuat lebih rapi.

## Akuntansi
- Ditambahkan modul **Akuntansi (inti)**:
  - **Dashboard Akuntansi** (`/accounting/overview`) untuk ringkasan posisi akun utama.
    - Mendukung filter periode `Harian`, `Bulanan`, dan `Custom Range`.
    - Tabel **Jurnal Terbaru** dirapikan (spacing kolom + nowrap nominal/user) agar mudah dibaca.
  - **Arus Kas** (`/accounting/cashflows`) untuk melihat pemasukan dan pengeluaran dalam satu halaman.
    - Menampilkan `Saldo Awal`, `Total Masuk`, `Total Keluar`, `Net Cashflow`, `Saldo Akhir`.
    - Menyediakan tabel mutasi kronologis dengan saldo berjalan.
    - Mendukung filter `Periode`, `Sumber (all/payment/expense)`, dan `Metode (cash/transfer/qris)`.
  - **Chart of Accounts** (`/accounting/accounts`) untuk kelola akun per cabang.
  - **Jurnal Umum** (`/accounting/journals`) untuk input jurnal manual dengan validasi debit = kredit.
- Implementasi menggunakan service and repository pattern:
  - `App\\Services\\AccountingAccountService`
  - `App\\Services\\AccountingJournalService`
  - `App\\Repositories\\AccountingAccountRepository`
  - `App\\Repositories\\AccountingJournalRepository`
- Struktur data baru:
  - `acc_accounts`
  - `acc_journals`
  - `acc_journal_lines`
- Seeder default COA:
  - `AccountingAccountSeeder` membuat akun standar awal pada cabang induk.
- Auto posting transaksi:
  - `Payment` otomatis membentuk jurnal:
    - Sebelum status `selesai`: Debit Kas/Bank (`1001`/`1002`), Kredit Uang Muka Pelanggan (`2003`)
    - Setelah status `selesai`: Debit Kas/Bank (`1001`/`1002`), Kredit Piutang Usaha (`1101`)
    - Jika lebih bayar: selisih ke Hutang Kembalian Pelanggan (`2002`)
  - Saat order berubah ke status `selesai`, sistem membuat jurnal pengakuan pendapatan (accrual):
    - Debit Uang Muka Pelanggan (`2003`) dan/atau Debit Piutang Usaha (`1101`)
    - Kredit Pendapatan Penjualan (`4001`)
    - Debit HPP (`5001`) dan Kredit Persediaan Bahan (`1201`) jika nilai HPP > 0
  - Jika status order diturunkan dari `selesai`, jurnal accrual order akan disinkronkan (dihapus dari sumber `order-accrual`) agar tidak salah saji.
  - `Expense` otomatis membentuk jurnal:
    - Expense material: Debit Persediaan Bahan (`1201`), Kredit Kas/Bank
    - Expense umum: Debit Beban Operasional (`6001`), Kredit Kas/Bank
  - Expense `update/delete` akan sinkron/hapus jurnal sumber terkait (`source_type=expense`).
  - Perbaikan stabilitas nomor jurnal: generator nomor jurnal (`AUTOYYYYMMDD-XXXX` dan `JUYYYYMMDD-XXXX`) kini aman dari error cast `Stringable` saat membaca nomor terakhir pada tanggal yang sama.
- RBAC akuntansi:
  - permission baru: `accounting-overview.view`, `cashflow.view`, `account.*`, `journal.view`, `journal.create`
  - menu baru: **Akuntansi** -> **Dashboard Akuntansi**, **Chart of Accounts**, **Jurnal Umum**

## Laporan
- Ditambahkan **Laporan Produksi** di `/reports/production`:
  - ringkasan job masuk/selesai, WIP, QC pass/fail, rata-rata lead time, on-time vs terlambat.
  - distribusi status board saat ini, workload per role, dan top produk produksi.
- Ditambahkan **Laporan Keuangan** di `/reports/financial`:
  - menggabungkan **Arus Kas**, **Laba Rugi Sederhana**, dan **Neraca Sederhana** dalam satu halaman.
  - filter periode (`harian`, `bulanan`, `custom`) + filter sumber/metode arus kas.
- Permission laporan baru:
  - `report.production.view`
  - `report.finance.view`
- Route permission baru:
  - `reports.production` -> `report.production.view`
  - `reports.financial` -> `report.finance.view`

## Struktur Menu
- Menu **Laporan** sekarang menjadi pintu utama untuk kebutuhan analitik operasional:
  - `Laporan Penjualan`
  - `Laporan Pengeluaran`
  - `Laporan Produksi`
  - `Laporan Keuangan`
  - `Laporan Per Cabang`
- Menu **Akuntansi** difokuskan untuk back-office:
  - `Dashboard Akuntansi`
  - `Arus Kas`
  - `Chart of Accounts`
  - `Jurnal Umum`
## Order
- Ditambahkan modul **Tracking Order Publik** dengan URL: `/track/order/{id_order_encrypted}`.
- Link tracking bersifat **public**: siapa pun yang memiliki URL dapat melihat progres order.
- ID order di URL tidak memakai ID mentah, tetapi token terenkripsi melalui `OrderTrackingToken`.
- Implementasi mengikuti **service and repository pattern**:
  - `App\\Services\\OrderTrackingService`
  - `App\\Repositories\\OrderTrackingRepository`
- Halaman tracking publik menampilkan status saat ini dan riwayat pengerjaan dari `order_status_logs`.
- Akses tracking dipindahkan ke **Daftar Order** pada kolom **Tracking** (aksi `Lihat` dan `Salin Link`) agar header halaman detail order tetap ringkas.
- Aksi **Salin Link** menampilkan **toast sukses** (bukan modal) agar feedback cepat dan tidak mengganggu alur kerja.
- Untuk environment `http` (non-HTTPS), salin link tetap dicoba otomatis via fallback `execCommand('copy')`; prompt manual hanya muncul jika browser menolak semua metode copy.
- Status `dibatalkan` sekarang diperlakukan sebagai status **locked/read-only** seperti `approval` ke atas.
- Pada status locked (termasuk `dibatalkan`), update dari halaman Edit Order hanya mengizinkan perubahan status via aksi daftar order; field lain tidak diproses.
- Label tombol aksi bawah pada halaman edit order diubah dari `Batal` menjadi `Kembali` agar konsisten dengan navigasi halaman lain.
- Opsi status `dibatalkan` tetap tersedia di UI perubahan status untuk kasus order batal (mis. Draft/Quotation tidak jadi lanjut).
- Key status order dinormalisasi dari `menunggu-dp` menjadi `pembayaran` (UI + value).
- Status `finishing` dihapus dari list flow order (diasumsikan masuk ke fase `produksi`).
- Ditambahkan migrasi normalisasi data existing: `finishing -> produksi` pada tabel `orders` dan `order_status_logs`.
- Ditambahkan migrasi normalisasi data existing: `menunggu-dp -> pembayaran` pada tabel `orders` dan `order_status_logs`.

## Testing
- Ditambahkan regression test untuk flow lock status `dibatalkan`:
  - `tests/Feature/Orders/CancelledOrderLockingTest.php`
  - mencakup:
    - halaman edit tampil read-only untuk order `dibatalkan`
    - update non-status diabaikan untuk user tanpa override `workflow.override.locked-order`
    - opsi `dibatalkan` tampil di modal ubah status (daftar order)
- `AuthServiceProvider` menggunakan pendekatan `Gate::before` berbasis ability slug sehingga bootstrap tidak lagi membutuhkan preload seluruh tabel permission.

## Optimisasi Query Auth & Sidebar
- Optimisasi akses permission dilakukan di `AuthServiceProvider` menggunakan `Gate::before` berbasis slug ability (`resource.action`) tanpa me-load seluruh tabel `permissions` setiap request.
- Pengecekan role superadmin/admin sekarang memanfaatkan cache in-request pada model `User` (`hasRoleSlug`) sehingga query `exists` berulang di setiap `@can`/middleware berkurang drastis.
- Ditambahkan helper cache in-request di model `User`:
  - `roleSlugs()`
  - `permissionSlugs()`
  - `menuIds()`
  - `hasPermissionSlug()`
- Helper akses user sekarang berbasis satu snapshot role (`accessRoles()`) sehingga query `roles` tidak lagi terduplikasi saat membaca menu + permission dalam request yang sama.
- `MenuCacheService` diperbarui agar memakai snapshot akses dari model `User` (menu + permission) dan tidak lagi menjalankan query relasi role berulang untuk membentuk cache key sidebar.

# Next Project
- Web COmpany Profile
- Web COmpany Profile - Ecommerce
- WhatsApp Blast
- WhatsApp Bot

# Nevacloud Object Storage
- Access Key : 
``` 
FXKGU1KENOES0RGWIFPQ 
```
- Secret Key : 
```
mGBYXExsmAW2aQY1fQ83YjDibovcjUXkHSG6WvVV
```
