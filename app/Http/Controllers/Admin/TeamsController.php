<?php

namespace App\Http\Controllers\Admin;

use App\Models\Team;
use App\Models\TeamTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\Admin\TeamRequest;
use Auth;

class TeamsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'teams';
        $this->path = 'teams';
    }

    public function getIndex()
    {
        parent::$data['companies'] = [];
        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function getList(Request $request)
    {
        $name = $request->get('name') ?? '';
        $companies = $request->get('companies') ?? '';
        
        $obj = new Team();
        $info = $obj->getSearch($name, $companies, 0);

        if ($request->filled('status')) {
            $info->where('teams.status', $request->get('status'));
        }

        return Datatables::of($info)
            ->editColumn('status', function ($row) {
                $data['id'] = $row->id;
                $data['status'] = $row->status;
                $data['active_menu'] = $this->path;
                return view('admin.' . $this->path . '.parts.status', $data)->render();
            })
            
            ->addColumn('image', function ($row) {
                if ($row->image) {
                    $imagePath = \Illuminate\Support\Str::startsWith($row->image, ['http', 'site/']) 
                        ? asset($row->image) 
                        : asset('storage/' . $row->image);
                    return '<div class="symbol symbol-50px symbol-circle me-5">
                            <img src="' . $imagePath . '" alt="image" class="symbol-label">
                        </div>';
                }
                return '-';
            })
            ->addColumn('name', function ($row) {
                return $row->translation ? $row->translation->name : '-';
            })
            ->addColumn('position', function ($row) {
                return $row->position ?? '---';
            })
            ->addColumn('socials', function ($row) {
                $html = '<div class="symbol-group symbol-hover flex-nowrap">';

                // فك JSON إلى array
                $socials = json_decode($row->socials, true);

                if (is_array($socials)) {
                    foreach ($socials as $item) {
                        $platform = strtolower($item['platform'] ?? '');
                        $link = $item['link'] ?? '';
                        if ($link) {
                            $icon = '';
                            $secondPart = 'primary';
                            switch ($platform) {
                                case 'facebook':
                                    $icon = 'bi-facebook';
                                    $secondPart = 'primary';
                                    break;
                                case 'twitter':
                                    $icon = 'bi-twitter';
                                    $secondPart = 'info';
                                    break;
                                case 'linkedin':
                                    $icon = 'bi-linkedin';
                                    $secondPart = 'primary';
                                    break;
                                case 'instagram':
                                    $icon = 'bi-instagram';
                                    $secondPart = 'danger';
                                    break;
                            }
                            $html .= '<a href="' . $link . '" target="_blank">
                                        <div class="symbol symbol-30px symbol-circle me-5">
                                            <span class="symbol-label bg-light-' . $secondPart . '">
                                                <i class="bi ' . $icon . ' fs-1x text-' . $secondPart . '"></i>
                                            </span>
                                        </div>
                                    </a>';
                        }
                    }
                }

                $html .= '</div>';
                return $html ?: '-';
            })


            ->addColumn('member_type', function ($row) {
                // تشفير الـ ID
                try {
                $id = Crypt::encrypt($row->id);
                } catch (\Exception $e) {
                    $id = $row->id;
                }
                
                $isChecked = $row->member_type == 'board' ? 'checked' : '';
                
                $html = '<div class="d-flex flex-column">';
                
                // Toggle Switch
                $html .= '<div class="d-flex align-items-center mb-2">';
                $html .= '<label class="form-check form-switch form-check-custom form-check-solid me-3">';
                $html .= '<input class="form-check-input h-20px w-30px type-toggle" type="checkbox" data-id="' . $id . '" ' . $isChecked . ' />';
                $html .= '</label>';
                $html .= '</div>';
                
                // Badges
                if ($row->member_type == 'board') {
                    $html .= '<div><span class="badge badge-light-primary fw-bold mb-1">' . __('Board Member') . '</span></div>';
                    if ($row->is_chairman) {
                        $html .= '<div><span class="badge badge-light-warning fw-bold">' . __('Chairman of the Board') . '</span></div>';
                    }
                } else {
                    $html .= '<div><span class="badge badge-light-secondary fw-bold">' . __('Team') . '</span></div>';
                }
                
                $html .= '</div>';
                return $html;
            })
            ->addColumn('is_chairman', function ($row) {
                // تشفير الـ ID
                try {
                    $id = Crypt::encrypt($row->id);
                } catch (\Exception $e) {
                    $id = $row->id;
                }
                
                // إذا لم يكن العضو من نوع board، لا يظهر الزر
                if ($row->member_type !== 'board') {
                    return '<span class="badge badge-light-secondary fw-bold">' . __('N/A') . '</span>';
                }
                
                $isChecked = $row->is_chairman ? 'checked' : '';
                $badgeClass = $row->is_chairman ? 'badge-light-warning' : 'badge-light-secondary';
                $badgeText = $row->is_chairman ? __('Chairman') : __('Member');
                
                $html = '<div class="d-flex flex-column align-items-center">';
                
                // Toggle Switch
                $html .= '<div class="d-flex align-items-center mb-2">';
                $html .= '<label class="form-check form-switch form-check-custom form-check-solid me-3">';
                $html .= '<input class="form-check-input h-20px w-30px chairman-toggle" type="checkbox" data-id="' . $id . '" ' . $isChecked . ' />';
                $html .= '</label>';
                $html .= '</div>';
                
                // Badge
                $html .= '<span class="badge ' . $badgeClass . ' fw-bold">' . $badgeText . '</span>';
                
                $html .= '</div>';
                return $html;
            })
            ->addColumn('actions', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                return view('admin.' . $this->path . '.parts.actions', $data)->render();
            })
            ->rawColumns(['status', 'actions', 'image', 'socials', 'member_type', 'is_chairman'])
            ->addIndexColumn()
            ->make(true);
    }


    public function getAdd()
    {
        parent::$data['info'] = null;
        parent::$data['companies'] = [];
        parent::$data['languages'] = collect([(object)['prefix'=>'ar','name'=>'العربية'],(object)['prefix'=>'en','name'=>'English']]);
        parent::$data['translations'] = [];
        

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function postAdd(TeamRequest $request)
    {
        $data = $request->validated();
        $data['status'] = $request->has('status') ? 1 : 0;
        $data['is_chairman'] = $request->has('is_chairman') ? 1 : 0;
        $data['display_order'] = $request->input('display_order', 0);

        // حفظ الـ socials كـ JSON (مع تصفية العناصر الفارغة)
        if ($request->has('socials')) {
            $socials = array_filter($request->input('socials'), function ($item) {
                return !empty($item['platform']);
            });
            $data['socials'] = !empty($socials) ? json_encode(array_values($socials)) : null;
        } else {
            $data['socials'] = null;
        }

        // إنشاء العضو
        $team = Team::create($data);

        // إنشاء الترجمات (مع caching)
        $languages = cache()->remember('active_language_prefixes', 3600, function () {
            return ['ar', 'en'];
        });
        foreach ($languages as $locale) {
            $translationData = $request->input($locale, []);
            if (!empty($translationData['name'])) {
                TeamTranslation::create([
                    'team_id'     => $team->id,
                    'locale'      => $locale,
                    'name'        => $translationData['name'],
                    'address1'    => $translationData['address1'] ?? null,
                    'address2'    => $translationData['address2'] ?? null,
                    'description' => $translationData['description'] ?? null,
                    'board_description' => $translationData['board_description'] ?? null]);
            }
        }

        Cache::forget('spatie.permission.cache');
        $request->session()->flash('success', __('app.insert_success'));
        return redirect(route($this->path . '.view'));
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $record = Team::with('translations')->findOrFail($decryptedId);

        $translations = [];
        foreach ($record->translations as $trans) {
            $translations[$trans->locale] = $trans;
        }

        parent::$data['info'] = $record;
        parent::$data['companies'] = [];
        parent::$data['languages'] = collect([(object)['prefix'=>'ar','name'=>'العربية'],(object)['prefix'=>'en','name'=>'English']]);
        parent::$data['translations'] = $translations;
        

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function postEdit(TeamRequest $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $team = Team::findOrFail($decryptedId);

        $data = $request->validated();
        $data['status'] = $request->has('status') ? 1 : 0;
        $data['is_chairman'] = $request->has('is_chairman') ? 1 : 0;
        $data['display_order'] = $request->input('display_order', 0);

        // الصورة مُختارة من مدير الملفات — ملف مشترك، لا يُحذف عند التغيير
        $data['image'] = $request->filled('image') ? $request->get('image') : $team->image;

        // تحديث الـ socials (مع تصفية العناصر الفارغة)
        if ($request->has('socials')) {
            $socials = array_filter($request->input('socials'), function ($item) {
                return !empty($item['platform']);
            });
            $data['socials'] = !empty($socials) ? json_encode(array_values($socials)) : null;
        } else {
            $data['socials'] = $team->socials;
        }

        // تحديث بيانات العضو
        $team->update($data);

        // تحديث الترجمات (مع caching)
        $languages = cache()->remember('active_language_prefixes', 3600, function () {
            return ['ar', 'en'];
        });
        foreach ($languages as $locale) {
            $translationData = $request->input($locale, []);
            if (!empty($translationData['name'])) {
                TeamTranslation::updateOrCreate(
                    ['team_id' => $team->id, 'locale' => $locale],
                    [
                        'name'        => $translationData['name'],
                        'address1'    => $translationData['address1'] ?? null,
                        'address2'    => $translationData['address2'] ?? null,
                    'description' => $translationData['description'] ?? null,
                    'board_description' => $translationData['board_description'] ?? null]
                );
            }
        }

        Cache::forget('spatie.permission.cache');
        $request->session()->flash('success', __('app.update_success'));
        return redirect(route($this->path . '.view'));
    }

    public function postStatus(Request $request)
    {
        $id = $request->get('id');
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }

        $record = Team::findOrFail($decryptedId);

        $newStatus = $record->status == 1 ? 0 : 1;
        $update = $record->update(['status' => $newStatus]);

        if ($update) {
            Cache::forget('spatie.permission.cache');
            return response()->json([
                'status' => 'success',
                'message' => $newStatus ? __('app.activation_success') : __('app.disable_success'),
                'type' => $newStatus ? 'yes' : 'no'
            ]);
        } else {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $decryptedId = Crypt::decrypt($request->input('id'));
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }

        try {
            $record = Team::findOrFail($decryptedId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.not_found')]);
        }

        if ($record->delete()) {
            Cache::forget('spatie.permission.cache');
            return response()->json(['status' => 'success', 'message' => __('app.delete_success')]);
        } else {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }
    }

    public function postType(Request $request)
    {
        $id = $request->get('id');
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }

        try {
            $record = Team::findOrFail($decryptedId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.not_found')]);
        }

        // التبديل بين board و team
        $newType = $record->member_type == 'board' ? 'team' : 'board';
        
        // إذا تحول إلى team، يجب إزالة صفة الرئيس
        $updateData = ['member_type' => $newType];
        if ($newType == 'team') {
            $updateData['is_chairman'] = false;
        }

        $update = $record->update($updateData);

        if ($update) {
            Cache::forget('spatie.permission.cache');
            return response()->json([
                'status'  => 'success',
                'message' => __('app.update_success'),
                'newType' => $newType
            ]);
        } else {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }
    }

    public function postChairman(Request $request)
    {
        $id = $request->get('id');
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }

        try {
            $record = Team::findOrFail($decryptedId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.not_found')]);
        }

        // التحقق من أن العضو من نوع board
        if ($record->member_type !== 'board') {
            return response()->json([
                'status' => 'error', 
                'message' => __('Only board members can be chairman')
            ]);
        }

        // تبديل حالة is_chairman
        $newChairman = !$record->is_chairman;
        $update = $record->update(['is_chairman' => $newChairman]);

        if ($update) {
            Cache::forget('spatie.permission.cache');
            return response()->json([
                'status'  => 'success',
                'message' => __('app.update_success'),
                'isChairman' => $newChairman
            ]);
        } else {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }
    }
}


