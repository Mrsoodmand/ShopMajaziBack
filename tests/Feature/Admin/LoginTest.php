<?php

namespace Tests\Feature\Admin;

class LoginTest extends AdminTestCase
{
    const LOGIN = '/admin-url-login-with-secure-path/login';

    public function test_login_page_is_the_react_page(): void
    {
        $this->get(self::LOGIN)->assertOk()->assertSee('id="admin-login"', false)->assertSee('ورود به مدیریت شاپ مجازی');
    }

    public function test_wrong_password_answers_with_a_field_error(): void
    {
        $admin = $this->admin();

        $this->postJson(self::LOGIN, ['email' => $admin->email, 'password' => 'wrong'])
            ->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'ایمیل یا کلمه عبور درست نیست.');
        $this->assertGuest('admin');
    }

    public function test_empty_fields_are_named(): void
    {
        $this->postJson(self::LOGIN, [])
            ->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'ایمیل را وارد کنید.')
            ->assertJsonPath('errors.password.0', 'کلمه عبور را وارد کنید.');
    }

    public function test_success_goes_to_the_react_panel(): void
    {
        $admin = $this->admin();

        $this->postJson(self::LOGIN, ['email' => $admin->email, 'password' => 'secret'])
            ->assertOk()
            ->assertJsonPath('redirect', url('/admin/app'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_success_returns_to_the_page_the_admin_was_sent_away_from(): void
    {
        $admin = $this->admin();

        $this->get('/admin/app/orders?view=all')->assertRedirect('/admin/login');
        $this->postJson(self::LOGIN, ['email' => $admin->email, 'password' => 'secret'])
            ->assertJsonPath('redirect', url('/admin/app/orders?view=all'));
    }

    public function test_signed_in_admin_skips_the_login_page_and_logout_returns_to_it(): void
    {
        $this->actingAsAdmin();

        $this->get(self::LOGIN)->assertRedirect('/admin/app');
        $this->get('/admin-url-login-with-secure-path/logout')->assertRedirect(self::LOGIN);
        $this->assertGuest('admin');
    }
}
