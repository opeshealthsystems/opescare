define("LARAVEL_START", microtime(true));
require "C:/laragon/www/opescare/apps/api-laravel/vendor/autoload.php";
$app = require "C:/laragon/www/opescare/apps/api-laravel/bootstrap/app.php";
$kernel = $app->make("Illuminate\Contracts\Http\Kernel");
$request = Illuminate\Http\Request::create("/document-preview/referral-letter", "GET");
$response = $kernel->handle($request);
echo $response->getStatusCode() . PHP_EOL;
echo $response->getContent();
