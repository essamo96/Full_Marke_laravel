<?php

namespace App\Http\Controllers\Admin;

use App\Models\SubjectResource;
use App\Services\ResourceLibrary\ContentManager;
use App\Services\ResourceLibrary\LibraryActor;
use Illuminate\Support\Facades\Auth;

/**
 * The archive of deleted resources (the full trash - with lessons and units - is also reachable
 * from every content screen, with Undo). Restoring brings back the resource together with its
 * audience, places and exclusions, because deleting only ever soft-deletes the row.
 */
class ResourceArchiveController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'resource-archive';
    }

    private function actor(): LibraryActor
    {
        return LibraryActor::admin(Auth::guard('admin')->user());
    }

    public function index()
    {
        $resources = SubjectResource::onlyTrashed()->with('subject')->orderBy('deleted_at', 'desc')->get();

        return view('admin.resource_archive.index', self::$data + compact('resources'));
    }

    public function restore(ContentManager $content, $id)
    {
        $result = $content->restoreResource($this->actor(), SubjectResource::onlyTrashed()->findOrFail($id));

        if ($result->data['file_missing'] ?? false) {
            return redirect()->back()->with(
                'danger',
                'تمت استعادة سجل المرفق، لكن ملفه غير موجود على السيرفر. '
                .'غالباً لأن نسخة هوستنغر رجّعت قاعدة البيانات بدون مجلد storage/app/private/protected_videos، '
                .'أو لأن الملف حُذف نهائياً من الأرشيف سابقاً. أعد رفع الملف أو استرجع مجلد الفيديوهات من النسخة الاحتياطية.'
            );
        }

        return redirect()->back()->with('success_message', 'تم استعادة المرفق بنجاح');
    }

    public function forceDelete(ContentManager $content, $id)
    {
        $resource = SubjectResource::onlyTrashed()->findOrFail($id);
        $isFile = ! $resource->isExternalLink();

        $content->forceDeleteResource($this->actor(), $resource);

        return redirect()->back()->with('success_message', $isFile ? 'تم حذف المرفق وتدمير الملف من السيرفر نهائياً' : 'تم حذف المرفق نهائياً');
    }
}
