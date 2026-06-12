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
  final _phoneCtrl    = TextEditingController();
  final _emailCtrl    = TextEditingController();
  final _passwordCtrl = TextEditingController();
  final _formKey      = GlobalKey<FormState>();
  bool _obscure       = true;
  bool _usePhone      = true;

  @override
  void dispose() {
    _phoneCtrl.dispose();
    _emailCtrl.dispose();
    _passwordCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_usePhone) {
      await ref.read(authProvider.notifier)
          .loginWithPhone(_phoneCtrl.text.trim(), _passwordCtrl.text);
    } else {
      await ref.read(authProvider.notifier)
          .loginWithEmail(_emailCtrl.text.trim(), _passwordCtrl.text);
    }
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final loading   = authState.status == AuthStatus.unknown;

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const SizedBox(height: 48),

                // ── Logo ─────────────────────────────────────────────────
                Center(
                  child: Column(children: [
                    Container(
                      width: 52, height: 52,
                      decoration: BoxDecoration(
                        color: AppColors.primary50,
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: const Center(
                        child: Icon(LucideIcons.heart,
                            size: 26, color: AppColors.primary500),
                      ),
                    ),
                    const SizedBox(height: 10),
                    Text('OpesCare',
                        style: AppTextStyles.h2.copyWith(
                          color: AppColors.primary500,
                          letterSpacing: -0.5,
                        )),
                    const SizedBox(height: 12),
                    Text('Welcome back',
                        style: AppTextStyles.h3),
                    const SizedBox(height: 4),
                    Text('Sign in with your phone number or email',
                        style: AppTextStyles.bodySm,
                        textAlign: TextAlign.center),
                  ]),
                ),
                const SizedBox(height: 28),

                // ── Phone / Email toggle ──────────────────────────────────
                Container(
                  decoration: BoxDecoration(
                    color: AppColors.surfaceMuted,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.divider),
                  ),
                  padding: const EdgeInsets.all(4),
                  child: Row(children: [
                    _Toggle('Phone', _usePhone,
                        () => setState(() => _usePhone = true)),
                    _Toggle('Email', !_usePhone,
                        () => setState(() => _usePhone = false)),
                  ]),
                ),
                const SizedBox(height: 16),

                // ── Contact field ─────────────────────────────────────────
                if (_usePhone) ...[
                  Text('PHONE NUMBER',
                      style: AppTextStyles.label.copyWith(
                          color: AppColors.textSecondary, letterSpacing: 0.6)),
                  const SizedBox(height: 6),
                  Row(children: [
                    Container(
                      width: 68,
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: AppColors.divider),
                      ),
                      padding: const EdgeInsets.symmetric(
                          vertical: 12, horizontal: 8),
                      child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                        const Text('🇨🇲', style: TextStyle(fontSize: 16)),
                        const SizedBox(width: 3),
                        const Icon(LucideIcons.chevronDown,
                            size: 12, color: AppColors.neutral400),
                      ]),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextFormField(
                        controller: _phoneCtrl,
                        keyboardType: TextInputType.phone,
                        style: AppTextStyles.body,
                        decoration: _dec('+237 6XX XXX XXX'),
                        validator: (v) => (v == null || v.trim().isEmpty)
                            ? 'Phone required' : null,
                      ),
                    ),
                  ]),
                ] else ...[
                  Text('EMAIL ADDRESS',
                      style: AppTextStyles.label.copyWith(
                          color: AppColors.textSecondary, letterSpacing: 0.6)),
                  const SizedBox(height: 6),
                  TextFormField(
                    controller: _emailCtrl,
                    keyboardType: TextInputType.emailAddress,
                    style: AppTextStyles.body,
                    decoration: _dec('you@example.com'),
                    validator: (v) => (v == null || !v.contains('@'))
                        ? 'Valid email required' : null,
                  ),
                ],
                const SizedBox(height: 14),

                // ── PIN / Password ────────────────────────────────────────
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text('PIN',
                        style: AppTextStyles.label.copyWith(
                            color: AppColors.textSecondary, letterSpacing: 0.6)),
                    TextButton(
                      onPressed: () {},
                      style: TextButton.styleFrom(
                        padding: EdgeInsets.zero,
                        minimumSize: Size.zero,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                      ),
                      child: Text('Forgot PIN?',
                          style: AppTextStyles.bodySm.copyWith(
                            color: AppColors.primary500,
                            fontWeight: FontWeight.w600,
                          )),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                TextFormField(
                  controller: _passwordCtrl,
                  obscureText: _obscure,
                  style: AppTextStyles.body
                      .copyWith(fontSize: 20, letterSpacing: 4),
                  decoration: _dec('••••••').copyWith(
                    suffixIcon: IconButton(
                      icon: Icon(
                        _obscure ? LucideIcons.eye : LucideIcons.eyeOff,
                        size: 18, color: AppColors.neutral400,
                      ),
                      onPressed: () => setState(() => _obscure = !_obscure),
                    ),
                  ),
                  validator: (v) => (v == null || v.isEmpty)
                      ? 'PIN required' : null,
                ),
                const SizedBox(height: 20),

                // ── Error message ─────────────────────────────────────────
                if (authState.errorMessage != null) ...[
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppColors.dangerLight,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: AppColors.danger.withValues(alpha: 0.3)),
                    ),
                    child: Text(authState.errorMessage!,
                        style: AppTextStyles.bodySm.copyWith(
                            color: AppColors.dangerDark)),
                  ),
                  const SizedBox(height: 14),
                ],

                // ── Sign In button ────────────────────────────────────────
                Container(
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [AppColors.cardGradientStart, AppColors.cardGradientEnd],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Material(
                    color: Colors.transparent,
                    child: InkWell(
                      borderRadius: BorderRadius.circular(12),
                      onTap: loading ? null : _submit,
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 15),
                        alignment: Alignment.center,
                        child: loading
                            ? const SizedBox(
                                width: 20, height: 20,
                                child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    color: Colors.white))
                            : Text('Sign In',
                                style: AppTextStyles.button.copyWith(
                                    color: Colors.white, fontSize: 15)),
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 20),

                // ── Register link ─────────────────────────────────────────
                Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                  Text('New to OpesCare? ',
                      style: AppTextStyles.bodySm),
                  GestureDetector(
                    onTap: () {},
                    child: Text('Create account →',
                        style: AppTextStyles.bodySm.copyWith(
                          color: AppColors.primary500,
                          fontWeight: FontWeight.w700,
                        )),
                  ),
                ]),
                const SizedBox(height: 20),

                // ── Terms note ────────────────────────────────────────────
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppColors.surfaceMuted,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: RichText(
                    textAlign: TextAlign.center,
                    text: TextSpan(
                      style: AppTextStyles.caption,
                      children: [
                        const TextSpan(text: 'By continuing you agree to our '),
                        TextSpan(text: 'Terms',
                            style: AppTextStyles.caption.copyWith(
                                color: AppColors.primary500)),
                        const TextSpan(text: ' & '),
                        TextSpan(text: 'Privacy Policy',
                            style: AppTextStyles.caption.copyWith(
                                color: AppColors.primary500)),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 32),
              ],
            ),
          ),
        ),
      ),
    );
  }

  InputDecoration _dec(String hint) => InputDecoration(
        hintText: hint,
        hintStyle: AppTextStyles.body.copyWith(
            color: AppColors.textMuted, fontSize: 13),
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: const BorderSide(color: AppColors.divider)),
        enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: const BorderSide(color: AppColors.divider)),
        focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide:
                const BorderSide(color: AppColors.primary500, width: 1.5)),
        filled: true,
        fillColor: AppColors.surface,
      );
}

class _Toggle extends StatelessWidget {
  const _Toggle(this.label, this.active, this.onTap);
  final String label;
  final bool active;
  final VoidCallback onTap;
  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 150),
          padding: const EdgeInsets.symmetric(vertical: 9),
          decoration: BoxDecoration(
            color: active ? AppColors.surface : Colors.transparent,
            borderRadius: BorderRadius.circular(9),
            boxShadow: active
                ? [BoxShadow(
                    color: Colors.black.withValues(alpha: 0.06),
                    blurRadius: 4, offset: const Offset(0, 1))]
                : null,
          ),
          child: Text(label,
              textAlign: TextAlign.center,
              style: AppTextStyles.bodySm.copyWith(
                fontWeight: FontWeight.w700,
                color: active
                    ? AppColors.textPrimary
                    : AppColors.textSecondary,
              )),
        ),
      ),
    );
  }
}
