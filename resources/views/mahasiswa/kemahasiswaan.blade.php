{{--
    Tampilan MAHASISWA untuk Kemahasiswaan ("Prestasi Saya").
    Dipakai KemahasiswaanController::index() untuk role mahasiswa (akses "biasa").
    Data: $initial (items+meta+summary), $summary, $mahasiswa
--}}
@include('partials.mhs-head', ['pageTitle' => 'Prestasi Saya'])

    @include('partials.sidebar', ['active' => 'kemahasiswaan'])

    <div class="app-content">
        @include('partials.header', ['active' => 'kemahasiswaan', 'crumbCurrent' => 'Prestasi Saya'])

        @include('partials.mhs-page', [
            'title' => 'Prestasi Saya',
            'subtitle' => 'Unggah prestasi & kegiatanmu, lalu pantau proses verifikasinya di sini.',
            'addLabel' => 'Tambah Prestasi',
            'searchPlaceholder' => 'Cari nama prestasi atau kegiatan...',
            'listTitle' => 'Prestasi / Kegiatan',
            'summary' => $summary,
            'canAdd' => (bool) $mahasiswa,
        ])
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    @include('partials.mhs-detail-modal')

    {{-- ============ MODAL: Tambah / Edit ============ --}}
    <div class="modal-backdrop" id="mvFormBackdrop"></div>
    <div class="modal-card" id="mvFormModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-drag-handle" id="mvFormDrag">
            <div>
                <h3 class="modal-title" id="mvFormTitle">Tambah Prestasi</h3>
                <p class="modal-subtitle">Isi detail prestasi atau kegiatanmu</p>
            </div>
            <button type="button" class="modal-close-btn" id="mvFormClose" aria-label="Tutup">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="mvForm" class="modal-body">
            <input type="hidden" id="mvFormId" value="">
            {{-- Nama otomatis terpilih (server juga memaksa mahasiswa_id = akun ini) --}}
            <input type="hidden" id="form-mahasiswa_id" value="{{ $mahasiswa->id ?? '' }}">
            <div class="mv-owner">
                <span class="material-symbols-outlined">account_circle</span>
                <div>
                    {{ optional($mahasiswa?->user)->name ?? '-' }}
                    <small>NIM {{ $mahasiswa->nim ?? '-' }}</small>
                </div>
            </div>

            <div class="field">
                <label class="field-label" for="form-nama_kegiatan">Nama Prestasi / Kegiatan</label>
                <div class="field-control">
                    <input id="form-nama_kegiatan" type="text" placeholder="Contoh: Juara 1 Kompetisi UI/UX Nasional" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label class="field-label">Tingkat</label>
                    <div class="dropdown" data-dropdown id="dd-tingkat">
                        <input type="hidden" id="form-tingkat" value="lokal" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">Lokal (Kota/Wilayah)</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            <button type="button" class="dropdown-option is-selected" data-value="lokal">Lokal (Kota/Wilayah)</button>
                            <button type="button" class="dropdown-option" data-value="nasional">Nasional (RI)</button>
                            <button type="button" class="dropdown-option" data-value="internasional">Internasional (Global)</button>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="form-tahun">Tahun</label>
                    <div class="field-control">
                        <input id="form-tahun" type="number" min="2000" max="2100" value="{{ now()->year }}" required>
                    </div>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label class="field-label">Bidang</label>
                    <div class="dropdown" data-dropdown id="dd-tab">
                        <input type="hidden" id="form-tab" value="akademik" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">Akademik</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            <button type="button" class="dropdown-option is-selected" data-value="akademik">Akademik</button>
                            <button type="button" class="dropdown-option" data-value="non_akademik">Non Akademik</button>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="field-label">Jenis</label>
                    <div class="dropdown" data-dropdown id="dd-jenis">
                        <input type="hidden" id="form-jenis" value="kemahasiswaan" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">Kemahasiswaan</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            <button type="button" class="dropdown-option is-selected" data-value="kemahasiswaan">Kemahasiswaan</button>
                            <button type="button" class="dropdown-option" data-value="inbis">Inbis (Inkubator Bisnis)</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="field-label" for="form-bukti_kegiatan">Link Bukti</label>
                <div class="field-control">
                    <input id="form-bukti_kegiatan" type="url" placeholder="https://drive.google.com/..." required>
                </div>
                <p class="mv-help">Tempel link Google Drive berisi sertifikat atau foto kegiatan (pastikan bisa dibuka).</p>
            </div>

            <div class="modal-error" id="mvFormError" hidden></div>

            <div class="modal-footer">
                <button type="button" class="btn-ghost" id="mvFormCancel">Batal</button>
                <button type="submit" class="btn-apply" id="mvFormSubmit">
                    <span class="material-symbols-outlined">send</span>
                    <span>Simpan</span>
                </button>
            </div>
        </form>
    </div>

    @include('partials.mhs-scripts')
    <script>
        const $v = (id) => document.getElementById(id);
        const L = {
            jenis: { kemahasiswaan: 'Kemahasiswaan', inbis: 'Inbis' },
            tab: { akademik: 'Akademik', non_akademik: 'Non Akademik' },
            tingkat: { lokal: 'Lokal', nasional: 'Nasional', internasional: 'Internasional' },
        };

        MhsView.init({
            endpoint: '/kemahasiswaan',
            approvalMenu: 'kemahasiswaan',
            initial: @json($initial),
            addTitle: 'Tambah Prestasi',
            editTitle: 'Edit Prestasi',
            emptyTitle: 'Belum ada prestasi',
            emptyDesc: 'Klik "Tambah Prestasi" untuk mulai mengunggah.',
            titleOf: (i) => i.nama_kegiatan,
            metaOf: (i) => [L.tingkat[i.tingkat] || i.tingkat, L.tab[i.tab] || i.tab, i.tahun].join(' · '),
            detailRows: (i) => [
                ['Tingkat', L.tingkat[i.tingkat] || i.tingkat],
                ['Bidang', L.tab[i.tab] || i.tab],
                ['Jenis', L.jenis[i.jenis] || i.jenis],
                ['Tahun', i.tahun],
                ['Diajukan', i.diajukan],
            ],
            linksOf: (i) => [{ label: 'Lihat Bukti', url: i.bukti_kegiatan }],
            fillForm: (i) => {
                SIDA.dropdown.select($v('dd-tingkat'), i?.tingkat || 'lokal');
                SIDA.dropdown.select($v('dd-tab'), i?.tab || 'akademik');
                SIDA.dropdown.select($v('dd-jenis'), i?.jenis || 'kemahasiswaan');
                $v('form-tahun').value = i?.tahun || new Date().getFullYear();
                $v('form-nama_kegiatan').value = i?.nama_kegiatan || '';
                $v('form-bukti_kegiatan').value = i?.bukti_kegiatan || '';
            },
            readForm: () => ({
                mahasiswa_id: $v('form-mahasiswa_id').value,
                jenis: $v('form-jenis').value,
                tab: $v('form-tab').value,
                tingkat: $v('form-tingkat').value,
                tahun: $v('form-tahun').value,
                nama_kegiatan: $v('form-nama_kegiatan').value,
                bukti_kegiatan: $v('form-bukti_kegiatan').value,
            }),
        });
    </script>
</body>

</html>
