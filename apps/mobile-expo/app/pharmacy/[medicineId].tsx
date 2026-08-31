import { useEffect, useMemo, useState } from 'react';
import { ActivityIndicator, Linking, Pressable, ScrollView, Text, View } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  Baby,
  Bug,
  Check,
  ChevronLeft,
  CircleAlert,
  CircleDot,
  Droplet,
  FileText,
  FlaskConical,
  HeartPulse,
  Minus,
  Package,
  Pill,
  Plus,
  ShieldPlus,
  ShoppingCart,
  Sparkles,
  Store,
  Wind,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { colors } from '../../theme/tokens';
import {
  useMedicine,
  useNearbyPharmacies,
  usePrescriptionsForReservation,
  useReserveMedicine,
  type MedicineReservation,
  type NearbyPharmacy,
} from '../../lib/api/queries';

/**
 * Selected Medicine - the detail panel of the Medicine Finder.
 *
 * On the reference design this sits beside the pharmacy list in a second
 * column; on a phone it is the drill-in from tapping a pharmacy, carrying the
 * search origin along so the same nearby query can be reused rather than
 * re-guessed.
 *
 * Reserving places a hold only. No payment is taken here and none is implied -
 * the patient pays at the counter (Mobile Money capture is a later step).
 */

const CATEGORY_ICONS: Record<string, LucideIcon> = {
  pill: Pill,
  'shield-plus': ShieldPlus,
  droplet: Droplet,
  'heart-pulse': HeartPulse,
  'flask-conical': FlaskConical,
  wind: Wind,
  sparkles: Sparkles,
  'circle-dot': CircleDot,
  bug: Bug,
  baby: Baby,
  package: Package,
};

