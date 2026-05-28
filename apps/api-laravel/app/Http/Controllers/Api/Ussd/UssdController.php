<?php
namespace App\Http\Controllers\Api\Ussd;

use App\Http\Controllers\Controller;
use App\Services\Ussd\UssdMenuService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UssdController extends Controller {
    public function __construct(private readonly UssdMenuService $menuService) {}

    public function callback(Request $request): Response {
        $sessionId   = $request->input('sessionId', '');
        $serviceCode = $request->input('serviceCode', '');
        $phoneNumber = $request->input('phoneNumber', '');
        $text        = $request->input('text', '');

        $inputs    = explode('*', $text);
        $lastInput = end($inputs);

        $responseText = $this->menuService->handleRequest(
            $sessionId,
            $phoneNumber,
            $lastInput === false ? '' : $lastInput,
            $serviceCode
        );

        return response($responseText, 200)->header('Content-Type', 'text/plain');
    }
}
