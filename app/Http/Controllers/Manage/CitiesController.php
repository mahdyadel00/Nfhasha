<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manage\StoreCityRequest;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;


class CitiesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $cities = City::with(['translations' => function ($query) {
                $query->whereIn('locale', ['ar', 'en']);
            }])
            ->select('id', 'is_active');

            return DataTables::of($cities)
                ->addColumn('is_active', function ($city) {
                    $badgeClass = $city->is_active ? 'bg-success' : 'bg-danger';
                    $badgeText = $city->is_active ? __('manage.active') : __('manage.inactive');
                    return '<span class="badge rounded-pill ' . $badgeClass . '">' . $badgeText . '</span>';
                })
                ->addColumn('name_ar', function ($city) {
                    return $city->translations->firstWhere('locale', 'ar')->name ?? __('manage.no_name');
                })
                ->addColumn('name_en', function ($city) {
                    return $city->translations->firstWhere('locale', 'en')->name ?? __('manage.no_name');
                })
                ->addColumn('action', function ($city) {
                    return '
                    <div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="' . route('manage.cities.edit', $city->id) . '">
                                <i class="bx bx-edit-alt me-1"></i>' . __("manage.edit") . '
                            </a>
                            <a class="dropdown-item" href="javascript:void(0);" data-id="' . $city->id . '" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="bx bx-trash me-1"></i>' . __("manage.delete") . '
                            </a>
                        </div>
                    </div>';
                })
                ->rawColumns(['is_active', 'action'])
                ->make(true);
        }

        return view('manage.cities.index');
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('manage.cities.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCityRequest $request)
    {
        $city = City::create($request->only('is_active'));

        $city->translations()->createMany([
            [
                'locale' => 'ar',
                'name' => $request->ar['name'],
            ],
            [
                'locale' => 'en',
                'name' => $request->en['name'],
            ],
        ]);

        return redirect()->route('manage.cities.index')->with('success', __('manage.created_successfully'));
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
    public function destroy($id)
    {
        $city = City::findOrFail($id);

        $city->translations()->delete();

        $city->delete();

        return response()->json(['message' => __('manage.deleted_successfully')]);
    }
}
