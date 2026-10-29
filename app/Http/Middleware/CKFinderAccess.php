<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
 * Who may use CKFinder (config/ckfinder.php `authentication`). Its routes already sit behind the admin login
 * (routes/admin.php); this tells CKFinder the same, and gives super admins the destructive actions: deleting,
 * renaming and moving files and folders, which can break images the site uses.
 */
class CKFinderAccess
{
    public function handle(Request $request, Closure $next)
    {
        $Admin = Auth::guard('admin')->user();
        config(['ckfinder.authentication' => fn() => !is_null($Admin)]);

        // CKFinder re-throws its own errors ("not allowed", "invalid file") when display_errors is on, instead of
        // answering with the JSON error its window shows. Off for these requests only.
        ini_set('display_errors', '0');

        if ($Admin && $Admin->hasRole('super-admin')) {
            $Rules = config('ckfinder.accessControl');
            $Rules[0] = array_merge($Rules[0], [
                'FOLDER_RENAME' => true, 'FOLDER_DELETE' => true, 'FILE_RENAME' => true, 'FILE_DELETE' => true,
            ]);
            config(['ckfinder.accessControl' => $Rules]);
        }

        return $next($request);
    }
}
