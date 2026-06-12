<?php
use App\Services\JwtService;
use Illuminate\Support\Facades\DB;
$svc   = app(JwtService::class);
$token = $svc->issue(['client_id'=>'verify','scopes'=>[],'facility_id'=>null,'env'=>'test']);
$parts   = explode('.', $token);
$payload = json_decode(base64_decode(strtr($parts[1],'-_','+/')), true);
$jti     = $payload['jti'];
assert(preg_match('/^[0-9a-f-]{36}$/', $jti) === 1, 'JTI must be UUID format');
$svc->revokeToken($jti, $payload['exp'], 'final-seal');
assert($svc->isRevoked($jti) === true, 'isRevoked must return true');
DB::table('revoked_tokens')->where('jti', $jti)->delete();
echo 'JWT seal: PASSED  jti=' . $jti . PHP_EOL;
