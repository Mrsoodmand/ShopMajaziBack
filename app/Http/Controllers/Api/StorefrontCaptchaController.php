<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\Captcha;
use Illuminate\Http\JsonResponse;

/* The storefront's captcha (App\Services\Captcha): which mode the forms use, and new number pictures. */
class StorefrontCaptchaController extends Controller
{
    use ApiResponse;

    public function config(Captcha $Captcha): JsonResponse
    {
        return $this->success($Captcha->publicConfig())->header('Cache-Control', 'no-store');
    }

    public function number(Captcha $Captcha): JsonResponse
    {
        return $this->success($Captcha->challenge())->header('Cache-Control', 'no-store');
    }
}
