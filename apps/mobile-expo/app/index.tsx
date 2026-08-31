import { ActivityIndicator } from 'react-native';
import { Screen } from '../components/ui/Screen';
import { colors } from '../theme/tokens';

/** Root route — shown only for the instant before _layout's redirect fires. */
export default function Index() {
  return (
    <Screen className="items-center justify-center">
      <ActivityIndicator size="large" color={colors.gold[500]} />
    </Screen>
  );
}
