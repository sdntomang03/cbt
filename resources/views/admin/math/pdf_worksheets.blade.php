<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Soal & Kunci - {{ $exam->title }}</title>

    @php
    $totalSoal = $exam->total_questions;

    if ($totalSoal <= 10) { $cols=4; $perPage=8; } elseif ($totalSoal <=15) { $cols=4; $perPage=4; } elseif ($totalSoal
        <=25) { $cols=3; $perPage=3; } else { $cols=2; $perPage=2; } $widthPercent=100 / $cols; $pageChunks=$examUsers->
        chunk($perPage);
        @endphp

        <style>
            @page {
                margin: 0.5cm;
            }

            body {
                font-family: 'Helvetica', 'Arial', sans-serif;
                color: #111;
                font-size: 11px;
                margin: 0;
                padding: 0;
            }

            table.page-layout {
                width: 100%;
                table-layout: fixed;
                border-collapse: collapse;
            }

            td.col-layout {
                vertical-align: top;
                padding: 3px;
            }

            .page-break {
                page-break-after: always;
            }

            .student-box {
                border: 1px dashed #666;
                border-radius: 6px;
                padding: 6px 10px;
            }

            .box-kunci {
                border: 2px solid #d946ef;
                background-color: #fdf4ff;
            }

            .header {
                text-align: center;
                border-bottom: 2px solid #000;
                padding-bottom: 3px;
                margin-bottom: 5px;
            }

            .header h3 {
                margin: 0;
                font-size: 11px;
                text-transform: uppercase;
            }

            .header p {
                margin: 1px 0 0 0;
                font-size: 8px;
                color: #555;
            }

            .info-row {
                margin-bottom: 5px;
                font-weight: bold;
                font-size: 10px;
            }

            .line {
                border-bottom: 1px dotted #000;
                display: inline-block;
                width: 65%;
            }

            /* ========================================================= */
            /* PERBAIKAN TABEL SOAL (Jauh Lebih Rapat & Natural)         */
            /* ========================================================= */
            .q-table {
                width: 100%;
                border-collapse: collapse;
            }

            .q-table td {
                padding: 3px 0;
                vertical-align: bottom;
            }

            .q-num {
                width: 12%;
                font-weight: bold;
                color: #555;
                font-size: 11px;
                text-align: left;
            }

            .q-math {
                width: 45%;
                font-size: 13px;
                text-align: left;
                white-space: nowrap;
                font-weight: bold;
            }

            .q-ans-empty {
                width: 43%;
                border-bottom: 1px dashed #999;
            }

            .q-ans-filled {
                width: 43%;
                text-align: center;
                font-weight: bold;
                color: #d946ef;
                font-size: 14px;
                border-bottom: 1px dashed #d946ef;
            }

            .footer-cut {
                text-align: center;
                margin-top: 6px;
                font-size: 7px;
                color: #999;
            }
        </style>
</head>

<body>

    {{-- BAGIAN 1: LEMBAR KERJA SISWA --}}
    @foreach($pageChunks as $pageChunk)
    @php
    $rowChunks = $pageChunk->chunk($cols);
    @endphp

    <table class="page-layout">
        @foreach($rowChunks as $rowChunk)
        <tr>
            @foreach($rowChunk as $eu)
            <td class="col-layout" style="width: {{ $widthPercent }}%;">
                <div class="student-box">
                    <div class="header">
                        <h3>LEMBAR TUGAS</h3>
                        <p>{{ strtoupper($exam->title) }}</p>
                    </div>

                    <div class="info-row">
                        Nama: <span class="line">{{ strtoupper($eu->student->name) }}</span>
                    </div>

                    <table class="q-table">
                        @foreach($eu->questions as $index => $q)
                        <tr>
                            <td class="q-num">{{ $index + 1 }}.</td>
                            {{-- SOAL DIGABUNG AGAR SPASI NATURAL --}}
                            <td class="q-math">
                                {{ $q->num1 }}
                                @if($q->operator == '*') x @elseif($q->operator == '/') : @else {{ $q->operator }}
                                @endif
                                {{ $q->num2 }}
                            </td>
                            <td class="q-ans-empty">=<span style="color:transparent;">_</span></td>
                        </tr>
                        @endforeach
                    </table>
                    <div class="footer-cut">✂ Gunting di sini</div>
                </div>
            </td>
            @endforeach

            @for($i = $rowChunk->count(); $i < $cols; $i++) <td class="col-layout" style="width: {{ $widthPercent }}%;">
                </td>
                @endfor
        </tr>
        @endforeach
    </table>
    <div class="page-break"></div>
    @endforeach

    {{-- BAGIAN 2: KUNCI JAWABAN GURU --}}
    @foreach($pageChunks as $pageChunk)
    @php
    $rowChunks = $pageChunk->chunk($cols);
    @endphp

    <table class="page-layout">
        @foreach($rowChunks as $rowChunk)
        <tr>
            @foreach($rowChunk as $eu)
            <td class="col-layout" style="width: {{ $widthPercent }}%;">
                <div class="student-box box-kunci">
                    <div class="header" style="border-bottom-color: #d946ef;">
                        <h3 style="color: #d946ef;">KUNCI JAWABAN</h3>
                        <p>{{ strtoupper($exam->title) }}</p>
                    </div>

                    <div class="info-row">
                        Siswa: <span class="line" style="color: #d946ef;">{{ strtoupper($eu->student->name) }}</span>
                    </div>

                    <table class="q-table">
                        @foreach($eu->questions as $index => $q)
                        <tr>
                            <td class="q-num">{{ $index + 1 }}.</td>
                            {{-- SOAL DIGABUNG --}}
                            <td class="q-math">
                                {{ $q->num1 }}
                                @if($q->operator == '*') x @elseif($q->operator == '/') : @else {{ $q->operator }}
                                @endif
                                {{ $q->num2 }} =
                            </td>
                            <td class="q-ans-filled">{{ $q->correct_answer }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </td>
            @endforeach

            @for($i = $rowChunk->count(); $i < $cols; $i++) <td class="col-layout" style="width: {{ $widthPercent }}%;">
                </td>
                @endfor
        </tr>
        @endforeach
    </table>

    @if(!$loop->last)
    <div class="page-break"></div>
    @endif
    @endforeach

</body>

</html>