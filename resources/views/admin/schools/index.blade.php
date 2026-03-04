<x-app-layout>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8fafc;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    <div class="min-h-screen py-10" x-data="schoolManager()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center shadow-lg text-white rotate-3">
                        <i class="fas fa-school text-xl -rotate-3"></i>
                    </div>
                    <div>
                        <h2 class="font-black text-2xl text-slate-800 tracking-tight">Manajemen Sekolah</h2>
                        <p class="text-sm text-slate-500 font-bold">Kelola data multi-tenancy sekolah</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button x-show="selected.length > 0" x-cloak @click="deleteSelected()"
                        class="bg-rose-500 hover:bg-rose-600 text-white px-5 py-2.5 rounded-xl font-bold transition shadow-lg shadow-rose-200 flex items-center gap-2 whitespace-nowrap active:scale-95">
                        <i class="fas fa-trash-alt"></i> Hapus (<span x-text="selected.length"></span>)
                    </button>

                    <button x-show="selected.length > 0" x-cloak @click="downloadSelected()"
                        class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold transition shadow-lg shadow-emerald-200 flex items-center gap-2 whitespace-nowrap active:scale-95">
                        <i class="fas fa-file-export"></i> Download (<span x-text="selected.length"></span>)
                    </button>

                    <div class="relative w-full md:w-64">
                        <i class="fas fa-search absolute left-4 top-3.5 text-slate-400"></i>
                        <input type="text" x-model="search" placeholder="Cari sekolah..."
                            class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 shadow-sm transition">
                    </div>
                    <button @click="openModal()"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold transition shadow-lg shadow-indigo-200 flex items-center gap-2 whitespace-nowrap active:scale-95">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <th class="p-4 pl-6 w-12 text-center">
                                    <input type="checkbox" x-model="selectAll"
                                        class="rounded border-slate-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 cursor-pointer">
                                </th>
                                <th class="p-4 w-16 text-center">No</th>
                                <th class="p-4">Nama Sekolah</th>
                                <th class="p-4">Domain/Subdomain</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm font-bold text-slate-700">
                            <tr x-show="filteredSchools.length === 0" x-cloak>
                                <td colspan="5" class="p-10 text-center text-slate-400">
                                    <i class="fas fa-inbox text-3xl mb-3 opacity-30 block"></i>
                                    Tidak ada data sekolah ditemukan.
                                </td>
                            </tr>
                            <template x-for="(school, index) in filteredSchools" :key="school.id">
                                <tr class="hover:bg-slate-50/50 transition-colors"
                                    :class="{'bg-indigo-50/50': selected.includes(school.id)}">
                                    <td class="p-4 pl-6 text-center">
                                        <input type="checkbox" x-model="selected" :value="school.id"
                                            class="rounded border-slate-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 cursor-pointer">
                                    </td>
                                    <td class="p-4 text-center text-slate-400" x-text="index + 1"></td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black text-sm border border-indigo-100 shrink-0"
                                                x-text="school.name.charAt(0)"></div>
                                            <span class="text-slate-800" x-text="school.name"></span>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span x-show="school.domain"
                                            class="font-mono text-xs bg-slate-100 text-slate-500 px-2 py-1 rounded"
                                            x-text="school.domain"></span>
                                        <span x-show="!school.domain" class="text-slate-300">-</span>
                                    </td>
                                    <td class="p-4 pr-6">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openModal(school)"
                                                class="w-8 h-8 rounded-lg bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white transition flex items-center justify-center"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button @click="deleteSchool(school.id, school.name)"
                                                class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white transition flex items-center justify-center"
                                                title="Hapus">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <div x-show="isModalOpen" style="display: none" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="closeModal()"></div>

            <div x-show="isModalOpen" x-transition.scale.origin.bottom
                class="bg-white rounded-[2rem] shadow-2xl max-w-md w-full relative z-10 overflow-hidden border-4 border-white">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                    <div>
                        <h3 class="font-black text-slate-800 text-lg"
                            x-text="isEdit ? 'Edit Sekolah' : 'Tambah Sekolah Baru'"></h3>
                    </div>
                    <button @click="closeModal()"
                        class="text-slate-400 hover:text-rose-500 w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm transition"><i
                            class="fas fa-times"></i></button>
                </div>

                <form @submit.prevent="submitForm" class="p-6 space-y-5">
                    <div>
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Nama
                            Sekolah</label>
                        <input type="text" x-model="form.name" required
                            class="w-full bg-slate-50 border border-slate-200 focus:border-indigo-500 focus:bg-white focus:ring-0 rounded-xl font-bold text-slate-700 py-3 px-4 transition"
                            placeholder="Contoh: SMAN 1 Jakarta">
                        <template x-if="errors.name">
                            <p class="text-rose-500 text-xs font-bold mt-1" x-text="errors.name[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Domain
                            (Opsional)</label>
                        <input type="text" x-model="form.domain"
                            class="w-full bg-slate-50 border border-slate-200 focus:border-indigo-500 focus:bg-white focus:ring-0 rounded-xl font-bold text-slate-700 py-3 px-4 transition"
                            placeholder="Contoh: sman1jkt.cbt.com">
                        <template x-if="errors.domain">
                            <p class="text-rose-500 text-xs font-bold mt-1" x-text="errors.domain[0]"></p>
                        </template>
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <button type="submit" :disabled="isLoading"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3.5 rounded-xl font-black transition shadow-lg shadow-indigo-200 flex items-center justify-center gap-2 disabled:opacity-70">
                            <i x-show="!isLoading" class="fas fa-save"></i>
                            <i x-show="isLoading" class="fas fa-spinner fa-spin"></i>
                            <span x-text="isLoading ? 'Menyimpan...' : 'Simpan Data'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        // --- TAMBAHAN PENTING: Setup CSRF Token untuk Axios ---
        // Pastikan di file layout utama Anda (contoh: resources/views/layouts/app.blade.php)
        // terdapat tag <meta name="csrf-token" content="{{ csrf_token() }}"> di dalam <head>
        let token = document.head.querySelector('meta[name="csrf-token"]');
        if (token) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
        } else {
            console.error('CSRF token tidak ditemukan. Pastikan ada tag meta csrf-token di head!');
        }
        // ------------------------------------------------------

        function schoolManager() {
            return {
                schools: @json($schools),
                search: '',
                isModalOpen: false,
                isEdit: false,
                isLoading: false,
                form: { id: '', name: '', domain: '' },
                errors: {},
                selected: [],

                get filteredSchools() {
                    if (this.search === '') return this.schools;
                    return this.schools.filter(s => s.name.toLowerCase().includes(this.search.toLowerCase()));
                },

                get selectAll() {
                    return this.filteredSchools.length > 0 && this.selected.length === this.filteredSchools.length;
                },
                set selectAll(value) {
                    if (value) {
                        this.selected = this.filteredSchools.map(school => school.id);
                    } else {
                        this.selected = [];
                    }
                },

                openModal(school = null) {
                    this.errors = {};
                    this.isEdit = !!school;
                    if (school) {
                        this.form = { id: school.id, name: school.name, domain: school.domain || '' };
                    } else {
                        this.form = { id: '', name: '', domain: '' };
                    }
                    this.isModalOpen = true;
                },

                closeModal() {
                    this.isModalOpen = false;
                },

                fetchData() {
                    axios.get('{{ route('admin.schools.index') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(res => { this.schools = res.data.schools; })
                        .catch(err => console.error(err));
                },

                submitForm() {
                    this.isLoading = true;
                    this.errors = {};
                    let url = this.isEdit ? `/admin/schools/${this.form.id}` : `/admin/schools`;
                    let method = this.isEdit ? 'put' : 'post';

                    axios[method](url, this.form)
                        .then(res => {
                            this.closeModal();
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.data.message, timer: 1500, showConfirmButton: false });
                            this.fetchData();
                        })
                        .catch(err => {
                            if (err.response && err.response.status === 422) {
                                this.errors = err.response.data.errors;
                            } else {
                                Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
                            }
                        })
                        .finally(() => this.isLoading = false);
                },

                deleteSchool(id, name) {
                    Swal.fire({
                        title: 'Hapus ' + name + '?',
                        text: "Data yang dihapus tidak bisa dikembalikan!",
                        icon: 'warning',
                        background: '#1e293b', color: '#fff',
                        showCancelButton: true, confirmButtonColor: '#f43f5e', cancelButtonColor: '#475569',
                        confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            axios.delete(`/admin/schools/${id}`)
                                .then(res => {
                                    Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.data.message, timer: 1500, showConfirmButton: false });
                                    this.fetchData();
                                    this.selected = this.selected.filter(selectedId => selectedId !== id);
                                })
                                .catch(err => Swal.fire('Gagal!', 'Terjadi kesalahan.', 'error'));
                        }
                    })
                },

                deleteSelected() {
                    if (this.selected.length === 0) return;

                    Swal.fire({
                        title: 'Hapus ' + this.selected.length + ' Data?',
                        text: "Semua data yang dipilih akan dihapus secara permanen!",
                        icon: 'warning',
                        background: '#1e293b', color: '#fff',
                        showCancelButton: true,
                        confirmButtonColor: '#f43f5e',
                        cancelButtonColor: '#475569',
                        confirmButtonText: 'Ya, Hapus Semua',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            axios.delete('/admin/schools/bulk-delete', {
                                data: { ids: this.selected }
                            })
                            .then(res => {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: res.data.message || 'Data berhasil dihapus',
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                this.selected = [];
                                this.fetchData();
                            })
                            .catch(err => {
                                // --- TAMBAHAN PENTING: Tangkap pesan error spesifik dari server ---
                                let errorMsg = 'Terjadi kesalahan saat menghapus data.';
                                if (err.response && err.response.data && err.response.data.message) {
                                    errorMsg = err.response.data.message; // Menampilkan pesan error asli dari backend
                                }
                                Swal.fire('Gagal!', errorMsg, 'error');
                                console.error('Error Detail:', err.response || err);
                            });
                        }
                    });
                },

                downloadSelected() {
                    if (this.selected.length === 0) return;

                    Swal.fire({
                        title: 'Menyiapkan Unduhan...',
                        text: `Sedang memproses ${this.selected.length} data sekolah.`,
                        icon: 'info',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    const idsParam = this.selected.join(',');
                    window.location.href = `/admin/schools/export?ids=${idsParam}`;
                }
            }
        }
    </script>
</x-app-layout>