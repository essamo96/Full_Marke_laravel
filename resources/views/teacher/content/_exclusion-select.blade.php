{{-- Searchable (select2) student picker limited to the teacher's own groups. --}}
<select name="excluded_student_ids[]" id="{{ $id }}" class="form-select student-exclusion-select"
        data-control="select2" data-placeholder="ابحث عن طالب لاستثنائه..." multiple="multiple">
  @foreach($subjectStudents as $st)
    <option value="{{ $st['id'] }}" data-group-ids="{{ implode(',', $st['group_ids']) }}">{{ $st['name'] }}</option>
  @endforeach
</select>
