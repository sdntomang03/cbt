<x-app-layout>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8fafc;
        }

        .hover-lift {
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
    </style>

    <div class="min-h-screen py-10" x-data="proctorManager()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-6">
                <div class="flex items-center gap-5">
                    <div
                        class="w-14 h-14 rounded-2xl bg-slate-900 flex items-center justify-center shadow-lg shadow-slate-200 rotate-3">
                        <i class="fas fa-desktop text-white text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="font-black text-3xl text-slate-800 tracking-tight">Monitoring Ujian</h2>
                        <p class="text-slate-400 font-bold text-sm">Pilih sesi ujian yang sedang berjalan hari ini untuk
                            diawasi</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($sessions as $session)
                <div
                    class="bg-white rounded-[2.5rem] p-6 border border-slate-100 transition-all hover-lift flex flex-col h-full relative overflow-hidden">

                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-slate-50 rounded-full z-0"></div>

                    <div class="relative z-10 flex-1 flex flex-col">
                        <div class="mb-5 inline-block">
                            <div class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wide border
                                {{ now()->between($session->start_time, $session->end_time)
                                    ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                                    : (now()->lessThan($session->start_time)
                                        ? 'bg-amber-50 text-amber-600 border-amber-100'
                                        : 'bg-slate-50 text-slate-400 border-slate-100') }}">
                                @if(now()->between($session->start_time, $session->end_time))
                                <i class="fas fa-circle text-[8px] mr-1 animate-pulse"></i> Sedang Berlangsung
                                @elseif(now()->lessThan($session->start_time))
                                <i class="fas fa-clock mr-1"></i> Akan Datang
                                @else
                                <i class="fas fa-check-circle mr-1"></i> Selesai
                                @endif
                            </div>
                        </div>

                        <h3 class="font-black text-xl text-slate-800 mb-1 leading-tight">{{ $session->session_name }}
                        </h3>
                        <p class="text-sm font-bold text-indigo-500 mb-6">{{ $session->exam->title }}</p>

                        <div class="space-y-4 mb-6">
                            <div class="flex items-center gap-3 text-sm font-semibold text-slate-500">
                                <div
                                    class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-500 shadow-sm border border-emerald-100 shrink-0">
                                    <i class="fas fa-play"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span
                                        class="text-[10px] font-black uppercase tracking-wider text-slate-400 leading-none mb-1">Mulai</span>
                                    <span class="text-slate-700 leading-none">{{ $session->start_time->format('H:i') }}
                                        WIB</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 text-sm font-semibold text-slate-500">
                                <div
                                    class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center text-rose-500 shadow-sm border border-rose-100 shrink-0">
                                    <i class="fas fa-stop"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span
                                        class="text-[10px] font-black uppercase tracking-wider text-slate-400 leading-none mb-1">Berakhir</span>
                                    <span class="text-slate-700 leading-none">{{ $session->end_time->format('H:i') }}
                                        WIB</span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-auto mb-6 bg-slate-50 rounded-2xl p-4 border border-slate-100 flex items-center justify-between group-hover:border-indigo-100 transition-colors">
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Token
                                    Akses</p>
                                <div class="font-mono font-black text-2xl text-slate-800 tracking-widest"
                                    id="token-{{ $session->id }}">
                                    {{ $session->token ?? '------' }}
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button @click="copyToken({{ $session->id }})"
                                    class="w-10 h-10 rounded-xl bg-white text-slate-400 hover:text-indigo-600 shadow-sm hover:shadow-md transition flex items-center justify-center border border-slate-100"
                                    title="Salin Token">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button @click="regenerateToken({{ $session->id }})"
                                    class="w-10 h-10 rounded-xl bg-white text-slate-400 hover:text-orange-500 shadow-sm hover:shadow-md transition flex items-center justify-center border border-slate-100"
                                    title="Acak Ulang Token">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>

                    </div>

                    <div class="relative z-10 pt-4 border-t border-slate-50">
                        <a href="{{ route('proctor.monitor', $session->id) }}" class="w-full flex items-center justify-center gap-2 py-3.5 rounded-xl font-black text-white transition-all active:scale-95
                           {{ now()->between($session->start_time, $session->end_time)
                              ? 'bg-slate-900 hover:bg-black shadow-lg shadow-slate-300'
                              : 'bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-200' }}">
                            <i class="fas fa-desktop"></i>
                            Masuk Ruang Monitor
                        </a>
                    </div>
                </div>
                @empty
                <div
                    class="col-span-full flex flex-col items-center justify-center py-20 text-center bg-white rounded-[3rem] border border-slate-100 shadow-sm">
                    <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-mug-hot text-4xl text-slate-300"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-800">Tidak Ada Ujian Hari Ini</h3>
                    <p class="text-slate-400 font-bold max-w-md mx-auto mt-2">Belum ada jadwal sesi ujian yang sedang
                        berjalan atau akan datang pada hari ini.</p>
                </div>
                @endforelse
            </div>

        </div>
    </div>

    <script>
        function proctorManager() {
            return {
                copyToken(id) {
                    // Ambil text dari DOM id token agar selalu sinkron meski sudah direset
                    const token = document.getElementById(`token-${id}`).innerText.trim();
                    navigator.clipboard.writeText(token);

                    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                    Toast.fire({ icon: 'success', title: 'Token disalin: ' + token });
                },

                regenerateToken(id) {
                    Swal.fire({
                        title: 'Acak Ulang Token?',
                        text: "Token lama akan hangus. Siswa yang belum login harus menggunakan token baru.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#f97316',
                        confirmButtonText: 'Ya, Acak',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Endpoint ini sudah kita buat sebelumnya di ExamSessionController
                            axios.post(`/admin/exam-sessions/${id}/regenerate-token`)
                                .then(res => {
                                    // Update UI Token tanpa perlu reload
                                    document.getElementById(`token-${id}`).innerText = res.data.token;
                                    Swal.fire({ icon: 'success', title: 'Token Baru: ' + res.data.token, timer: 1500, showConfirmButton: false });
                                })
                                .catch(err => {
                                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan saat mengacak token.' });
                                });
                        }
                    });
                }
            }
        }
    </script>
</x-app-layout>