@extends('layouts.teacher')

@section('title', 'My Profile | FULL MARK ACADEMY')
@section('page_title_en', 'My Profile')
@section('page_title_ar', 'الملف الشخصي')

@section('content')
  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="My Profile" data-ar="الملف الشخصي">الملف الشخصي</h1>

  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
  @endif

  <div class="glass-panel rounded-4 p-4 mb-4">
    <form method="POST" action="{{ route('teacher.profile.update') }}" enctype="multipart/form-data">
      @csrf
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label" data-en="Name" data-ar="الاسم">الاسم</label>
          <input type="text" name="name" value="{{ old('name', $teacher->name) }}" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label" data-en="Phone" data-ar="الهاتف">الهاتف</label>
          <input type="text" name="phone" value="{{ old('phone', $teacher->phone) }}" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label" data-en="Photo" data-ar="الصورة">الصورة</label>
          <input type="file" name="photo" class="form-control" accept="image/*">
        </div>
      </div>
      <button type="submit" class="btn btn-luxury mt-4" data-en="Save Changes" data-ar="حفظ التغييرات">حفظ التغييرات</button>
    </form>
  </div>

  <div class="glass-panel rounded-4 p-4">
    <h5 class="fw-bold mb-3" style="color: var(--text-primary);" data-en="Change Password" data-ar="تغيير كلمة المرور">تغيير كلمة المرور</h5>
    <form method="POST" action="{{ route('teacher.profile.password') }}">
      @csrf
      <div class="row g-3">
        <div class="col-md-4">
          <input type="password" name="current_password" class="form-control" placeholder="كلمة المرور الحالية" required>
        </div>
        <div class="col-md-4">
          <input type="password" name="password" class="form-control" placeholder="كلمة المرور الجديدة" required>
        </div>
        <div class="col-md-4">
          <input type="password" name="password_confirmation" class="form-control" placeholder="تأكيد كلمة المرور" required>
        </div>
      </div>
      <button type="submit" class="btn btn-luxury mt-3" data-en="Update Password" data-ar="تحديث كلمة المرور">تحديث كلمة المرور</button>
    </form>
  </div>
@endsection
