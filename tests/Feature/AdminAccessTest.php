<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/products')->assertRedirect('/admin/login');
    }

    public function test_login_and_logout(): void
    {
        $user = User::factory()->admin()->create();

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);

        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_inactive_users_cannot_log_in_or_stay_logged_in(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');

        $this->actingAs($user)->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_editor_manages_content_but_not_admin_areas(): void
    {
        $editor = User::factory()->create();

        foreach (['/admin', '/admin/products', '/admin/posts', '/admin/faqs', '/admin/testimonials', '/admin/collections', '/admin/pages', '/admin/messages', '/admin/content/home', '/admin/content/about', '/admin/profile'] as $url) {
            $this->actingAs($editor)->get($url)->assertOk();
        }
        foreach (['/admin/users', '/admin/settings', '/admin/menu'] as $url) {
            $this->actingAs($editor)->get($url)->assertForbidden();
        }
    }

    public function test_admin_sees_every_screen(): void
    {
        $admin = User::factory()->admin()->create();
        foreach (['/admin/users', '/admin/users/create', '/admin/settings', '/admin/menu', '/admin/products/create', '/admin/posts/create'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_admin_cannot_delete_or_demote_self(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete("/admin/users/{$admin->id}")->assertSessionHas('error');
        $this->actingAs($admin)->put("/admin/users/{$admin->id}", [
            'name' => 'x', 'email' => $admin->email, 'role' => 'editor', 'is_active' => '0',
        ]);
        $admin->refresh();
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->is_active);
    }

    public function test_admin_creates_editor(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'محرر', 'email' => 'ed@example.com', 'role' => 'editor', 'is_active' => '1',
            'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1',
        ])->assertRedirect('/admin/users');

        $this->post('/admin/logout');
        $this->post('/admin/login', ['email' => 'ed@example.com', 'password' => 'secret-pass-1'])->assertRedirect('/admin');
    }
}
