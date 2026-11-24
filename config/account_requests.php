<?php

// «درخواست اکانت»: customers ask for a subscription the site doesn't sell (App\Services\AccountRequests).
return [
    // Files customers attach. "local" = storage/app, never reachable from the web; admins open them through
    // admin/api/account-requests/{id}/files/{key} only.
    'disk' => env('ACCOUNT_REQUEST_DISK', 'local'),

    // Largest file an admin may allow on one field, in MB.
    'max_mb' => 10,

    // Requests one customer may have waiting for an answer at once.
    'max_pending' => 3,
];
