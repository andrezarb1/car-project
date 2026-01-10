<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Dealer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\CarImage;
use App\Services\VinValidationService;


class CarController extends Controller
{
    public function index(Request $request)
    {
        // Filtering + sorting (good for marks)
        $q = Car::query()->with('dealer');

        if ($request->filled('make')) {
            $q->where('make', $request->make);
        }

        if ($request->filled('fuel_type')) {
            $q->where('fuel_type', $request->fuel_type);
        }

        $sort = $request->get('sort', 'created_at');
        $dir  = $request->get('dir', 'desc');

        if (!in_array($sort, ['price', 'year', 'created_at'])) {
            $sort = 'created_at';
        }
        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'desc';
        }

        $cars = $q->orderBy($sort, $dir)->paginate(10)->withQueryString();

        // For filter dropdowns
        $makes = Car::select('make')->distinct()->orderBy('make')->pluck('make');
        $fuelTypes = Car::select('fuel_type')->whereNotNull('fuel_type')->distinct()->orderBy('fuel_type')->pluck('fuel_type');

        return view('cars.index', compact('cars', 'makes', 'fuelTypes', 'sort', 'dir'));
    }

    public function create()
    {
        $dealers = Dealer::orderBy('name')->get();

        return view('cars.create', compact('dealers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dealer_id' => 'required|exists:dealers,id',
            'make'      => 'required|string|max:255',
            'model'     => 'required|string|max:255',
            'year'      => 'required|integer|min:1950|max:' . (date('Y') + 1),
            'price'     => 'required|numeric|min:0',
            'vin'       => 'required|string|max:255|unique:cars,vin',
            'fuel_type' => 'nullable|string|max:255',
            'images'   => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $vinService = new VinValidationService();

        if (!$vinService->isValidFormat($validated['vin'])) {
            return back()->withInput()->withErrors(['vin' => 'VIN format is invalid (must be 17 chars, no I/O/Q).']);
        }

        $apiResult = $vinService->validateWithApi($validated['vin']);
        if (!$apiResult['ok']) {
            return back()->withInput()->withErrors(['vin' => 'VIN failed validation: ' . $apiResult['message']]);
        }


        $validated['slug'] = $this->uniqueSlug($validated['make'], $validated['model'], $validated['year']);

        $car = Car::create($validated);

        // Save uploaded images (if any)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('cars', 'public'); // storage/app/public/cars
                CarImage::create([
                    'car_id' => $car->id,
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->route('cars.index')
            ->with('success', 'Car created successfully!');
    }

    public function show(Car $car)
    {
        $car->load('dealer', 'images');

        return view('cars.show', compact('car'));
    }

    public function edit(Car $car)
    {
        $dealers = Dealer::orderBy('name')->get();

        return view('cars.edit', compact('car', 'dealers'));
    }

    public function update(Request $request, Car $car)
    {
        $validated = $request->validate([
            'dealer_id' => 'required|exists:dealers,id',
            'make'      => 'required|string|max:255',
            'model'     => 'required|string|max:255',
            'year'      => 'required|integer|min:1950|max:' . (date('Y') + 1),
            'price'     => 'required|numeric|min:0',
            'vin'       => 'required|string|max:255|unique:cars,vin,' . $car->id,
            'fuel_type' => 'nullable|string|max:255',
            'images'   => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',

        ]);

        $vinService = new VinValidationService();

        if (!$vinService->isValidFormat($validated['vin'])) {
            return back()->withInput()->withErrors(['vin' => 'VIN format is invalid (must be 17 chars, no I/O/Q).']);
        }

        $apiResult = $vinService->validateWithApi($validated['vin']);
        if (!$apiResult['ok']) {
            return back()->withInput()->withErrors(['vin' => 'VIN failed validation: ' . $apiResult['message']]);
        }


        $validated['slug'] = $this->uniqueSlug($validated['make'], $validated['model'], $validated['year'], $car->id);

        $car->update($validated);

        // Save uploaded images (if any)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('cars', 'public');
                CarImage::create([
                    'car_id' => $car->id,
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->route('cars.index')
            ->with('success', 'Car updated successfully!');
    }

    public function destroy(Car $car)
    {
        $car->delete();

        return redirect()->route('cars.index')
            ->with('success', 'Car deleted successfully!');
    }

    private function uniqueSlug(string $make, string $model, int $year, ?int $ignoreId = null): string
    {
        $base = Str::slug("$make $model $year");
        $slug = $base;
        $i = 1;

        $query = Car::where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = $base . '-' . $i++;
            $query = Car::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
        }

        return $slug;
    }
}
