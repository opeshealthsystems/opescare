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
  static const _keyEmail = 'last_email';
  static const _keyPushTokenId = 'push_token_id';

  Future<void> saveToken(String token) => _storage.write(key: _keyToken, value: token);
  Future<String?> getToken() => _storage.read(key: _keyToken);
  Future<void> deleteToken() => _storage.delete(key: _keyToken);
  Future<bool> hasToken() => _storage.containsKey(key: _keyToken);
  Future<void> savePhone(String phone) => _storage.write(key: _keyPhone, value: phone);
  Future<String?> getPhone() => _storage.read(key: _keyPhone);
  Future<void> saveEmail(String email) => _storage.write(key: _keyEmail, value: email);
  Future<String?> getEmail() => _storage.read(key: _keyEmail);

  Future<void> savePushTokenId(String id) =>
      _storage.write(key: _keyPushTokenId, value: id);
  Future<String?> getPushTokenId() => _storage.read(key: _keyPushTokenId);
  Future<void> deletePushTokenId() => _storage.delete(key: _keyPushTokenId);

  Future<void> clearAll() => _storage.deleteAll();
}
