<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthAndRoleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_seeded_data_counts_match_prd(): void
    {
        $this->assertGreaterThanOrEqual(5, User::count());
        $this->assertGreaterThanOrEqual(4, Category::count());
        $this->assertGreaterThanOrEqual(8, Product::count());
        $this->assertGreaterThanOrEqual(1, Favorite::count());
        $this->assertGreaterThanOrEqual(2, Order::count());

        $this->assertDatabaseHas('users', ['email' => 'admin@sewao.id', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['email' => 'seller@sewao.id', 'role' => 'seller']);
        $this->assertDatabaseHas('users', ['email' => 'pembeli@sewao.id', 'role' => 'pembeli']);
    }

    public function test_admin_login_redirects_to_admin_users_and_blocks_seller_routes(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@sewao.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/users');
        $this->get('/admin/users')->assertOk();
        $this->get('/seller/products')->assertForbidden();
    }

    public function test_seller_login_redirects_to_seller_products_and_blocks_admin_routes(): void
    {
        $response = $this->post('/login', [
            'email' => 'seller@sewao.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/seller/products');
        $this->get('/seller/products')->assertOk();
        $this->get('/admin/users')->assertForbidden();
    }

    public function test_pembeli_login_redirects_to_jelajah_and_blocks_backoffice_routes(): void
    {
        $response = $this->post('/login', [
            'email' => 'pembeli@sewao.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/jelajah');
        $this->get('/jelajah')->assertOk();
        $this->get('/admin/users')->assertForbidden();
        $this->get('/seller/products')->assertForbidden();
    }

    public function test_register_creates_pembeli_and_redirects_to_jelajah(): void
    {
        $response = $this->post('/register', [
            'name' => 'Dewi Tester',
            'email' => 'dewi.tester@sewao.id',
            'wa_phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/jelajah');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'dewi.tester@sewao.id',
            'role' => 'pembeli',
            'wa_phone' => '6281234567890',
        ]);
    }

    public function test_logout_clears_session_and_redirects_to_beranda_for_all_roles(): void
    {
        foreach (['admin@sewao.id', 'seller@sewao.id', 'pembeli@sewao.id'] as $email) {
            $user = User::where('email', $email)->firstOrFail();

            $this->actingAs($user)
                ->post('/logout')
                ->assertRedirect('/jelajah');

            $this->assertGuest();
        }
    }

    public function test_login_and_register_pages_include_password_visibility_toggle(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee("show ? 'text' : 'password'", false)
            ->assertSee('Lihat password', false);

        $this->get('/register')
            ->assertOk()
            ->assertSee("show ? 'text' : 'password'", false)
            ->assertSee('password_confirmation', false);
    }
}
