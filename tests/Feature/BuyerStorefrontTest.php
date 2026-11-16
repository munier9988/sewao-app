<?php

namespace Tests\Feature;

use App\Models\CartItem;
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
        // Beranda (/jelajah): menampilkan Beranda, Pemesanan, Preview 4 Produk Marketplace, Ulasan, dan CS WA 085216114321
        $this->get('/jelajah')
            ->assertOk()
            ->assertSee('Butuh Gadget Dadakan?')
            ->assertSee('Sewa Aja di Sewao!')
            ->assertSee('Beranda')
            ->assertSee('Pemesanan')
            ->assertSee('Marketplace')
            ->assertSee('Ulasan')
            ->assertSee('Sewao Marketplace: Etalasemu Ditemukan Ribuan Calon Penyewa')
            ->assertSee('Jelajahi Semua Produk di Marketplace')
            ->assertSee('085216114321')
            ->assertSee('https://wa.me/6285216114321', false)
            ->assertSee('sewao-char', false)
            ->assertSee('Sewaooooo!', false)
            ->assertDontSee('Cari iPhone, MacBook, Sony A7...');

        // Marketplace (/marketplace): menampilkan semua katalog, search/filter/sort, detail produk, dan Sewa via WhatsApp
        $this->get('/marketplace')
            ->assertOk()
            ->assertSee('lg:grid-cols-4')
            ->assertSee('open-product-detail', false)
            ->assertSee('Cari iPhone, MacBook, Sony A7...')
            ->assertSee('iPhone 15 Pro 256GB Natural')
            ->assertSee('DJI Osmo Pocket 3 Creator')
            ->assertSee('Sewa via WhatsApp');

        $pembeli = User::where('email', 'pembeli@sewao.id')->firstOrFail();
        $this->actingAs($pembeli)
            ->get('/jelajah')
            ->assertOk()
            ->assertSee('Pesanan')
            ->assertSee('Favorit')
            ->assertDontSee('>Beranda<', false)
            ->assertDontSee('>Ulasan<', false);
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

        $expectedWa = User::normalizeWaPhone($product->seller->wa_phone);
        $targetUrl = $response->headers->get('Location');
        $this->assertNotNull($targetUrl);
        $this->assertStringStartsWith("https://wa.me/{$expectedWa}?text=", $targetUrl);
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
            $this->actingAs($user)->get('/keranjang')->assertForbidden();
            $this->actingAs($user)->post('/orders', ['product_id' => $product->id])->assertForbidden();
            $this->actingAs($user)->post('/cart/add', ['product_id' => $product->id])->assertForbidden();
            $this->actingAs($user)->post("/favorites/toggle/{$product->id}")->assertForbidden();
        }
    }

    public function test_pembeli_can_add_items_to_cart_and_checkout_via_bank_transfer_and_wa(): void
    {
        $pembeli = User::where('email', 'pembeli@sewao.id')->firstOrFail();
        CartItem::where('user_id', $pembeli->id)->delete();

        $iphone = Product::where('name', 'iPhone 15 Pro 256GB Natural')->firstOrFail();
        $sony = Product::where('name', 'Sony A7 III + Lensa 28-70mm')->firstOrFail();

        // Add 1st product via AJAX JSON (ShopeeFood floating popup trigger)
        $this->actingAs($pembeli)
            ->postJson('/cart/add', [
                'product_id' => $iphone->id,
                'quantity' => 1,
                'duration_days' => 2,
            ])
            ->assertOk()
            ->assertJson([
                'count' => 1,
                'product_name' => 'iPhone 15 Pro 256GB Natural',
            ]);

        $this->actingAs($pembeli)
            ->post('/cart/add', [
                'product_id' => $sony->id,
                'quantity' => 2,
                'duration_days' => 1,
            ])
            ->assertRedirect('/keranjang');

        $expectedGrandTotal = ($iphone->price_per_day * 1 * 2) + ($sony->price_per_day * 2 * 1);

        // View cart page with combined total & bank accounts
        $this->actingAs($pembeli)
            ->get('/keranjang')
            ->assertOk()
            ->assertSee('iPhone 15 Pro 256GB Natural')
            ->assertSee('Sony A7 III + Lensa 28-70mm')
            ->assertSee(number_format($expectedGrandTotal, 0, ',', '.'))
            ->assertSee('BCA — 8720192831 a.n. Toko Sewao Pusat');

        // Checkout via bank transfer -> creates transfer orders, clears cart, redirects to WA confirmation
        $response = $this->actingAs($pembeli)->post('/cart/checkout', [
            'bank_name' => 'BCA — 8720192831 a.n. Toko Sewao Pusat',
            'payment_note' => 'Transfer BCA a.n. Budi Penyewa',
        ]);

        $this->assertSame(0, CartItem::where('user_id', $pembeli->id)->count());

        $transferOrder = Order::where('buyer_id', $pembeli->id)
            ->where('payment_method', 'transfer')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('menunggu_verifikasi', $transferOrder->payment_status);
        $this->assertSame('BCA — 8720192831 a.n. Toko Sewao Pusat', $transferOrder->bank_name);
        $this->assertSame('Transfer BCA a.n. Budi Penyewa', $transferOrder->payment_note);
        $this->assertNotNull($transferOrder->checkout_group);

        $targetUrl = $response->headers->get('Location');
        $this->assertNotNull($targetUrl);
        $this->assertStringStartsWith('https://wa.me/', $targetUrl);
        $this->assertStringContainsString(rawurlencode($transferOrder->checkout_group), $targetUrl);
        $this->assertStringContainsString(rawurlencode(number_format($expectedGrandTotal, 0, ',', '.')), $targetUrl);
    }
}
