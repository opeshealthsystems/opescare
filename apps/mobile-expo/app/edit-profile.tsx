import { useEffect, useRef, useState } from 'react';
import { Pressable, ScrollView, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import { ArrowLeft, MapPin, Phone, User, UserRound, Users } from 'lucide-react-native';
import { Screen } from '../components/ui/Screen';
import { Button } from '../components/ui/Button';
import { TextField } from '../components/ui/TextField';
import { useAuthStore } from '../lib/store/auth';
import { useUpdateProfile } from '../lib/api/queries';
import { colors } from '../theme/tokens';

const SEX_OPTIONS = ['male', 'female', 'other'] as const;
const BLOOD_GROUPS = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as const;

function extractErrorMessage(err: unknown, fallback: string): string {
  const anyErr = err as any;
  return anyErr?.response?.data?.message ?? fallback;
}

/** Edit Profile — PATCH /mobile/me. The only place a patient can correct
 * their own demographic data and emergency contact after signup; both were
 * previously write-once (captured at registration, never editable again). */
export default function EditProfileScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const patient = useAuthStore((s) => s.patient);
  const updateProfile = useUpdateProfile();

  const [firstName, setFirstName] = useState(patient?.first_name ?? '');
  const [lastName, setLastName] = useState(patient?.last_name ?? '');
  const [sex, setSex] = useState<(typeof SEX_OPTIONS)[number] | null>(
    (patient?.sex as (typeof SEX_OPTIONS)[number] | null) ?? null,
  );
  const [bloodGroup, setBloodGroup] = useState<string | null>(patient?.blood_group ?? null);
  const [address, setAddress] = useState(patient?.address ?? '');
  const [emergencyName, setEmergencyName] = useState(patient?.emergency_contact?.name ?? '');
  const [emergencyRelationship, setEmergencyRelationship] = useState(
    patient?.emergency_contact?.relationship ?? '',
  );
  const [emergencyPhone, setEmergencyPhone] = useState(patient?.emergency_contact?.phone ?? '');

  // The auth store's `patient` can still be null at mount (e.g. a deep link,
  // or a slow /me fetch that hasn't resolved yet) — the useState initializers
  // above only run once, so without this the form would stay blank forever
  // even after `patient` populates. Syncs exactly once, the first time
  // `patient` becomes available, so it never clobbers what the guardian is
  // actively typing.
  const hasHydrated = useRef(false);
  useEffect(() => {
    if (hasHydrated.current || !patient) return;
    hasHydrated.current = true;
    setFirstName(patient.first_name ?? '');
    setLastName(patient.last_name ?? '');
    setSex((patient.sex as (typeof SEX_OPTIONS)[number] | null) ?? null);
    setBloodGroup(patient.blood_group ?? null);
    setAddress(patient.address ?? '');
    setEmergencyName(patient.emergency_contact?.name ?? '');
    setEmergencyRelationship(patient.emergency_contact?.relationship ?? '');
    setEmergencyPhone(patient.emergency_contact?.phone ?? '');
  }, [patient]);

  const [formError, setFormError] = useState<string | null>(null);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);

  const handleSave = async () => {
    setFormError(null);
    setSuccessMessage(null);

    const hasAnyEmergencyField =
      emergencyName.trim() || emergencyRelationship.trim() || emergencyPhone.trim();
    if (
      hasAnyEmergencyField &&
      (!emergencyName.trim() || !emergencyRelationship.trim() || !emergencyPhone.trim())
    ) {
      setFormError(t('editProfile.emergencyIncomplete'));
      return;
    }

    try {
      await updateProfile.mutateAsync({
        first_name: firstName.trim(),
        last_name: lastName.trim(),
        sex,
        blood_group: bloodGroup,
        address: address.trim() || null,
        ...(hasAnyEmergencyField
          ? {
              emergency_contact: {
                name: emergencyName.trim(),
                relationship: emergencyRelationship.trim(),
                phone: emergencyPhone.trim(),
              },
            }
          : {}),
      });
      setSuccessMessage(t('editProfile.saved'));
    } catch (err) {
      setFormError(extractErrorMessage(err, t('editProfile.saveFailed')));
    }
  };

  return (
    <Screen className="px-0">
      <ScrollView
        className="flex-1 px-6"
        showsVerticalScrollIndicator={false}
        keyboardShouldPersistTaps="handled"
        contentContainerStyle={{ paddingBottom: 40 }}
      >
        <View className="flex-row items-center pt-2">
          <Pressable
            onPress={() => router.back()}
            hitSlop={8}
            className="h-11 w-11 items-center justify-center rounded-full border border-gold-300"
          >
            <ArrowLeft size={18} color={colors.gold[600]} />
          </Pressable>
          <Text className="ml-4 text-xl font-extrabold text-navy-text">
            {t('editProfile.title')}
          </Text>
        </View>
        <Text className="mt-2 text-sm text-navy-secondary">{t('editProfile.subtitle')}</Text>

        <View className="mt-6">
          <TextField
            label={t('signup.firstName')}
            icon={User}
            value={firstName}
            onChangeText={setFirstName}
          />
          <TextField
            label={t('signup.lastName')}
            icon={User}
            value={lastName}
            onChangeText={setLastName}
          />

          <Text className="mb-2 text-sm font-semibold text-navy-text">{t('editProfile.sex')}</Text>
          <View className="mb-4 flex-row" style={{ gap: 8 }}>
            {SEX_OPTIONS.map((option) => {
              const active = sex === option;
              return (
                <Pressable
                  key={option}
                  onPress={() => setSex(option)}
                  className="flex-1 items-center rounded-full border px-3 py-2"
                  style={{
                    borderColor: active ? colors.gold[500] : colors.cream[300],
                    backgroundColor: active ? colors.gold[50] : colors.white,
                  }}
                >
                  <Text
                    className="text-xs font-semibold"
                    style={{ color: active ? colors.gold[600] : colors.navy.secondary }}
                  >
                    {t(`signup.sex${option.charAt(0).toUpperCase()}${option.slice(1)}`)}
                  </Text>
                </Pressable>
              );
            })}
          </View>

          <Text className="mb-2 text-sm font-semibold text-navy-text">
            {t('editProfile.bloodGroup')}
          </Text>
          <View className="mb-4 flex-row flex-wrap" style={{ gap: 8 }}>
            {BLOOD_GROUPS.map((option) => {
              const active = bloodGroup === option;
              return (
                <Pressable
                  key={option}
                  onPress={() => setBloodGroup(active ? null : option)}
                  className="rounded-full border px-4 py-2"
                  style={{
                    borderColor: active ? colors.gold[500] : colors.cream[300],
                    backgroundColor: active ? colors.gold[50] : colors.white,
                  }}
                >
                  <Text
                    className="text-xs font-semibold"
                    style={{ color: active ? colors.gold[600] : colors.navy.secondary }}
                  >
                    {option}
                  </Text>
                </Pressable>
              );
            })}
          </View>

          <TextField
            label={t('editProfile.address')}
            placeholder={t('editProfile.addressPlaceholder')}
            icon={MapPin}
            value={address}
            onChangeText={setAddress}
          />

          <Text className="mb-3 mt-2 text-base font-bold text-navy-text">
            {t('editProfile.emergencyContactTitle')}
          </Text>
          <TextField
            label={t('signup.emergencyName')}
            placeholder={t('signup.emergencyNamePlaceholder')}
            icon={UserRound}
            value={emergencyName}
            onChangeText={setEmergencyName}
          />
          <TextField
            label={t('signup.emergencyRelationship')}
            placeholder={t('signup.emergencyRelationshipPlaceholder')}
            icon={Users}
            value={emergencyRelationship}
            onChangeText={setEmergencyRelationship}
          />
          <TextField
            label={t('signup.emergencyPhone')}
            placeholder={t('signup.emergencyPhonePlaceholder')}
            icon={Phone}
            keyboardType="phone-pad"
            value={emergencyPhone}
            onChangeText={setEmergencyPhone}
          />

          {formError ? <Text className="mb-3 text-sm text-danger">{formError}</Text> : null}
          {successMessage ? (
            <View
              className="mb-3 rounded-2xl p-3"
              style={{ backgroundColor: colors.semantic.successSurface }}
            >
              <Text
                className="text-center text-sm font-semibold"
                style={{ color: colors.semantic.success }}
              >
                {successMessage}
              </Text>
            </View>
          ) : null}

          <Button
            label={t('editProfile.save')}
            onPress={handleSave}
            loading={updateProfile.isPending}
            showChevron={false}
          />
        </View>
      </ScrollView>
    </Screen>
  );
}
