<?php

// Organisation accounts (App\Services\Organizations).
return [
    // Files applicants upload (company documents). "local" = storage/app, never reachable from the web; admins
    // open them through admin/api/organizations/{id}/files/{key} only.
    'disk' => env('ORGANIZATION_DISK', 'local'),

    // Largest file an admin may allow on one field, in MB.
    'max_mb' => 10,

    // Highest discount (percent) an admin can give one organisation.
    'max_discount' => 50,

    // sms.ir template for «درخواست حساب سازمانی شما: {STATUS}». Empty = no SMS (the customer still sees the
    // result in /account). Create the template in sms.ir with the parameter STATUS, then set its id here.
    'sms_template' => env('ORGANIZATION_SMS_TEMPLATE'),
];
