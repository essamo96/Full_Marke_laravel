<div class="d-flex flex-column">
    <span class="text-dark fw-bold fs-6">{{ app()->getLocale() == 'ar' ? $point->name_ar : $point->name_en }}</span>
    <span class="text-muted fw-semibold fs-7">{{ app()->getLocale() == 'ar' ? $point->address_ar : $point->address_en }}</span>
</div>
