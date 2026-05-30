import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../providers/auth_provider.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _emailController    = TextEditingController();
  final _passwordController = TextEditingController();
  final _formKey            = GlobalKey<FormState>();
  bool _obscurePassword     = true;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    await ref.read(authProvider.notifier).loginWithEmail(
          _emailController.text.trim(),
          _passwordController.text,
        );
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: 56),

                // Brand mark
                Container(
                  width: 56, height: 56,
                  decoration: BoxDecoration(
                    color: AppColors.primary500,
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: const Icon(LucideIcons.heart,
                      color: AppColors.textOnPrimary, size: 28),
                ),
                const SizedBox(height: 24),
                Text('Welcome back', style: AppTextStyles.h1),
                const SizedBox(height: 6),
                Text(
                  'Sign in with your OpesCare patient account.',
                  style: AppTextStyles.bodySm,
                ),
                const SizedBox(height: 40),

                // Email
                Text('EMAIL ADDRESS', style: AppTextStyles.label),
                const SizedBox(height: 8),
                TextFormField(
                  controller: _emailController,
                  keyboardType: TextInputType.emailAddress,
                  autocorrect: false,
                  style: AppTextStyles.bodyLg,
                  decoration: const InputDecoration(
                    hintText: 'you@example.com',
                    prefixIcon: Icon(LucideIcons.mail,
                        color: AppColors.neutral400, size: 20),
                  ),
                  validator: (v) {
                    if (v == null || v.trim().isEmpty) {
                      return 'Please enter your email address';
                    }
                    if (!v.contains('@') || !v.contains('.')) {
                      return 'Enter a valid email address';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),

                // Password
                Text('PASSWORD', style: AppTextStyles.label),
                const SizedBox(height: 8),
                TextFormField(
                  controller: _passwordController,
                  obscureText: _obscurePassword,
                  style: AppTextStyles.bodyLg,
                  decoration: InputDecoration(
                    hintText: '••••••••',
                    prefixIcon: const Icon(LucideIcons.lock,
                        color: AppColors.neutral400, size: 20),
                    suffixIcon: Semantics(
                      label: _obscurePassword
                          ? 'Show password'
                          : 'Hide password',
                      child: IconButton(
                        icon: Icon(
                          _obscurePassword
                              ? LucideIcons.eyeOff
                              : LucideIcons.eye,
                          color: AppColors.neutral400,
                          size: 20,
                        ),
                        onPressed: () =>
                            setState(() => _obscurePassword = !_obscurePassword),
                      ),
                    ),
                  ),
                  validator: (v) {
                    if (v == null || v.isEmpty) return 'Please enter your password';
                    if (v.length < 4) return 'Password is too short';
                    return null;
                  },
                ),
                const SizedBox(height: 12),

                // Error banner
                if (authState.errorMessage != null)
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppColors.dangerLight,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(children: [
                      const Icon(LucideIcons.alertCircle,
                          color: AppColors.danger, size: 16),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          authState.errorMessage!,
                          style: AppTextStyles.bodySm
                              .copyWith(color: AppColors.danger),
                        ),
                      ),
                    ]),
                  ),

                const SizedBox(height: 28),

                // Sign in button
                ElevatedButton(
                  onPressed: authState.isLoading ? null : _submit,
                  child: authState.isLoading
                      ? const SizedBox(
                          height: 20, width: 20,
                          child: CircularProgressIndicator(
                              color: Colors.white, strokeWidth: 2),
                        )
                      : Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: const [
                            Text('Sign In'),
                            SizedBox(width: 8),
                            Icon(LucideIcons.arrowRight, size: 18),
                          ],
                        ),
                ),
                const SizedBox(height: 24),

                // Privacy note
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(LucideIcons.shield,
                        color: AppColors.neutral400, size: 14),
                    const SizedBox(width: 6),
                    Text('Secured with end-to-end encryption.',
                        style: AppTextStyles.caption),
                  ],
                ),
                const SizedBox(height: 32),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
