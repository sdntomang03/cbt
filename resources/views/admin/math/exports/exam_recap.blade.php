<table>
    <tr>
        <td colspan="5" style="font-weight: bold; text-align: center; font-size: 14px;">REKAPITULASI NILAI UJIAN
            MATEMATIKA</td>
    </tr>
    <tr>
        <td colspan="5" style="text-align: center;">{{ $exam->title }}</td>
    </tr>
    <tr>
        <td colspan="5"></td>
    </tr>
    <tr>
        <th style="font-weight: bold; text-align: center; border: 1px solid #000;">No</th>
        <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Nama Siswa</th>
        <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Sekolah</th>
        <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Status</th>
        <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Nilai Akhir</th>
    </tr>

    @foreach($exam->examUsers->sortByDesc('score') as $index => $user)
    <tr>
        <td style="text-align: center; border: 1px solid #000;">{{ $index + 1 }}</td>
        <td style="border: 1px solid #000;">{{ $user->student->name ?? 'Siswa Terhapus' }}</td>
        <td style="border: 1px solid #000;">{{ $user->student->school->name ?? 'Pusat' }}</td>
        <td style="text-align: center; border: 1px solid #000;">
            @if($user->status === 'completed') Selesai
            @elseif($user->status === 'ongoing') Sedang Ujian
            @else Belum Mulai @endif
        </td>
        <td style="text-align: center; font-weight: bold; border: 1px solid #000;">
            {{ $user->status === 'completed' ? $user->score : '-' }}
        </td>
    </tr>
    @endforeach
</table>