<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BuyerStorefrontTest extends TestCase
{
    use DatabaseTransactions;

    public function test_jelajah_is_public_and_displays_top_navbar_and_catalog(): void
    {
        $this->get('/jelajah')
            ->assertOk()
            ->assertSee('Butuh Gadget Dadakan? Sewa Aja di Sewao!')
            ->assertSee('Jelajah')
            ->assertSee('Pesanan')
            ->assertSee('Favorit')
            ->assertSee('iPhone 15 Pro 256GB Natural')
            ->assertSee('DJI Osmo Pocket 3 Creator')
            ->assertSee('Sewa via WhatsApp');
    }

    public function test_guest_is_redirected_to_login_on_buyer_actions(): void
    {
        $product = Product::where('stock', '>', 0)->firstOrFail();

        $this->get('/pesanan')->assertRedirect('/login');
        $this->get('/favorit')->assertRedirect('/login');
        $this->post('/orders', ['product_id' => $product->id])->assertRedirect('/login');
        $this->post("/favorites/toggle/{$product->id}")->assertRedirect('/login');
    }

    public function test_pembeli_can_toggle_favorite_and_view_favorit_page(): void
    {
        $pembeli = User::where('email', 'pembeli@sewao.id')->firstOrFail();
        $product = Product::where('name', 'Samsung Galaxy S24 Ultra')->firstOrFail();

        // Add to favorites via JSON
        $this->actingAs($pembeli)
            ->postJson("/favorites/toggle/{$product->id}")
            ->assertOk()
            ->assertJson(['favorited' => true]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $pembeli->id,
            'product_id' => $product->id,
        ]);

        // Appears on /favorit
        $this->actingAs($pembeli)
            ->get('/favorit')
            ->assertOk()
            ->assertSee('Samsung Galaxy S24 Ultra');

        // Toggle again removes from favorites
        $this->actingAs($pembeli)
            ->postJson("/favorites/toggle/{$product->id}")
            ->assertOk()
            ->assertJson(['favorited' => false]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $pembeli->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_pembeli_sewa_via_whatsapp_creates_order_and_redirects_to_wa_me(): void
    {
        $pembeli = User::where('email', 'pembeli@sewao.id')->firstOrFail();
        $product = Product::where('name', 'MacBook Air M2 13-inch 16GB')->firstOrFail();

        $response = $this->actingAs($pembeli)->post('/orders', [
            'product_id' => $product->id,
            'duration_days' => 3,
        ]);

        $order = Order::where('buyer_id', $pembeli->id)
            ->where('product_id', $product->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('menunggu_wa', $order->status);
        $this->assertSame(3, $order->duration_days);
        $this->assertSame($product->price_per_day * 3, $order->total_price);

        $targetUrl = $response->headers->get('Location');
        $this->assertNotNull($targetUrl);
        $this->assertStringStartsWith('https://wa.me/6281200000000?text=', $targetUrl);
        $this->assertStringContainsString(rawurlencode('MacBook Air M2 13-inch 16GB'), $targetUrl);
        $this->assertStringContainsString(rawurlencode('220.000'), $targetUrl);

        // Order immediately visible on /pesanan
        $this->actingAs($pembeli)
            ->get('/pesanan')
            ->assertOk()
            ->assertSee($order->order_code)
            ->assertSee('MacBook Air M2 13-inch 16GB')
            ->assertSee('Chat Ulang WhatsApp');
    }

    public function test_pembeli_cannot_order_out_of_stock_product(): void
    {
        $pembeli = User::where('email', 'pembeli@sewao.id')->firstOrFail();
        $outOfStock = Product::where('stock', 0)->firstOrFail();

        $beforeCount = Order::count();

        $this->actingAs($pembeli)
            ->from('/jelajah')
            ->post('/orders', ['product_id' => $outOfStock->id])
            ->assertRedirect('/jelajah')
            ->assertSessionHas('error');

        $this->assertSame($beforeCount, Order::count());
    }

    public function test_admin_and_seller_can_view_jelajah_but_cannot_access_buyer_only_routes(): void
    {
        $admin = User::where('email', 'admin@sewao.id')->firstOrFail();
        $seller = User::where('email', 'seller@sewao.id')->firstOrFail();
        $product = Product::where('stock', '>', 0)->firstOrFail();

        foreach ([$admin, $seller] as $user) {
            $this->actingAs($user)->get('/jelajah')->assertOk();
            $this->actingAs($user)->get('/pesanan')->assertForbidden();
            $this->actingAs($user)->get('/favorit')->assertForbidden();
            $this->actingAs($user)->post('/orders', ['product_id' => $product->id])->assertForbidden();
            $this->actingAs($user)->post("/favorites/toggle/{$product->id}")->assertForbidden();
        }
    }
}