/** XAF is quoted in whole francs, grouped with spaces - "12 500 FCFA". */
function formatPrice(value: number | null | undefined): string | null {
  if (value === null || value === undefined) return null;
  const grouped = String(Math.round(value)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
  return `${grouped} FCFA`;
}

/** Pulls the backend's `error_code` out of an axios failure, if there is one. */
function errorCodeOf(error: unknown): string | null {
  const body = (error as { response?: { data?: { error_code?: string } } })?.response?.data;
  return body?.error_code ?? null;
}

export default function MedicineDetailScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const params = useLocalSearchParams<{
    medicineId?: string;
    pharmacyId?: string;
    lat?: string;
    lng?: string;
    radiusKm?: string;
  }>();

  const medicineId = params.medicineId;
  const lat = Number(params.lat ?? 4.0511);
  const lng = Number(params.lng ?? 9.7679);
  const radiusKm = Number(params.radiusKm ?? 5);

  const [pharmacyId, setPharmacyId] = useState<string | null>(params.pharmacyId ?? null);
  const [packSize, setPackSize] = useState<string | null>(null);
  const [quantity, setQuantity] = useState(1);
  const [prescriptionId, setPrescriptionId] = useState<string | null>(null);
  const [prescriptionListOpen, setPrescriptionListOpen] = useState(false);
  const [confirmed, setConfirmed] = useState<MedicineReservation | null>(null);
  const [errorCode, setErrorCode] = useState<string | null>(null);

  const medicine = useMedicine(medicineId);
  const pharmacies = useNearbyPharmacies({
    lat,
    lng,
    radiusKm,
    medicineId: medicineId ?? null,
    enabled: !!medicineId,
  });
  const reserve = useReserveMedicine();

  const needsPrescription = medicine.data?.prescription_required ?? false;
  const prescriptions = usePrescriptionsForReservation(needsPrescription);

  const pharmacyRows = useMemo(() => pharmacies.data ?? [], [pharmacies.data]);
  const pharmacy = useMemo(
    () => pharmacyRows.find((p) => p.id === pharmacyId) ?? pharmacyRows[0] ?? null,
    [pharmacyRows, pharmacyId],
  );

  // Default the pack size to whatever the chosen pharmacy actually prices.
  useEffect(() => {
    if (packSize) return;
    const fallback = pharmacy?.stock?.pack_size ?? medicine.data?.default_pack_size ?? null;
    if (fallback) setPackSize(fallback);
  }, [pharmacy, medicine.data, packSize]);

  if (!medicineId) {
    return (
      <Screen>
        <View className="flex-1 items-center justify-center">
          <Text className="text-sm text-navy-secondary">{t('pharmacy.loadFailed')}</Text>
        </View>
      </Screen>
    );
  }

  const unitPrice = pharmacy?.stock?.unit_price ?? null;
  const totalPrice = unitPrice === null ? null : unitPrice * quantity;
  const canReserve =
    !!pharmacy?.stock?.reservation_enabled && (!needsPrescription || !!prescriptionId);

  const CategoryIcon = CATEGORY_ICONS[medicine.data?.category_icon ?? 'pill'] ?? Pill;

  const onReserve = () => {
    if (!pharmacy) return;
    setErrorCode(null);
    reserve.mutate(
      {
        medicine_id: medicineId,
        care_facility_id: pharmacy.id,
        quantity,
        pack_size: packSize,
        prescription_id: prescriptionId,
      },
      {
        onSuccess: (reservation) => setConfirmed(reservation),
        onError: (error) => setErrorCode(errorCodeOf(error) ?? 'generic'),
      },
    );
  };

  return (
    <Screen className="px-0">
      <ScrollView className="flex-1 px-6" showsVerticalScrollIndicator={false}>
        {/* Header */}
        <View className="mt-2 flex-row items-center">
          <Pressable
            onPress={() => (router.canGoBack() ? router.back() : router.replace('/pharmacy'))}
            hitSlop={8}
            accessibilityRole="button"
            accessibilityLabel={t('pharmacy.back')}
            className="h-10 w-10 items-center justify-center rounded-xl border border-cream-300 bg-white"
          >
            <ChevronLeft size={20} color={colors.navy.text} />
          </Pressable>
          <Text className="ml-3 text-lg font-extrabold text-navy-text">
            {t('pharmacy.selectedMedicine')}
          </Text>
        </View>

        {medicine.isPending ? (
          <View className="mt-16 items-center">
            <ActivityIndicator color={colors.gold[500]} />
          </View>
        ) : medicine.isError || !medicine.data ? (
          <View className="mt-10 items-center rounded-2xl border border-cream-300 bg-white px-6 py-8">
            <CircleAlert size={22} color={colors.semantic.danger} />
            <Text className="mt-2 text-center text-sm text-navy-secondary">
              {t('pharmacy.loadFailed')}
            </Text>
            <Pressable
              onPress={() => medicine.refetch()}
              className="mt-3 rounded-xl border border-gold-500 px-4 py-2"
            >
              <Text className="text-xs font-bold text-gold-600">{t('pharmacy.retry')}</Text>
            </Pressable>
          </View>
        ) : (
          <>
            {/* Medicine summary */}
            <View className="mt-5 flex-row rounded-2xl border border-gold-100 bg-cream-200 p-4">
              <View className="h-16 w-16 items-center justify-center rounded-xl bg-white">
                <CategoryIcon size={24} color={colors.gold[600]} />
              </View>
              <View className="ml-4 flex-1">
                <Text className="text-base font-extrabold text-navy-text">
                  {medicine.data.name}
                </Text>
                <Text className="mt-1 text-xs text-navy-secondary">
                  {t('pharmacy.generic', { name: medicine.data.generic_name })}
                </Text>
                <Text
                  className="mt-1 text-xs font-semibold"
                  style={{
                    color: medicine.data.availability.is_available
                      ? colors.semantic.success
                      : colors.semantic.danger,
                  }}
                >
                  {medicine.data.availability.is_available
                    ? t('pharmacy.available')
                    : t('pharmacy.unavailable')}
                </Text>
              </View>
            </View>

            {/* About */}
            <Text className="mt-6 text-base font-bold text-navy-text">
              {t('pharmacy.aboutMedicine')}
            </Text>
            <Text className="mt-2 text-sm leading-5 text-navy-secondary">
              {medicine.data.description ?? t('pharmacy.noDescription')}
            </Text>
            {medicine.data.indications.length > 0 ? (
              <View className="mt-3 flex-row flex-wrap" style={{ gap: 6 }}>
                {medicine.data.indications.map((tag) => (
                  <View key={tag} className="rounded-lg bg-white px-2 py-1">
                    <Text className="text-[11px] font-medium text-navy-secondary">{tag}</Text>
                  </View>
                ))}
              </View>
            ) : null}

            {/* Pack sizes */}
            {medicine.data.pack_size_options.length > 0 ? (
              <>
                <Text className="mt-6 text-base font-bold text-navy-text">
                  {t('pharmacy.packSizeOptions')}
                </Text>
                <View className="mt-2 flex-row flex-wrap" style={{ gap: 8 }}>
                  {medicine.data.pack_size_options.map((option) => {
                    const active = option === packSize;
                    return (
                      <Pressable
                        key={option}
                        onPress={() => setPackSize(option)}
                        className={`rounded-xl border px-3 py-2 ${
                          active ? 'border-gold-500 bg-gold-50' : 'border-cream-300 bg-white'
                        }`}
                      >
                        <Text
                          className={`text-xs font-semibold ${
                            active ? 'text-gold-700' : 'text-navy-secondary'
                          }`}
                        >
                          {option}
                        </Text>
                      </Pressable>
                    );
                  })}
                </View>
              </>
            ) : null}

            {/* Quantity */}
            <Text className="mt-6 text-base font-bold text-navy-text">
              {t('pharmacy.quantity')}
            </Text>
            <View className="mt-2 flex-row items-center">
              <Pressable
                onPress={() => setQuantity((q) => Math.max(1, q - 1))}
                className="h-10 w-10 items-center justify-center rounded-xl border border-cream-300 bg-white"
                accessibilityRole="button"
              >
                <Minus size={16} color={colors.navy.text} />
              </Pressable>
              <Text className="mx-5 text-base font-extrabold text-navy-text">{quantity}</Text>
              <Pressable
                onPress={() => setQuantity((q) => Math.min(10, q + 1))}
                className="h-10 w-10 items-center justify-center rounded-xl border border-cream-300 bg-white"
                accessibilityRole="button"
              >
                <Plus size={16} color={colors.navy.text} />
              </Pressable>
            </View>

            {/* Pharmacy choice */}
            <Text className="mt-6 text-base font-bold text-navy-text">
              {t('pharmacy.choosePharmacy')}
            </Text>
            {pharmacies.isPending ? (
              <View className="mt-4 items-center">
                <ActivityIndicator color={colors.gold[500]} />
              </View>
            ) : pharmacyRows.length === 0 ? (
              <Text className="mt-2 text-sm text-navy-secondary">
                {t('pharmacy.noPharmacies', { km: radiusKm })}
              </Text>
            ) : (
              <View className="mt-2" style={{ gap: 8 }}>
                {pharmacyRows.map((row) => (
                  <PharmacyOption
                    key={row.id}
                    pharmacy={row}
                    selected={pharmacy?.id === row.id}
                    onPress={() => {
                      setPharmacyId(row.id);
                      setConfirmed(null);
                      setErrorCode(null);
                    }}
                  />
                ))}
              </View>
            )}

            {/* Price */}
            <View className="mt-6 rounded-2xl border border-cream-300 bg-white p-4">
              <Text className="text-[11px] text-navy-muted">{t('pharmacy.priceInPharmacy')}</Text>
              <Text className="mt-1 text-2xl font-extrabold text-gold-600">
                {formatPrice(totalPrice) ?? t('pharmacy.priceUnavailable')}
              </Text>
              <Text className="mt-1 text-xs text-navy-secondary">
                {pharmacy?.name ?? t('pharmacy.noPharmacySelected')}
              </Text>
              <Text className="mt-3 text-[11px] leading-4 text-navy-muted">
                {t('pharmacy.paymentNote')}
              </Text>
            </View>

            {/* Prescription */}
            {needsPrescription ? (
              <View className="mt-4 rounded-2xl border border-gold-100 bg-gold-50 p-4">
                <Text className="text-sm font-bold text-navy-text">
                  {t('pharmacy.prescriptionCardTitle')}
                </Text>
                <Text className="mt-1 text-xs leading-4 text-navy-secondary">
                  {t('pharmacy.prescriptionCardBody')}
                </Text>

                <Pressable
                  onPress={() => setPrescriptionListOpen((open) => !open)}
                  className="mt-3 h-11 flex-row items-center justify-center rounded-xl border border-gold-500 bg-white px-3"
                  accessibilityRole="button"
                >
                  <FileText size={15} color={colors.gold[600]} />
                  <Text className="ml-2 text-xs font-bold text-gold-600">
                    {prescriptionId
                      ? t('pharmacy.prescriptionAttached')
                      : t('pharmacy.attachPrescription')}
                  </Text>
                </Pressable>

                {prescriptionListOpen ? (
                  prescriptions.isPending ? (
                    <View className="mt-3 items-center">
                      <ActivityIndicator color={colors.gold[500]} />
                    </View>
                  ) : (prescriptions.data ?? []).length === 0 ? (
                    <Text className="mt-3 text-xs text-navy-secondary">
                      {t('pharmacy.noPrescriptions')}
                    </Text>
                  ) : (
                    <View className="mt-3 overflow-hidden rounded-xl border border-cream-300 bg-white">
                      {(prescriptions.data ?? []).map((rx) => {
                        const active = rx.id === prescriptionId;
                        return (
                          <Pressable
                            key={rx.id}
                            onPress={() => {
                              setPrescriptionId(active ? null : rx.id);
                              setPrescriptionListOpen(false);
                              setErrorCode(null);
                            }}
                            className="flex-row items-center justify-between border-b border-cream-200 px-3 py-3"
                          >
                            <View className="flex-1 pr-2">
                              <Text className="text-xs font-semibold text-navy-text" numberOfLines={1}>
                                {rx.facility_name ?? t('pharmacy.prescriptionCardTitle')}
                              </Text>
                              <Text className="mt-0.5 text-[11px] text-navy-secondary">
                                {t('pharmacy.prescriptionItems', { count: rx.item_count })}
                                {rx.prescribed_at
                                  ? `  •  ${new Date(rx.prescribed_at).toLocaleDateString()}`
                                  : ''}
                              </Text>
                            </View>
                            {active ? <Check size={16} color={colors.gold[600]} /> : null}
                          </Pressable>
                        );
                      })}
                    </View>
                  )
                ) : null}
              </View>
            ) : null}

            {/* Result banners */}
            {confirmed ? (
              <View
                className="mt-4 rounded-2xl p-4"
                style={{ backgroundColor: colors.semantic.successSurface }}
              >
                <Text
                  className="text-sm font-bold"
                  style={{ color: colors.semantic.success }}
                >
                  {t('pharmacy.reserveSuccessTitle')}
                </Text>
                <Text className="mt-1 text-xs leading-4 text-navy-secondary">
                  {t('pharmacy.reserveSuccessBody', {
                    reference: confirmed.reference,
                    pharmacy: confirmed.pharmacy?.name ?? pharmacy?.name ?? '',
                  })}
                </Text>
              </View>
            ) : null}

            {errorCode ? (
              <View
                className="mt-4 rounded-2xl p-4"
                style={{ backgroundColor: colors.semantic.dangerSurface }}
              >
                <Text className="text-xs leading-4" style={{ color: colors.semantic.danger }}>
                  {t(`pharmacy.reserveError.${errorCode}`, {
                    defaultValue: t('pharmacy.reserveError.generic'),
                  })}
                </Text>
              </View>
            ) : null}

            {/* Actions */}
            <View className="mt-5" style={{ gap: 12 }}>
              <Button
                label={t('pharmacy.reserve')}
                onPress={onReserve}
                leftIcon={ShoppingCart}
                showChevron={false}
                loading={reserve.isPending}
                disabled={!canReserve || !!confirmed}
              />

              {pharmacy?.latitude != null && pharmacy?.longitude != null ? (
                <Button
                  label={t('pharmacy.getDirections')}
                  variant="outline"
                  onPress={() => {
                    const label = encodeURIComponent(pharmacy.name);
                    Linking.openURL(
                      `https://www.google.com/maps/search/?api=1&query=${pharmacy.latitude},${pharmacy.longitude}&query_place_id=${label}`,
                    ).catch(() => undefined);
                  }}
                />
              ) : null}

              {pharmacy?.phone ? (
                <Button
                  label={t('pharmacy.callPharmacy')}
                  variant="outline"
                  onPress={() => {
                    Linking.openURL(`tel:${pharmacy.phone}`).catch(() => undefined);
                  }}
                />
              ) : null}
            </View>

            {!canReserve && !confirmed ? (
              <Text className="mt-3 text-center text-[11px] text-navy-muted">
                {needsPrescription && !prescriptionId
                  ? t('pharmacy.reserveError.PRESCRIPTION_REQUIRED')
                  : t('pharmacy.noPharmacySelected')}
              </Text>
            ) : null}
          </>
        )}

        <View className="h-10" />
      </ScrollView>
    </Screen>
  );
}

