# Load test API siswa dengan k6

Skrip [student-api.js](../tests/k6/student-api.js) menguji tiga endpoint baca-saja API siswa v1:

- `GET /api/v1/student/profile`
- `GET /api/v1/student/dashboard`
- `GET /api/v1/student/exams`

Skrip menjalankan satu virtual user selama sekitar 30 detik dan memberi jeda 2 detik antar-request. Setiap iterasi mengirim satu request ke masing-masing endpoint. Skrip memeriksa HTTP `200`, wrapper response `success: true`, dan bentuk data yang diharapkan. Skrip tidak memanggil endpoint login, ujian, jawaban, submit, atau transaksi. Ini uji awal berbeban rendah, bukan tes kapasitas.

## Persiapan

1. Jalankan aplikasi lokal (`php artisan serve`) atau siapkan environment staging.
2. Siapkan token Sanctum dari akun siswa **khusus pengujian** yang emailnya sudah terverifikasi. Token harus tetap aktif selama tes.
3. Instal k6 di Windows, misalnya dengan `winget install k6 --source winget`, lalu buka terminal baru dan pastikan `k6 version` berhasil.

Jangan gunakan akun pengguna nyata untuk load test. API menerapkan satu sesi login aktif per akun; login ulang dapat membatalkan token yang sedang digunakan. Skrip ini sengaja menggunakan token yang disediakan, tidak melakukan login otomatis.

## Jalankan dari PowerShell

Dari root proyek:

```powershell
$env:API_BASE_URL = "https://cbt.sdntomang03.sch.id/api/v1/student"
$secureToken = Read-Host "Tempel token Sanctum akun pengujian" -AsSecureString
$env:K6_TOKEN = (New-Object System.Net.NetworkCredential("", $secureToken)).Password
Remove-Variable secureToken
k6 run .\tests\k6\student-api.js
```

Untuk pengujian lokal Laragon, ubah base URL sebelum menjalankan:

```powershell
$env:API_BASE_URL = "http://cbt.test/api/v1/student"
k6 run .\tests\k6\student-api.js
```

Untuk Laravel `artisan serve`, gunakan `http://127.0.0.1:8000/api/v1/student`. Token hanya disimpan pada environment terminal saat ini. Jangan menaruh token sungguhan di file skrip atau commit ke repository.

## Beban dan ambang batas

Konfigurasi uji awal hanya memakai 1 virtual user selama sekitar 30 detik dengan jeda antar-request. Ambang bawaan:

- Request gagal kurang dari 1%.
- Persentil ke-95 waktu respons di bawah 1 detik.

Mulai dari tes ini dan pantau CPU, memori, database, serta log Laravel/hosting. Karena sebelumnya deployment mengembalikan HTTP 500 dengan kegagalan koneksi MySQL, jangan menaikkan VU sebelum 1 VU ini berjalan tanpa error dan penyebab koneksi DB ditangani. Jangan jalankan beban lebih tinggi pada production tanpa persetujuan hosting dan batas beban yang disepakati.

Jika token tidak diatur, skrip berhenti dengan pesan error. Respons `401`, `403`, format JSON yang tidak valid, atau bentuk data yang tidak sesuai akan membuat pemeriksaan gagal dan terlihat di ringkasan hasil k6.
