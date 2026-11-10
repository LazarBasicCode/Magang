{{--
    Tampilan MAHASISWA / DOSEN untuk Kerja Sama ("Kerja Sama Saya").
    Dipakai KerjaSamaController::index() untuk akses "biasa" milik role mahasiswa atau dosen.
    Data: $initial (items+meta[+summary]), $summary, $ownerType (mahasiswa|dosen), $verify (bool), $jenisByTipe

    $verify = false (dosen): data langsung disetujui, jadi tanpa kartu status / progres verifikasi.
    Pilihan jenis diambil dari KerjaSamaController::JENIS_BY_TIPE (satu sumber dengan validasi server).
--}}
@php
    $me      = auth()->user();
    $isDosen = $ownerType === 'dosen';
    $jenisLabels = [
        'conference_internasional' => 'Conference Internasional',
        'pkl'                      => 'PKL (Output)',
        'fgd'                      => 'FGD',
        'keynote_session'          => 'Keynote Speaker',
        'guest_lecture'            => 'Guest Lecture',
        'pengabdian'               => 'Pengabdian',
        'penelitian'               => 'Penelitian',
        'pertukaran_pelajar'       => 'Pertukaran Pelajar',
        'lainnya'                  => 'Lainnya',
    ];
    $allowedJenis = $jenisByTipe[$ownerType] ?? [];
    $defaultJenis = $allowedJenis[0] ?? 'conference_internasional';