function PharmacyOption({
  pharmacy,
  selected,
  onPress,
}: {
  pharmacy: NearbyPharmacy;
  selected: boolean;
  onPress: () => void;
}) {
  const { t } = useTranslation();
  const price = formatPrice(pharmacy.stock?.unit_price);

  return (
    <Pressable
      onPress={onPress}
      className={`flex-row items-center rounded-2xl border p-3 ${
        selected ? 'border-gold-500 bg-gold-50' : 'border-cream-300 bg-white'
      }`}
      accessibilityRole="button"
    >
      <View className="h-11 w-11 items-center justify-center rounded-xl bg-white">
        <Store size={18} color={colors.gold[600]} />
      </View>
      <View className="ml-3 flex-1">
        <Text className="text-sm font-bold text-navy-text" numberOfLines={1}>
          {pharmacy.name}
        </Text>
        <Text className="mt-0.5 text-[11px] text-navy-secondary">
          {t('pharmacy.distanceKm', { km: pharmacy.distance_km })}
          {pharmacy.stock ? `  •  ${t(`pharmacy.stock.${pharmacy.stock.status}`)}` : ''}
        </Text>
      </View>
      {price ? <Text className="text-xs font-bold text-gold-700">{price}</Text> : null}
    </Pressable>
  );
}
