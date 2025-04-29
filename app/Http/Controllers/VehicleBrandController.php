namespace App\Http\Controllers;

use App\Models\VehicleBrand;
use Illuminate\Http\Request;

class VehicleBrandController extends Controller
{
public function index()
{
$vehicleBrands = VehicleBrand::all();
return view('dashboard.vehicle-brands.index', compact('vehicleBrands'));
}

public function create()
{
return view('dashboard.vehicle-brands.create');
}

public function store(Request $request)
{
$request->validate([
'title' => 'required|string|max:255',
'status' => 'required|boolean',
]);

VehicleBrand::create($request->all());
return redirect()->route('vehicle-brands.index')->with('success', 'Vehicle Brand created successfully.');
}

public function destroy(VehicleBrand $vehicleBrand)
{
$vehicleBrand->delete();
return redirect()->route('vehicle-brands.index')->with('success', 'Vehicle Brand deleted successfully.');
}
}
