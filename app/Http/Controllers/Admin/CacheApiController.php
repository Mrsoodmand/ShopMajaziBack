<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CacheManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** admin › Cache (super-admin): status, clearing, and the storefront's cache time per section. See App\Services\CacheManager. */
class CacheApiController extends Controller
{
    public function index(CacheManager $cache)
    {
        $times = $cache->times();
        return response()->json([
            'status' => $cache->status(),
            'sections' => collect(CacheManager::SECTIONS)->map(fn ($s, $key) => [
                'key' => $key,
                'label' => $s[0],
                'default' => $s[1],
                'note' => $s[2],
                'seconds' => $times[$key] ?? null,
            ])->values(),
        ]);
    }

    public function clear(Request $request, CacheManager $cache)
    {
        $data = $request->validate([
            'kind' => 'required|in:' . implode(',', CacheManager::KINDS),
            'section' => 'nullable|string|max:40',
        ]);
        $admin = Auth::guard('admin')->user();
        [$ok, $message] = $cache->clear($data['kind'], $data['section'] ?? null, $admin->full_name ?: $admin->name ?: (string) $admin->mobile);
        return response()->json($ok ? ['success' => $message] : ['error' => [$message]], $ok ? 200 : 422);
    }

    public function saveTimes(Request $request, CacheManager $cache)
    {
        $rules = [];
        foreach (array_keys(CacheManager::SECTIONS) as $key) {
            $rules["times.$key"] = 'nullable|integer|min:0|max:86400';
        }
        $data = $request->validate($rules, [], collect(CacheManager::SECTIONS)->mapWithKeys(fn ($s, $k) => ["times.$k" => $s[0]])->all());
        $cache->saveTimes($data['times'] ?? []);
        return response()->json(['success' => 'زمان‌های کش ذخیره شد. سایت تا یک دقیقه دیگر از آن‌ها استفاده می‌کند.']);
    }
}
