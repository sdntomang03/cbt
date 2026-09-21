<table>
    <thead>
        <tr>
            <th colspan="11" style="font-weight: bold; text-align: center; font-size: 14pt;">Analisis Butir Soal: {{
                $session->exam->title }}</th>
        </tr>
        <tr>
            <th colspan="11" style="font-align: center;">Sesi: {{ $session->session_name }}</th>
        </tr>
        <tr>
            <th>No</th>
            <th>Tipe Soal</th>
            <th>Konten Soal (Preview)</th>
            <th>Total Siswa Menjawab</th>
            <th>Rata-rata Skor (%)</th>
            <th>Tingkat Kesukaran (P)</th>
            <th>Kategori TK</th>
            <th>Daya Pembeda (D)</th>
            <th>Kategori DB</th>
            <th>Validitas r</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($analysisData as $data)
        <tr>
            <td>{{ $data['nomor'] }}</td>
            <td>{{ $data['tipe'] }}</td>
            <td>{{ Str::limit($data['soal'], 100) }}</td>
            <td>{{ $data['total_menjawab'] }}</td>
            <td>{{ $data['rata_rata'] }}</td>
            <td>{{ $data['tingkat_kesukaran'] }}</td>
            <td>{{ $data['kategori'] }}</td>
            <td>{{ $data['daya_pembeda'] }}</td>
            <td>{{ $data['kategori_daya_beda'] }}</td>
            <td>{{ $data['validitas'] }}</td>
            <td>{{ $data['status'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>