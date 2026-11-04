{{--
    Pemilih tanggal untuk form tampilan mandiri (markup & perilaku dari public/js/script.js: SIDA.datepicker).
    Parameter: $id (mis. 'tanggal_mulai' -> #form-tanggal_mulai & #dp-tanggal_mulai), $label
--}}
<div class="field">
    <label class="field-label">{{ $label }}</label>
    <div class="datepicker" data-datepicker id="dp-{{ $id }}">
        <input type="hidden" id="form-{{ $id }}" value="" required />
        <button type="button" class="datepicker-trigger">
            <span class="material-symbols-outlined dp-icon">calendar_month</span>
            <span class="datepicker-value">Pilih tanggal</span>
        </button>
        <div class="datepicker-panel">
            <div class="dp-header">
                <button type="button" class="dp-nav dp-prev" aria-label="Bulan sebelumnya"><span class="material-symbols-outlined">chevron_left</span></button>
                <span class="dp-month-label"></span>
                <button type="button" class="dp-nav dp-next" aria-label="Bulan berikutnya"><span class="material-symbols-outlined">chevron_right</span></button>
            </div>
            <div class="dp-weekdays"><span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span></div>
            <div class="dp-days"></div>
            <div class="dp-footer">
                <button type="button" class="dp-today-btn">Hari ini</button>
                <button type="button" class="dp-clear-btn">Bersihkan</button>
            </div>
        </div>
    </div>
</div>
