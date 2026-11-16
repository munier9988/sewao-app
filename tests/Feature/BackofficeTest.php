<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BackofficeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_view_and_crud_users_and_cannot_delete_self(): void
    {
        $admin = User::where('email', 'admin@sewao.id')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('Kelola Pengguna')
            ->assertSee('Lihat Katalog Publik')
            ->assertSee('Budi Penyewa');

        // Create user with 08xxx phone normalization
        $this->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'Seller Cabang',
                'email' => 'cabang@sewao.id',
                'role' => 'seller',
                'wa_phone' => '081122334455',
                'password' => 'password123',
            ])
            ->assertRedirect('/admin/users')
            ->assertSessionHas('success');

        $created = User::where('email', 'cabang@sewao.id')->firstOrFail();
        $this->assertSame('6281122334455', $created->wa_phone);
        $this->assertSame('seller', $created->role);

        // Update user role & name without changing password
        $oldPassword = $created->password;
        $this->actingAs($admin)
            ->put("/admin/users/{$created->id}", [
                'name' => 'Pembeli Cabang',
                'email' => 'cabang@sewao.id',
                'role' => 'pembeli',
                'wa_phone' => '+62 811-2233-4455',
                'password' => '',
            ])
            ->assertRedirect('/admin/users')
            ->assertSessionHas('success');

        $created->refresh();
        $this->assertSame('Pembeli Cabang', $created->name);
        $this->assertSame('pembeli', $created->role);
        $this->assertSame($oldPassword, $created->password);

        // Delete other user
        $this->actingAs($admin)
            ->delete("/admin/users/{$created->id}")
            ->assertRedirect('/admin/users')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $created->id]);

        // Prevent self-deletion
        $this->actingAs($admin)
            ->delete("/admin/users/{$admin->id}")
            ->assertRedirect('/admin/users')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_seller_can_crud_categories_and_cannot_delete_used_category(): void
    {
        $seller = User::where('email', 'seller@sewao.id')->firstOrFail();

        $this->actingAs($seller)
            ->get('/seller/categories')
            ->assertOk()
            ->assertSee('Kelola Produk')
            ->assertSee('Kelola Kategori')
            ->assertSee('Pengaturan WhatsApp');

        // Create category
        $this->actingAs($seller)
            ->post('/seller/categories', ['name' => 'Drone & Gimbal'])
            ->assertRedirect('/seller/categories')
            ->assertSessionHas('success');

        $category = Category::where('slug', 'drone-gimbal')->firstOrFail();

        // Update category
        $this->actingAs($seller)
            ->put("/seller/categories/{$category->id}", ['name' => 'Drone & Action Cam'])
            ->assertRedirect('/seller/categories')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'slug' => 'drone-action-cam',
        ]);

        // Delete unused category
        $this->actingAs($seller)
            ->delete("/seller/categories/{$category->id}")
            ->assertRedirect('/seller/categories')
            ->assertSessionHas('success');

        // Cannot delete category with products
        $usedCategory = Category::where('name', 'iPhone & iOS')->firstOrFail();
        $this->actingAs($seller)
            ->delete("/seller/categories/{$usedCategory->id}")
            ->assertRedirect('/seller/categories')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $usedCategory->id]);
    }

    public function test_seller_can_crud_products_with_svg_placeholder_fallback(): void
    {
        $seller = User::where('email', 'seller@sewao.id')->firstOrFail();
        $category = Category::where('name', 'Komputer & Laptop')->firstOrFail();

        $this->actingAs($seller)
            ->get('/seller/products')
            ->assertOk()
            ->assertSee('<svg', false)
            ->assertSee('window.__sewaoProductsMap', false)
            ->assertDontSee("openEdit(JSON.parse('", false);

        $longImageUrl = 'https://cdn.example.com/images/electronics/laptops/2026/thinkpad-x1-carbon-gen11/highres-photo.jpg?'.str_repeat('param=value&', 25).'v=1';

        // Create product with long image_url (>255 chars)
        $this->actingAs($seller)
            ->post('/seller/products', [
                'category_id' => $category->id,
                'name' => 'ThinkPad X1 Carbon Gen 11',
                'price_per_day' => 195000,
                'stock' => 2,
                'description' => 'Intel Core i7, RAM 16GB, ringan 1.1kg.',
                'image_url' => $longImageUrl,
                'is_featured' => '1',
            ])
            ->assertRedirect('/seller/products')
            ->assertSessionHas('success');

        $product = Product::where('name', 'ThinkPad X1 Carbon Gen 11')->firstOrFail();
        $this->assertSame($longImageUrl, $product->image_url);
        $this->assertTrue($product->is_featured);

        // Verify image renders on Buyer /marketplace storefront with referrerpolicy="no-referrer"
        $this->get('/marketplace')
            ->assertOk()
            ->assertSee($longImageUrl)
            ->assertSee('referrerpolicy="no-referrer"', false);

        // Update product
        $this->actingAs($seller)
            ->put("/seller/products/{$product->id}", [
                'category_id' => $category->id,
                'name' => 'ThinkPad X1 Carbon Gen 11 Pro',
                'price_per_day' => 210000,
                'stock' => 0,
                'description' => 'Updated spec.',
                'image_url' => '',
            ])
            ->assertRedirect('/seller/products')
            ->assertSessionHas('success');

        $product->refresh();
        $this->assertFalse($product->is_featured);
        $this->assertSame(0, $product->stock);

        // Delete product without orders
        $this->actingAs($seller)
            ->delete("/seller/products/{$product->id}")
            ->assertRedirect('/seller/products')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);

        // Cannot delete product with existing orders
        $orderedProduct = Product::whereHas('orders')->firstOrFail();
        $this->actingAs($seller)
            ->delete("/seller/products/{$orderedProduct->id}")
            ->assertRedirect('/seller/products')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('products', ['id' => $orderedProduct->id]);
    }

    public function test_seller_can_update_and_normalize_whatsapp_number(): void
    {
        $seller = User::where('email', 'seller@sewao.id')->firstOrFail();

        $this->actingAs($seller)
            ->get('/seller/settings')
            ->assertOk()
            ->assertSee('Nomor Penerima Pesanan')
            ->assertSee('SW-20261116-001');

        $this->actingAs($seller)
            ->put('/seller/settings', ['wa_phone' => '0899-1234-5678'])
            ->assertRedirect('/seller/settings')
            ->assertSessionHas('success');

        $seller->refresh();
        $this->assertSame('6289912345678', $seller->wa_phone);

        // Invalid phone should fail validation
        $this->actingAs($seller)
            ->put('/seller/settings', ['wa_phone' => '12345'])
            ->assertSessionHasErrors('wa_phone');
    }

    public function test_seller_can_view_and_manage_order_payments(): void
    {
        $seller = User::where('email', 'seller@sewao.id')->firstOrFail();
        $order = Order::whereHas('product', fn ($q) => $q->where('seller_id', $seller->id))
            ->firstOrFail();

        $this->actingAs($seller)
            ->get('/seller/payments')
            ->assertOk()
            ->assertSee('Manajemen Pembayaran')
            ->assertSee($order->order_code);

        $this->actingAs($seller)
            ->patch("/seller/payments/{$order->id}", [
                'payment_status' => 'lunas',
                'status' => 'disewa',
            ])
            ->assertRedirect('/seller/payments')
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('lunas', $order->payment_status);
        $this->assertSame('disewa', $order->status);
    }
}
