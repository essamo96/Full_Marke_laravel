<?php
use App\Http\Controllers\Admin\EducationalMaterialController;
use Illuminate\Support\Facades\Route;

Route::get('/educational-materials', [EducationalMaterialController::class, 'index'])->name('educational_materials.index');
Route::post('/educational-materials/stage', [EducationalMaterialController::class, 'storeStage'])->name('educational_materials.store_stage');
Route::post('/educational-materials/unit', [EducationalMaterialController::class, 'storeUnit'])->name('educational_materials.store_unit');
Route::post('/educational-materials/lesson', [EducationalMaterialController::class, 'storeLesson'])->name('educational_materials.store_lesson');
Route::post('/educational-materials/resource', [EducationalMaterialController::class, 'storeResource'])->name('educational_materials.store_resource');
Route::delete('/educational-materials/stage/{stage}', [EducationalMaterialController::class, 'destroyStage']);
Route::delete('/educational-materials/unit/{unit}', [EducationalMaterialController::class, 'destroyUnit']);
Route::delete('/educational-materials/lesson/{lesson}', [EducationalMaterialController::class, 'destroyLesson']);
Route::delete('/educational-materials/resource/{resource}', [EducationalMaterialController::class, 'destroyResource']);
