import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class SecureStorage {
  SecureStorage()
      : _storage = const FlutterSecureStorage(
          aOptions: AndroidOptions(encryptedSharedPreferences: true),
          iOptions: IOSOptions(accessibility: KeychainAccessibility.first_unlock),
        );

  final FlutterSecureStorage _storage;
  static const _keyToken = 'auth_token';
  static const _keyPhone = 'last_phone';

  Future<void> saveToken(String token) => _storage.write(key: _keyToken, value: token);
  Future<String?> getToken() => _storage.read(key: _keyToken);
  Future<void> deleteToken() => _storage.delete(key: _keyToken);
  Future<bool> hasToken() async => (await _storage.read(key: _keyToken)) != null;
  Future<void> savePhone(String phone) => _storage.write(key: _keyPhone, value: phone);
  Future<String?> getPhone() => _storage.read(key: _keyPhone);
  Future<void> clearAll() => _storage.deleteAll();
}
