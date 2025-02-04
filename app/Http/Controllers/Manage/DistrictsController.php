<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manage\StoreDistrictRequest;
use App\Models\City;
use App\Models\District;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DistrictsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $districts = District::with([
                'translations' => function ($query) {
                    $query->whereIn('locale', ['ar', 'en']);
                },
                'city.translations' => function ($query) {
                    $query->whereIn('locale', ['ar', 'en']);
                }
            ])
            ->select('id', 'is_active', 'city_id');

            return DataTables::of($districts)
                ->filter(function ($query) {
                    if ($search = request('search')['value']) {
                        $query->whereHas('translations', function ($q) use ($search) {
                            $q->where('name', 'LIKE', "%{$search}%");
                        })->orWhereHas('city.translations', function ($q) use ($search) {
                            $q->where('name', 'LIKE', "%{$search}%");
                        });
                    }

                    if ($cityId = request('city_id')) {
                        $query->where('city_id', $cityId);
                    }
                })
                ->addColumn('city_name', function ($district) {
                    return $district->city->translations->pluck('name')->join(' / ');
                })
                ->addColumn('name_ar', function ($district) {
                    return $district->translations->firstWhere('locale', 'ar')->name ?? __('manage.no_name');
                })
                ->addColumn('name_en', function ($district) {
                    return $district->translations->firstWhere('locale', 'en')->name ?? __('manage.no_name');
                })
                ->addColumn('action', function ($district) {
                    return '
                    <div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="' . route('manage.districts.edit', $district->id) . '">
                                <i class="bx bx-edit-alt me-1"></i>' . __("manage.edit") . '
                            </a>
                            <a class="dropdown-item" href="javascript:void(0);" data-id="' . $district->id . '" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="bx bx-trash me-1"></i>' . __("manage.delete") . '
                            </a>
                        </div>
                    </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $cities = City::with('translations')->get(); 
        return view('manage.districts.index', compact('cities'));
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $cities = City::with([
            'translations' => function ($query) {
                $query->whereIn('locale', ['ar', 'en']);
            }
        ])->get();

        return view('manage.districts.create', compact('cities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDistrictRequest $request)
    {
        $district = District::create($request->only('city_id' , 'is_active'));

        $district->translations()->createMany([
            ['locale' => 'ar', 'name' => $request->ar['name']],
            ['locale' => 'en', 'name' => $request->en['name']]
        ]);

        return redirect()->route('manage.districts.index')->with('success', __('manage.created_successfully'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $district = District::findOrFail($id);

        $district->translations()->delete();

        $district->delete();

        return response()->json(['message' => __('manage.deleted_successfully')]);
    }
}
