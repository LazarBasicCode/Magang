{{-- Script bersama tampilan mahasiswa. Letakkan sebelum <script> khusus halaman (MhsView.init). --}}
<script src="{{ asset('js/script.js') }}"></script>
<script src="{{ asset('js/pager.js') }}?v={{ @filemtime(public_path('js/pager.js')) }}"></script>
<script src="{{ asset('js/delete-confirm.js') }}?v={{ @filemtime(public_path('js/delete-confirm.js')) }}"></script>
<script src="{{ asset('js/toast.js') }}?v={{ @filemtime(public_path('js/toast.js')) }}"></script>
<script src="{{ asset('js/mahasiswa-view.js') }}?v={{ @filemtime(public_path('js/mahasiswa-view.js')) }}"></script>
<script src="{{ asset('js/profile-account.js') }}?v={{ @filemtime(public_path('js/profile-account.js')) }}"></script>
<script src="{{ asset('js/notifications.js') }}"></script>
