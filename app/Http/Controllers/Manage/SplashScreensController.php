<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manage\StoreSplashScreenRequest;
use App\Models\SplashScreen;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;


class SplashScreensController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function index()
    {
        if (request()->ajax()) {
            $splashScreens = SplashScreen::with(['translations' => function($query) {
                $query->whereIn('locale', ['ar', 'en']);
            }])
                ->select('id', 'is_active', 'order', 'image')
                ->get();
        
            return DataTables::of($splashScreens)
                ->addColumn('image', function ($splashScreen) {
                    return '<img src="' . $splashScreen->image_url . '" alt="Splash Screen" class="rounded" width="50" height="50">';
                })
                ->addColumn('is_active', function ($splashScreen) {
                    $badgeClass = $splashScreen->is_active ? 'bg-success' : 'bg-danger';
                    $badgeText = $splashScreen->is_active ? __('manage.active') : __('manage.inactive');
                    return '<span class="badge rounded-pill ' . $badgeClass . '">' . $badgeText . '</span>';
                })
                ->addColumn('title_ar', function ($splashScreen) {
                    return Str::limit($splashScreen->translations->firstWhere('locale', 'ar')->title ?? 'No title in Arabic', 20);
                })
                ->addColumn('title_en', function ($splashScreen) {
                    return Str::limit($splashScreen->translations->firstWhere('locale', 'en')->title ?? 'No title in English', limit: 20);
                })
                ->addColumn('description_ar', function ($splashScreen) {
                    return Str::limit($splashScreen->translations->firstWhere('locale', 'ar')->description ?? 'No description in Arabic', 30);
                })
                ->addColumn('description_en', function ($splashScreen) {
                    return Str::limit($splashScreen->translations->firstWhere('locale', 'en')->description ?? 'No description in English', 30);
                })
                ->addColumn('action', function ($splashScreen) {
                    return '
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="' . route('manage.splash-screens.edit', $splashScreen->id) . '">
                                    <i class="bx bx-edit-alt me-1"></i>' . __("manage.edit") . '
                                </a>
                                <a class="dropdown-item" href="javascript:void(0);" data-id="' . $splashScreen->id . '" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="bx bx-trash me-1"></i>' . __("manage.delete") . '
                                </a>
                            </div>
                        </div>
                    </td>
                ';
                })
                ->rawColumns(['image', 'action' , 'is_active'])
                ->make(true);
        }
        
        return view('manage.splash_screens.index');        
    }
     

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('manage.splash_screens.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSplashScreenRequest $request)
    {
        $splashScreen = SplashScreen::create(
            [
                'image' => uploadImage($request->file('image'), 'splash-screens'),
                'is_active' => $request->is_active,
            ]
        );

        $splashScreen->translations()->createMany([
            [
                'locale' => 'ar',
                'title' => $request->ar['title'],
                'description' => $request->ar['description'],
            ],
            [
                'locale' => 'en',
                'title' => $request->en['title'],
                'description' => $request->en['description'],
            ],
        ]);

        return redirect()->route('manage.splash-screens.index')->with('success', __('manage.created_successfully'));
    }
    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('manage.splash-screens.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $splashScreen = SplashScreen::findOrFail($id);

        return view('manage.splash_screens.edit', compact('splashScreen'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSplashScreenRequest $request, SplashScreen $splashScreen)
    {
        if ($request->hasFile('image')) {
            $splashScreen->image = uploadImage($request->file('image'), 'splash-screens');
        }
    
        $splashScreen->is_active = $request->is_active;
        $splashScreen->save();
    
        foreach (['ar', 'en'] as $locale) {
            $translation = $splashScreen->translations->firstWhere('locale', $locale);
    
            if ($translation) {
                $translation->update([
                    'title' => $request->{$locale}['title'],
                    'description' => $request->{$locale}['description'],
                ]);
            } else {
                $splashScreen->translations()->create([
                    'locale' => $locale,
                    'title' => $request->{$locale}['title'],
                    'description' => $request->{$locale}['description'],
                ]);
            }
        }
    
        return redirect()->route('manage.splash-screens.index')->with('success', __('manage.updated_successfully'));
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $splashScreen = SplashScreen::findOrFail($id);

        $splashScreen->translations()->delete();

        $splashScreen->delete();

        return response()->json(['message' => __('manage.deleted_successfully')]);
    }
}
