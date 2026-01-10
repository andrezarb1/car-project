<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class VinValidationService
{
    /**
     * Basic offline VIN format validation.
     * VIN is typically 17 chars, excludes I, O, Q.
     */
    public function isValidFormat(string $vin): bool
    {
        $vin = strtoupper(trim($vin));

        if (strlen($vin) !== 17) return false;
        if (preg_match('/[IOQ]/', $vin)) return false;

        // Alphanumeric only
        return (bool) preg_match('/^[A-Z0-9]{17}$/', $vin);
    }

    /**
     * External API validation (VPIC NHTSA) – only runs when enabled.
     * Returns: ['ok' => bool, 'message' => string]
     */
    public function validateWithApi(string $vin): array
    {
        $vin = strtoupper(trim($vin));

        if (!config('services.vin.enabled')) {
            return ['ok' => true, 'message' => 'VIN API disabled (offline mode).'];
        }

        $baseUrl = rtrim(config('services.vin.url'), '/') . '/';

        try {
            $response = Http::timeout(5)->get($baseUrl . $vin, [
                'format' => 'json',
            ]);

            if (!$response->successful()) {
                return ['ok' => true, 'message' => 'VIN API not available, skipped.'];
            }

            $data = $response->json();

            // VPIC returns Results array with decoded values
            $result = $data['Results'][0] ?? null;

            if (!$result) {
                return ['ok' => true, 'message' => 'VIN API returned no result, skipped.'];
            }

            // ErrorCode "0" usually means OK
            $errorCode = (string)($result['ErrorCode'] ?? '');
            if (!Str::contains($errorCode, '0')) {
                $errorText = $result['ErrorText'] ?? 'VIN appears invalid.';
                return ['ok' => false, 'message' => $errorText];
            }

            return ['ok' => true, 'message' => 'VIN validated successfully.'];
        } catch (\Throwable $e) {
            // Network/DNS errors will land here in your VM — we fail gracefully
            return ['ok' => true, 'message' => 'VIN API unreachable, skipped (offline).'];
        }
    }
}
