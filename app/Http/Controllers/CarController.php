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
    // Base query with eager load (for $car->dealer in the view)
    $q = Car::query()->with('dealer');

    // Filters
    if ($request->filled('make')) {
        $q->where('make', $request->make);
    }

    if ($request->filled('fuel_type')) {
        $q->where('fuel_type', $request->fuel_type);
    }

    /**
     * Multi-sort support:
     * Example: ?sorts=year:desc,price:asc,dealer:asc
     */
    $rawSorts = $request->get('sorts', 'created_at:desc');

    // Map safe sort keys to real DB columns
    $allowed = [
        'price'      => 'cars.price',
        'year'       => 'cars.year',
        'created_at' => 'cars.created_at',
        'dealer'     => 'dealers.name',
    ];

    // Parse sorts
    $sorts = collect(explode(',', $rawSorts))
        ->map(fn ($s) => trim($s))
        ->filter()
        ->map(function ($s) {
            [$field, $dir] = array_pad(explode(':', $s), 2, 'asc');
            $dir = strtolower($dir);
            return ['field' => $field, 'dir' => $dir];
        })
        ->filter(fn ($s) => in_array($s['dir'], ['asc', 'desc']));

    // Join dealers only if dealer sorting is being used
    $needsDealerJoin = $sorts->contains(fn ($s) => $s['field'] === 'dealer');

    if ($needsDealerJoin) {
        $q->leftJoin('dealers', 'cars.dealer_id', '=', 'dealers.id')
          ->select('cars.*'); // IMPORTANT so we still get Car models
    }

    // Apply all sorts in the order user selected
    foreach ($sorts as $s) {
        if (!array_key_exists($s['field'], $allowed)) {
            continue;
        }
        $q->orderBy($allowed[$s['field']], $s['dir']);
    }

    // Stable final ordering (prevents "random" order when equal)
    $q->orderBy('cars.id', 'desc');

    $cars = $q->paginate(10)->withQueryString();

    // Filter dropdown data
    $makes = Car::select('make')->distinct()->orderBy('make')->pluck('make');
    $fuelTypes = Car::select('fuel_type')->whereNotNull('fuel_type')->distinct()->orderBy('fuel_type')->pluck('fuel_type');

    return view('cars.index', compact('cars', 'makes', 'fuelTypes', 'rawSorts'));
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
            'fuel_type' => 'required|in:petrol,diesel,hybrid,electric',
            'images'    => 'nullable|array',
            'images.*'  => 'image|mimes:jpg,jpeg,png,webp|max:2048',
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
            'fuel_type' => 'required|in:petrol,diesel,hybrid,electric',
            'images'    => 'nullable|array',
            'images.*'  => 'image|mimes:jpg,jpeg,png,webp|max:2048',
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
