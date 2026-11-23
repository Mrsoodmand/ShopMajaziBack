<?php

// Verification products (App\Services\VerificationOrders): files buyers upload (ID photo, selfie, documents).
return [
    // Disk the files are kept on. "local" = storage/app, never reachable from the web; admins open them through
    // admin/api/orders/{id}/files/{field} only.
    'disk' => env('VERIFICATION_DISK', 'local'),

    // Days after an order is finished (done, refunded, cancelled, failed) before its files are deleted
    // (php artisan verification:prune-files, daily). Unpaid orders lose theirs after `unpaid_days`.
    'keep_days' => (int) env('VERIFICATION_KEEP_DAYS', 30),
    'unpaid_days' => (int) env('VERIFICATION_UNPAID_DAYS', 2),

    // Largest file an admin may allow on one field, in MB (each field sets its own, up to this).
    'max_mb' => 10,
];
