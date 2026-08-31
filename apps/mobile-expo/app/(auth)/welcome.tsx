import { Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import Svg, { Path } from 'react-native-svg';
import { LinearGradient } from 'expo-linear-gradient';
import {
  ShieldCheck,
  UserRound,
  ChevronRight,
  Stethoscope,
  Syringe,
  Pill,
  FlaskConical,
  Microscope,
  ClipboardList,
  ShieldPlus,
  Dna,
  Cross,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { Logo } from '../../components/ui/Logo';
import { colors } from '../../theme/tokens';

/** Faint recurring medical iconography scattered behind the whole screen —
 * mirrors the reference's watermark pattern of stethoscope/DNA/pill/syringe
 * line-icons on the cream background. Purely decorative, non-interactive. */
const BACKGROUND_ICONS: Array<{
  Icon: LucideIcon;
  top: number;
  left?: number;
  right?: number;
  size: number;
  rotate?: string;
}> = [
  { Icon: ClipboardList, top: 40, left: 132, size: 30, rotate: '-8deg' },
  { Icon: Cross, top: 54, right: 84, size: 24 },
  { Icon: Stethoscope, top: 108, left: 20, size: 34, rotate: '10deg' },
  { Icon: Dna, top: 88, right: 132, size: 30, rotate: '-6deg' },
  { Icon: Syringe, top: 244, right: 34, size: 30, rotate: '18deg' },
  { Icon: Microscope, top: 268, left: 26, size: 28 },
  { Icon: ShieldPlus, top: 372, right: 26, size: 26 },
  { Icon: FlaskConical, top: 400, left: 40, size: 24, rotate: '-10deg' },
  { Icon: Pill, top: 336, right: 150, size: 22, rotate: '24deg' },
];

function BackgroundPattern() {
  return (
    <View
      pointerEvents="none"
      style={{ position: 'absolute', top: 0, bottom: 0, left: -24, right: -24 }}
    >
      {BACKGROUND_ICONS.map(({ Icon, top, left, right, size, rotate }, index) => (
        <View
          key={index}
          style={{
            position: 'absolute',
            top,
            left,
            right,
            opacity: 0.28,
            transform: rotate ? [{ rotate }] : undefined,
          }}
        >
          <Icon size={size} color={colors.gold[300]} strokeWidth={1.25} />
        </View>
      ))}
    </View>
  );
}

const AVATAR_SIZE = 46;

/** Stand-in for the reference's family photo: no real photograph asset exists
 * yet, so this renders a tasteful gold-gradient card with an overlapping
 * avatar cluster instead of leaving the area blank. Swap for a real photo
 * once brand photography is available. */
function FamilyIllustration() {
  const { t } = useTranslation();

  return (
    <View
      accessible
      accessibilityLabel={t('auth.familyIllustrationAlt')}
      style={{
        width: 134,
        height: 164,
        marginRight: -24,
        borderTopLeftRadius: 28,
        borderBottomLeftRadius: 28,
        overflow: 'hidden',
      }}
    >
      <LinearGradient
        colors={[colors.gold[100], colors.cream[200]]}
        style={{ flex: 1, alignItems: 'center', justifyContent: 'center' }}
      >
        <View style={{ flexDirection: 'row', alignItems: 'flex-end' }}>
          <View
            style={{
              width: AVATAR_SIZE - 8,
              height: AVATAR_SIZE - 8,
              borderRadius: (AVATAR_SIZE - 8) / 2,
              marginRight: -12,
              backgroundColor: colors.gold[300],
              borderWidth: 2,
              borderColor: colors.cream[50],
              alignItems: 'center',
              justifyContent: 'center',
              zIndex: 1,
            }}
          >
            <UserRound size={18} color={colors.white} />
          </View>
          <View
            style={{
              width: AVATAR_SIZE,
              height: AVATAR_SIZE,
              borderRadius: AVATAR_SIZE / 2,
              marginBottom: 10,
              backgroundColor: colors.gold[500],
              borderWidth: 2,
              borderColor: colors.cream[50],
              alignItems: 'center',
              justifyContent: 'center',
              zIndex: 2,
            }}
          >
            <UserRound size={22} color={colors.white} />
          </View>
          <View
            style={{
              width: AVATAR_SIZE - 10,
              height: AVATAR_SIZE - 10,
              borderRadius: (AVATAR_SIZE - 10) / 2,
              marginLeft: -12,
              backgroundColor: colors.gold[600],
              borderWidth: 2,
              borderColor: colors.cream[50],
              alignItems: 'center',
              justifyContent: 'center',
              zIndex: 1,
            }}
          >
            <UserRound size={16} color={colors.white} />
          </View>
        </View>
      </LinearGradient>
    </View>
  );
}

/** Shallow gold arc separating the hero block from the CTA stack — echoes the
 * curved line sweeping under the photo in the reference. */
function CurveDivider() {
  return (
    <View style={{ marginHorizontal: -24, marginTop: 18 }}>
      <Svg width="100%" height={22} viewBox="0 0 400 22" preserveAspectRatio="none">
        <Path
          d="M0,4 Q200,26 400,4"
          stroke={colors.gold[300]}
          strokeWidth={2}
          fill="none"
          strokeLinecap="round"
        />
      </Svg>
    </View>
  );
}

export default function WelcomeScreen() {
  const { t } = useTranslation();
  const router = useRouter();

  return (
    <Screen className="justify-between py-6">
      <BackgroundPattern />

      <View className="items-center pt-4">
        <Logo />
      </View>

      <View>
        <View className="flex-row items-start">
          <View className="flex-1 pr-3">
            <Text className="text-3xl font-extrabold leading-tight text-navy-text">
              {t('auth.welcomeHeadline1')}
            </Text>
            <Text className="text-3xl font-extrabold leading-tight text-navy-text">
              {t('auth.welcomeHeadline2')}
            </Text>
            <Text className="text-3xl font-extrabold leading-tight text-gold-500">
              {t('auth.welcomeHeadline3')}
            </Text>
          </View>
          <FamilyIllustration />
        </View>

        <View className="my-3 h-1 w-12 rounded-full bg-gold-500" />
        <Text className="text-base text-navy-secondary">{t('auth.welcomeBody')}</Text>

        <CurveDivider />
      </View>

      <View>
        <Button label={t('auth.getStarted')} onPress={() => router.push('/(auth)/login')} />
        <View className="h-3" />
        <View className="relative">
          <Button
            label={t('auth.haveAccount')}
            variant="outline"
            onPress={() => router.push('/(auth)/login')}
          />
          <View
            pointerEvents="none"
            style={{ position: 'absolute', top: 0, bottom: 0, left: 16, justifyContent: 'center' }}
          >
            <UserRound size={18} color={colors.gold[600]} />
          </View>
          <View
            pointerEvents="none"
            style={{ position: 'absolute', top: 0, bottom: 0, right: 16, justifyContent: 'center' }}
          >
            <ChevronRight size={18} color={colors.gold[600]} />
          </View>
        </View>
        <View className="mt-5 flex-row items-center justify-center px-4">
          <ShieldCheck size={14} color={colors.gold[600]} />
          <Text className="ml-2 text-center text-xs text-navy-muted">{t('auth.securityNote')}</Text>
        </View>
      </View>
    </Screen>
  );
}
