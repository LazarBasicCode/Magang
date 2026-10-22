{{-- Fallback untuk role lain yang belum punya dashboard. --}}
<div class="dash-welcome">
    <p class="dash-welcome-title">Halo, {{ $user->name }} 👋</p>
    <p class="dash-welcome-sub">Dashboard ringkasan untuk role "{{ $user->role }}" belum tersedia &mdash; silakan pilih menu di sidebar.</p>
</div>
