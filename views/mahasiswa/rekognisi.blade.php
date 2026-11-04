{{--
    Tampilan MAHASISWA / ALUMNI / DOSEN untuk Rekognisi ("Rekognisi Saya").
    Dipakai LppmRekognisiController::index() untuk akses "biasa" milik role mahasiswa atau dosen.
    Data: $initial (items+meta[+summary]), $summary, $ownerType (mahasiswa|alumni|dosen), $verify (bool)

    $verify = false (dosen): data langsung disetujui, jadi tanpa kartu status / progres verifikasi.
    Jenis karya yang boleh dipilih sama dengan aturan server (LppmRekognisiController::validated()).
--}}
@php
    $me        = auth()->user();
    $isDosen   = $ownerType === 'dosen';
    $isAlumni  = $ownerType === 'alumni';
    $kinds     = $isDosen
        ? ['jurnal' => 'Jurnal', 'hki' => 'Hak Kekayaan Intelektual', 'book' => 'Buku']
        : ['sinta' => 'Publikasi SINTA', 'conference' => 'Conference', 'jurnal' => 'Jurnal'];
    $defaultKind = array_key_first($kinds);
    $mitraRequired = $isDosen || $isAlumni;
@endphp
@include('partials.mhs-head', ['pageTitle' => 'Rekognisi Saya'])

    @include('partials.sidebar', ['active' => 'rekognisi'])

    <div class="app-content">
        @include('partials.header', ['active' => 'rekognisi', 'crumbCurrent' => 'Rekognisi Saya'])

        @include('partials.mhs-page', [
            'title' => 'Rekognisi Saya',
            'subtitle' => $verify
                ? 'Unggah rekognisi, publikasi & karyamu, lalu pantau proses verifikasinya di sini.'
                : 'Kelola rekognisi, publikasi & karyamu di sini.',
            'addLabel' => 'Tambah Rekognisi',
            'searchPlaceholder' => 'Cari judul, penulis, jurnal, atau mitra...',
            'listTitle' => 'Rekognisi / Karya',
            'summary' => $summary ?? ['total' => 0, 'disetujui' => 0, 'diproses' => 0, 'ditolak' => 0],
            'canAdd' => true,
            'showStatus' => $verify,
        ])
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    @include('partials.mhs-detail-modal', ['detailSubtitle' => $verify ? 'Pantau status verifikasi pengajuanmu' : 'Rincian rekognisi'])

    {{-- ============ MODAL: Tambah / Edit ============ --}}
    <div class="modal-backdrop" id="mvFormBackdrop"></div>
    <div class="modal-card" id="mvFormModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-drag-handle" id="mvFormDrag">
            <div>
                <h3 class="modal-title" id="mvFormTitle">Tambah Rekognisi</h3>
                <p class="modal-subtitle">Isi detail rekognisi atau karyamu</p>
            </div>
            <button type="button" class="modal-close-btn" id="mvFormClose" aria-label="Tutup">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="mvForm" class="modal-body">
            <input type="hidden" id="mvFormId" value="">
            {{-- Pemilik otomatis akun ini (server juga memaksa user_id = akun yang login) --}}
            <input type="hidden" id="form-user_id" value="{{ $me->id }}">
            <div class="mv-owner">
                <span class="material-symbols-outlined">account_circle</span>
                <div>
                    {{ $me->name }}
                    <small>{{ $isDosen ? 'NIDN' : 'NIM' }} {{ $me->nim_nidn ?? '-' }}@if($isAlumni) &middot; Alumni @endif</small>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label class="field-label">Jenis Karya / Publikasi</label>
                    <div class="dropdown" data-dropdown id="dd-jenis">
                        <input type="hidden" id="form-jenis" value="{{ $defaultKind }}" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">{{ $kinds[$defaultKind] }}</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            @foreach($kinds as $value => $label)
                            <button type="button" class="dropdown-option {{ $value === $defaultKind ? 'is-selected' : '' }}" data-value="{{ $value }}">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="field" id="field-skala">
                    <label class="field-label">Skala</label>
                    <div class="dropdown" data-dropdown id="dd-skala">
                        <input type="hidden" id="form-skala" value="nasional" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">Nasional</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            <button type="button" class="dropdown-option is-selected" data-value="nasional">Nasional</button>
                            <button type="button" class="dropdown-option" data-value="internasional">Internasional</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label class="field-label" for="form-judul">Judul Karya / Publikasi</label>
                    <div class="field-control">
                        <input id="form-judul" type="text" maxlength="255" placeholder="Judul karya atau publikasi" required>
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="form-penulis">Penulis</label>
                    <div class="field-control">
                        <input id="form-penulis" type="text" maxlength="255" placeholder="Nama-nama penulis" required>
                    </div>
                </div>
            </div>

            {{-- Khusus Jurnal / SINTA --}}
            <div class="field-row hidden" id="row-jurnal">
                <div class="field">
                    <label class="field-label" for="form-nama_jurnal">Nama Jurnal</label>
                    <div class="field-control">
                        <input id="form-nama_jurnal" type="text" maxlength="255" placeholder="Nama jurnal">
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="form-peringkat">Peringkat</label>
                    <div class="field-control">
                        <input id="form-peringkat" type="text" maxlength="100" placeholder="Contoh: Q2 atau S3">
                    </div>
                </div>
            </div>
            <div class="field hidden" id="field-link_doi">
                <label class="field-label" for="form-link_doi">Link DOI (opsional)</label>
                <div class="field-control">
                    <input id="form-link_doi" type="url" placeholder="https://doi.org/...">
                </div>
            </div>

            {{-- Khusus HKI / Buku (hanya tersedia untuk dosen) --}}
            @if($isDosen)
            <div class="field hidden" id="field-jenis_hki">
                <label class="field-label">Jenis HKI</label>
                <div class="dropdown" data-dropdown id="dd-jenis_hki">
                    <input type="hidden" id="form-jenis_hki" value="hak_cipta" />
                    <button type="button" class="dropdown-trigger"><span class="dropdown-value">Hak Cipta</span><span class="material-symbols-outlined caret">expand_more</span></button>
                    <div class="dropdown-panel">
                        <button type="button" class="dropdown-option is-selected" data-value="hak_cipta">Hak Cipta</button>
                        <button type="button" class="dropdown-option" data-value="paten">Paten</button>
                        <button type="button" class="dropdown-option" data-value="merek">Merek</button>
                    </div>
                </div>
            </div>
            <div class="field hidden" id="field-kategori_buku">
                <label class="field-label">Kategori Buku</label>
                <div class="dropdown" data-dropdown id="dd-kategori_buku">
                    <input type="hidden" id="form-kategori_buku" value="ajar" />
                    <button type="button" class="dropdown-trigger"><span class="dropdown-value">Buku Ajar</span><span class="material-symbols-outlined caret">expand_more</span></button>
                    <div class="dropdown-panel">
                        <button type="button" class="dropdown-option is-selected" data-value="ajar">Buku Ajar</button>
                        <button type="button" class="dropdown-option" data-value="referensi">Referensi</button>
                        <button type="button" class="dropdown-option" data-value="chapter">Chapter</button>
                    </div>
                </div>
            </div>
            @endif

            <div class="field-row">
                <div class="field">
                    <label class="field-label" for="form-mitra">Mitra / Instansi{{ $mitraRequired ? '' : ' (opsional)' }}</label>
                    <div class="field-control">
                        <input id="form-mitra" type="text" placeholder="Nama perusahaan/instansi" {{ $mitraRequired ? 'required' : '' }}>
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="form-tahun">Tahun</label>
                    <div class="field-control">
                        <input id="form-tahun" type="number" min="2000" max="2100" value="{{ now()->year }}" required>
                    </div>
                </div>
            </div>

            @if($isAlumni)
            <div class="field">
                <label class="field-label" for="form-jabatan">Jabatan (Alumni)</label>
                <div class="field-control">
                    <input id="form-jabatan" type="text" placeholder="Contoh: Manager Operasional" required>
                </div>
            </div>
            @endif

            <div class="field-row">
                @include('partials.mhs-datepicker', ['id' => 'tanggal_mulai', 'label' => 'Tanggal Mulai'])
                @include('partials.mhs-datepicker', ['id' => 'tanggal_selesai', 'label' => 'Tanggal Selesai'])
            </div>

            <div class="field">
                <label class="field-label" for="form-bukti_kegiatan">Link Bukti</label>
                <div class="field-control">
                    <input id="form-bukti_kegiatan" type="url" placeholder="https://drive.google.com/..." required>
                </div>
                <p class="mv-help">Tempel link Google Drive berisi bukti karya atau publikasi (pastikan bisa dibuka).</p>
            </div>
            <div class="field">
                <label class="field-label" for="form-bukti_tambahan">Link Bukti Tambahan (opsional)</label>
                <div class="field-control">
                    <input id="form-bukti_tambahan" type="url" placeholder="https://drive.google.com/...">
                </div>
            </div>

            <div class="modal-error" id="mvFormError" hidden></div>

            <div class="modal-footer">
                <button type="button" class="btn-ghost" id="mvFormCancel">Batal</button>
                <button type="submit" class="btn-apply" id="mvFormSubmit">
                    <span class="material-symbols-outlined">{{ $verify ? 'send' : 'save' }}</span>
                    <span>Simpan</span>
                </button>
            </div>
        </form>
    </div>

    @include('partials.mhs-scripts')
    <script>
        const $v = (id) => document.getElementById(id);
        const IS_DOSEN = @json($isDosen);
        const IS_ALUMNI = @json($isAlumni);
        const DEFAULT_KIND = @json($defaultKind);

        const L = {
            jenis: { jurnal: 'Jurnal', hki: 'HKI', book: 'Buku', sinta: 'Publikasi SINTA', conference: 'Conference' },
            skala: { nasional: 'Nasional', internasional: 'Internasional' },
            hki: { hak_cipta: 'Hak Cipta', paten: 'Paten', merek: 'Merek' },
            buku: { ajar: 'Buku Ajar', referensi: 'Referensi', chapter: 'Chapter' },
        };
        const SCALABLE = ['jurnal', 'sinta', 'conference'];
        const JOURNAL = ['jurnal', 'sinta'];

        const fmtDate = (d) => {
            if (!d) return '';
            const dt = new Date(d + 'T00:00:00');
            return isNaN(dt) ? d : dt.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        };

        // Datepicker (Tanggal Mulai & Selesai): SIDA.datepicker dari script.js
        const { start: dpMulai, end: dpSelesai } = SIDA.datepicker.pair($v('dp-tanggal_mulai'), $v('dp-tanggal_selesai'));

        // Field kondisional mengikuti jenis karya (sama dengan aturan server).
        function syncKind() {
            const kind = $v('form-jenis').value;
            const scalable = SCALABLE.includes(kind);
            const journal = JOURNAL.includes(kind);
            $v('field-skala').classList.toggle('hidden', !scalable);
            $v('row-jurnal').classList.toggle('hidden', !journal);
            $v('field-link_doi').classList.toggle('hidden', !journal);
            $v('form-nama_jurnal').required = journal;
            $v('form-peringkat').required = journal;
            if (IS_DOSEN) {
                $v('field-jenis_hki').classList.toggle('hidden', kind !== 'hki');
                $v('field-kategori_buku').classList.toggle('hidden', kind !== 'book');
            }
        }
        // setTimeout 0: tunggu script.js selesai mengisi nilai dropdown tersembunyi
        document.querySelectorAll('#dd-jenis .dropdown-option').forEach((o) => o.addEventListener('click', () => setTimeout(syncKind, 0)));

        MhsView.init({
            endpoint: '/lppm/rekognisi',
            approvalMenu: 'rekognisi',
            verification: @json($verify),
            initial: @json($initial),
            addTitle: 'Tambah Rekognisi',
            editTitle: 'Edit Rekognisi',
            emptyTitle: 'Belum ada rekognisi',
            emptyDesc: 'Klik "Tambah Rekognisi" untuk mulai mengisi.',
            titleOf: (i) => i.judul || 'Rekognisi',
            metaOf: (i) => [L.jenis[i.jenis] || i.jenis, i.skala ? L.skala[i.skala] : null, i.tahun].filter(Boolean).join(' · '),
            detailRows: (i) => [
                ['Jenis', L.jenis[i.jenis] || i.jenis],
                ['Skala', i.skala ? (L.skala[i.skala] || i.skala) : null],
                ['Penulis', i.penulis],
                ['Nama Jurnal', i.nama_jurnal],
                ['Peringkat', i.peringkat],
                ['Jenis HKI', i.jenis_hki ? (L.hki[i.jenis_hki] || i.jenis_hki) : null],
                ['Kategori Buku', i.kategori_buku ? (L.buku[i.kategori_buku] || i.kategori_buku) : null],
                ['Mitra', i.mitra],
                ['Jabatan', i.jabatan_tampil],
                ['Tahun', i.tahun],
                ['Periode', i.tanggal_mulai ? fmtDate(i.tanggal_mulai) + ' – ' + fmtDate(i.tanggal_selesai) : null],
                ['Diajukan', i.diajukan],
            ],
            linksOf: (i) => [
                { label: 'Lihat Bukti', url: i.bukti_kegiatan },
                { label: 'Bukti Tambahan', url: i.bukti_tambahan },
                { label: 'DOI', url: i.link_doi, icon: 'open_in_new' },
            ],
            fillForm: (i) => {
                SIDA.dropdown.select($v('dd-jenis'), i?.jenis || DEFAULT_KIND);
                SIDA.dropdown.select($v('dd-skala'), i?.skala || 'nasional');
                if (IS_DOSEN) {
                    SIDA.dropdown.select($v('dd-jenis_hki'), i?.jenis_hki || 'hak_cipta');
                    SIDA.dropdown.select($v('dd-kategori_buku'), i?.kategori_buku || 'ajar');
                }
                $v('form-judul').value = i?.judul || '';
                $v('form-penulis').value = i?.penulis || '';
                $v('form-nama_jurnal').value = i?.nama_jurnal || '';
                $v('form-peringkat').value = i?.peringkat || '';
                $v('form-link_doi').value = i?.link_doi || '';
                $v('form-mitra').value = i?.mitra || '';
                $v('form-tahun').value = i?.tahun || new Date().getFullYear();
                if (IS_ALUMNI) $v('form-jabatan').value = i?.jabatan || '';
                dpMulai.setValue(i?.tanggal_mulai || '');
                dpSelesai.setMinDate(i?.tanggal_mulai || null);
                dpSelesai.setValue(i?.tanggal_selesai || '');
                $v('form-bukti_kegiatan').value = i?.bukti_kegiatan || '';
                $v('form-bukti_tambahan').value = i?.bukti_tambahan || '';
                syncKind();
            },
            readForm: () => {
                const kind = $v('form-jenis').value;
                const journal = JOURNAL.includes(kind);
                return {
                    user_id: $v('form-user_id').value,
                    jenis: kind,
                    skala: SCALABLE.includes(kind) ? $v('form-skala').value : null,
                    mitra: $v('form-mitra').value || null,
                    jabatan: IS_ALUMNI ? $v('form-jabatan').value : null,
                    judul: $v('form-judul').value,
                    penulis: $v('form-penulis').value,
                    nama_jurnal: journal ? $v('form-nama_jurnal').value : null,
                    peringkat: journal ? $v('form-peringkat').value : null,
                    jenis_hki: IS_DOSEN && kind === 'hki' ? $v('form-jenis_hki').value : null,
                    kategori_buku: IS_DOSEN && kind === 'book' ? $v('form-kategori_buku').value : null,
                    link_doi: journal ? ($v('form-link_doi').value || null) : null,
                    tahun: $v('form-tahun').value,
                    tanggal_mulai: $v('form-tanggal_mulai').value,
                    tanggal_selesai: $v('form-tanggal_selesai').value,
                    bukti_kegiatan: $v('form-bukti_kegiatan').value,
                    bukti_tambahan: $v('form-bukti_tambahan').value || null,
                };
            },
        });
    </script>
</body>

</html>
