<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProduitFini;
use App\Models\NomenclatureProduit;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProduitFiniController extends Controller
{
    /**
     * Generate a unique reference from designation: PF_[3 first chars UPPER]_[increment padded 4]
     */
    private function generateReference(string $designation): string
    {
        $prefix = 'PF_' . mb_strtoupper(mb_substr(trim($designation), 0, 3));
        if (mb_strlen($prefix) < 6) {
            $prefix = str_pad($prefix, 6, 'X');
        }

        $lastRef = ProduitFini::withoutGlobalScopes()
            ->where('reference', 'like', $prefix . '_%')
            ->orderByRaw("CAST(SUBSTRING_INDEX(reference, '_', -1) AS UNSIGNED) DESC")
            ->value('reference');

        $nextNum = 1;
        if ($lastRef) {
            $parts = explode('_', $lastRef);
            $lastNum = (int) end($parts);
            $nextNum = $lastNum + 1;
        }

        do {
            $reference = $prefix . '_' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            $exists = ProduitFini::withoutGlobalScopes()
                ->where('reference', $reference)
                ->exists();
            if ($exists) {
                $nextNum++;
            }
        } while ($exists);

        return $reference;
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ProduitFini::class);

        $allowedSorts = ['designation', 'reference', 'prix_ht', 'created_at'];
        $sortBy = in_array($request->sort_by, $allowedSorts) ? $request->sort_by : 'designation';
        $sortDir = $request->sort_dir === 'desc' ? 'desc' : 'asc';

        $query = ProduitFini::with(['famille.parent', 'nomenclature.produit'])
            ->when($request->search, fn($q, $v) => $q->where(function($sq) use ($v) {
                $sq->where('reference', 'like', "%{$v}%")
                   ->orWhere('designation', 'like', "%{$v}%");
            }))
            ->when($request->famille_id, fn($q, $v) => $q->where('famille_id', $v))
            ->orderBy($sortBy, $sortDir);

        $produitsFinis = $request->per_page 
            ? $query->paginate($request->per_page) 
            : $query->get();

        return response()->json($produitsFinis);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', ProduitFini::class);

        $data = $request->validate([
            'famille_id'          => 'nullable|integer',
            'reference'           => 'nullable|string|max:50',
            'designation'         => 'required|string',
            'detail'              => 'nullable|string',
            'image_path'          => 'nullable|string|max:500',
            'taux_tva'            => 'required|numeric',
            'components'          => 'required|array|min:1',
            'components.*.produit_id' => 'required|integer',
            'components.*.quantite'   => 'required|numeric|min:0.01',
        ]);

        $tenantId = $request->get('current_tenant')->id ?? 1;
        $data['tenant_id'] = $tenantId;
        $data['created_by'] = auth()->id();

        if (empty($data['reference'])) {
            $data['reference'] = $this->generateReference($data['designation']);
        }

        return DB::transaction(function () use ($data, $tenantId) {
            // Initial save for headers (amounts computed below)
            $produitFini = ProduitFini::create(collect($data)->except('components')->toArray());

            $totalHt = 0;
            $totalTva = 0;
            $totalTtc = 0;

            foreach ($data['components'] as $compData) {
                $product = Produit::findOrFail($compData['produit_id']);
                $qty = (float) $compData['quantite'];
                
                $puHt = (float) $product->prix_ht_vente;
                $tvaRate = (float) $product->taux_tva;
                
                $mHT = $qty * $puHt;
                $mTva = $mHT * ($tvaRate / 100);
                $mTtc = $mHT + $mTva;

                NomenclatureProduit::create([
                    'tenant_id'       => $tenantId,
                    'produit_fini_id' => $produitFini->id,
                    'produit_id'      => $product->id,
                    'quantite'        => $qty,
                    'montant_ht'      => $mHT,
                    'montant_tva'     => $mTva,
                    'montant_ttc'     => $mTtc,
                ]);

                $totalHt += $mHT;
                $totalTva += $mTva;
                $totalTtc += $mTtc;
            }

            // Update finished product with final computed amounts
            $produitFini->update([
                'prix_ht'  => $totalHt,
                'prix_tva' => $totalTva,
                'prix_ttc' => $totalTtc,
            ]);

            return response()->json($produitFini->load(['famille.parent', 'nomenclature.produit']), 201);
        });
    }

    public function show(int $id): JsonResponse
    {
        $produitFini = ProduitFini::with(['famille.parent', 'nomenclature.produit'])->findOrFail($id);
        $this->authorize('view', $produitFini);

        return response()->json($produitFini);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $produitFini = ProduitFini::findOrFail($id);
        $this->authorize('update', $produitFini);

        $data = $request->validate([
            'famille_id'          => 'nullable|integer',
            'reference'           => 'required|string|max:50',
            'designation'         => 'required|string',
            'detail'              => 'nullable|string',
            'image_path'          => 'nullable|string|max:500',
            'taux_tva'            => 'required|numeric',
            'components'          => 'required|array|min:1',
            'components.*.produit_id' => 'required|integer',
            'components.*.quantite'   => 'required|numeric|min:0.01',
        ]);

        $tenantId = $produitFini->tenant_id;

        return DB::transaction(function () use ($request, $data, $produitFini, $tenantId) {
            // Delete old nomenclature rows
            $produitFini->nomenclature()->delete();

            $totalHt = 0;
            $totalTva = 0;
            $totalTtc = 0;

            foreach ($data['components'] as $compData) {
                $product = Produit::findOrFail($compData['produit_id']);
                $qty = (float) $compData['quantite'];
                
                $puHt = (float) $product->prix_ht_vente;
                $tvaRate = (float) $product->taux_tva;
                
                $mHT = $qty * $puHt;
                $mTva = $mHT * ($tvaRate / 100);
                $mTtc = $mHT + $mTva;

                NomenclatureProduit::create([
                    'tenant_id'       => $tenantId,
                    'produit_fini_id' => $produitFini->id,
                    'produit_id'      => $product->id,
                    'quantite'        => $qty,
                    'montant_ht'      => $mHT,
                    'montant_tva'     => $mTva,
                    'montant_ttc'     => $mTtc,
                ]);

                $totalHt += $mHT;
                $totalTva += $mTva;
                $totalTtc += $mTtc;
            }

            // Update finished product attributes
            $data['prix_ht']  = $totalHt;
            $data['prix_tva'] = $totalTva;
            $data['prix_ttc'] = $totalTtc;

            $produitFini->update(collect($data)->except('components')->toArray());

            return response()->json($produitFini->fresh(['famille.parent', 'nomenclature.produit']));
        });
    }

    public function destroy(int $id): JsonResponse
    {
        $produitFini = ProduitFini::findOrFail($id);
        $this->authorize('delete', $produitFini);

        // Nomenclature will be deleted automatically due to CASCADE in DB foreign key
        $produitFini->delete();

        return response()->json(['message' => 'Produit composé supprimé avec succès.']);
    }

    public function nextReference(Request $request): JsonResponse
    {
        $designation = $request->query('designation', '');
        if (mb_strlen(trim($designation)) < 1) {
            return response()->json(['reference' => '']);
        }

        return response()->json([
            'reference' => $this->generateReference($designation)
        ]);
    }
}
