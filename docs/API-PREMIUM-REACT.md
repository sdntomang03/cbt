# API Premium React (Capacitor Android)

Untuk pembelian Android, gunakan RevenueCat SDK di aplikasi React/Capacitor dan
Google Play Billing. React tidak mengirim harga atau status premium yang
dipercayai server. Laravel memverifikasi status langsung ke RevenueCat.

Semua endpoint v1 memerlukan token Sanctum dan email terverifikasi:

```http
Authorization: Bearer {sanctum_token}
Accept: application/json
Content-Type: application/json
```

Response v1 menggunakan wrapper `success`, `message`, dan `data`.

## Konfigurasi server

Atur variabel berikut pada `.env` backend. Secret API key dan authorization
webhook hanya disimpan di server/RevenueCat dashboard, jangan pernah dimasukkan
ke aplikasi React:

```dotenv
REVENUECAT_SECRET_API_KEY=
REVENUECAT_WEBHOOK_AUTHORIZATION=
REVENUECAT_PREMIUM_ENTITLEMENT=premium
REVENUECAT_APP_ID=
REVENUECAT_PRODUCT_MONTHLY=
REVENUECAT_PRODUCT_SIX_MONTHS=
REVENUECAT_PRODUCT_LIFETIME=
```

Di RevenueCat, buat entitlement dengan ID yang sama dengan
`REVENUECAT_PREMIUM_ENTITLEMENT` dan kaitkan produk Google Play dengannya.
Product ID di `.env` harus sama persis dengan product ID/store product yang
dipakai oleh RevenueCat. Atur webhook RevenueCat ke:

```text
https://{domain-api}/api/webhook/revenuecat
```

Set header `Authorization` webhook sama persis dengan
`REVENUECAT_WEBHOOK_AUTHORIZATION`. Backend menerima event, lalu mengambil
subscriber terbaru dari RevenueCat; webhook bukan satu-satunya cara status
diperbarui, karena aplikasi juga dapat meminta sinkronisasi setelah purchase
atau restore.

## Endpoint

### Katalog produk

```http
GET /api/v1/student/premium/plans
```

`data.plans` berisi `code`, `name`, `product_id`, dan `available`. Harga,
currency, offering, serta package untuk purchase Android diambil dari RevenueCat
SDK/Google Play, bukan endpoint ini.

### Status premium backend

```http
GET /api/v1/student/premium/status
```

Contoh `data`:

```json
{
  "is_premium": true,
  "status": "active",
  "premium_until": "2026-10-27T02:00:00.000000Z",
  "is_permanent": false,
  "remaining_days": 30,
  "provider": "revenuecat",
  "product_id": "premium_monthly",
  "store": "PLAY_STORE",
  "environment": "PRODUCTION",
  "total_poin": 0
}
```

`status` dapat bernilai `active`, `expired`, atau `inactive`. Gunakan
`is_premium` dari backend sebagai keputusan akses fitur; jika `is_permanent`
bernilai `true`, `premium_until` dapat bernilai `null`.

### Sinkronisasi dari RevenueCat

```http
POST /api/v1/student/premium/revenuecat/sync
```

Request body kosong. Backend memakai ID user yang sedang terautentikasi sebagai
RevenueCat `app_user_id` dan meminta subscriber melalui Secret API. Client tidak
dapat memilih ID user lain atau mengirim klaim status.

## Alur React/Capacitor

1. Login user melalui API Laravel.
2. Setelah login, identifikasi RevenueCat dengan ID user Laravel yang stabil
   (contoh konseptual: `Purchases.logIn(String(user.id))`). Jangan gunakan
   anonymous ID untuk purchase user login.
3. Ambil offerings dari RevenueCat SDK dan tampilkan harga yang disediakan
   Google Play/RevenueCat.
4. Jalankan purchase package melalui SDK. Tangani pembatalan dan kegagalan
   purchase dari hasil SDK; jangan mengaktifkan fitur premium hanya dari hasil
   purchase client.
5. Setelah purchase berhasil, panggil
   `POST /api/v1/student/premium/revenuecat/sync`, lalu ambil
   `GET /api/v1/student/premium/status`.
6. Saat aplikasi dibuka kembali, refresh status backend. Jika sinkronisasi
   sementara gagal, pertahankan tampilan status terakhir dan tawarkan retry;
   jangan menganggap kegagalan verifikasi sebagai purchase baru.
7. Untuk pemulihan pembelian, panggil restore purchases dari RevenueCat SDK,
   kemudian lakukan sync dan baca ulang status backend.
8. Pembatalan renewal dikelola melalui Google Play/RevenueCat SDK atau halaman
   pengelolaan langganan Google Play. Pembatalan tidak langsung menghapus akses
   yang masih berlaku; perubahan expiry akan disinkronkan oleh webhook atau
   endpoint sync.

## Endpoint transaksi lama

`GET /api/v1/student/premium/transactions` dan endpoint pembatalan transaksi
pending tetap menampilkan/mengelola transaksi Midtrans lama. Transaksi RevenueCat
tidak dibuat sebagai transaksi Midtrans. Integrasi React Android baru tidak
memanggil checkout Snap. Endpoint legacy `/api/subscription/*` dan webhook
Midtrans tetap tersedia untuk kompatibilitas pembayaran lama.

## Error yang perlu ditangani

- `401`: token tidak ada/tidak valid; minta login.
- `403`: email belum diverifikasi atau event webhook berasal dari aplikasi lain.
- `404`: akun RevenueCat tidak dapat dipetakan ke user lokal pada webhook.
- `422`: data request tidak valid.
- `502`: backend gagal memverifikasi subscriber ke RevenueCat; sediakan retry.
- `503` pada webhook: sinkronisasi provider gagal, sehingga RevenueCat dapat
  mencoba mengirim ulang event.
