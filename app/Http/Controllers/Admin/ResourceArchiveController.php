<?php

namespace App\Http\Controllers\Admin;

use App\Models\SubjectResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResourceArchiveController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'resource-archive';
    }

    public function index()
    {
        $resources = SubjectResource::onlyTrashed()->with(['subject', 'deletedBy'])->orderBy('deleted_at', 'desc')->get();
        return view('admin.resource_archive.index', self::$data + compact('resources'));
    }

    public function restore($id)
    {
        $resource = SubjectResource::onlyTrashed()->findOrFail($id);

        $resource->update(['deleted_by' => null]);
        $resource->restore();

        $fileMissing = ! $resource->isExternalLink()
            && $resource->url
            && ! Storage::disk('protected_videos')->exists($resource->url);

        if ($fileMissing) {
            return redirect()->back()->with(
                'danger',
                'تمت استعادة سجل المرفق، لكن ملف الفيديو/المستند غير موجود على السيرفر. '
                .'غالباً لأن نسخة هوستنغر رجّعت قاعدة البيانات بدون مجلد storage/app/private/protected_videos، '
                .'أو لأن الملف حُذف نهائياً من الأرشيف سابقاً. أعد رفع الملف أو استرجع مجلد الفيديوهات من النسخة الاحتياطية.'
            );
        }

        return redirect()->back()->with('success_message', 'تم استعادة المرفق بنجاح');
    }

    public function forceDelete($id)
    {
        $resource = SubjectResource::onlyTrashed()->findOrFail($id);

        if ($resource->isExternalLink()) {
            $resource->forceDelete();
            return redirect()->back()->with('success_message', 'تم حذف المرفق نهائياً');
        }

        Storage::disk('protected_videos')->deleteDirectory("resources/{$resource->id}");

        if ($resource->url && Storage::disk('protected_videos')->exists($resource->url)) {
            Storage::disk('protected_videos')->delete($resource->url);
        }

        $resource->forceDelete();

        return redirect()->back()->with('success_message', 'تم حذف المرفق وتدمير الملف من السيرفر نهائياً');
    }
}
