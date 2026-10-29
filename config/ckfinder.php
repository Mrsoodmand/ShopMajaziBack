<?php

/*
 * CKFinder (media library of the React admin, /admin/app/media and the "از کتابخانه" button on image fields).
 * Docs: https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html
 *
 * - Files live in public/upload, where every image on the site already is, and are addressed as /upload/…
 * - The connector is only reachable inside the admin (routes/admin.php `ckfinder/*`, web + auth:admin). Who may
 *   delete, rename or move is decided per request in App\Http\Middleware\CKFinderAccess.
 * - Without CKFINDER_LICENSE_NAME / CKFINDER_LICENSE_KEY in .env, CKFinder runs in demo mode (evaluation only:
 *   no deleting, 10 uploads per folder, a demo notice).
 * Closures are set at runtime by the middleware, so `php artisan config:cache` keeps working.
 */

$config = array();

// Our own routes behind the admin login replace the package's public ones.
$config['loadRoutes'] = false;

$config['authentication'] = '\App\Http\Middleware\CKFinderAccess';

/*============================ License Key ============================================*/

$config['licenseName'] = env('CKFINDER_LICENSE_NAME', '');
$config['licenseKey']  = env('CKFINDER_LICENSE_KEY', '');

/*============================ CKFinder Internal Directory ============================*/
// Thumbnails, cache and logs: in storage, never public.

$config['privateDir'] = array(
    'backend' => 'laravel_cache',
    'tags'    => 'ckfinder/tags',
    'cache'   => 'ckfinder/cache',
    'thumbs'  => 'ckfinder/cache/thumbs',
    'logs'    => array(
        'backend' => 'laravel_logs',
        'path'    => 'ckfinder/logs'
    )
);

/*============================ Images and Thumbnails ==================================*/
// Uploads larger than this are scaled down (share images are 1200 × 630, banners up to 2560 wide).

$config['images'] = array(
    'maxWidth'  => 2560,
    'maxHeight' => 2560,
    'quality'   => 85,
    'sizes' => array(
        'small'  => array('width' => 480, 'height' => 320, 'quality' => 80),
        'medium' => array('width' => 800, 'height' => 600, 'quality' => 80),
        'large'  => array('width' => 1200, 'height' => 900, 'quality' => 85)
    )
);

/*=================================== Backends ========================================*/

$config['backends']['laravel_cache'] = array(
    'name'         => 'laravel_cache',
    'adapter'      => 'local',
    'root'         => storage_path('framework/cache')
);

$config['backends']['laravel_logs'] = array(
    'name'         => 'laravel_logs',
    'adapter'      => 'local',
    'root'         => storage_path('logs')
);

// public/ is the root so files come out as /upload/… (relative: the same path works on the admin and the storefront,
// which proxies /upload). Only the `upload` folder below is reachable, through the resource type.
$config['backends']['default'] = array(
    'name'         => 'default',
    'adapter'      => 'local',
    'baseUrl'      => '/',
    'root'         => public_path(),
    'chmodFiles'   => 0644,
    'chmodFolders' => 0755,
    'filesystemEncoding' => 'UTF-8'
);

/*================================ Resource Types =====================================*/
// One library: images only (what the site's image fields take). Anything else in the folder stays hidden.
// No SVG: it can carry script, and these files are served from the site's own domain.

$config['defaultResourceTypes'] = 'Images';

$config['resourceTypes'][] = array(
    'name'              => 'Images', // Single quotes not allowed.
    'directory'         => 'upload',
    'maxSize'           => '5M',
    'allowedExtensions' => 'gif,jpeg,jpg,png,webp',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);

/*================================ Access Control =====================================*/
// Everyone in the admin may view, upload and make folders. App\Http\Middleware\CKFinderAccess adds the rest
// (delete, rename, move) for super admins only.

$config['roleSessionVar'] = 'CKFinder_UserRole';

$config['accessControl'][] = array(
    'role'                => '*',
    'resourceType'        => '*',
    'folder'              => '/',

    'FOLDER_VIEW'         => true,
    'FOLDER_CREATE'       => true,
    'FOLDER_RENAME'       => false,
    'FOLDER_DELETE'       => false,

    'FILE_VIEW'           => true,
    'FILE_UPLOAD'         => true,
    'FILE_RENAME'         => false,
    'FILE_DELETE'         => false,

    'IMAGE_RESIZE'        => true,
    'IMAGE_RESIZE_CUSTOM' => true
);

/*================================ Other Settings =====================================*/

$config['overwriteOnUpload'] = false;
$config['checkDoubleExtension'] = true;
$config['disallowUnsafeCharacters'] = true;
$config['secureImageUploads'] = true;
$config['checkSizeAfterScaling'] = true;
$config['htmlExtensions'] = array('html', 'htm', 'xml', 'js');
$config['hideFolders'] = array('.*', 'CVS', '__thumbs');
$config['hideFiles'] = array('.*');
$config['forceAscii'] = false;
$config['xSendfile'] = false;

$config['debug'] = false;

$config['plugins'] = array();

$config['cache'] = array(
    'imagePreview' => 24 * 3600,
    'thumbnails'   => 24 * 3600 * 365
);

$config['tempDirectory'] = sys_get_temp_dir();

$config['sessionWriteClose'] = true;

// CKFinder's own double-submit cookie (Laravel's token check skips admin/ckfinder/*, see VerifyCsrfToken).
$config['csrfProtection'] = true;

return $config;
