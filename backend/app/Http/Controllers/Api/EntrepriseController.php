<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEntrepriseRequest;
use App\Models\Entreprise;
use Illuminate\Http\JsonResponse;

class EntrepriseController extends Controller
{
    /**
     * Get the tenant's company details.
     */
    public function show(): JsonResponse
    {
        $entreprise = Entreprise::first();

        if (!$entreprise) {
            return response()->json(['message' => 'Aucune entreprise configurée'], 404);
        }

        return response()->json($entreprise);
    }

    /**
     * Update the tenant's company details.
     */
    public function update(UpdateEntrepriseRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $finalLogoPath = $validated['logo_path'] ?? null;
        
        // Traitement de l'image Base64 si reçue
        if ($finalLogoPath && str_starts_with($finalLogoPath, 'data:image')) {
            try {
                $imageParts = explode(";base64,", $finalLogoPath);
                if (count($imageParts) !== 2) {
                    return response()->json(['message' => 'Format logo invalide'], 422);
                }

                $imageTypeAux = explode("image/", $imageParts[0]);
                $imageType = $imageTypeAux[1] ?? 'png';

                // Whitelist des types autorisés
                $allowedTypes = ['png', 'jpg', 'jpeg', 'gif', 'svg+xml', 'webp'];
                if (!in_array($imageType, $allowedTypes)) {
                    return response()->json(['message' => 'Type d\'image non autorisé'], 422);
                }

                if ($imageType === 'jpeg') $imageType = 'jpg';
                if ($imageType === 'svg+xml') $imageType = 'svg';

                $imageBase64 = base64_decode($imageParts[1], true);
                if ($imageBase64 === false) {
                    return response()->json(['message' => 'Données Base64 invalides'], 422);
                }

                $fileName = 'tenant_logo_' . uniqid() . '.' . $imageType;
                
                $uploadPath = public_path('uploads/logos');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                file_put_contents($uploadPath . '/' . $fileName, $imageBase64);
                $finalLogoPath = asset('uploads/logos/' . $fileName);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Erreur lors du traitement du logo'], 500);
            }
        }

        $validated['logo_path'] = $finalLogoPath;

        $entreprise = Entreprise::first();

        if ($entreprise) {
            $entreprise->update($validated);
        } else {
            $entreprise = Entreprise::create($validated);
        }

        return response()->json([
            'message' => 'Configuration sauvegardée.',
            'entreprise' => $entreprise->fresh()
        ]);
    }
}
