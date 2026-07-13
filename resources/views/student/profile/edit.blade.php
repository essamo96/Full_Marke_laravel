@extends('layouts.student')

@section('title', 'My Profile | FULL MARK ACADEMY')
@section('page_title_en', 'My Profile')
@section('page_title_ar', 'الملف الشخصي')

@section('content')
  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="My Profile" data-ar="الملف الشخصي">My Profile</h1>

  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
  @endif

  <div class="glass-panel rounded-4 p-4 mb-4">
    <form method="POST" action="{{ route('student.profile.update') }}" enctype="multipart/form-data">
      @csrf
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label" data-en="Full Name (English)" data-ar="الاسم الكامل (إنجليزي)">Full Name (English)</label>
          <input type="text" name="full_name_en" value="{{ old('full_name_en', $student->full_name_en) }}" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label" data-en="Full Name (Arabic)" data-ar="الاسم الكامل (عربي)">Full Name (Arabic)</label>
          <input type="text" name="full_name_ar" value="{{ old('full_name_ar', $student->full_name_ar) }}" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label" data-en="Phone" data-ar="الهاتف">Phone</label>
          <input type="text" name="phone" value="{{ old('phone', $student->phone) }}" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label" data-en="Photo" data-ar="الصورة">Photo</label>
          <input type="file" name="image" class="form-control" accept="image/*">
        </div>
        <div class="col-12">
          <label class="form-label" data-en="Address" data-ar="العنوان">Address</label>
          <textarea name="address" class="form-control" rows="2">{{ old('address', $student->address) }}</textarea>
        </div>
      </div>
      <button type="submit" class="btn btn-luxury mt-4" data-en="Save Changes" data-ar="حفظ التغييرات">Save Changes</button>
    </form>
  </div>

  <div class="glass-panel rounded-4 p-4">
    <h5 class="fw-bold mb-3" style="color: var(--text-primary);" data-en="Change Password" data-ar="تغيير كلمة المرور">Change Password</h5>
    <form method="POST" action="{{ route('student.profile.password') }}">
      @csrf
      <div class="row g-3">
        <div class="col-md-4">
          <input type="password" name="current_password" class="form-control" placeholder="{{ __('app.password') }}" required>
        </div>
        <div class="col-md-4">
          <input type="password" name="password" class="form-control" placeholder="New Password" required>
        </div>
        <div class="col-md-4">
          <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" required>
        </div>
      </div>
      <button type="submit" class="btn btn-luxury mt-3" data-en="Update Password" data-ar="تحديث كلمة المرور">Update Password</button>
    </form>
  </div>
@endsection
