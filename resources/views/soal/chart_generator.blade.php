<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div
                class="w-11 h-11 rounded-xl bg-indigo-600 flex items-center justify-center shadow-md shadow-indigo-200">
                <i class="fas fa-chart-pie text-white text-base"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Tools</p>
                <h2 class="font-black text-lg text-slate-800 tracking-tight leading-tight">
                    Chart Generator Pro
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-10 min-h-screen bg-slate-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex flex-col gap-8">

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                    <h3 class="font-bold text-slate-700 mb-6"><i
                            class="fas fa-sliders-h mr-2 text-slate-400"></i>Konfigurasi Data & Grafik</h3>

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Judul
                                    Grafik</label>
                                <input type="text" id="chartTitle" value="Statistik Nilai Ujian"
                                    class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Tema
                                    Warna</label>
                                <select id="chartTheme"
                                    class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer">
                                    <option value="default">Vibrant Mix (Default)</option>
                                    <option value="corporate">Corporate (Elegan & Resmi)</option>
                                    <option value="ocean">Ocean (Biru Laut)</option>
                                    <option value="sunset">Sunset (Senja Hangat)</option>
                                    <option value="earth">Earth (Hijau Alam)</option>
                                    <option value="pastel">Soft Pastel (Lembut)</option>
                                    <option value="retro">Retro / Vintage</option>
                                    <option value="candy">Candy (Warna Ceria)</option>
                                    <option value="monokrom">Grayscale (Abu-abu)</option>
                                    <option value="neon">Neon Flash</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                            <div class="md:col-span-3 grid grid-cols-1 gap-4">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jenis
                                        Grafik</label>
                                    <select id="chartType"
                                        class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer">
                                        <option value="bar">Bar (Batang)</option>
                                        <option value="line">Line (Garis)</option>
                                        <option value="pie">Pie (Kue)</option>
                                        <option value="doughnut">Doughnut (Donat)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"
                                        title="Hanya berlaku untuk tipe Pie/Doughnut">Format Label (Pie/Donat)</label>
                                    <select id="labelFormat"
                                        class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer">
                                        <option value="value">Nilai Asli</option>
                                        <option value="percent">Persentase (%)</option>
                                        <option value="degree">Derajat (°)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="md:col-span-4 grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"
                                        title="Lebar Gambar">Lebar (px)</label>
                                    <input type="number" id="chartWidth" value="800"
                                        class="w-full bg-indigo-50/50 border-indigo-200 text-indigo-700 font-bold rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500 text-center">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"
                                        title="Tinggi Gambar">Tinggi (px)</label>
                                    <input type="number" id="chartHeight" value="400"
                                        class="w-full bg-indigo-50/50 border-indigo-200 text-indigo-700 font-bold rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500 text-center">
                                </div>
                            </div>

                            <div class="md:col-span-5 grid grid-cols-3 gap-2">
                                <div>
                                    <label
                                        class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2"
                                        title="Batas Bawah Y">Min Skala</label>
                                    <input type="number" id="scaleMin" value="0" placeholder="Auto"
                                        class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500 text-center">
                                </div>
                                <div>
                                    <label
                                        class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2"
                                        title="Batas Atas Y">Max Skala</label>
                                    <input type="number" id="scaleMax" value="100" placeholder="Auto"
                                        class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500 text-center">
                                </div>
                                <div>
                                    <label
                                        class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2"
                                        title="Jarak Kelipatan Y">Kelipatan</label>
                                    <input type="number" id="scaleStep" value="20" placeholder="Auto"
                                        class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500 text-center">
                                </div>
                            </div>

                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Tabel
                                Data Kategori</label>
                            <div class="overflow-y-auto max-h-[300px] border border-slate-200 rounded-xl">
                                <table class="w-full text-left border-collapse" id="dataTable">
                                    <thead class="bg-slate-100 sticky top-0 z-10 shadow-sm">
                                        <tr>
                                            <th class="px-4 py-3 text-xs font-bold text-slate-600 border-b border-slate-200 w-16 text-center"
                                                title="Tampilkan atau sembunyikan angka di grafik">Angka</th>
                                            <th
                                                class="px-4 py-3 text-xs font-bold text-slate-600 border-b border-slate-200">
                                                Label Kategori (Sumbu X)</th>
                                            <th
                                                class="px-4 py-3 text-xs font-bold text-slate-600 border-b border-slate-200 w-32">
                                                Nilai (Y)</th>
                                            <th
                                                class="px-4 py-3 text-xs font-bold text-slate-600 border-b border-slate-200 w-16 text-center">
                                                <i class="fas fa-cog"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody id="dataBody" class="divide-y divide-slate-100">
                                        <tr class="data-row hover:bg-slate-50 transition-colors">
                                            <td class="p-3 text-center"><input type="checkbox" checked
                                                    class="input-show w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer">
                                            </td>
                                            <td class="p-3"><input type="text" value="Matematika"
                                                    class="input-label w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500">
                                            </td>
                                            <td class="p-3"><input type="number" value="85"
                                                    class="input-value w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500 text-center">
                                            </td>
                                            <td class="p-3 text-center"><button type="button"
                                                    class="btn-remove text-rose-400 hover:text-rose-600 transition-colors"><i
                                                        class="fas fa-times-circle text-lg"></i></button></td>
                                        </tr>
                                        <tr class="data-row hover:bg-slate-50 transition-colors">
                                            <td class="p-3 text-center"><input type="checkbox" checked
                                                    class="input-show w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer">
                                            </td>
                                            <td class="p-3"><input type="text" value="B. Indonesia"
                                                    class="input-label w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500">
                                            </td>
                                            <td class="p-3"><input type="number" value="92"
                                                    class="input-value w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500 text-center">
                                            </td>
                                            <td class="p-3 text-center"><button type="button"
                                                    class="btn-remove text-rose-400 hover:text-rose-600 transition-colors"><i
                                                        class="fas fa-times-circle text-lg"></i></button></td>
                                        </tr>
                                        <tr class="data-row hover:bg-slate-50 transition-colors">
                                            <td class="p-3 text-center"><input type="checkbox" checked
                                                    class="input-show w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer">
                                            </td>
                                            <td class="p-3"><input type="text" value="IPA"
                                                    class="input-label w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500">
                                            </td>
                                            <td class="p-3"><input type="number" value="78"
                                                    class="input-value w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500 text-center">
                                            </td>
                                            <td class="p-3 text-center"><button type="button"
                                                    class="btn-remove text-rose-400 hover:text-rose-600 transition-colors"><i
                                                        class="fas fa-times-circle text-lg"></i></button></td>
                                        </tr>
                                        <tr class="data-row hover:bg-slate-50 transition-colors">
                                            <td class="p-3 text-center"><input type="checkbox" checked
                                                    class="input-show w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer">
                                            </td>
                                            <td class="p-3"><input type="text" value="IPS"
                                                    class="input-label w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500">
                                            </td>
                                            <td class="p-3"><input type="number" value="88"
                                                    class="input-value w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500 text-center">
                                            </td>
                                            <td class="p-3 text-center"><button type="button"
                                                    class="btn-remove text-rose-400 hover:text-rose-600 transition-colors"><i
                                                        class="fas fa-times-circle text-lg"></i></button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4 pt-2">
                            <button type="button" id="btnAddRow"
                                class="w-full sm:w-1/3 bg-slate-100 text-slate-600 font-bold py-3 px-4 rounded-xl hover:bg-slate-200 transition-all border border-slate-200 text-sm">
                                <i class="fas fa-plus mr-1"></i> Tambah Baris
                            </button>
                            <button id="btnGenerate"
                                class="w-full sm:w-2/3 bg-indigo-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-indigo-700 transition-all shadow-md shadow-indigo-200">
                                <i class="fas fa-sync-alt mr-2"></i> Terapkan & Update Grafik
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                    <div class="flex flex-wrap justify-between items-center mb-6 gap-4 border-b border-slate-100 pb-4">
                        <h3 class="font-bold text-slate-700"><i class="fas fa-eye mr-2 text-slate-400"></i>Live Preview
                            Grafik</h3>

                        <div class="flex gap-3">
                            <button id="btnCopy"
                                class="bg-emerald-50 text-emerald-600 border border-emerald-200 hover:bg-emerald-500 hover:text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm">
                                <i class="fas fa-copy mr-1"></i> Copy ke Word
                            </button>
                            <button id="btnDownload"
                                class="bg-cyan-50 text-cyan-600 border border-cyan-200 hover:bg-cyan-500 hover:text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm">
                                <i class="fas fa-download mr-1"></i> Download PNG
                            </button>
                        </div>
                    </div>

                    <div
                        class="w-full flex-1 min-h-[450px] lg:min-h-[550px] flex items-center justify-center p-4 border border-dashed border-slate-300 rounded-xl bg-slate-50 overflow-auto">

                        <div id="chartWrapper" style="width: 800px; height: 400px;"
                            class="bg-white relative rounded-xl shadow-sm border border-slate-100 p-2 shrink-0 transition-all duration-300">
                            <canvas id="myChart"></canvas>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        Chart.register(ChartDataLabels);

        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('myChart');
            const chartWrapper = document.getElementById('chartWrapper');
            const btnGenerate = document.getElementById('btnGenerate');
            const btnAddRow = document.getElementById('btnAddRow');
            const dataBody = document.getElementById('dataBody');

            const btnCopy = document.getElementById('btnCopy');
            const btnDownload = document.getElementById('btnDownload');

            let myChartInstance = null;

            const customCanvasBackgroundColor = {
                id: 'customCanvasBackgroundColor',
                beforeDraw: (chart, args, options) => {
                    const {ctx} = chart;
                    ctx.save();
                    ctx.globalCompositeOperation = 'destination-over';
                    ctx.fillStyle = options.color || '#ffffff';
                    ctx.fillRect(0, 0, chart.width, chart.height);
                    ctx.restore();
                }
            };

            const palettes = {
                default: {
                    bg: ['rgba(99, 102, 241, 0.7)', 'rgba(16, 185, 129, 0.7)', 'rgba(245, 158, 11, 0.7)', 'rgba(239, 68, 68, 0.7)', 'rgba(6, 182, 212, 0.7)', 'rgba(139, 92, 246, 0.7)', 'rgba(236, 72, 153, 0.7)'],
                    border: ['rgb(79, 70, 229)', 'rgb(5, 150, 105)', 'rgb(217, 119, 6)', 'rgb(220, 38, 38)', 'rgb(8, 145, 178)', 'rgb(124, 58, 237)', 'rgb(219, 39, 119)']
                },
                pastel: {
                    bg: ['rgba(255, 182, 193, 0.8)', 'rgba(174, 198, 207, 0.8)', 'rgba(119, 221, 119, 0.8)', 'rgba(253, 253, 150, 0.8)', 'rgba(203, 153, 201, 0.8)', 'rgba(255, 179, 71, 0.8)'],
                    border: ['rgb(255, 105, 180)', 'rgb(100, 149, 237)', 'rgb(60, 179, 113)', 'rgb(255, 215, 0)', 'rgb(186, 85, 211)', 'rgb(255, 140, 0)']
                },
                monokrom: {
                    bg: ['rgba(50, 50, 50, 0.7)', 'rgba(100, 100, 100, 0.7)', 'rgba(140, 140, 140, 0.7)', 'rgba(180, 180, 180, 0.7)', 'rgba(210, 210, 210, 0.7)', 'rgba(15, 23, 42, 0.7)'],
                    border: ['rgb(0, 0, 0)', 'rgb(50, 50, 50)', 'rgb(90, 90, 90)', 'rgb(130, 130, 130)', 'rgb(160, 160, 160)', 'rgb(15, 23, 42)']
                },
                neon: {
                    bg: ['rgba(57, 255, 20, 0.8)', 'rgba(255, 20, 147, 0.8)', 'rgba(0, 255, 255, 0.8)', 'rgba(255, 255, 0, 0.8)', 'rgba(255, 69, 0, 0.8)', 'rgba(138, 43, 226, 0.8)'],
                    border: ['rgb(0, 200, 0)', 'rgb(200, 0, 100)', 'rgb(0, 200, 200)', 'rgb(200, 200, 0)', 'rgb(200, 30, 0)', 'rgb(138, 43, 226)']
                },
                ocean: {
                    bg: ['rgba(2, 132, 199, 0.7)', 'rgba(14, 165, 233, 0.7)', 'rgba(56, 189, 248, 0.7)', 'rgba(20, 184, 166, 0.7)', 'rgba(45, 212, 191, 0.7)', 'rgba(59, 130, 246, 0.7)'],
                    border: ['rgb(3, 105, 161)', 'rgb(2, 132, 199)', 'rgb(14, 165, 233)', 'rgb(13, 148, 136)', 'rgb(20, 184, 166)', 'rgb(37, 99, 235)']
                },
                sunset: {
                    bg: ['rgba(225, 29, 72, 0.7)', 'rgba(244, 63, 94, 0.7)', 'rgba(249, 115, 22, 0.7)', 'rgba(251, 146, 60, 0.7)', 'rgba(250, 204, 21, 0.7)', 'rgba(217, 70, 239, 0.7)'],
                    border: ['rgb(190, 18, 60)', 'rgb(225, 29, 72)', 'rgb(194, 65, 12)', 'rgb(234, 88, 12)', 'rgb(202, 138, 4)', 'rgb(192, 38, 211)']
                },
                earth: {
                    bg: ['rgba(21, 128, 61, 0.7)', 'rgba(34, 197, 94, 0.7)', 'rgba(101, 163, 13, 0.7)', 'rgba(202, 138, 4, 0.7)', 'rgba(180, 83, 9, 0.7)', 'rgba(63, 98, 18, 0.7)'],
                    border: ['rgb(22, 101, 52)', 'rgb(21, 128, 61)', 'rgb(77, 124, 15)', 'rgb(161, 98, 7)', 'rgb(146, 64, 14)', 'rgb(54, 83, 20)']
                },
                corporate: {
                    bg: ['rgba(30, 58, 138, 0.7)', 'rgba(71, 85, 105, 0.7)', 'rgba(51, 65, 85, 0.7)', 'rgba(100, 116, 139, 0.7)', 'rgba(15, 23, 42, 0.7)', 'rgba(148, 163, 184, 0.7)'],
                    border: ['rgb(30, 64, 175)', 'rgb(51, 65, 85)', 'rgb(30, 41, 59)', 'rgb(71, 85, 105)', 'rgb(2, 6, 23)', 'rgb(100, 116, 139)']
                },
                retro: {
                    bg: ['rgba(203, 106, 73, 0.7)', 'rgba(226, 179, 87, 0.7)', 'rgba(113, 142, 93, 0.7)', 'rgba(172, 110, 121, 0.7)', 'rgba(90, 107, 124, 0.7)', 'rgba(214, 140, 69, 0.7)'],
                    border: ['rgb(183, 86, 53)', 'rgb(206, 159, 67)', 'rgb(93, 122, 73)', 'rgb(152, 90, 101)', 'rgb(70, 87, 104)', 'rgb(194, 120, 49)']
                },
                candy: {
                    bg: ['rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)', 'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)', 'rgba(153, 102, 255, 0.7)', 'rgba(255, 159, 64, 0.7)'],
                    border: ['rgb(255, 99, 132)', 'rgb(54, 162, 235)', 'rgb(255, 206, 86)', 'rgb(75, 192, 192)', 'rgb(153, 102, 255)', 'rgb(255, 159, 64)']
                }
            };

            function getChartData() {
                const title = document.getElementById('chartTitle').value || 'Grafik';
                const type = document.getElementById('chartType').value;
                const theme = document.getElementById('chartTheme').value;
                const format = document.getElementById('labelFormat').value;

                const minVal = document.getElementById('scaleMin').value;
                const maxVal = document.getElementById('scaleMax').value;
                const stepVal = document.getElementById('scaleStep').value;

                const cWidth = document.getElementById('chartWidth').value || 800;
                const cHeight = document.getElementById('chartHeight').value || 400;

                const labels = [];
                const data = [];
                const showValues = []; // Array untuk menyimpan status apakah angka ditampilkan atau tidak

                document.querySelectorAll('.data-row').forEach(row => {
                    const isShowValue = row.querySelector('.input-show').checked;
                    const labelVal = row.querySelector('.input-label').value;
                    const dataVal = row.querySelector('.input-value').value;

                    // Data SELALU dimasukkan asalkan kolom label tidak kosong
                    // Ini memastikan potongan diagram/batang tetap dirender
                    if (labelVal.trim() !== '') {
                        labels.push(labelVal);
                        data.push(parseFloat(dataVal) || 0);
                        showValues.push(isShowValue); // Simpan status true/false untuk tiap data
                    }
                });

                return {
                    title, type, theme, format, labels, data, showValues,
                    min: minVal !== '' ? parseFloat(minVal) : null,
                    max: maxVal !== '' ? parseFloat(maxVal) : null,
                    step: stepVal !== '' ? parseFloat(stepVal) : null,
                    width: cWidth,
                    height: cHeight
                };
            }

            function renderChart() {
                const chartData = getChartData();

                if (chartData.labels.length === 0) {
                    Swal.fire('Data Kosong', 'Harap isi minimal 1 baris data di tabel.', 'warning');
                    return;
                }

                chartWrapper.style.width = chartData.width + 'px';
                chartWrapper.style.height = chartData.height + 'px';

                if (myChartInstance) {
                    myChartInstance.destroy();
                }

                const activePalette = palettes[chartData.theme] || palettes.default;
                const isCircular = (chartData.type === 'pie' || chartData.type === 'doughnut');

                const dataLabelsConfig = {
                    color: isCircular ? '#000000' : '#334155',
                    anchor: isCircular ? 'center' : 'end',
                    align: isCircular ? 'center' : 'top',
                    offset: 4,
                    textAlign: 'center',
                    font: {
                        weight: isCircular ? 'normal' : 'bold',
                        size: 13
                    },
                    textShadowColor: 'transparent',
                    textShadowBlur: 0,
                    formatter: function(value, context) {
                        const isShowValue = chartData.showValues[context.dataIndex];

                        if (isCircular) {
                            const labelName = context.chart.data.labels[context.dataIndex];

                            // Jika checkbox tidak dicentang, hanya tampilkan nama kategorinya
                            if (!isShowValue) {
                                return labelName;
                            }

                            const dataset = context.chart.data.datasets[0].data;
                            const total = dataset.reduce((acc, curr) => acc + curr, 0);

                            let displayValue = value;

                            if (chartData.format === 'percent') {
                                displayValue = ((value / total) * 100).toFixed(1) + '%';
                            } else if (chartData.format === 'degree') {
                                displayValue = ((value / total) * 360).toFixed(1) + '°';
                            }

                            return labelName + '\n' + displayValue;
                        }

                        // Untuk tipe Bar/Line, kembalikan string kosong jika checkbox tidak dicentang
                        if (!isShowValue) {
                            return '';
                        }
                        return value;
                    }
                };

                myChartInstance = new Chart(ctx, {
                    type: chartData.type,
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: chartData.title,
                            data: chartData.data,
                            backgroundColor: activePalette.bg,
                            borderColor: activePalette.border,
                            borderWidth: 1,
                            borderRadius: (chartData.type === 'bar') ? 6 : 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: { padding: { top: 30, bottom: 10, left: 10, right: 10 } },
                        plugins: {
                            customCanvasBackgroundColor: { color: 'white' },
                            legend: { display: false },
                            title: {
                                display: true,
                                text: chartData.title,
                                font: { size: 18 }
                            },
                            datalabels: dataLabelsConfig
                        },
                        scales: (chartData.type === 'bar' || chartData.type === 'line') ? {
                            y: {
                                beginAtZero: true,
                                min: chartData.min !== null ? chartData.min : undefined,
                                max: chartData.max !== null ? chartData.max : undefined,
                                ticks: {
                                    stepSize: chartData.step !== null ? chartData.step : undefined
                                }
                            }
                        } : {
                            x: { display: false },
                            y: { display: false }
                        }
                    },
                    plugins: [customCanvasBackgroundColor]
                });
            }

            renderChart();

            btnGenerate.addEventListener('click', renderChart);

            btnAddRow.addEventListener('click', function() {
                const tr = document.createElement('tr');
                tr.className = 'data-row hover:bg-slate-50 transition-colors';
                tr.innerHTML = `
                    <td class="p-3 text-center"><input type="checkbox" checked class="input-show w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer"></td>
                    <td class="p-3"><input type="text" placeholder="Kategori baru..." class="input-label w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500"></td>
                    <td class="p-3"><input type="number" value="0" class="input-value w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500 text-center"></td>
                    <td class="p-3 text-center"><button type="button" class="btn-remove text-rose-400 hover:text-rose-600 transition-colors"><i class="fas fa-times-circle text-lg"></i></button></td>
                `;
                dataBody.appendChild(tr);
            });

            dataBody.addEventListener('click', function(e) {
                if (e.target.closest('.btn-remove')) {
                    e.target.closest('tr').remove();
                }
            });

            btnCopy.addEventListener('click', function() {
                if (!myChartInstance) return;
                ctx.toBlob(function(blob) {
                    try {
                        const item = new ClipboardItem({ "image/png": blob });
                        navigator.clipboard.write([item]).then(function() {
                            Swal.fire({ title: 'Berhasil di-copy!', text: 'Silakan Paste (CTRL+V) di Word / PPT.', icon: 'success', timer: 1500, showConfirmButton: false });
                        });
                    } catch (err) {
                        Swal.fire('Gagal!', 'Browser Anda tidak mendukung fitur copy otomatis.', 'error');
                    }
                }, "image/png");
            });

            btnDownload.addEventListener('click', function() {
                if (!myChartInstance) return;
                const link = document.createElement('a');
                link.href = myChartInstance.toBase64Image();
                link.download = document.getElementById('chartTitle').value.replace(/\s+/g, '_').toLowerCase() + '.png';
                link.click();
            });
        });
    </script>
</x-app-layout>