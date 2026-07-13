@extends('layouts.student')

@section('title', 'Syncing Cart... | FULL MARK ACADEMY')

@section('content')
  <div class="d-flex flex-column align-items-center justify-content-center py-5 text-center">
    <div class="spinner-border text-gold mb-3" role="status" style="width: 3rem; height: 3rem;">
      <span class="visually-hidden">Loading...</span>
    </div>
    <h4 style="color: var(--text-primary);" data-en="Syncing your cart, please wait..." data-ar="جاري تحضير السلة والتحويل لإتمام الدفع...">جاري تحضير السلة والتحويل لإتمام الدفع...</h4>
  </div>
@endsection

@push('scripts')
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const STORAGE_KEY = 'full-mark-academy-cart';
      const rawData = localStorage.getItem(STORAGE_KEY);
      const items = rawData ? JSON.parse(rawData) : [];

      if (items && items.length > 0) {
        $.ajax({
          url: '/student/cart/sync',
          method: 'POST',
          data: {
            _token: '{{ csrf_token() }}',
            items: items.map(i => ({ subject_id: i.id, group_id: i.group_id || null }))
          },
          success: function(res) {
            if (res.status === 'success') {
              localStorage.removeItem(STORAGE_KEY);
              window.location.reload();
            } else {
              window.location.href = '{{ route("site.home") }}';
            }
          },
          error: function() {
            window.location.href = '{{ route("site.home") }}';
          }
        });
      } else {
        window.location.href = '{{ route("site.home") }}';
      }
    });
  </script>
@endpush
