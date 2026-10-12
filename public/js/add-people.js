/* ==========================================================
   Tambah Mahasiswa / Tambah Dosen (Data Master)
   - Role dikunci sesuai tombol (tidak ada input role).
   - Atas: isi manual (opsional). Bawah: tabel NIM/NIDN dari data LPPM
     yang belum punya akun (scroll di dalam tabel, bisa pilih banyak).
   - Simpan -> POST /data-master/users/people, lalu memicu event
     "dm:people-added" agar tabel pengguna dimuat ulang.
   ========================================================== */
(function () {
    'use strict';

    const $ = (id) => document.getElementById(id);
    const modal = $('apModal');
    if (!modal) return;

    const backdrop = $('apBackdrop');
    const form = $('apForm');
    const rowsEl = $('apRows');
    const emptyEl = $('apEmpty');
    const errorEl = $('apError');
    const submitBtn = $('apSubmitBtn');
    const checkAll = $('apCheckAll');
    const search = $('apSearch');

    const STATUS = JSON.parse($('apInit')?.textContent || '{}');
    const YEAR = new Date().getFullYear();

    const CFG = {
        mahasiswa: {
            title: 'Tambah Mahasiswa',
            idLabel: 'NIM',
            idPlaceholder: 'Contoh: 222011005',
            pendingTitle: 'NIM dari LPPM yang belum punya akun',
            hasAngkatan: true,
            empty: 'Tidak ada NIM yang menunggu akun. NIM muncul di sini setelah data LPPM Mahasiswa diimpor dengan NIM yang belum terdaftar.',
            note: 'Nama terisi otomatis dari kolom penulis data LPPM (ubah jika perlu; kosong = isi sendiri). Password awal = nama depan + angkatan (contoh: budi2022); angkatan kosong memakai tahun ' + YEAR + '.',
        },
        dosen: {
            title: 'Tambah Dosen',
            idLabel: 'NIDN',
            idPlaceholder: 'Contoh: 0712048901',
            pendingTitle: 'NIDN dari LPPM yang belum punya akun',
            hasAngkatan: false,
            empty: 'Tidak ada NIDN yang menunggu akun. NIDN muncul di sini setelah data LPPM Dosen diimpor dengan NIDN yang belum terdaftar.',
            note: 'Nama terisi otomatis dari kolom penulis data LPPM (ubah jika perlu; kosong = isi sendiri). Password awal = nama depan + tahun ' + YEAR + ' (dosen tidak punya angkatan), contoh: budi' + YEAR + '.',
        },
    };

    let type = null;
    let busy = false;
    // id -> { checked, name, angkatan }  (tetap tersimpan saat tabel difilter)
    let state = new Map();

    const csrf = () => (window.SIDA?.util?.csrfToken ? SIDA.util.csrfToken() : document.querySelector('meta[name="csrf-token"]')?.content || '');
    const toast = (t, title, message) => window.Toast && Toast.show(title ? { type: t, title, message } : { type: t, message });

    function showError(msg) {
        errorEl.textContent = msg;
        errorEl.hidden = false;
    }
    function clearError() {
        errorEl.hidden = true;
    }

    // ---------- buka / tutup ----------
    function open(kind) {
        type = kind;
        const c = CFG[kind];
        state = new Map();
        form.reset();
        search.value = '';
        clearError();

        $('apTitle').textContent = c.title;
        $('apIdLabel').textContent = c.idLabel;
        $('ap-identifier').placeholder = c.idPlaceholder;
        $('apPendingTitle').textContent = c.pendingTitle;
        $('apThId').textContent = c.idLabel;
        $('apNote').textContent = c.note;
        $('apFieldAngkatan').hidden = !c.hasAngkatan;
        modal.classList.toggle('ap-no-angkatan', !c.hasAngkatan);

        const status = $('ap-status');
        status.innerHTML = '';
        Object.entries(STATUS[kind] || {}).forEach(([value, label]) => {
            const o = document.createElement('option');
            o.value = value;
            o.textContent = label;
            status.appendChild(o);
        });
        status.value = 'aktif';

        rowsEl.innerHTML = '';
        emptyEl.hidden = false;
        emptyEl.textContent = 'Memuat data...';
        updateSummary();

        backdrop.classList.add('is-active');
        modal.classList.add('is-active');
        modal.setAttribute('aria-hidden', 'false');
        setTimeout(() => $('ap-name').focus(), 60);

        loadCalon(kind);
    }

    function close() {
        if (busy) return;
        backdrop.classList.remove('is-active');
        modal.classList.remove('is-active');
        modal.setAttribute('aria-hidden', 'true');
    }

    // ---------- data tabel ----------
    async function loadCalon(kind) {
        try {
            const res = await fetch(`/data-master/users/calon/${kind}`, { headers: { Accept: 'application/json' } });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Gagal memuat data.');
            if (type !== kind) return; // modal sudah dibuka untuk jenis lain
            renderRows(json.data || []);
        } catch (e) {
            emptyEl.hidden = false;
            emptyEl.textContent = 'Gagal memuat daftar. Tutup lalu buka lagi modal ini.';
        }
    }

    function renderRows(list) {
        rowsEl.innerHTML = '';
        const c = CFG[type];
        emptyEl.hidden = list.length > 0;
        if (!list.length) emptyEl.textContent = c.empty;

        list.forEach((item) => {
            state.set(item.id, { checked: false, name: item.name || '', angkatan: item.angkatan || '' });

            const tr = document.createElement('tr');
            tr.dataset.id = item.id;
            tr.dataset.nim = String(item.identifier || '').toLowerCase();

            const tdCheck = document.createElement('td');
            tdCheck.className = 'ap-col-check';
            const cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.setAttribute('aria-label', 'Pilih ' + item.identifier);
            tdCheck.appendChild(cb);

            const tdId = document.createElement('td');
            const code = document.createElement('span');
            code.className = 'nim-code';
            code.textContent = item.identifier;
            tdId.appendChild(code);

            const tdName = document.createElement('td');
            const name = document.createElement('input');
            name.type = 'text';
            name.placeholder = item.hint ? 'Penulis: ' + item.hint : 'Nama lengkap';
            name.title = item.hint ? 'Ada beberapa penulis di data LPPM: ' + item.hint + '. Ketik nama pemilik NIM ini.' : '';
            name.autocomplete = 'off';
            name.value = item.name || ''; // otomatis dari kolom penulis di data LPPM
            tdName.appendChild(name);

            const tdAng = document.createElement('td');
            tdAng.className = 'ap-col-angkatan';
            const ang = document.createElement('input');
            ang.type = 'number';
            ang.min = 1990;
            ang.max = 2100;
            ang.placeholder = String(YEAR);
            ang.value = item.angkatan || '';
            tdAng.appendChild(ang);

            const tdRefs = document.createElement('td');
            tdRefs.className = 'ap-col-refs';
            tdRefs.textContent = item.refs + ' data';

            tr.append(tdCheck, tdId, tdName, tdAng, tdRefs);
            rowsEl.appendChild(tr);

            const s = state.get(item.id);
            const sync = () => {
                tr.classList.toggle('is-checked', s.checked);
                cb.checked = s.checked;
                updateSummary();
            };
            cb.addEventListener('change', () => { s.checked = cb.checked; sync(); });
            name.addEventListener('input', () => {
                s.name = name.value;
                name.classList.remove('is-invalid');
                // mengetik nama = berarti baris ini mau disimpan
                if (!s.checked && name.value.trim()) { s.checked = true; sync(); }
                if (search.value) applyFilter();
            });
            ang.addEventListener('input', () => { s.angkatan = ang.value; });
        });

        updateSummary();
    }

    function applyFilter() {
        const q = search.value.trim().toLowerCase();
        rowsEl.querySelectorAll('tr').forEach((tr) => {
            const s = state.get(Number(tr.dataset.id));
            const hay = tr.dataset.nim + ' ' + (s?.name || '').toLowerCase();
            tr.hidden = q !== '' && !hay.includes(q);
        });
        updateSummary();
    }

    function visibleRows() {
        return [...rowsEl.querySelectorAll('tr')].filter((tr) => !tr.hidden);
    }

    function manualFilled() {
        return $('ap-name').value.trim() !== '' || $('ap-identifier').value.trim() !== '';
    }

    function selectedCount() {
        let n = 0;
        state.forEach((s) => { if (s.checked) n++; });
        return n;
    }

    function updateSummary() {
        const vis = visibleRows();
        const checkedVis = vis.filter((tr) => state.get(Number(tr.dataset.id))?.checked).length;
        checkAll.checked = vis.length > 0 && checkedVis === vis.length;
        checkAll.indeterminate = checkedVis > 0 && checkedVis < vis.length;
        checkAll.disabled = vis.length === 0;

        const total = selectedCount() + (manualFilled() ? 1 : 0);
        $('apSubmitLabel').textContent = total > 0 ? `Simpan (${total})` : 'Simpan';
    }

    checkAll.addEventListener('change', () => {
        visibleRows().forEach((tr) => {
            const s = state.get(Number(tr.dataset.id));
            s.checked = checkAll.checked;
            tr.classList.toggle('is-checked', s.checked);
            tr.querySelector('input[type="checkbox"]').checked = s.checked;
        });
        updateSummary();
    });
    search.addEventListener('input', applyFilter);
    ['ap-name', 'ap-identifier'].forEach((id) => $(id).addEventListener('input', updateSummary));

    // ---------- simpan ----------
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (busy) return;
        clearError();
        const c = CFG[type];

        const name = $('ap-name').value.trim();
        const identifier = $('ap-identifier').value.trim();
        const hasManual = name !== '' || identifier !== '';

        if (hasManual && !name) return showError('Nama lengkap wajib diisi untuk isian manual.');
        if (hasManual && !identifier) return showError(`${c.idLabel} wajib diisi untuk isian manual.`);

        const angkatanManual = $('ap-angkatan').value.trim();
        if (hasManual && c.hasAngkatan && angkatanManual && !/^\d{4}$/.test(angkatanManual)) {
            return showError('Angkatan harus berupa tahun 4 digit (contoh: 2022).');
        }

        const selected = [];
        let firstInvalid = null;
        rowsEl.querySelectorAll('tr').forEach((tr) => {
            const id = Number(tr.dataset.id);
            const s = state.get(id);
            if (!s || !s.checked) return;
            const nameInput = tr.querySelector('input[type="text"]');
            if (!s.name.trim()) {
                nameInput.classList.add('is-invalid');
                firstInvalid = firstInvalid || { tr, nameInput };
                return;
            }
            const ang = String(s.angkatan || '').trim();
            if (c.hasAngkatan && ang && !/^\d{4}$/.test(ang)) {
                tr.querySelector('input[type="number"]').classList.add('is-invalid');
                firstInvalid = firstInvalid || { tr, nameInput };
                return;
            }
            selected.push({ id, name: s.name.trim(), angkatan: c.hasAngkatan && ang ? Number(ang) : null });
        });

        if (firstInvalid) {
            firstInvalid.tr.scrollIntoView({ block: 'nearest' });
            firstInvalid.nameInput.focus();
            return showError('Lengkapi nama (dan angkatan 4 digit jika diisi) untuk setiap baris yang dipilih.');
        }
        if (!hasManual && selected.length === 0) {
            return showError(`Isi data manual atau pilih minimal satu ${c.idLabel} dari tabel.`);
        }

        const payload = {
            role: type,
            manual: hasManual ? {
                name,
                identifier,
                email: $('ap-email').value.trim() || null,
                angkatan: c.hasAngkatan && angkatanManual ? Number(angkatanManual) : null,
                status: $('ap-status').value || 'aktif',
                password: $('ap-password').value || null,
            } : null,
            selected,
        };

        busy = true;
        submitBtn.disabled = true;
        try {
            const res = await fetch('/data-master/users/people', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                },
                body: JSON.stringify(payload),
            });
            const json = await res.json().catch(() => ({}));

            if (!res.ok) {
                const first = json.errors ? Object.values(json.errors)[0][0] : (json.message || 'Terjadi kesalahan, coba lagi.');
                showError(first);
                toast('error', 'Gagal menyimpan', first);
                return;
            }

            busy = false;
            close();
            toast('success', null, json.message || 'Akun berhasil ditambahkan.');
            window.dispatchEvent(new CustomEvent('dm:people-added', { detail: { role: type, created: json.created } }));
        } catch (err) {
            showError('Gagal terhubung ke server.');
            toast('error', 'Gagal menyimpan', 'Tidak bisa terhubung ke server. Coba lagi.');
        } finally {
            busy = false;
            submitBtn.disabled = false;
        }
    });

    // ---------- pemicu & penutup ----------
    document.querySelectorAll('[data-ap-open]').forEach((btn) => {
        btn.addEventListener('click', () => open(btn.dataset.apOpen));
    });
    $('apCloseBtn').addEventListener('click', close);
    $('apCancelBtn').addEventListener('click', close);
    backdrop.addEventListener('mousedown', (e) => { if (e.target === backdrop) close(); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('is-active')) close();
    });
})();
