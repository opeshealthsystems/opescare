import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { apiClient } from './client';
import { endpoints } from './endpoints';
import type { HealthIdCard, PaginatedAppointments } from './types';

export function useHealthIdCard() {
  return useQuery({
    queryKey: ['health-id-card'],
    queryFn: async () => (await apiClient.get<HealthIdCard>(endpoints.healthIdCard)).data,
  });
}

export function useUpcomingAppointments() {
  return useQuery({
    queryKey: ['appointments', 'upcoming'],
    queryFn: async () =>
      (
        await apiClient.get<PaginatedAppointments>(endpoints.appointments, {
          params: { scope: 'upcoming', limit: 3 },
        })
      ).data,
  });
}

/* ────────────────────────────────────────────────────────────────────────────
 * Pharmacy / Medicine Finder
 * Mirrors the payloads built by MobilePharmacyController. Category and stock
 * values are the backed values of App\Enums\MedicineCategory and
 * App\Enums\PharmacyStockStatus — keep them in step with those enums.
 * ──────────────────────────────────────────────────────────────────────────── */

export type MedicineCategoryValue =
  | 'pain_relief'
  | 'antibiotics'
  | 'diabetes'
  | 'cardio'
  | 'vitamins'
  | 'respiratory'
  | 'skin_care'
  | 'digestive'
  | 'antimalarial'
  | 'maternal_child'
  | 'other';

export type StockStatusValue = 'in_stock' | 'low_stock' | 'out_of_stock' | 'unknown';

export interface MedicineCategorySummary {
  value: MedicineCategoryValue;
  label: string;
  icon: string;
  medicine_count: number;
}

export interface MedicineAvailability {
  pharmacy_count: number;
  price_min: number | null;
  price_max: number | null;
  currency: string;
  is_available: boolean;
}

export interface Medicine {
  id: string;
  name: string;
  generic_name: string;
  brand_name: string | null;
  strength: string | null;
  form: string | null;
  category: MedicineCategoryValue;
  category_label: string;
  category_icon: string;
  description: string | null;
  indications: string[];
  prescription_required: boolean;
  is_controlled: boolean;
  default_pack_size: string | null;
  pack_size_options: string[];
  price_min: number | null;
  price_max: number | null;
  currency: string;
  availability: MedicineAvailability;
}

export interface PharmacyStock {
  status: StockStatusValue;
  status_label: string;
  is_available: boolean;
  packs_available: number | null;
  pack_size: string | null;
  unit_price: number | null;
  currency: string;
  reservation_enabled: boolean;
  last_reported_at: string | null;
}

export interface NearbyPharmacy {
  id: string;
  name: string;
  city: string | null;
  region: string | null;
  address: string | null;
  latitude: number | null;
  longitude: number | null;
  phone: string | null;
  verification_status: string | null;
  distance_km: number;
  is_open: boolean | null;
  opens_at: string | null;
  closes_at: string | null;
  is_24_hours: boolean;
  stock: PharmacyStock | null;
}

export interface MedicineReservation {
  id: string;
  reference: string;
  status: string;
  status_label: string;
  is_open: boolean;
  is_cancellable: boolean;
  quantity: number;
  pack_size: string | null;
  unit_price: number | null;
  total_price: number | null;
  currency: string;
  prescription_id: string | null;
  patient_note: string | null;
  pharmacy_note: string | null;
  expires_at: string | null;
  created_at: string | null;
  medicine: { id: string; name: string; generic_name: string } | null;
  pharmacy: { id: string; name: string; city: string | null; phone: string | null } | null;
}

export interface PrescriptionSummary {
  id: string;
  facility_name: string | null;
  status: string;
  item_count: number;
  prescribed_at: string | null;
  expires_at: string | null;
}

export function useMedicineCategories() {
  return useQuery({
    queryKey: ['pharmacy', 'categories'],
    queryFn: async () =>
      (
        await apiClient.get<{
          data: { total_medicines: number; categories: MedicineCategorySummary[] };
        }>(endpoints.pharmacyCategories)
      ).data.data,
    staleTime: 5 * 60 * 1000,
  });
}

export function useMedicineSearch(params: { q?: string; category?: MedicineCategoryValue | null }) {
  const q = params.q?.trim() ?? '';
  const category = params.category ?? null;

  return useQuery({
    queryKey: ['pharmacy', 'medicines', q, category],
    queryFn: async () =>
      (
        await apiClient.get<{ data: Medicine[] }>(endpoints.medicineSearch, {
          params: {
            ...(q ? { q } : {}),
            ...(category ? { category } : {}),
            per_page: 20,
          },
        })
      ).data.data,
  });
}

export function useMedicine(medicineId: string | undefined) {
  return useQuery({
    queryKey: ['pharmacy', 'medicine', medicineId],
    enabled: !!medicineId,
    queryFn: async () =>
      (await apiClient.get<{ data: Medicine }>(endpoints.medicine(medicineId as string))).data.data,
  });
}

export function useNearbyPharmacies(args: {
  lat: number;
  lng: number;
  radiusKm: number;
  medicineId?: string | null;
  onlyStocking?: boolean;
  enabled?: boolean;
}) {
  const { lat, lng, radiusKm, medicineId = null, onlyStocking = false, enabled = true } = args;

  return useQuery({
    queryKey: ['pharmacy', 'nearby', lat, lng, radiusKm, medicineId, onlyStocking],
    enabled,
    queryFn: async () =>
      (
        await apiClient.get<{ data: NearbyPharmacy[] }>(endpoints.pharmacyNearby, {
          params: {
            lat,
            lng,
            radius_km: radiusKm,
            ...(medicineId ? { medicine_id: medicineId } : {}),
            ...(onlyStocking ? { only_stocking: 1 } : {}),
          },
        })
      ).data.data,
  });
}

export function useMedicineReservations(scope: 'all' | 'open' = 'all') {
  return useQuery({
    queryKey: ['pharmacy', 'reservations', scope],
    queryFn: async () =>
      (
        await apiClient.get<{ data: MedicineReservation[] }>(endpoints.medicineReservations, {
          params: { scope },
        })
      ).data.data,
  });
}

export interface ReserveMedicineInput {
  medicine_id: string;
  care_facility_id: string;
  quantity?: number;
  pack_size?: string | null;
  prescription_id?: string | null;
  note?: string | null;
}

export function useReserveMedicine() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (input: ReserveMedicineInput) =>
      (
        await apiClient.post<{ data: MedicineReservation }>(endpoints.medicineReservations, input)
      ).data.data,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['pharmacy', 'reservations'] });
    },
  });
}

export function useCancelMedicineReservation() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (args: { id: string; reason?: string }) =>
      (
        await apiClient.post<{ data: MedicineReservation }>(
          endpoints.cancelMedicineReservation(args.id),
          { reason: args.reason ?? null },
        )
      ).data.data,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['pharmacy', 'reservations'] });
    },
  });
}

/** The patient's own prescriptions — attachable to a prescription-only reservation. */
export function usePrescriptions(enabled = true) {
  return useQuery({
    queryKey: ['prescriptions', 'list'],
    enabled,
    queryFn: async () =>
      (
        await apiClient.get<{ data: PrescriptionSummary[] }>(endpoints.prescriptions, {
          params: { limit: 20 },
        })
      ).data.data,
  });
}
