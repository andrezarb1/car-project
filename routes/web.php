<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DealerController;
use App\Http\Controllers\CarController;
use Illuminate\Http\Request;
use App\Services\VinValidationService;

Route::resource('dealers', DealerController::class);
Route::resource('cars', CarController::class);


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/vin/validate', function (Request $request) {
    $request->validate([
        'vin' => 'required|string'
    ]);

    $vinService = new VinValidationService();
    $vin = $request->input('vin');

    if (!$vinService->isValidFormat($vin)) {
        return response()->json([
            'ok' => false,
            'message' => 'VIN format is invalid (must be 17 chars, no I/O/Q).'
        ], 422);
    }

    $apiResult = $vinService->validateWithApi($vin);

    return response()->json([
        'ok' => $apiResult['ok'],
        'message' => $apiResult['ok']
            ? 'VIN looks valid. ' . $apiResult['message']
            : 'VIN failed validation: ' . $apiResult['message']
    ]);
});

