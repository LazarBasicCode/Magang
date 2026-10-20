{{--
    Tampilan MAHASISWA untuk LPPM Mahasiswa ("Publikasi Saya").
    Dipakai LppmMahasiswaController::index() untuk role mahasiswa (akses "biasa").
    Data: $initial (items+meta+summary), $summary, $mahasiswa
--}}
@include('partials.mhs-head', ['pageTitle' => 'Publikasi Saya'])

    @include('partials.sidebar', ['active' => 'lppm_mahasiswa'])

    <div class="app-content">
        @include('partials.header', ['active' => 'lppm_mahasiswa', 'crumbCurrent' => 'Publikasi Saya'])

        @include('partials.mhs-page', [
            'title' => 'Publikasi Saya',
            'subtitle' => 'Unggah publikasi jurnal & conference-mu, lalu pantau proses verifikasinya di sini.',
            'addLabel' => 'Tambah Publikasi',
            'searchPlaceholder' => 'Cari judul atau nama jurnal...',
            'listTitle' => 'Publikasi',
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
                <h3 class="modal-title" id="mvFormTitle">Tambah Publikasi</h3>
                <p class="modal-subtitle">Isi detail publikasi ilmiahmu</p>
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

            <div class="field-row">
                <div class="field">
                    <label class="field-label">Jenis Publikasi</label>
                    <div class="dropdown" data-dropdown id="dd-jenis">
                        <input type="hidden" id="form-jenis" value="sinta_nasional" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">SINTA Nasional</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            <button type="button" class="dropdown-option is-selected" data-value="sinta_nasional">SINTA Nasional</button>
                            <button type="button" class="dropdown-option" data-value="conference_internasional">Conference Internasional</button>
                            <button type="button" class="dropdown-option" data-value="jurnal_internasional">Jurnal Internasional</button>
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

            <div class="field">
                <label class="field-label" for="form-judul">Judul Publikasi</label>
                <div class="field-control">
                    <input id="form-judul" type="text" placeholder="Judul penelitian/publikasi" required>
                </div>
            </div>

            <div class="field">
                <label class="field-label" for="form-penulis">Penulis</label>
                <div class="field-control">
                    <input id="form-penulis" type="text" placeholder="Nama-nama penulis" required>
                </div>
            </div>

            {{-- Khusus jurnal (SINTA / Jurnal Internasional); disembunyikan untuk conference --}}
            <div class="field-row" id="field-jurnal-extra">
                <div class="field">
                    <label class="field-label" for="form-nama_jurnal">Nama Jurnal</label>
                    <div class="field-control">
                        <input id="form-nama_jurnal" type="text" placeholder="Nama jurnal">
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="form-peringkat">Peringkat</label>
                    <div class="field-control">
                        <input id="form-peringkat" type="text" placeholder="S1–S4 atau Q1–Q4">
                    </div>
                </div>
            </div>

            <div class="field" id="field-link_doi">
                <label class="field-label" for="form-link_doi">Link DOI <span style="font-weight:400;color:var(--ink-muted)">(opsional)</span></label>
                <div class="field-control">
                    <input id="form-link_doi" type="url" placeholder="https://doi.org/...">
                </div>
            </div>

            <div class="field">
                <label class="field-label" for="form-bukti_kegiatan">Link Bukti</label>
                <div class="field-control">
                    <input id="form-bukti_kegiatan" type="url" placeholder="https://drive.google.com/..." required>
                </div>
                <p class="mv-help">Tempel link Google Drive berisi file publikasi atau sertifikat (pastikan bisa dibuka).</p>
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
        const OWNER_NAME = @json(optional($mahasiswa?->user)->name ?? '');
        const JENIS = {
            sinta_nasional: 'SINTA Nasional',
            conference_internasional: 'Conference Internasional',
            jurnal_internasional: 'Jurnal Internasional',
        };
        const isJurnal = (j) => j === 'sinta_nasional' || j === 'jurnal_internasional';

        // Nama jurnal, peringkat & DOI hanya untuk jenis jurnal
        function toggleJurnal(jenis) {
            $v('field-jurnal-extra').classList.toggle('hidden', !isJurnal(jenis));
            $v('field-link_doi').classList.toggle('hidden', !isJurnal(jenis));
        }
        document.querySelectorAll('#dd-jenis .dropdown-option').forEach((opt) => {
            opt.addEventListener('click', () => toggleJurnal(opt.dataset.value));
        });

        MhsView.init({
            endpoint: '/lppm/mahasiswa',
            approvalMenu: 'lppm-mahasiswa',
            initial: @json($initial),
            addTitle: 'Tambah Publikasi',
            editTitle: 'Edit Publikasi',
            emptyTitle: 'Belum ada publikasi',
            emptyDesc: 'Klik "Tambah Publikasi" untuk mulai mengunggah.',
            titleOf: (i) => i.judul,
            metaOf: (i) => [JENIS[i.jenis] || i.jenis, isJurnal(i.jenis) ? i.peringkat : null, i.tahun].filter(Boolean).join(' · '),
            detailRows: (i) => [
                ['Jenis', JENIS[i.jenis] || i.jenis],
                ['Penulis', i.penulis],
                ['Nama Jurnal', i.nama_jurnal],
                ['Peringkat', i.peringkat],
                ['Tahun', i.tahun],
                ['Diajukan', i.diajukan],
            ],
            linksOf: (i) => [
                { label: 'Lihat Bukti', url: i.bukti_kegiatan },
                { label: 'Link DOI', url: i.link_doi },
            ],
            fillForm: (i) => {
                const jenis = i?.jenis || 'sinta_nasional';
                SIDA.dropdown.select($v('dd-jenis'), jenis);
                toggleJurnal(jenis);
                $v('form-tahun').value = i?.tahun || new Date().getFullYear();
                $v('form-judul').value = i?.judul || '';
                $v('form-penulis').value = i ? (i.penulis || '') : OWNER_NAME; // penulis default = nama sendiri
                $v('form-nama_jurnal').value = i?.nama_jurnal || '';
                $v('form-peringkat').value = i?.peringkat || '';
                $v('form-link_doi').value = i?.link_doi || '';
                $v('form-bukti_kegiatan').value = i?.bukti_kegiatan || '';
            },
            readForm: () => ({
                mahasiswa_id: $v('form-mahasiswa_id').value,
                jenis: $v('form-jenis').value,
                judul: $v('form-judul').value,
                penulis: $v('form-penulis').value,
                nama_jurnal: $v('form-nama_jurnal').value || null,
                peringkat: $v('form-peringkat').value || null,
                link_doi: $v('form-link_doi').value || null,
                bukti_kegiatan: $v('form-bukti_kegiatan').value,
                tahun: $v('form-tahun').value,
            }),
        });
    </script>
</body>

</html>
