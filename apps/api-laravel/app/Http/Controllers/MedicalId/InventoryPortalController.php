<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Modules\Inventory\Services\PharmacyInventoryService;
use App\Modules\Inventory\Services\BloodInventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InventoryPortalController extends Controller
{
    public function __construct(
        private PharmacyInventoryService $pharmacyService,
        private BloodInventoryService    $bloodService,
    ) {}

    // ── Helpers ──────────────────────────────────────────────

    private function demoFacilityId(): string
    {
        return Facility::value('id') ?? '';
    }

    // ── Pharmacy Inventory ───────────────────────────────────

    public function pharmacy(Request $request): View
    {
        $facilityId = $this->demoFacilityId();
        $items      = $this->pharmacyService->list($facilityId, $request->only([
            'stock_status', 'form', 'is_expired', 'search',
        ]));
        $summary    = $this->pharmacyService->summary($facilityId);
        $forms      = $items->pluck('form')->filter()->unique()->sort()->values();

        return view('portals.staff.inventory.pharmacy', compact('items', 'summary', 'forms'));
    }

    public function pharmacyStore(Request $request): RedirectResponse
    {
        $request->validate([
            'medicine_name' => 'required|string|max:200',
            'generic_name'  => 'required|string|max:200',
            'form'          => 'required|string|max:80',
            'strength'      => 'required|string|max:80',
            'available_quantity' => 'required|integer|min:0',
        ]);

        $this->pharmacyService->addItem($this->demoFacilityId(), $request->except(['_token']));

        return redirect()->route('portals.staff.inventory.pharmacy')
            ->with('success', 'Item added to pharmacy inventory.');
    }

    public function pharmacyRestock(Request $request, string $id): RedirectResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        $this->pharmacyService->adjustQuantity($id, (int) $request->input('quantity'), 'add');
        return redirect()->route('portals.staff.inventory.pharmacy')
            ->with('success', 'Stock restocked.');
    }

    public function pharmacyDispense(Request $request, string $id): RedirectResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        try {
            $this->pharmacyService->adjustQuantity($id, (int) $request->input('quantity'), 'subtract');
            return redirect()->route('portals.staff.inventory.pharmacy')
                ->with('success', 'Stock dispensed.');
        } catch (\Throwable $e) {
            return redirect()->route('portals.staff.inventory.pharmacy')
                ->with('error', $e->getMessage());
        }
    }

    public function pharmacyFlag(Request $request, string $id): RedirectResponse
    {
        $this->pharmacyService->setFlags($id, $request->only([
            'is_expired', 'is_recalled', 'is_quarantined',
        ]));
        return redirect()->route('portals.staff.inventory.pharmacy')
            ->with('success', 'Item flags updated.');
    }

    public function pharmacyDelete(string $id): RedirectResponse
    {
        $this->pharmacyService->removeItem($id);
        return redirect()->route('portals.staff.inventory.pharmacy')
            ->with('success', 'Item removed from inventory.');
    }

    // ── Blood Inventory ───────────────────────────────────────

    public function blood(Request $request): View
    {
        $facilityId = $this->demoFacilityId();
        $items      = $this->bloodService->list($facilityId, $request->only(['blood_group', 'component']));
        $summary    = $this->bloodService->summary($facilityId);

        return view('portals.staff.inventory.blood', compact('items', 'summary'));
    }

    public function bloodUpsert(Request $request): RedirectResponse
    {
        $request->validate([
            'blood_group'    => 'required|string|max:10',
            'component'      => 'required|string|max:80',
            'available_units'=> 'required|integer|min:0',
        ]);

        $this->bloodService->upsertUnit($this->demoFacilityId(), $request->except(['_token']));

        return redirect()->route('portals.staff.inventory.blood')
            ->with('success', 'Blood inventory updated.');
    }

    public function bloodAdjust(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'units'     => 'required|integer|min:1',
            'direction' => 'required|in:add,subtract',
        ]);

        $this->bloodService->adjustUnits($id, (int) $request->input('units'), $request->input('direction'));

        return redirect()->route('portals.staff.inventory.blood')
            ->with('success', 'Blood stock adjusted.');
    }

    public function bloodFlag(Request $request, string $id): RedirectResponse
    {
        $this->bloodService->setFlags($id, $request->only([
            'is_expired', 'is_quarantined', 'is_unsafe',
        ]));
        return redirect()->route('portals.staff.inventory.blood')
            ->with('success', 'Blood unit flags updated.');
    }
}
