<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

/**
 * Turns Persian/Arabic digits (۰-۹, ٠-٩, decimal ٫) into 0-9 in request values
 * that are numbers: prices, counts, mobiles, codes, dates like ۱۴۰۳/۰۵/۱۲.
 * Free text (titles, descriptions, posts) is left untouched, so content that
 * deliberately uses Persian digits keeps them.
 */
class ConvertPersianDigits extends TransformsRequest
{
    /** @var array<int, string> */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    private const MAP = [
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        '٫' => '.',
    ];

    protected function transform($key, $value)
    {
        if (!is_string($value) || in_array($key, $this->except, true)) {
            return $value;
        }
        // Only digits, spaces and number punctuation, with at least one Persian/Arabic digit.
        if (!preg_match('/^[\s0-9۰-۹٠-٩٫٬.,+\-\/:()]+$/u', $value) || !preg_match('/[۰-۹٠-٩]/u', $value)) {
            return $value;
        }

        return strtr($value, self::MAP);
    }
}