@endphp
@include('partials.mhs-head', ['pageTitle' => 'Kerja Sama Saya'])
    <link rel="stylesheet" href="{{ asset('css/bukti-tambahan.css') }}?v={{ @filemtime(public_path('css/bukti-tambahan.css')) }}">

    @include('partials.sidebar', ['active' => 'kerja_sama'])

    <div class="app-content">
        @include('partials.header', ['active' => 'kerja_sama', 'crumbCurrent' => 'Kerja Sama Saya'])

        @include('partials.mhs-page', [
            'title' => 'Kerja Sama Saya',
            'subtitle' => $verify
                ? 'Unggah kegiatan kerja samamu, lalu pantau proses verifikasinya di sini.'
                : 'Kelola kegiatan kerja samamu di sini.',
            'addLabel' => 'Tambah Kerja Sama',
            'searchPlaceholder' => 'Cari judul kegiatan atau mitra...',
            'listTitle' => 'Kegiatan Kerja Sama',
            'summary' => $summary ?? ['total' => 0, 'disetujui' => 0, 'diproses' => 0, 'ditolak' => 0],
            'canAdd' => true,
            'showStatus' => $verify,
        ])
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    @include('partials.mhs-detail-modal', ['detailSubtitle' => $verify ? 'Pantau status verifikasi pengajuanmu' : 'Rincian kerja sama'])

    {{-- ============ MODAL: Tambah / Edit ============ --}}
    <div class="modal-backdrop" id="mvFormBackdrop"></div>
    <div class="modal-card" id="mvFormModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-drag-handle" id="mvFormDrag">
            <div>
                <h3 class="modal-title" id="mvFormTitle">Tambah Kerja Sama</h3>
                <p class="modal-subtitle">Isi detail kegiatan kerja samamu</p>
            </div>
            <button type="button" class="modal-close-btn" id="mvFormClose" aria-label="Tutup">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="mvForm" class="modal-body">
            <input type="hidden" id="mvFormId" value="">
            {{-- Pemilik & tipe otomatis akun ini (server juga memaksa user_id & tipe_user) --}}
            <input type="hidden" id="form-user_id" value="{{ $me->id }}">
            <input type="hidden" id="form-tipe_user" value="{{ $ownerType }}">
            <div class="mv-owner">
                <span class="material-symbols-outlined">account_circle</span>
                <div>
                    {{ $me->name }}
                    <small>{{ $isDosen ? 'NIDN' : 'NIM' }} {{ $me->nim_nidn ?? '-' }}</small>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label class="field-label">Jenis Kerja Sama</label>
                    <div class="dropdown" data-dropdown id="dd-jenis">
                        <input type="hidden" id="form-jenis" value="{{ $defaultJenis }}" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">{{ $jenisLabels[$defaultJenis] ?? $defaultJenis }}</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            @foreach($allowedJenis as $value)
                            <button type="button" class="dropdown-option {{ $value === $defaultJenis ? 'is-selected' : '' }}" data-value="{{ $value }}">{{ $jenisLabels[$value] ?? $value }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="field">
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

            {{-- Hanya muncul bila Jenis = Lainnya --}}
            <div class="field hidden" id="field-lainnya">
                <label class="field-label" for="form-jenis_lainnya">Jenis Kerja Sama Lainnya</label>
                <div class="field-control">
                    <input id="form-jenis_lainnya" type="text" maxlength="255" placeholder="Tulis jenis kerja sama...">
                </div>
            </div>

            {{-- Arah: hanya untuk Guest Lecture, yang hanya tersedia bagi dosen --}}
            @if(in_array('guest_lecture', $allowedJenis, true))
            <div class="field hidden" id="field-arah">
                <label class="field-label">Arah</label>
                <div class="dropdown" data-dropdown id="dd-arah">
                    <input type="hidden" id="form-arah" value="inbound" />
                    <button type="button" class="dropdown-trigger">
                        <span class="dropdown-value">Inbound</span>
                        <span class="material-symbols-outlined caret">expand_more</span>
                    </button>
                    <div class="dropdown-panel">
                        <button type="button" class="dropdown-option is-selected" data-value="inbound">Inbound</button>
                        <button type="button" class="dropdown-option" data-value="outbound">Outbound</button>
                    </div>
                </div>
            </div>
            @endif

            <div class="field">
                <label class="field-label" for="form-judul_kegiatan">Judul Kegiatan</label>
                <div class="field-control">
                    <input id="form-judul_kegiatan" type="text" maxlength="255" placeholder="Judul kegiatan kerja sama" required>
                </div>
            </div>

            <div class="field">
                <label class="field-label" for="form-mitra">Mitra (Institusi/Perusahaan/Negara)</label>
                <div class="field-control">
                    <input id="form-mitra" type="text" maxlength="255" placeholder="Nama mitra kerja sama" required>
                </div>
            </div>

            <div class="field-row">
                @include('partials.mhs-datepicker', ['id' => 'tanggal_mulai', 'label' => 'Tanggal Mulai'])
                @include('partials.mhs-datepicker', ['id' => 'tanggal_selesai', 'label' => 'Tanggal Selesai'])
            </div>

            <div class="field">
                <label class="field-label" for="form-bukti_kegiatan">Link Bukti</label>
                <div class="field-control">
                    <input id="form-bukti_kegiatan" type="url" placeholder="https://drive.google.com/..." required>
                </div>
                <p class="mv-help">Tempel link Google Drive berisi bukti kegiatan (pastikan bisa dibuka).</p>
            </div>
            <div class="field">
                <div id="buktiTambahanList"></div>
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

    @include('partials.duplicate-confirm-modal')
    @include('partials.mhs-scripts')
    <script src="{{ asset('js/duplicate-confirm.js') }}?v={{ @filemtime(public_path('js/duplicate-confirm.js')) }}"></script>
    <script src="{{ asset('js/bukti-tambahan.js') }}?v={{ @filemtime(public_path('js/bukti-tambahan.js')) }}"></script>
    <script>
        const $v = (id) => document.getElementById(id);
        const buktiTambahan = BuktiTambahan.mount($v('buktiTambahanList'));
        const HAS_ARAH = !!$v('field-arah');
        const DEFAULT_JENIS = @json($defaultJenis);

        const L = {
            jenis: @json($jenisLabels),
            skala: { nasional: 'Nasional', internasional: 'Internasional' },
            arah: { inbound: 'Inbound', outbound: 'Outbound' },
        };
        const jenisText = (i) => (i.jenis === 'lainnya' && i.jenis_lainnya) ? i.jenis_lainnya : (L.jenis[i.jenis] || i.jenis);

        const fmtDate = (d) => {
            if (!d) return '';
            const dt = new Date(d + 'T00:00:00');
            return isNaN(dt) ? d : dt.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        };

        // Datepicker (Tanggal Mulai & Selesai): SIDA.datepicker dari script.js
        const { start: dpMulai, end: dpSelesai } = SIDA.datepicker.pair($v('dp-tanggal_mulai'), $v('dp-tanggal_selesai'));

        // "Lainnya" & "Arah" hanya muncul bila jenisnya cocok (sama dengan aturan server).
        function syncJenis() {
            const jenis = $v('form-jenis').value;
            const lainnya = jenis === 'lainnya';
            $v('field-lainnya').classList.toggle('hidden', !lainnya);
            $v('form-jenis_lainnya').required = lainnya;
            if (!lainnya) $v('form-jenis_lainnya').value = '';
            if (HAS_ARAH) $v('field-arah').classList.toggle('hidden', jenis !== 'guest_lecture');
        }
        // setTimeout 0: tunggu script.js selesai mengisi nilai dropdown tersembunyi
        document.querySelectorAll('#dd-jenis .dropdown-option').forEach((o) => o.addEventListener('click', () => setTimeout(syncJenis, 0)));

        MhsView.init({
            endpoint: '/kerja-sama',
            approvalMenu: 'kerja-sama',
            verification: @json($verify),
            initial: @json($initial),
            addTitle: 'Tambah Kerja Sama',
            editTitle: 'Edit Kerja Sama',
            emptyTitle: 'Belum ada kerja sama',
            emptyDesc: 'Klik "Tambah Kerja Sama" untuk mulai mengisi.',
            titleOf: (i) => i.judul_kegiatan,
            metaOf: (i) => [jenisText(i), i.mitra, i.skala ? 'Skala ' + (L.skala[i.skala] || i.skala) : null].filter(Boolean).join(' · '),
            detailRows: (i) => [
                ['Jenis', jenisText(i)],
                ['Skala', i.skala ? (L.skala[i.skala] || i.skala) : null],
                ['Arah', i.arah ? (L.arah[i.arah] || i.arah) : null],
                ['Mitra', i.mitra],
                ['Periode', i.tanggal_mulai ? fmtDate(i.tanggal_mulai) + ' – ' + fmtDate(i.tanggal_selesai) : null],
                ['Diajukan', i.diajukan],
            ],
            linksOf: (i) => [
                { label: 'Lihat Bukti', url: i.bukti_kegiatan },
                ...(i.bukti_tambahan || []).map((b, n, all) => ({ label: b.nama || (all.length > 1 ? 'Bukti Tambahan ' + (n + 1) : 'Bukti Tambahan'), url: b.url })),
            ],
            fillForm: (i) => {
                SIDA.dropdown.select($v('dd-jenis'), i?.jenis || DEFAULT_JENIS);
                SIDA.dropdown.select($v('dd-skala'), i?.skala || 'nasional');
                if (HAS_ARAH) SIDA.dropdown.select($v('dd-arah'), i?.arah || 'inbound');
                $v('form-jenis_lainnya').value = i?.jenis_lainnya || '';
                $v('form-judul_kegiatan').value = i?.judul_kegiatan || '';
                $v('form-mitra').value = i?.mitra || '';
                dpMulai.setValue(i?.tanggal_mulai || '');
                dpSelesai.setMinDate(i?.tanggal_mulai || null);
                dpSelesai.setValue(i?.tanggal_selesai || '');
                $v('form-bukti_kegiatan').value = i?.bukti_kegiatan || '';
                buktiTambahan.set(i?.bukti_tambahan);
                syncJenis();
            },
            readForm: () => {
                const jenis = $v('form-jenis').value;
                return {
                    user_id: $v('form-user_id').value,
                    tipe_user: $v('form-tipe_user').value,
                    jenis,
                    jenis_lainnya: jenis === 'lainnya' ? $v('form-jenis_lainnya').value.trim() : null,
                    skala: $v('form-skala').value,
                    arah: HAS_ARAH && jenis === 'guest_lecture' ? $v('form-arah').value : null,
                    mitra: $v('form-mitra').value,
                    judul_kegiatan: $v('form-judul_kegiatan').value,
                    tanggal_mulai: $v('form-tanggal_mulai').value,
                    tanggal_selesai: $v('form-tanggal_selesai').value,
                    bukti_kegiatan: $v('form-bukti_kegiatan').value,
                bukti_tambahan: buktiTambahan.get(),
                };
            },
        });
    </script>
</body>

</html>
