<?php

namespace Tests\Feature\Admin;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * CKFinder media library (config/ckfinder.php, App\Http\Middleware\CKFinderAccess): only for signed-in admins, over
 * public/upload as /upload/…, images only, and only super admins may delete or rename.
 * Uploads go to a throwaway folder in public/upload that each test removes.
 */
class MediaLibraryTest extends AdminTestCase
{
    // CKFinder's ACL bits (FOLDER_VIEW 1 … IMAGE_RESIZE_CUSTOM 512).
    private const EVERYONE = 1 | 2 | 16 | 32 | 256 | 512; // view, make folders, upload, resize
    private const SUPER = 1023;

    private ?string $folder = null;

    protected function setUp(): void
    {
        parent::setUp();
        // The package only binds its connector for web requests, not in the console; tests bind it the same way.
        $this->app->bind('ckfinder.connector', fn() => new \CKSource\CKFinder\CKFinder(config('ckfinder')));
    }

    protected function tearDown(): void
    {
        if ($this->folder) {
            File::deleteDirectory(public_path('upload/' . $this->folder));
        }
        parent::tearDown();
    }

    private function connector(string $command, array $query = [], array $post = [], array $files = [])
    {
        $token = str_repeat('a', 32); // CKFinder's double-submit token: the same value as cookie and as field
        $url = '/admin/ckfinder/connector?' . http_build_query(['command' => $command, 'type' => 'Images'] + $query);
        $this->withUnencryptedCookie('ckCsrfToken', $token);
        $level = ob_get_level();
        $res = $post || $files
            ? $this->call('POST', $url, $post + ['ckCsrfToken' => $token], $this->prepareCookiesForRequest(), $files)
            : $this->get($url);
        while (ob_get_level() < $level) {
            ob_start(); // CKFinder closes output buffers while it answers; give PHPUnit its own back
        }
        return $res;
    }

    private function freshFolder(): string
    {
        $this->folder = '__test-' . Str::lower(Str::random(8));
        File::makeDirectory(public_path('upload/' . $this->folder));
        return '/' . $this->folder . '/';
    }

    public function test_only_signed_in_admins_reach_it_and_the_package_public_route_is_off(): void
    {
        $this->get('/admin/ckfinder/connector?command=Init')->assertRedirect();
        $this->get('/ckfinder/connector?command=Init')->assertNotFound();
    }

    public function test_library_is_the_upload_folder_and_images_only(): void
    {
        $this->actingAsAdmin($this->admin(false));
        $init = $this->connector('Init')->assertOk()->json();

        $this->assertTrue($init['enabled']);
        $this->assertCount(1, $init['resourceTypes']);
        $type = $init['resourceTypes'][0];
        $this->assertSame('Images', $type['name']);
        $this->assertSame('/upload/', $type['url']);
        $this->assertSame('gif,jpeg,jpg,png,webp', $type['allowedExtensions']);
        $this->assertSame(self::EVERYONE, $type['acl']); // no delete, rename or move
    }

    public function test_super_admins_may_also_delete_and_rename(): void
    {
        $this->actingAsAdmin();
        $this->assertSame(self::SUPER, $this->connector('Init')->json('resourceTypes.0.acl'));
    }

    public function test_upload_lands_in_upload_and_scripts_are_refused(): void
    {
        $this->actingAsAdmin($this->admin(false));
        $folder = $this->freshFolder();

        $ok = $this->connector('FileUpload', ['currentFolder' => $folder], [], ['upload' => UploadedFile::fake()->image('Share Card.png', 1200, 630)])->assertOk()->json();
        $this->assertSame(1, $ok['uploaded'] ?? null, json_encode($ok));
        $this->assertFileExists(public_path('upload/' . $this->folder . '/' . $ok['fileName']));
        $listed = $this->connector('GetFiles', ['currentFolder' => $folder])->json();
        $this->assertSame('/upload/' . $this->folder . '/', $listed['currentFolder']['url']); // what "choose" hands the form
        $this->assertSame([$ok['fileName']], array_column($listed['files'], 'name'));

        $shell = UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;');
        $bad = $this->connector('FileUpload', ['currentFolder' => $folder], [], ['upload' => $shell])->json();
        $this->assertArrayHasKey('error', $bad);
        $this->assertFileDoesNotExist(public_path('upload/' . $this->folder . '/shell.php'));

        // A script dressed up as an image is refused too.
        $fake = UploadedFile::fake()->createWithContent('photo.png', '<?php system($_GET["c"]); ?>');
        $this->assertArrayHasKey('error', $this->connector('FileUpload', ['currentFolder' => $folder], [], ['upload' => $fake])->json());
    }

    public function test_old_editor_browser_page_uses_the_admin_connector(): void
    {
        $this->get('/admin/ckfinder/browser?type=Images')->assertRedirect();

        $this->actingAsAdmin($this->admin(false));
        $this->get('/admin/ckfinder/browser?type=Images')->assertOk()
            ->assertSee('/js/ckfinder/ckfinder.js', false)
            ->assertSee("connectorPath: '/admin/ckfinder/connector'", false)
            ->assertSee('CKFinder.start()', false);
    }

    public function test_old_editor_quick_upload_hands_the_image_back_to_ckeditor(): void
    {
        $this->actingAsAdmin($this->admin(false));
        $folder = $this->freshFolder();

        // CKEditor 4's upload tab posts the file with its callback number and expects a script calling it back.
        $res = $this->connector('QuickUpload', ['currentFolder' => $folder, 'CKEditor' => 'detail', 'CKEditorFuncNum' => 7, 'langCode' => 'fa'],
            [], ['upload' => UploadedFile::fake()->image('cover.png', 800, 450)])->assertOk();

        $this->assertFileExists(public_path('upload/' . $this->folder . '/cover.png'));
        $this->assertStringContainsString('CKEDITOR.tools.callFunction("7",', $res->getContent());
        $this->assertStringContainsString('/upload/' . $this->folder . '/cover.png', stripslashes($res->getContent()));
    }

    public function test_old_admin_pages_pick_images_from_the_library(): void
    {
        $this->actingAsAdmin();

        $this->get('/admin/blog/create')->assertOk()
            ->assertSee('data-ckfinder-field="gallery_image_input_1"', false)
            ->assertSee('/js/admin-image-picker.js', false)
            ->assertSee("filebrowserUploadUrl: '/admin/ckfinder/connector?command=QuickUpload&type=Images'", false)
            ->assertDontSee('filemanager', false)
            ->assertDontSee('api_access', false);

        // The old file manager (no login, hard-coded key) is gone from the site.
        $this->assertDirectoryDoesNotExist(public_path('js/filemanager'));
    }

    public function test_regular_admins_cannot_delete(): void
    {
        $this->actingAsAdmin($this->admin(false));
        $folder = $this->freshFolder();
        File::put(public_path('upload/' . $this->folder . '/keep.png'), UploadedFile::fake()->image('keep.png')->getContent());

        $res = $this->connector('DeleteFiles', [], ['files' => [['name' => 'keep.png', 'type' => 'Images', 'folder' => $folder]]])->json();

        $this->assertArrayHasKey('error', $res);
        $this->assertFileExists(public_path('upload/' . $this->folder . '/keep.png'));
    }
}
