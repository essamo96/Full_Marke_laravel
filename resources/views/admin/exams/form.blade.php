@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <style>
        .question-card {
            border: 1px solid #e4e6ef;
            transition: all 0.3s;
        }
        .question-card:hover {
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .drag-handle {
            cursor: grab;
        }
        .drag-handle:active {
            cursor: grabbing;
        }
        .ck-editor__editable_inline {
            min-height: 150px;
        }
        .exam-pick-box {
            border: 1px solid var(--separator-color, #e4e6ef);
            border-radius: .475rem;
            max-height: 220px;
            overflow-y: auto;
            padding: .35rem .75rem;
        }
        .exam-pick-box .form-check {
            margin: .4rem 0;
            display: flex;
            align-items: center;
            gap: .5rem;
            padding-inline-start: 0;
        }
        .exam-pick-box .form-check-input {
            float: none;
            margin: 0;
            flex-shrink: 0;
        }
        .exam-pick-box .form-check-label {
            cursor: pointer;
            flex: 1;
        }
        .exam-pick-box .pick-sub {
            font-size: .75rem;
            opacity: .65;
        }
    </style>
@endpush

<div class="row g-5">
    <!-- Left Column: Exam Details -->
    <div class="col-lg-4">
        <div class="card card-flush mb-5">
            <div class="card-header">
                <div class="card-title"><h2>بيانات الامتحان الأساسية</h2></div>
            </div>
            <div class="card-body pt-0">
                <div class="mb-5">
                    <label class="required form-label">عنوان الامتحان</label>
                    <input type="text" name="title" class="form-control mb-2" placeholder="امتحان منتصف الفصل..." value="{{ old('title', $exam->title ?? '') }}" required />
                </div>
                
                <div class="mb-5">
                    <label class="form-label">الوصف (اختياري)</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $exam->description ?? '') }}</textarea>
                </div>

                <div class="mb-5">
                    <label class="required form-label">المادة الدراسية</label>
                    <select name="subject_id" id="subject_id" class="form-select" data-control="select2" data-placeholder="اختر المادة" required>
                        <option></option>
                        @foreach($subjects as $subject)
                            {{-- value stays the raw id because it is submitted as subject_id;
                                 data-key carries the encrypted route key for the groups AJAX URL. --}}
                            <option value="{{ $subject->id }}" data-key="{{ $subject->getRouteKey() }}" data-groups="{{ json_encode($subject->groups) }}" {{ old('subject_id', $exam->subject_id ?? ($preselectedSubjectId ?? '')) == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="required form-label mb-0">المجموعات المستهدفة</label>
                        <button type="button" class="btn btn-sm btn-link p-0" id="toggleAllGroups">تحديد الكل</button>
                    </div>
                    <div id="groupsBox" class="exam-pick-box" role="group" aria-label="المجموعات المستهدفة">
                        <div class="text-muted fs-7 p-3">اختر المادة أولاً لعرض مجموعاتها.</div>
                    </div>
                    <div class="text-muted fs-7 mt-1" id="groupsSummary">يمكنك اختيار أكثر من مجموعة، وسيُنشر الامتحان لها دفعة واحدة ويصل الإشعار اللحظي لجميع طلابها.</div>
                </div>

                <div class="mb-5">
                    <label class="form-label">الطلاب المستثنون (لن يتمكنوا من التقديم ولن يصلهم إشعار)</label>
                    {{-- Always posted, so un-ticking every student really clears the exclusions. --}}
                    <input type="hidden" name="exclusions_present" value="1">
                    <input type="search" id="excludedSearch" class="form-control form-control-sm mb-2" placeholder="ابحث باسم الطالب..." autocomplete="off">
                    <div id="excludedBox" class="exam-pick-box" role="group" aria-label="الطلاب المستثنون">
                        <div class="text-muted fs-7 p-3">اختر مجموعة واحدة على الأقل لعرض طلابها.</div>
                    </div>
                    <div class="text-muted fs-7 mt-1" id="excludedSummary"></div>
                </div>

                @if(!isset($exam))
                <div class="alert alert-primary d-flex align-items-center p-4 mb-5 rounded border-primary border-dashed">
                    <i class="ki-duotone ki-information-5 fs-2hx text-primary me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-primary">تنبيه: التواريخ الافتراضية</h4>
                        <span>تم ضبط وقت البدء الافتراضي ليكون الوقت الحالي، ووقت الانتهاء ليكون بعد نصف ساعة من وقت البدء. يُرجى تعديلها بما يتناسب مع موعد الامتحان.</span>
                    </div>
                </div>
                @endif

                <div class="mb-5">
                    <label class="form-label">وقت البدء</label>
                    <input class="form-control flatpickr" name="start_time" placeholder="اختر وقت البدء" value="{{ old('start_time', isset($exam->start_time) ? $exam->start_time->format('Y-m-d H:i') : now()->format('Y-m-d H:i')) }}" />
                </div>

                <div class="mb-5">
                    <label class="form-label">وقت الانتهاء</label>
                    <input class="form-control flatpickr" name="end_time" placeholder="اختر وقت الانتهاء" value="{{ old('end_time', isset($exam->end_time) ? $exam->end_time->format('Y-m-d H:i') : now()->addMinutes(30)->format('Y-m-d H:i')) }}" />
                </div>

                <div class="mb-5">
                    <label class="form-label">مدة الامتحان (بالدقائق)</label>
                    <input type="number" name="duration_minutes" class="form-control mb-2" placeholder="مثال: 60" value="{{ old('duration_minutes', $exam->duration_minutes ?? '') }}" />
                    <div class="text-muted fs-7">اتركه فارغاً للامتحانات المفتوحة.</div>
                </div>

                <div class="mb-5">
                    <label class="required form-label">حالة الامتحان</label>
                    <select name="status" class="form-select" required>
                        <option value="draft" {{ old('status', $exam->status ?? '') == 'draft' ? 'selected' : '' }}>مسودة (غير ظاهر)</option>
                        <option value="published" {{ old('status', $exam->status ?? '') == 'published' ? 'selected' : '' }}>منشور (متاح حسب الموعد)</option>
                        <option value="completed" {{ old('status', $exam->status ?? '') == 'completed' ? 'selected' : '' }}>مكتمل (مغلق)</option>
                    </select>
                </div>

                <div class="mb-5">
                    <label class="required form-label">الفئة المستهدفة</label>
                    <select name="audience" class="form-select" required>
                        <option value="students" {{ old('audience', $exam->audience ?? 'students') == 'students' ? 'selected' : '' }}>طلاب المنصة فقط</option>
                        <option value="guests" {{ old('audience', $exam->audience ?? '') == 'guests' ? 'selected' : '' }}>ضيوف فقط (غير مسجلين)</option>
                        <option value="both" {{ old('audience', $exam->audience ?? '') == 'both' ? 'selected' : '' }}>الطلاب المسجلين والضيوف معاً</option>
                    </select>
                    <div class="text-muted fs-7">عند اختيار "ضيوف" سيُطلب من غير المسجلين إدخال الاسم ورقم الجوال والإيميل قبل بدء الامتحان.</div>
                </div>

                <div class="mb-5">
                    <label class="form-label fw-bold">سماح مراجعة الإجابات للطلاب</label>
                    <div class="form-check form-switch form-check-custom form-check-solid">
                        <input type="hidden" name="allow_student_review" value="0">
                        <input class="form-check-input" type="checkbox" value="1" name="allow_student_review" id="allow_student_review"
                            {{ old('allow_student_review', $exam->allow_student_review ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="allow_student_review">
                            تمكين الطالب من معاينة أسئلته والإجابات الصحيحة/الخاطئة وتحميل PDF بعد التسليم
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Questions Builder -->
    <div class="col-lg-8">
        <div class="card card-flush mb-5">
            <div class="card-header align-items-center">
                <div class="card-title"><h2>بناء الأسئلة</h2></div>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-sm btn-light-primary" id="addQuestionBtn">
                        <i class="ki-duotone ki-plus fs-2"></i>إضافة سؤال
                    </button>
                </div>
            </div>
            <div class="card-body" id="questionsContainer">
                <!-- Alpine component for Questions -->
                <div x-data="questionsBuilder()">
                    <div id="sortable-questions">
                        <template x-for="(q, qIndex) in questions" :key="q.id">
                            <div class="card question-card mb-5" :data-id="q.id">
                                <div class="card-header bg-light-secondary min-h-40px px-4 py-2 align-items-center">
                                    <div class="card-title m-0 drag-handle">
                                        <i class="ki-duotone ki-burger-menu fs-2 me-2 text-gray-500"></i>
                                        <span class="fw-bold fs-6">سؤال #<span x-text="qIndex + 1"></span></span>
                                    </div>
                                    <div class="card-toolbar">
                                        <button type="button" class="btn btn-icon btn-sm btn-light-danger" @click="removeQuestion(qIndex)">
                                            <i class="ki-duotone ki-trash fs-3"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <input type="hidden" :name="`questions[${qIndex}][id]`" x-model="q.id">
                                    <input type="hidden" :name="`questions[${qIndex}][sort_order]`" :value="qIndex">
                                    
                                    <div class="row mb-4">
                                        <div class="col-md-8">
                                            <label class="form-label required">نوع السؤال</label>
                                            <select :name="`questions[${qIndex}][type]`" class="form-select form-select-sm" x-model="q.type" @change="handleTypeChange(qIndex)">
                                                <option value="multiple_choice">اختيار من متعدد</option>
                                                <option value="true_false">صح أو خطأ</option>
                                                <option value="essay">مقال قصير</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label required">النقاط (العلامة)</label>
                                            <input type="number" :name="`questions[${qIndex}][points]`" class="form-control form-control-sm" x-model="q.points" min="1" required>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label required">نص السؤال</label>
                                        <textarea :name="`questions[${qIndex}][content]`" class="form-control editor-textarea" x-init="$nextTick(() => { $el.value = q.content; initEditor($el, q) }); return () => { if ($el.ckeditorInstance) { $el.ckeditorInstance.destroy(); delete $el.ckeditorInstance; $el.removeAttribute('data-editor-init'); } }"></textarea>
                                    </div>

                                    <!-- Options Area -->
                                    <div x-show="q.type !== 'essay'" class="mt-5 border-top pt-4">
                                        <label class="form-label fw-bold">الخيارات</label>
                                        
                                        <div class="options-list">
                                            <template x-for="(opt, oIndex) in q.options" :key="opt.id">
                                                <div class="d-flex align-items-center mb-2 gap-3">
                                                    <div class="form-check form-check-custom form-check-solid form-check-success">
                                                        <input class="form-check-input" type="radio" 
                                                            :name="`questions[${qIndex}][correct_option]`" 
                                                            :value="oIndex" 
                                                            :checked="opt.is_correct"
                                                            @change="setCorrectOption(qIndex, oIndex)"
                                                            required>
                                                    </div>
                                                    <input type="hidden" :name="`questions[${qIndex}][options][${oIndex}][id]`" x-model="opt.id">
                                                    <input type="hidden" :name="`questions[${qIndex}][options][${oIndex}][is_correct]`" :value="opt.is_correct ? 1 : 0">
                                                    
                                                    <!-- Use input text for multiple choice, readonly for true/false -->
                                                    <input type="text" :name="`questions[${qIndex}][options][${oIndex}][option_text]`" 
                                                        class="form-control form-control-sm" 
                                                        x-model="opt.option_text" 
                                                        :readonly="q.type === 'true_false'"
                                                        required placeholder="نص الخيار">
                                                    
                                                    <button type="button" class="btn btn-icon btn-sm btn-light-danger" 
                                                            @click="removeOption(qIndex, oIndex)" 
                                                            x-show="q.type === 'multiple_choice' && q.options.length > 2">
                                                        <i class="ki-duotone ki-cross fs-2"></i>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>

                                        <div class="mt-3" x-show="q.type === 'multiple_choice'">
                                            <button type="button" class="btn btn-sm btn-light-primary" @click="addOption(qIndex)">
                                                <i class="ki-duotone ki-plus fs-3"></i> إضافة خيار
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="questions.length === 0" class="text-center py-10 text-muted">
                        لا يوجد أسئلة حالياً. اضغط على زر "إضافة سؤال" للبدء.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Load CKEditor 5 (vendored locally so a flaky/blocked CDN never leaves questions
     showing raw, un-rendered HTML tags in a fallback plain textarea). The Arabic
     translation must load separately — without it, requesting language: 'ar' below
     can prevent the editor from initializing at all. -->
<script src="{{ asset('assets/vendor/ckeditor5/ckeditor.js') }}"></script>
<script src="{{ asset('assets/vendor/ckeditor5/translations-ar.js') }}"></script>
<!-- Load SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<!-- Load Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/ar.js"></script>
<!-- Load Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
    // Flatpickr
    flatpickr(".flatpickr", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        locale: "ar"
    });

    // Subject -> groups (multi-select) -> students (exclusion picker)
    const subjectSelect = document.getElementById('subject_id');
    const groupsBox = document.getElementById('groupsBox');
    const excludedBox = document.getElementById('excludedBox');
    const excludedSearch = document.getElementById('excludedSearch');
    const groupsSummary = document.getElementById('groupsSummary');
    const excludedSummary = document.getElementById('excludedSummary');
    const toggleAllGroups = document.getElementById('toggleAllGroups');
    const groupsStudentsUrl = `{{ $groupsStudentsAjaxUrl ?? route('exams.ajax.groups-students') }}`;

    // Selections are kept in these sets (not read back from the DOM) so they survive
    // re-rendering the lists when the subject / groups change.
    const selectedGroupIds = new Set(@json(collect(old('group_ids', isset($exam) ? $exam->allGroupIds() : (($preselectedGroupId ?? null) ? [$preselectedGroupId] : [])))->map(fn ($id) => (string) $id)->values()));
    const excludedIds = new Set(@json(collect(old('excluded_student_ids', isset($exam) ? $exam->excludedStudentIds() : []))->map(fn ($id) => (string) $id)->values()));
    let subjectLoadedOnce = false;
    let studentsRequest = 0;

    // Arabic number agreement: واحدة / مجموعتان / ٣ مجموعات / ١١ مجموعة
    const countLabel = (n, one, two, few, many) => n === 1 ? one : n === 2 ? two : n <= 10 ? `${n} ${few}` : `${n} ${many}`;
    const groupsLabel = (n) => countLabel(n, 'مجموعة واحدة', 'مجموعتان', 'مجموعات', 'مجموعة');
    const studentsLabel = (n) => countLabel(n, 'طالب واحد', 'طالبان', 'طلاب', 'طالباً');

    const escapeHtml = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    function groupCheckboxes() {
        return Array.from(groupsBox.querySelectorAll('input[name="group_ids[]"]'));
    }

    function renderGroups(groups) {
        if (!groups.length) {
            groupsBox.innerHTML = '<div class="text-muted fs-7 p-3">لا توجد مجموعات لهذه المادة.</div>';
            return;
        }
        groupsBox.innerHTML = groups.map((g) => `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="group_ids[]" value="${escapeHtml(g.id)}" id="grp_${escapeHtml(g.id)}" ${selectedGroupIds.has(String(g.id)) ? 'checked' : ''}>
                <label class="form-check-label" for="grp_${escapeHtml(g.id)}">${escapeHtml(g.name)}</label>
            </div>`).join('');
    }

    function syncGroupSelection() {
        selectedGroupIds.clear();
        groupCheckboxes().filter((c) => c.checked).forEach((c) => selectedGroupIds.add(String(c.value)));

        const all = groupCheckboxes();
        toggleAllGroups.textContent = all.length && all.every((c) => c.checked) ? 'إلغاء التحديد' : 'تحديد الكل';
        groupsSummary.textContent = selectedGroupIds.size
            ? `تم اختيار ${groupsLabel(selectedGroupIds.size)} — سيصل الإشعار اللحظي لجميع طلابها عدا المستثنين.`
            : 'اختر مجموعة واحدة على الأقل.';
    }

    function loadGroups() {
        const subjectKey = subjectSelect.selectedOptions[0]?.dataset.key;

        // A different subject has different groups: drop the previous choice (but keep
        // the saved one on the very first load of an existing exam).
        if (subjectLoadedOnce) {
            selectedGroupIds.clear();
        }
        subjectLoadedOnce = true;

        if (!subjectKey) {
            groupsBox.innerHTML = '<div class="text-muted fs-7 p-3">اختر المادة أولاً لعرض مجموعاتها.</div>';
            syncGroupSelection();
            loadStudents();
            return;
        }

        // The route binds the Subject model, whose route key is encrypted, so
        // the URL must use data-key rather than the raw id in option.value.
        fetch(`{{ $subjectGroupsAjaxBase ?? '/admin/exams/ajax/subject' }}/${subjectKey}/groups`, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(groups => {
                renderGroups(groups);
                syncGroupSelection();
                loadStudents();
            })
            .catch(() => {
                groupsBox.innerHTML = '<div class="text-danger fs-7 p-3">تعذر تحميل المجموعات، حدّث الصفحة وحاول مجدداً.</div>';
            });
    }

    function renderStudents(students, showGroups) {
        if (!students.length) {
            excludedBox.innerHTML = '<div class="text-muted fs-7 p-3">لا يوجد طلاب مسجلون في المجموعات المختارة.</div>';
            return;
        }
        excludedBox.innerHTML = students.map((s) => {
            const name = (s.full_name_ar || s.full_name_en || '') + (s.full_name_ar && s.full_name_en ? ' - ' + s.full_name_en : '');
            const sub = showGroups && s.groups ? `<span class="pick-sub d-block">${escapeHtml(s.groups)}</span>` : '';
            return `
            <div class="form-check" data-name="${escapeHtml(name.toLowerCase())}">
                <input class="form-check-input" type="checkbox" name="excluded_student_ids[]" value="${escapeHtml(s.id)}" id="stu_${escapeHtml(s.id)}" ${excludedIds.has(String(s.id)) ? 'checked' : ''}>
                <label class="form-check-label" for="stu_${escapeHtml(s.id)}">${escapeHtml(name)}${sub}</label>
            </div>`;
        }).join('');
    }

    function syncExcludedSummary() {
        const checked = excludedBox.querySelectorAll('input[name="excluded_student_ids[]"]:checked').length;
        excludedSummary.textContent = checked ? `تم استثناء ${studentsLabel(checked)}.` : '';
    }

    function filterStudents() {
        const q = excludedSearch.value.trim().toLowerCase();
        excludedBox.querySelectorAll('.form-check').forEach((row) => {
            row.style.display = !q || (row.dataset.name || '').includes(q) ? '' : 'none';
        });
    }

    function loadStudents() {
        const groupIds = Array.from(selectedGroupIds);
        const requestId = ++studentsRequest;

        if (!groupIds.length) {
            excludedBox.innerHTML = '<div class="text-muted fs-7 p-3">اختر مجموعة واحدة على الأقل لعرض طلابها.</div>';
            syncExcludedSummary();
            return;
        }

        const qs = groupIds.map((id) => 'group_ids[]=' + encodeURIComponent(id)).join('&');
        fetch(`${groupsStudentsUrl}?${qs}`, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(students => {
                if (requestId !== studentsRequest) return; // a newer selection already replaced this one
                renderStudents(students, groupIds.length > 1);
                filterStudents();
                syncExcludedSummary();
            })
            .catch(() => {
                excludedBox.innerHTML = '<div class="text-danger fs-7 p-3">تعذر تحميل الطلاب، حدّث الصفحة وحاول مجدداً.</div>';
            });
    }

    groupsBox.addEventListener('change', () => { syncGroupSelection(); loadStudents(); });
    excludedBox.addEventListener('change', (e) => {
        if (e.target.name === 'excluded_student_ids[]') {
            e.target.checked ? excludedIds.add(String(e.target.value)) : excludedIds.delete(String(e.target.value));
            syncExcludedSummary();
        }
    });
    excludedSearch.addEventListener('input', filterStudents);
    toggleAllGroups.addEventListener('click', () => {
        const all = groupCheckboxes();
        const target = !all.every((c) => c.checked);
        all.forEach((c) => { c.checked = target; });
        syncGroupSelection();
        loadStudents();
    });
    document.getElementById('examForm').addEventListener('submit', (e) => {
        if (!selectedGroupIds.size) {
            e.preventDefault();
            alert('اختر مجموعة واحدة على الأقل لنشر الامتحان لها.');
        }
    });

    $(subjectSelect).on('change', loadGroups);

    if (subjectSelect.value) {
        loadGroups();
    }

    // Alpine component for Questions
    function questionsBuilder() {
        let initialQuestions = @json(old('questions', isset($exam) ? $exam->questions->load('options')->toArray() : []));
        
        if (initialQuestions.length === 0 && !'{{ isset($exam) }}') {
            // Give 1 default question for new exam
            initialQuestions = [{
                id: 'new_' + Date.now(),
                type: 'multiple_choice',
                content: '',
                points: 1,
                options: [
                    { id: 'opt_' + Date.now() + 1, option_text: 'الخيار الأول', is_correct: true },
                    { id: 'opt_' + Date.now() + 2, option_text: 'الخيار الثاني', is_correct: false }
                ]
            }];
        }

        return {
            questions: initialQuestions,
            editors: {},

            init() {
                // Initialize Sortable
                const sortableEl = document.getElementById('sortable-questions');
                if (sortableEl) {
                    new Sortable(sortableEl, {
                        handle: '.drag-handle',
                        animation: 150,
                        onEnd: (evt) => {
                            // Reorder the array based on DOM order
                            const newOrder = [];
                            const items = sortableEl.querySelectorAll('.question-card');
                            items.forEach((item, index) => {
                                const qId = item.getAttribute('data-id');
                                const q = this.questions.find(q => q.id == qId);
                                if (q) newOrder.push(q);
                            });
                            this.questions = newOrder;

                            // If this is an edit view, we could fire an AJAX to save order instantly
                            @if(isset($exam))
                                this.saveOrderToBackend();
                            @endif
                        }
                    });
                }
                // Add Question Button outside alpine
                document.getElementById('addQuestionBtn').addEventListener('click', () => {
                    this.addQuestion();
                });

                // Sync CKEditor data on form submit to ensure no validation errors
                const form = document.getElementById('examForm');
                if (form) {
                    form.addEventListener('submit', () => {
                        for (let id in this.editors) {
                            if (this.editors[id]) {
                                this.editors[id].updateSourceElement();
                            }
                        }
                    });
                }
            },

            addQuestion() {
                this.questions.push({
                    id: 'new_' + Date.now(),
                    type: 'multiple_choice',
                    content: '',
                    points: 1,
                    options: [
                        { id: 'opt_' + Date.now() + 1, option_text: 'الخيار الأول', is_correct: true },
                        { id: 'opt_' + Date.now() + 2, option_text: 'الخيار الثاني', is_correct: false }
                    ]
                });
            },

            removeQuestion(index) {
                if (confirm('هل أنت متأكد من حذف هذا السؤال؟')) {
                    this.questions.splice(index, 1);
                }
            },

            handleTypeChange(index) {
                const q = this.questions[index];
                if (q.type === 'true_false') {
                    q.options = [
                        { id: 'opt_' + Date.now() + 1, option_text: 'صح', is_correct: true },
                        { id: 'opt_' + Date.now() + 2, option_text: 'خطأ', is_correct: false }
                    ];
                } else if (q.type === 'multiple_choice') {
                    q.options = [
                        { id: 'opt_' + Date.now() + 1, option_text: 'الخيار الأول', is_correct: true },
                        { id: 'opt_' + Date.now() + 2, option_text: 'الخيار الثاني', is_correct: false }
                    ];
                } else {
                    q.options = [];
                }
            },

            addOption(qIndex) {
                this.questions[qIndex].options.push({
                    id: 'opt_' + Date.now(),
                    option_text: '',
                    is_correct: false
                });
            },

            removeOption(qIndex, oIndex) {
                this.questions[qIndex].options.splice(oIndex, 1);
            },

            setCorrectOption(qIndex, oIndex) {
                this.questions[qIndex].options.forEach((opt, idx) => {
                    opt.is_correct = (idx === oIndex);
                });
            },

            initEditor(el, q) {
                // To avoid multiple instantiations
                if(el.getAttribute('data-editor-init')) return;
                el.setAttribute('data-editor-init', 'true');

                ClassicEditor
                    .create(el, {
                        language: 'ar',
                        initialData: q.content || ''
                    })
                    .then(editor => {
                        this.editors[q.id] = editor;
                        el.ckeditorInstance = editor;
                        editor.model.document.on('change:data', () => {
                            q.content = editor.getData();
                            el.value = editor.getData(); // forcefully update the textarea value for form submission
                        });
                    })
                    .catch(error => {
                        console.error(error);
                    });
            },

            saveOrderToBackend() {
                const orderedIds = this.questions.map(q => q.id).filter(id => !String(id).startsWith('new_'));
                if (orderedIds.length === 0) return;

                fetch(`{{ isset($exam) ? route($examReorderRouteName ?? 'exams.reorder-questions', $exam->id ?? 0) : '' }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ ordered_ids: orderedIds })
                }).then(res => res.json()).then(data => {
                    if(data.success) {
                        console.log('Order saved successfully');
                    }
                });
            }
        }
    }
</script>
@endpush
