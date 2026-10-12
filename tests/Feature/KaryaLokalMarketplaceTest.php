<?php

namespace Tests\Feature;

use App\Models\ArtisanProfile;
use App\Models\ArtisanCustomerChat;
use App\Models\Article;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductProcessStep;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class KaryaLokalMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Rani Pembeli',
            'email' => 'rani@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'rani@example.test', 'role' => User::ROLE_CUSTOMER]);
    }

    public function test_artisan_join_links_to_registration_with_artisan_role_selected(): void
    {
        $registerUrl = route('register', ['role' => User::ROLE_ARTISAN]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($registerUrl, false)
            ->assertSee('<a href="' . route('storefront.catalog') . '"><svg class="navlink-icon"', false);

        $this->get($registerUrl)
            ->assertOk()
            ->assertSee('value="artisan" checked', false);
    }

    public function test_customer_can_register_a_separate_artisan_account(): void
    {
        $customer = $this->user(User::ROLE_CUSTOMER);

        $this->actingAs($customer)
            ->get(route('register', ['role' => User::ROLE_ARTISAN]))
            ->assertOk()
            ->assertSee('value="artisan" checked', false);

        $this->actingAs($customer)
            ->post(route('register'), [
                'name' => 'Ayu Perajin',
                'email' => 'ayu@example.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => User::ROLE_ARTISAN,
                'city' => 'Bantul',
                'province' => 'Yogyakarta',
            ])
            ->assertRedirect(route('artisan.dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'ayu@example.test', 'role' => User::ROLE_ARTISAN]);
        $this->assertAuthenticatedAs(User::where('email', 'ayu@example.test')->first());
    }

    public function test_artisan_home_redirects_to_dashboard(): void
    {
        $artisan = $this->user(User::ROLE_ARTISAN);

        $this->actingAs($artisan)
            ->get(route('home'))
            ->assertRedirect(route('artisan.dashboard'));
    }

    public function test_artisan_about_redirects_to_dashboard(): void
    {
        $artisan = $this->user(User::ROLE_ARTISAN);

        $this->actingAs($artisan)
            ->get(route('about'))
            ->assertRedirect(route('artisan.dashboard'));
    }

    public function test_artisan_can_open_create_product_page(): void
    {
        $artisan = $this->user(User::ROLE_ARTISAN);

        $this->actingAs($artisan)
            ->get(route('artisan.products.create'))
            ->assertOk()
            ->assertSee('Tambah karya baru', false)
            ->assertSee('Kirim ke kurator', false);
    }

    public function test_customer_explore_navigation_returns_to_storefront_home(): void
    {
        $customer = $this->user(User::ROLE_CUSTOMER);

        $this->actingAs($customer)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee('role-menu-trigger', false)
            ->assertSee(route('home'), false)
            ->assertSee('Beranda')
            ->assertSee('Pesanan saya');

            $this->get(route('home'))
                ->assertOk()
                ->assertSee('role-menu-trigger', false)
                ->assertSee('Wishlist')
                ->assertSee('Chat perajin')
                ->assertSee(asset('images/aksesori.jpg'), false)
                ->assertSee(asset('images/Keramik.jpg'), false)
                ->assertSee(asset('images/tekstil.jpg'), false)
                ->assertSee(asset('images/kerajinan%20kayu.jpg'), false)
                ->assertSee(asset('images/Batik.jpg'), false)
                ->assertSee(asset('images/anyaman%20logo.jpg'), false)
                ->assertSee('data-category="Fashion"', false)
                ->assertSee('data-category="Kerajinan Kayu"', false)
                ->assertSee('data-category="Personal Gift"', false)
                ->assertSee('.product-image>a>img{display:block;width:100%;height:100%;object-fit:contain', false);
    }

    public function test_homepage_featured_products_include_anyaman_and_gift_filter_matches(): void
    {
        $artisan = $this->user(User::ROLE_ARTISAN);
        $regularCategory = Category::create([
            'name' => 'Aksesori',
            'slug' => 'aksesori',
            'is_active' => true,
        ]);
        $anyamanCategory = Category::create([
            'name' => 'Anyaman',
            'slug' => 'anyaman',
            'is_active' => true,
        ]);

        foreach (range(1, 10) as $number) {
            $this->product($artisan, $regularCategory, [
                'name' => 'Produk Terbaru ' . $number,
                'slug' => 'produk-terbaru-' . $number,
                'created_at' => now()->subDay(),
            ]);
        }

        $this->product($artisan, $anyamanCategory, [
            'name' => 'Anyaman Unggulan Uji',
            'slug' => 'anyaman-unggulan-uji',
            'created_at' => now(),
        ]);
        $this->product($artisan, $regularCategory, [
            'name' => 'Hadiah Custom Uji',
            'slug' => 'hadiah-custom-uji',
            'is_customizable' => true,
            'created_at' => now(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Anyaman Unggulan Uji')
            ->assertSee('Hadiah Custom Uji')
            ->assertSee('data-category="Anyaman"', false)
            ->assertSee('data-tags=" Gift"', false);
    }

        public function test_customer_can_upload_profile_photo_and_see_profile_icon(): void
        {
            Storage::fake('public');
            $customer = $this->user(User::ROLE_CUSTOMER);

            $this->actingAs($customer)
                ->put(route('profile.update'), [
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'profile_photo' => UploadedFile::fake()->image('profile.png'),
                ])
                ->assertRedirect(route('profile.edit'));

            $customer->refresh();
            $this->assertNotNull($customer->profile_photo_path);
            $this->assertTrue(Storage::disk('public')->exists($customer->profile_photo_path));
            $this->get(route('profile.edit'))
                ->assertOk()
                ->assertSee('Foto profil')
                ->assertSee('profile-avatar', false);
            $this->get(route('orders.index'))
                ->assertOk()
                ->assertSee('Foto profil ' . $customer->name)
                ->assertSee(Storage::url($customer->profile_photo_path), false);
        }

    public function test_operational_roles_can_manage_profiles_and_see_dashboard_visualizations(): void
    {
        $rolePages = [
            [User::ROLE_ADMIN, 'admin.dashboard', 'Pesanan per bulan'],
            [User::ROLE_CURATOR, 'curator.dashboard', 'Status verifikasi'],
            [User::ROLE_CUSTOMER_SERVICE, 'support.dashboard', 'Status tiket'],
            [User::ROLE_ARTISAN, 'artisan.dashboard', 'Status karya'],
        ];

        foreach ($rolePages as [$role, $dashboardRoute, $visualizationTitle]) {
            $user = $this->user($role);
            $this->actingAs($user)
                ->get(route($dashboardRoute))
                ->assertOk()
                ->assertSee($visualizationTitle);
            $this->get(route('profile.edit'))->assertOk()->assertSee('Profil akun');

            $profileData = [
                'name' => 'Nama Diperbarui ' . $role,
                'email' => $role . '.updated@example.test',
                'phone' => '081234567890',
            ];
            if ($role === User::ROLE_ARTISAN) {
                $profileData += [
                    'bio' => 'Perajin anyaman dari Bantul.',
                    'maker_story' => 'Cerita perjalanan berkarya.',
                    'city' => 'Bantul',
                    'province' => 'Yogyakarta',
                    'specialty' => 'Anyaman',
                    'started_year' => 2015,
                ];
            }

            $this->put(route('profile.update'), $profileData)
                ->assertRedirect(route('profile.edit'));
            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'name' => $profileData['name'],
                'email' => $profileData['email'],
                'role' => $role,
            ]);

            if ($role === User::ROLE_ARTISAN) {
                $this->assertDatabaseHas('artisan_profiles', [
                    'user_id' => $user->id,
                    'city' => 'Bantul',
                    'verification_status' => 'pending',
                ]);
            }
        }
    }

    public function test_artisan_registration_creates_profile_and_workspace(): void
    {
        $response = $this->post('/register', [
            'name' => 'Siti Perajin',
            'email' => 'siti@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_ARTISAN,
            'city' => 'Bantul',
            'province' => 'Yogyakarta',
        ]);

        $response->assertRedirect(route('artisan.dashboard'));
        $this->assertDatabaseHas('artisan_profiles', [
            'user_id' => User::where('email', 'siti@example.test')->value('id'),
            'city' => 'Bantul',
            'province' => 'Yogyakarta',
        ]);
    }

    public function test_guest_cart_moves_to_customer_account_after_registration(): void
    {
        $product = $this->product($this->user(User::ROLE_ARTISAN), $this->category());
        $this->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 1])
            ->assertRedirect(route('cart.index'));

        $this->post('/register', [
            'name' => 'Pembeli Cart',
            'email' => 'cart@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ])->assertRedirect(route('home'));

        $this->assertDatabaseHas('carts', [
            'product_id' => $product->id,
            'user_id' => User::where('email', 'cart@example.test')->value('id'),
            'session_id' => null,
        ]);
    }

    public function test_public_catalog_only_lists_approved_products(): void
    {
        $category = $this->category();
        $artisan = $this->user(User::ROLE_ARTISAN);
        $this->product($artisan, $category, ['name' => 'Anyaman Terverifikasi', 'slug' => 'anyaman-terverifikasi']);
        $this->product($artisan, $category, [
            'name' => 'Karya Belum Disetujui',
            'slug' => 'karya-belum-disetujui',
            'verification_status' => Product::STATUS_PENDING,
            'craft_id' => null,
            'qr_active' => false,
        ]);

        $this->get('/karya')
            ->assertOk()
            ->assertSee('Anyaman Terverifikasi')
            ->assertDontSee('Karya Belum Disetujui');
    }

    public function test_customer_can_toggle_a_product_in_their_wishlist(): void
    {
        $product = $this->product($this->user(User::ROLE_ARTISAN), $this->category());
        $customer = $this->user(User::ROLE_CUSTOMER);

        $this->actingAs($customer)->post(route('wishlist.toggle', $product))
            ->assertRedirect();
        $this->assertDatabaseHas('wishlists', ['user_id' => $customer->id, 'product_id' => $product->id]);

        $this->get(route('home'))->assertSee('Hapus dari wishlist: ' . $product->name);
        $wishlist = $customer->wishlists()->firstOrFail();
        $this->delete(route('wishlist.destroy', $wishlist))->assertRedirect();
        $this->assertDatabaseMissing('wishlists', ['user_id' => $customer->id, 'product_id' => $product->id]);
    }

    public function test_updating_an_approved_product_keeps_it_visible_on_storefront(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/existing.jpg', 'existing image');
        Storage::disk('public')->put('products/latest-existing.jpg', 'latest existing image');
        $category = $this->category();
        $artisan = $this->user(User::ROLE_ARTISAN);
        $product = $this->product($artisan, $category, [
            'name' => 'Produk Sudah Terbit',
            'slug' => 'produk-sudah-terbit',
            'verification_status' => Product::STATUS_APPROVED,
            'craft_id' => 'KL-0001-000001',
            'qr_active' => true,
            'images' => ['https://images.example.test/legacy.jpg', 'products/existing.jpg', 'products/latest-existing.jpg'],
        ]);

        $this->actingAs($artisan)
            ->get(route('artisan.products.show', $product))
            ->assertOk()
            ->assertSee(Storage::url('products/latest-existing.jpg'), false)
            ->assertDontSee(Storage::url('products/existing.jpg'), false)
            ->assertDontSee(Storage::url('https://images.example.test/legacy.jpg'), false);

        $this->actingAs($artisan)
            ->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Edit karya')
            ->assertSee(route('artisan.products.edit', $product), false)
            ->assertDontSee('Tambah ke keranjang');

        $this->put(route('artisan.products.update', $product), [
                'name' => 'Produk Sudah Terbit Diperbarui',
                'category_id' => $category->id,
                'sku' => 'SKU-001',
                'description' => 'Deskripsi produk yang diperbarui.',
                'story' => 'Cerita karya yang diperbarui.',
                'material' => 'Rotan',
                'technique' => 'Anyaman',
                'origin' => 'Bantul',
                'dimensions' => '25 x 18 x 10 cm',
                'weight_grams' => 450,
                'price' => 180000,
                'stock' => 8,
                'fulfillment_type' => 'ready_stock',
                'production_days' => 3,
                'images' => [UploadedFile::fake()->image('produk-baru.jpg')],
                'process_steps' => [[
                    'title' => 'Pembersihan bahan',
                    'description' => 'Bahan dikeringkan dan dibersihkan.',
                    'duration_minutes' => 30,
                ]],
                'save_as' => 'pending',
            ])
            ->assertRedirect(route('artisan.products.index'));

        $product->refresh();
        $this->assertSame(Product::STATUS_APPROVED, $product->verification_status);
        $this->assertCount(4, $product->images);
        $this->assertContains('products/existing.jpg', $product->images);
        $this->assertContains('products/latest-existing.jpg', $product->images);
        $this->assertContains('https://images.example.test/legacy.jpg', $product->images);
        $this->assertTrue(Storage::disk('public')->exists($product->images[0]));
        $this->get(route('artisan.products.show', $product))
            ->assertOk()
            ->assertSee(Storage::url($product->images[0]), false);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Produk Sudah Terbit Diperbarui');
    }

    public function test_all_admin_dashboard_metrics_are_clickable(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $response = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        $this->assertSame(12, substr_count($response->getContent(), 'class="metric-link"'));
        $response->assertSee(route('admin.users.index'), false)
            ->assertSee(route('admin.users.index', ['role' => 'artisan']), false)
            ->assertSee(route('admin.users.index', ['role' => 'customer']), false)
            ->assertSee(route('storefront.catalog'), false)
            ->assertSee(route('admin.pending-products.index'), false)
            ->assertSee(route('admin.dashboard') . '#recent-orders', false)
            ->assertSee(route('admin.sales.index'), false)
            ->assertSee(route('admin.tickets.index', ['status' => 'active']), false)
            ->assertSee(route('admin.qr.index'), false);
    }

    public function test_admin_product_detail_is_not_customer_checkout_and_has_edit_action(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $artisan = $this->user(User::ROLE_ARTISAN);
        $product = $this->product($artisan, $this->category(), [
            'name' => 'Produk Admin Detail',
            'verification_status' => Product::STATUS_APPROVED,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.products.show', $product))
            ->assertOk()
            ->assertSee('Produk Admin Detail')
            ->assertSee(route('admin.products.edit', $product), false)
            ->assertDontSee('Tambah ke keranjang')
            ->assertDontSee('Keranjang');

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Produk Admin Detail')
            ->assertSee(route('admin.products.edit', $product), false)
            ->assertDontSee('Tambah ke keranjang')
            ->assertDontSee('Keranjang');
    }

    public function test_admin_saw_recommendations_use_marketplace_product_criteria(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $artisan = $this->user(User::ROLE_ARTISAN);
        $customer = $this->user(User::ROLE_CUSTOMER);
        $category = $this->category();
        $economicalProduct = $this->product($artisan, $category, [
            'name' => 'Karya SAW Ekonomis',
            'price' => 100000,
            'stock' => 20,
        ]);
        $popularProduct = $this->product($artisan, $category, [
            'name' => 'Karya SAW Premium',
            'price' => 200000,
            'stock' => 10,
        ]);

        \App\Models\Review::create([
            'customer_id' => $customer->id,
            'product_id' => $economicalProduct->id,
            'product_rating' => 4,
            'body' => 'Ulasan produk ekonomis.',
            'is_published' => true,
        ]);
        \App\Models\Review::create([
            'customer_id' => $customer->id,
            'product_id' => $popularProduct->id,
            'product_rating' => 5,
            'body' => 'Ulasan produk populer.',
            'is_published' => true,
        ]);

        $economicalOrder = $this->salesOrder($customer, [
            'order_number' => 'KL-SAW-ECONOMICAL',
            'payment_status' => 'paid',
            'status' => 'completed',
        ]);
        OrderItem::create([
            'order_id' => $economicalOrder->id,
            'product_id' => $economicalProduct->id,
            'artisan_id' => $artisan->id,
            'product_name' => $economicalProduct->name,
            'quantity' => 4,
            'unit_price' => $economicalProduct->price,
        ]);
        $popularOrder = $this->salesOrder($customer, [
            'order_number' => 'KL-SAW-POPULAR',
            'payment_status' => 'paid',
            'status' => 'completed',
        ]);
        OrderItem::create([
            'order_id' => $popularOrder->id,
            'product_id' => $popularProduct->id,
            'artisan_id' => $artisan->id,
            'product_name' => $popularProduct->name,
            'quantity' => 8,
            'unit_price' => $popularProduct->price,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.saw.index'))
            ->assertOk()
            ->assertSee('Metode Simple Additive Weighting (SAW)')
            ->assertSee('Unit terjual')
            ->assertSee('Rating pembeli')
            ->assertSee('Karya SAW Ekonomis')
            ->assertSee('Karya SAW Premium')
            ->assertSee('0.8100')
            ->assertSee('0.7500')
            ->assertSee(route('admin.products.edit', $economicalProduct), false);

        $this->assertLessThan(
            strpos($response->getContent(), 'Karya SAW Premium'),
            strpos($response->getContent(), 'Karya SAW Ekonomis')
        );
    }

    public function test_saw_recommendations_are_restricted_to_admins(): void
    {
        $artisan = $this->user(User::ROLE_ARTISAN);

        $this->actingAs($artisan)
            ->get(route('admin.saw.index'))
            ->assertForbidden();
    }

    public function test_admin_can_edit_approved_product_without_changing_approval_status(): void
    {
        Storage::fake('public');
        $admin = $this->user(User::ROLE_ADMIN);
        $artisan = $this->user(User::ROLE_ARTISAN);
        $category = $this->category();
        $product = $this->product($artisan, $category, [
            'name' => 'Produk Sudah Disetujui',
            'slug' => 'produk-sudah-disetujui',
            'verification_status' => Product::STATUS_APPROVED,
            'craft_id' => 'KL-0001-000001',
            'qr_active' => true,
            'images' => ['products/admin-existing.jpg'],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('Perbarui karya')
            ->assertSee(route('storefront.catalog'), false);

        $this->actingAs($admin)
            ->get(route('admin.products.edit', ['product' => $product, 'return_to' => route('admin.saw.index')]))
            ->assertOk()
            ->assertSee('Kembali ke dashboard SPK')
            ->assertSee(route('admin.saw.index'), false);

        $this->put(route('admin.products.update', $product), [
            'name' => 'Produk Sudah Disetujui Diedit',
            'category_id' => $category->id,
            'sku' => 'SKU-ADMIN-EDIT-001',
            'description' => 'Deskripsi produk yang diedit oleh admin.',
            'story' => 'Cerita karya yang diperbarui oleh admin.',
            'material' => 'Rotan',
            'technique' => 'Anyaman',
            'origin' => 'Bantul',
            'dimensions' => '30 x 22 x 12 cm',
            'weight_grams' => 500,
            'price' => 250000,
            'stock' => 12,
            'fulfillment_type' => 'ready_stock',
            'production_days' => 5,
            'images' => [UploadedFile::fake()->image('admin-new.jpg')],
            'process_steps' => [[
                'title' => 'Pembersihan bahan',
                'description' => 'Bahan dibersihkan dan dikeringkan.',
                'duration_minutes' => 30,
            ]],
        ])->assertRedirect(route('storefront.catalog'));

        $product->refresh();
        $this->assertSame(Product::STATUS_APPROVED, $product->verification_status);
        $this->assertSame('Produk Sudah Disetujui Diedit', $product->name);
        $this->assertSame('SKU-ADMIN-EDIT-001', $product->sku);
        $this->assertCount(2, $product->images);
        $this->assertContains('products/admin-existing.jpg', $product->images);
        $this->assertTrue(Storage::disk('public')->exists($product->images[0]));
    }

    public function test_admin_can_open_pending_products_and_paginate_all_active_complaints(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $artisan = $this->user(User::ROLE_ARTISAN);
        $customer = $this->user(User::ROLE_CUSTOMER);
        $pendingProduct = $this->product($artisan, $this->category(), [
            'name' => 'Produk Menunggu Admin',
            'verification_status' => Product::STATUS_PENDING,
        ]);
        $revisionProduct = $this->product($artisan, $this->category(), [
            'name' => 'Produk Revisi Tidak Termasuk',
            'verification_status' => Product::STATUS_REVISION,
        ]);

        $pendingResponse = $this->actingAs($admin)
            ->get(route('admin.pending-products.index'))
            ->assertOk()
            ->assertSee('Produk Menunggu Admin')
            ->assertDontSee('Produk Revisi Tidak Termasuk');
        $pendingResponse->assertSee(route('admin.pending-products.show', $pendingProduct), false);
        $this->get(route('admin.pending-products.show', $pendingProduct))
            ->assertOk()
            ->assertSee('Produk Menunggu Admin')
            ->assertSee(route('admin.products.edit', $pendingProduct), false)
            ->assertDontSee('Setujui & terbitkan QR');

        foreach (range(1, 11) as $number) {
            SupportTicket::create([
                'ticket_number' => 'CS-ADMIN-' . str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'category' => 'other',
                'subject' => 'Komplain aktif ' . $number,
                'description' => 'Deskripsi komplain aktif ' . $number,
                'status' => match ($number % 3) {
                    0 => 'waiting_customer',
                    1 => 'open',
                    default => 'in_progress',
                },
            ]);
        }
        SupportTicket::create([
            'ticket_number' => 'CS-ADMIN-DONE',
            'customer_id' => $customer->id,
            'category' => 'other',
            'subject' => 'Komplain selesai tidak termasuk',
            'description' => 'Tiket resolved tidak masuk hitungan aktif.',
            'status' => 'resolved',
        ]);

        $ticketsResponse = $this->get(route('admin.tickets.index', ['status' => 'active']))
            ->assertOk()
            ->assertSee('Komplain aktif 1')
            ->assertDontSee('Komplain selesai tidak termasuk')
            ->assertSee('Berikutnya');
        $this->assertSame(10, substr_count($ticketsResponse->getContent(), 'class="ticket-row"'));
        $ticket = SupportTicket::where('ticket_number', 'CS-ADMIN-001')->firstOrFail();
        $ticketsResponse->assertSee(route('admin.tickets.show', $ticket), false);
        $this->get(route('admin.tickets.show', $ticket))
            ->assertOk()
            ->assertSee($ticket->subject)
            ->assertSee('Perbarui tiket');
    }

    public function test_admin_can_open_qr_and_account_management_and_create_an_account(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $this->actingAs($admin)->get(route('admin.qr.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();

        $this->post(route('admin.users.store'), [
            'name' => 'Kurator Baru',
            'email' => 'kurator-baru@example.test',
            'password' => 'password123',
            'role' => User::ROLE_CURATOR,
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'kurator-baru@example.test',
            'role' => User::ROLE_CURATOR,
            'status_akun' => 'aktif',
        ]);
    }

    public function test_admin_can_upload_and_edit_articles_visible_to_customers(): void
    {
        Storage::fake('public');
        $admin = $this->user(User::ROLE_ADMIN);
        $customer = $this->user(User::ROLE_CUSTOMER);

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Merawat Anyaman Nusantara',
                'excerpt' => 'Panduan merawat kerajinan anyaman.',
                'body' => 'Simpan anyaman di tempat kering dan bersihkan secara berkala.',
                'image' => UploadedFile::fake()->image('anyaman.jpg'),
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.articles.index'));

        $article = Article::where('title', 'Merawat Anyaman Nusantara')->firstOrFail();
        $this->assertTrue($article->is_published);
        $this->assertTrue(Storage::disk('public')->exists($article->image_path));

        $this->actingAs($customer)
            ->get(route('articles.index'))
            ->assertOk()
            ->assertSee($article->title)
            ->assertSee($article->excerpt);
        $this->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee('Simpan anyaman di tempat kering dan bersihkan secara berkala.')
            ->assertSee('Artikel Lainnya');

        $this->actingAs($admin)
            ->put(route('admin.articles.update', $article), [
                'title' => 'Merawat Anyaman dengan Benar',
                'excerpt' => 'Panduan perawatan anyaman.',
                'body' => 'Jauhkan anyaman dari kelembapan berlebih.',
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.articles.index'));

        $article->refresh();
        $this->assertSame('merawat-anyaman-dengan-benar', $article->slug);
        $this->assertSame('Jauhkan anyaman dari kelembapan berlebih.', $article->body);
        $this->get(route('articles.show', $article->slug))->assertOk()->assertSee($article->title);
    }

    public function test_unpublished_articles_are_not_visible_to_customers(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $article = Article::create([
            'author_id' => $admin->id,
            'title' => 'Draft Artikel Internal',
            'slug' => 'draft-artikel-internal',
            'body' => 'Artikel yang belum diterbitkan.',
            'is_published' => false,
        ]);

        $this->get(route('articles.index'))->assertOk()->assertDontSee($article->title);
        $this->get(route('articles.show', $article->slug))->assertNotFound();
    }

    public function test_curator_and_artisan_dashboard_metrics_open_matching_product_filters(): void
    {
        $curator = $this->user(User::ROLE_CURATOR);
        $artisan = $this->user(User::ROLE_ARTISAN);
        $otherArtisan = $this->user(User::ROLE_ARTISAN);
        $category = $this->category();
        $pending = $this->product($artisan, $category, ['name' => 'Produk Menunggu Uji', 'verification_status' => Product::STATUS_PENDING]);
        $approved = $this->product($artisan, $category, ['name' => 'Produk Disetujui Uji', 'verification_status' => Product::STATUS_APPROVED]);
        $revision = $this->product($artisan, $category, [
            'name' => 'Produk Revisi Uji',
            'verification_status' => Product::STATUS_REVISION,
            'verification_note' => 'Lengkapi foto detail bahan.',
        ]);
        $rejected = $this->product($artisan, $category, [
            'name' => 'Produk Ditolak Uji',
            'verification_status' => Product::STATUS_REJECTED,
            'verification_note' => 'Foto produk belum menunjukkan detail anyaman.',
            'dimensions' => '20 x 15 x 10 cm',
            'weight_grams' => 350,
        ]);
        ProductProcessStep::create([
            'product_id' => $rejected->id,
            'step_number' => 1,
            'title' => 'Penyusunan bahan',
            'description' => 'Bahan disusun sebelum dianyam.',
        ]);
        $this->product($otherArtisan, $category, ['name' => 'Produk Perajin Lain', 'verification_status' => Product::STATUS_REVISION]);

        $this->actingAs($curator)
            ->get(route('curator.dashboard'))
            ->assertOk()
            ->assertSee(route('curator.products.index', ['status' => 'pending']), false)
            ->assertSee(route('curator.products.index', ['status' => 'approved']), false)
            ->assertSee(route('curator.products.index', ['status' => 'revision']), false)
            ->assertSee(route('curator.products.index', ['status' => 'rejected']), false);
        $this->get(route('curator.products.index', ['status' => 'approved']))
            ->assertOk()
            ->assertSee($approved->name)
            ->assertDontSee($pending->name)
            ->assertDontSee($revision->name)
            ->assertDontSee($rejected->name);
        $this->get(route('curator.products.show', $approved))
            ->assertOk()
            ->assertSee('Detail status karya')
            ->assertDontSee('Setujui & terbitkan QR');
        $this->get(route('curator.products.show', $rejected))
            ->assertOk()
            ->assertSee('Detail penolakan')
            ->assertSee('Alasan penolakan dari kurator')
            ->assertSee('Foto produk belum menunjukkan detail anyaman.')
            ->assertSee('20 x 15 x 10 cm')
            ->assertSee('350 gram')
            ->assertSee('Penyusunan bahan')
            ->assertDontSee('Setujui & terbitkan QR');
        $this->get(route('curator.products.show', $revision))
            ->assertOk()
            ->assertSee('Detail revisi')
            ->assertSee('Catatan revisi dari kurator')
            ->assertSee('Lengkapi foto detail bahan.')
            ->assertSee('Revisi')
            ->assertDontSee('Setujui & terbitkan QR');
        $this->from(route('curator.products.show', $revision))
            ->post(route('curator.products.decide', $revision), ['decision' => Product::STATUS_APPROVED])
            ->assertRedirect(route('curator.products.show', $revision))
            ->assertSessionHas('error', 'Karya ini tidak lagi menunggu verifikasi.');

        $this->actingAs($artisan)
            ->get(route('artisan.dashboard'))
            ->assertOk()
            ->assertSee(route('artisan.products.index', ['status' => 'approved']), false)
            ->assertSee(route('artisan.products.index', ['status' => 'pending']), false)
            ->assertSee(route('artisan.products.index', ['status' => 'revision']), false);
        $this->get(route('artisan.products.index', ['status' => 'revision']))
            ->assertOk()
            ->assertSee($revision->name)
            ->assertDontSee($pending->name)
            ->assertDontSee($approved->name)
            ->assertDontSee('Produk Perajin Lain');
    }

    public function test_curator_approval_issues_a_unique_qr_and_story_records_scan(): void
    {
        Storage::fake('public');
        $category = $this->category();
        $artisan = $this->user(User::ROLE_ARTISAN);
        $curator = $this->user(User::ROLE_CURATOR);
        $product = $this->product($artisan, $category, [
            'verification_status' => Product::STATUS_PENDING,
            'craft_id' => null,
            'qr_active' => false,
        ]);
        ProductProcessStep::create([
            'product_id' => $product->id,
            'step_number' => 1,
            'title' => 'Anyaman',
            'description' => 'Dirangkai dengan tangan.',
        ]);

        $this->actingAs($curator)->post(route('curator.products.decide', $product), [
            'decision' => Product::STATUS_APPROVED,
            'checklist' => [
                'photos' => '1', 'story' => '1', 'materials' => '1',
                'artisan_origin' => '1', 'process' => '1', 'originality' => '1',
            ],
        ])->assertRedirect(route('curator.products.index'));

        $product->refresh();
        $this->assertTrue($product->qr_active);
        $this->assertMatchesRegularExpression('/^KL-\d{4}-\d{6}$/', $product->craft_id);
        $this->assertTrue(Storage::disk('public')->exists("craft-qr/{$product->craft_id}.svg"));

        $this->get(route('craft.story', $product->craft_id))
            ->assertOk()
            ->assertSee($product->craft_id)
            ->assertSee($product->name);
        $this->assertDatabaseHas('qr_scans', ['product_id' => $product->id]);
        $this->assertSame(1, $product->fresh()->scan_count);
    }

    public function test_same_product_is_merged_into_one_cart_row_and_quantity_increases(): void
    {
        $category = $this->category();
        $artisan = $this->user(User::ROLE_ARTISAN);
        $customer = $this->user(User::ROLE_CUSTOMER);
        $product = $this->product($artisan, $category, ['stock' => 5, 'price' => 125000]);

        $this->actingAs($customer)->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 1]);
        $this->actingAs($customer)->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 1]);

        $this->assertDatabaseCount('carts', 1);
        $this->assertDatabaseHas('carts', ['product_id' => $product->id, 'user_id' => $customer->id, 'quantity' => 2]);
    }

    public function test_checkout_creates_order_snapshot_and_decreases_stock(): void
    {
        $category = $this->category();
        $artisan = $this->user(User::ROLE_ARTISAN);
        $customer = $this->user(User::ROLE_CUSTOMER);
        $product = $this->product($artisan, $category, ['stock' => 5, 'price' => 125000]);

        $this->actingAs($customer)->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
            'recipient_name' => 'Aisyah',
            'sender_name' => 'Rani',
            'gift_message' => 'Selamat ulang tahun!',
        ])->assertRedirect(route('cart.index'));

        $response = $this->post(route('checkout.store'), [
            'recipient_name' => 'Rani Pembeli',
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Jalan Melati 10',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40123',
            'shipping_method' => 'regular',
            'payment_method' => 'qris',
        ]);

        $order = Order::with('items')->firstOrFail();
        $response->assertRedirect(route('orders.show', $order));
        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Pengiriman & pembayaran');
        $this->assertSame('awaiting_payment', $order->status);
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertSame('125000.00', $order->items->first()->unit_price);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertDatabaseMissing('carts', ['user_id' => $customer->id]);

        $admin = $this->user(User::ROLE_ADMIN);
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.orders.show', $order));
        $this->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee($customer->email)
            ->assertSee($product->name);
    }

    public function test_customer_can_checkout_only_the_selected_cart_items(): void
    {
        $category = $this->category();
        $artisan = $this->user(User::ROLE_ARTISAN);
        $customer = $this->user(User::ROLE_CUSTOMER);
        $firstProduct = $this->product($artisan, $category, ['name' => 'Karya Pertama', 'stock' => 5, 'price' => 125000]);
        $secondProduct = $this->product($artisan, $category, ['name' => 'Karya Kedua', 'stock' => 5, 'price' => 250000]);

        $this->actingAs($customer)->post(route('cart.store'), ['product_id' => $firstProduct->id, 'quantity' => 1]);
        $this->actingAs($customer)->post(route('cart.store'), ['product_id' => $secondProduct->id, 'quantity' => 1]);

        $this->actingAs($customer)
            ->get(route('checkout', ['selected' => [$this->getCartItemId($customer, $firstProduct)->id]]))
            ->assertOk()
            ->assertSee('Karya Pertama')
            ->assertDontSee('Karya Kedua');

        $this->actingAs($customer)->post(route('checkout.store'), [
            'selected_items' => [$this->getCartItemId($customer, $firstProduct)->id],
            'recipient_name' => 'Rani Pembeli',
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Jalan Melati 10',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40123',
            'shipping_method' => 'regular',
            'payment_method' => 'qris',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', ['subtotal' => 125000.00]);
        $this->assertDatabaseMissing('carts', ['product_id' => $firstProduct->id, 'user_id' => $customer->id]);
        $this->assertDatabaseHas('carts', ['product_id' => $secondProduct->id, 'user_id' => $customer->id]);
    }

    private function getCartItemId(User $customer, Product $product): \App\Models\Cart
    {
        return $customer->cartItems()->where('product_id', $product->id)->firstOrFail();
    }

    public function test_customer_and_customer_service_can_follow_a_support_ticket_to_resolution(): void
    {
        $customer = $this->user(User::ROLE_CUSTOMER);
        $agent = $this->user(User::ROLE_CUSTOMER_SERVICE);

        $this->actingAs($customer)->get(route('support.customer.index'))->assertOk();
        $this->get(route('support.customer.create'))->assertOk();
        $this->post(route('support.customer.store'), [
            'category' => 'shipping',
            'subject' => 'Paket belum diterima',
            'description' => 'Status pengiriman belum berubah sejak kemarin.',
        ])->assertSessionHasNoErrors();

        $ticket = SupportTicket::firstOrFail();
        $this->get(route('support.customer.show', $ticket))
            ->assertOk()
            ->assertSee($ticket->ticket_number)
            ->assertSee('Status pengiriman belum berubah sejak kemarin.');

        $this->actingAs($agent)->get(route('support.agent.show', $ticket))
            ->assertOk()
            ->assertSee('Perbarui tiket');
        $this->put(route('support.agent.update', $ticket), [
            'status' => 'resolved',
            'resolution' => 'Paket sudah dikonfirmasi diterima oleh kurir.',
        ])->assertRedirect();

        $this->actingAs($customer)->get(route('support.customer.show', $ticket))
            ->assertOk()
            ->assertSee('Paket sudah dikonfirmasi diterima oleh kurir.');
        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'status' => 'resolved',
            'resolution' => 'Paket sudah dikonfirmasi diterima oleh kurir.',
        ]);
    }

    public function test_customer_service_dashboard_links_each_ticket_to_detail_page(): void
    {
        $customer = $this->user(User::ROLE_CUSTOMER);
        $agent = $this->user(User::ROLE_CUSTOMER_SERVICE);

        $this->actingAs($customer)->post(route('support.customer.store'), [
            'category' => 'shipping',
            'subject' => 'Pesanan diterima terlambat',
            'description' => 'Pengiriman barang masih belum turun status selama tiga hari.',
        ])->assertSessionHasNoErrors();

        $ticket = SupportTicket::firstOrFail();
        foreach (range(1, 15) as $number) {
            SupportTicket::create([
                'ticket_number' => 'CS-TEST-' . str_pad((string) $number, 6, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'assigned_to' => $agent->id,
                'category' => 'shipping',
                'subject' => 'Pertanyaan pengiriman ' . $number,
                'description' => 'Mohon periksa pembaruan status pengiriman.',
                'status' => 'open',
            ]);
        }

        $this->actingAs($agent)
            ->get(route('support.dashboard'))
            ->assertOk()
            ->assertSee('Antrean bantuan')
            ->assertSee(route('support.agent.show', ['ticket' => $ticket->id]))
            ->assertSee($ticket->subject)
            ->assertSee('Menampilkan')
            ->assertSee('Berikutnya')
            ->assertDontSee('w-5 h-5');

        $this->get(route('support.tickets.index'))
            ->assertOk()
            ->assertSee('Tiket bantuan')
            ->assertSee('Semua')
            ->assertSee(route('support.agent.show', ['ticket' => $ticket->id]));
    }

    public function test_customer_and_artisan_can_chat_about_a_product_with_private_participant_access(): void
    {
        $customer = $this->user(User::ROLE_CUSTOMER);
        $artisan = $this->user(User::ROLE_ARTISAN);
        $otherArtisan = $this->user(User::ROLE_ARTISAN);
        $product = $this->product($artisan, $this->category());

        $this->actingAs($customer)->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Tanya langsung ke perajin');
        $this->post(route('chat.customer.start'), [
            'product_id' => $product->id,
            'body' => 'Apakah karya ini tersedia dalam warna lain?',
        ])->assertRedirect();

        $chat = ArtisanCustomerChat::firstOrFail();
        $this->get(route('chat.customer.index'))->assertOk()->assertSee($artisan->name);
        $this->get(route('chat.customer.show', $chat))
            ->assertOk()
            ->assertSee('Apakah karya ini tersedia dalam warna lain?');

        $this->actingAs($otherArtisan)->get(route('artisan.chats.show', $chat))->assertNotFound();
        $this->actingAs($artisan)->get(route('artisan.chats.index'))->assertOk()->assertSee($customer->name);
        $this->get(route('artisan.chats.show', $chat))
            ->assertOk()
            ->assertSee('Apakah karya ini tersedia dalam warna lain?');
        $this->post(route('artisan.chats.message', $chat), [
            'body' => 'Bisa, saya dapat memilihkan warna yang tersedia.',
        ])->assertRedirect(route('artisan.chats.show', $chat));

        $this->actingAs($customer)->get(route('chat.customer.show', $chat))
            ->assertOk()
            ->assertSee('Bisa, saya dapat memilihkan warna yang tersedia.');
        $this->assertDatabaseHas('artisan_customer_chat_messages', [
            'chat_id' => $chat->id,
            'sender_id' => $artisan->id,
            'body' => 'Bisa, saya dapat memilihkan warna yang tersedia.',
        ]);
    }

    public function test_public_about_page_and_customer_invoice_are_available(): void
    {
        $artisan = $this->user(User::ROLE_ARTISAN);
        $customer = $this->user(User::ROLE_CUSTOMER);
        $product = $this->product($artisan, $this->category(), ['name' => 'Produk Invoice']);

        $order = Order::create([
            'order_number' => 'KL-261002-TEST01',
            'customer_id' => $customer->id,
            'recipient_name' => 'Rina Pembeli',
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Jl. Mawar No. 14',
            'city' => 'Yogyakarta',
            'province' => 'DIY',
            'postal_code' => '55281',
            'shipping_method' => 'regular',
            'status' => 'awaiting_payment',
            'payment_method' => 'bank_transfer',
            'payment_status' => 'pending',
            'subtotal' => 200000,
            'shipping_cost' => 18000,
            'discount_total' => 0,
            'total' => 218000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'artisan_id' => $artisan->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 200000,
        ]);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Tentang KaryaLokal')
            ->assertSee('Yogyakarta');

        $this->actingAs($customer)
            ->get(route('orders.invoice', $order))
            ->assertOk()
            ->assertSee('Invoice Pembelian')
            ->assertSee($order->order_number)
            ->assertSee('Produk Invoice');
    }

    public function test_about_page_has_address_and_map_and_order_detail_lists_review_data(): void
    {
        $artisan = $this->user(User::ROLE_ARTISAN);
        $customer = $this->user(User::ROLE_CUSTOMER);
        $product = $this->product($artisan, $this->category(), ['name' => 'Produk Review Order']);

        $order = Order::create([
            'order_number' => 'KL-261002-ORDER-REV',
            'customer_id' => $customer->id,
            'recipient_name' => 'Rina Pembeli',
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Jl. Mawar No. 14',
            'city' => 'Yogyakarta',
            'province' => 'DIY',
            'postal_code' => '55281',
            'shipping_method' => 'regular',
            'status' => 'awaiting_payment',
            'payment_method' => 'bank_transfer',
            'payment_status' => 'paid',
            'subtotal' => 200000,
            'shipping_cost' => 18000,
            'discount_total' => 0,
            'total' => 218000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'artisan_id' => $artisan->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 200000,
        ]);

        $product->reviews()->create([
            'customer_id' => $customer->id,
            'product_rating' => 5,
            'body' => 'Barangnya bagus dan pengirimannya cepat.',
            'is_published' => true,
        ]);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Alamat & lokasi')
            ->assertSee('Bantul, Yogyakarta')
            ->assertSee('google.com/maps');

        $this->actingAs($customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Ulasan pembeli')
            ->assertSee('Barangnya bagus dan pengirimannya cepat.');
    }

    public function test_customer_can_submit_a_product_review_and_artisan_can_reply(): void
    {
        $artisan = $this->user(User::ROLE_ARTISAN);
        $customer = $this->user(User::ROLE_CUSTOMER);
        $product = $this->product($artisan, $this->category(), ['name' => 'Karya dengan Ulasan']);
        $order = $this->salesOrder($customer, [
            'payment_status' => 'paid',
            'status' => 'completed',
            'tracking_number' => 'RESI-ULASAN-123',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'artisan_id' => $artisan->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => $product->price,
        ]);

        $this->actingAs($customer)
            ->post(route('products.reviews.store', $product), [
                'product_rating' => 5,
                'body' => 'Barangnya bagus, detailnya rapi dan pengiriman cepat.',
            ])
            ->assertRedirect(route('products.show', $product->slug));

        $review = $product->reviews()->firstOrFail();
        $this->assertSame(5, $review->product_rating);
        $this->assertSame('Barangnya bagus, detailnya rapi dan pengiriman cepat.', $review->body);
        $this->assertDatabaseHas('reviews', [
            'order_item_id' => $order->items()->value('id'),
            'product_id' => $product->id,
            'customer_id' => $customer->id,
        ]);

        $this->actingAs($artisan)
            ->post(route('artisan.products.reviews.reply', [$product, $review]), [
                'artisan_reply' => 'Terima kasih atas ulasan dan dukungannya, semoga karya ini bermanfaat.',
            ])
            ->assertRedirect(route('artisan.products.show', $product));

        $review->refresh();
        $this->assertSame('Terima kasih atas ulasan dan dukungannya, semoga karya ini bermanfaat.', $review->artisan_reply);

        $this->actingAs($artisan)
            ->get(route('artisan.products.index'))
            ->assertOk()
            ->assertSee('Karya dengan Ulasan')
            ->assertSee('5.0')
            ->assertSee('1 ulasan');

        $this->get(route('artisan.products.show', $product))
            ->assertOk()
            ->assertSee($customer->name)
            ->assertSee('Barangnya bagus, detailnya rapi dan pengiriman cepat.')
            ->assertSee('Balas komentar pembeli');

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Barangnya bagus, detailnya rapi dan pengiriman cepat.')
            ->assertSee('Terima kasih atas ulasan dan dukungannya, semoga karya ini bermanfaat.');
    }

    public function test_artisan_product_reviews_are_hidden_until_product_is_approved(): void
    {
        $artisan = $this->user(User::ROLE_ARTISAN);
        $customer = $this->user(User::ROLE_CUSTOMER);
        $product = $this->product($artisan, $this->category(), [
            'name' => 'Karya Menunggu Persetujuan',
            'verification_status' => Product::STATUS_PENDING,
        ]);
        $product->reviews()->create([
            'customer_id' => $customer->id,
            'product_rating' => 4,
            'body' => 'Komentar rahasia sebelum disetujui.',
            'is_published' => true,
        ]);

        $this->actingAs($artisan)
            ->get(route('artisan.products.index'))
            ->assertOk()
            ->assertSee('Karya Menunggu Persetujuan')
            ->assertDontSee('Komentar rahasia sebelum disetujui.')
            ->assertDontSee('ulasan');

        $this->get(route('artisan.products.show', $product))
            ->assertOk()
            ->assertSee('Karya Menunggu Persetujuan')
            ->assertSee('Ulasan dan komentar akan tersedia setelah karya disetujui.')
            ->assertDontSee('Komentar rahasia sebelum disetujui.')
            ->assertDontSee('Balas komentar pembeli');
    }

    public function test_manual_payment_proof_can_be_rejected_resubmitted_and_approved(): void
    {
        Storage::fake('local');
        $customer = $this->user(User::ROLE_CUSTOMER);
        $admin = $this->user(User::ROLE_ADMIN);
        $product = $this->product($this->user(User::ROLE_ARTISAN), $this->category());

        $this->actingAs($customer)->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect(route('cart.index'));
        $this->post(route('checkout.store'), [
            'recipient_name' => 'Pembeli Uji',
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Jalan Melati 10',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40123',
            'shipping_method' => 'regular',
            'payment_method' => 'bank_transfer',
        ]);
        $order = Order::firstOrFail();

        $this->post(route('orders.payment-proof', $order), [
            'payment_proof' => UploadedFile::fake()->image('transfer.png'),
        ])->assertRedirect(route('orders.show', $order));
        $order->refresh();
        $firstProof = $order->payment_proof_path;
        $this->assertSame('pending_verification', $order->payment_status);
        $this->assertNull($order->tracking_number);
        $this->assertTrue(Storage::disk('local')->exists($firstProof));
        $this->actingAs($customer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Nomor resi akan tersedia setelah pembayaran diverifikasi dan pesanan dikirim.');

        $this->actingAs($admin)->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee($order->order_number);
        $this->put(route('admin.payments.update', $order), [
            'decision' => 'rejected',
            'rejection_reason' => 'Nominal pada bukti tidak terbaca.',
        ])->assertRedirect(route('admin.payments.index'));

        $this->actingAs($customer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Nominal pada bukti tidak terbaca.');
        $this->post(route('orders.payment-proof', $order), [
            'payment_proof' => UploadedFile::fake()->image('transfer-baru.png'),
        ])->assertRedirect(route('orders.show', $order));
        $order->refresh();
        $this->assertFalse(Storage::disk('local')->exists($firstProof));
        $this->assertTrue(Storage::disk('local')->exists($order->payment_proof_path));

        $this->actingAs($admin)->put(route('admin.payments.update', $order), [
            'decision' => 'approved',
        ])->assertRedirect(route('admin.payments.index'));
        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('processing', $order->status);
        $this->assertSame('KL-RESI-' . strtoupper(preg_replace('/[^A-Z0-9]/', '', $order->order_number)), $order->tracking_number);
        $this->assertNotNull($order->paid_at);
        $this->actingAs($customer)->get(route('orders.invoice', $order))
            ->assertOk()
            ->assertSee('Nomor resi')
            ->assertSee($order->tracking_number);
        $this->actingAs($customer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Pesanan selesai');

        $this->actingAs($admin)->put(route('admin.orders.tracking', $order), [
            'tracking_number' => 'JNE123456789',
        ])->assertRedirect();
        $order->refresh();
        $this->assertSame('shipped', $order->status);
        $this->actingAs($customer)->get(route('orders.index'))
            ->assertOk()
            ->assertSee('JNE123456789');
        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Nomor resi:')
            ->assertSee('JNE123456789')
            ->assertSee('Pesanan selesai');

        $orderItem = $order->items()->firstOrFail();
        $this->post(route('orders.items.reviews.store', [$order, $orderItem]), [
            'product_rating' => 5,
            'body' => 'Karya diterima dengan baik dan sesuai deskripsi.',
        ])->assertForbidden();

        $this->post(route('orders.complete', $order))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success');
        $order->refresh();
        $this->assertSame('completed', $order->status);
        $this->assertNotNull($order->received_at);
        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('order-star-rating')
            ->assertSee('type="radio"', false);

        $this->post(route('orders.items.reviews.store', [$order, $orderItem]), [
            'product_rating' => 5,
            'body' => 'Karya diterima dengan baik dan sesuai deskripsi.',
        ])->assertRedirect(route('products.show', $product->slug) . '#reviews');
        $this->assertDatabaseHas('reviews', [
            'order_item_id' => $orderItem->id,
            'customer_id' => $customer->id,
            'body' => 'Karya diterima dengan baik dan sesuai deskripsi.',
        ]);
        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Karya diterima dengan baik dan sesuai deskripsi.')
            ->assertDontSee('Pilih bintang');
    }

    public function test_admin_and_artisan_sales_recap_only_counts_paid_product_items(): void
    {
        $customer = $this->user(User::ROLE_CUSTOMER);
        $artisan = $this->user(User::ROLE_ARTISAN);
        $product = $this->product($artisan, $this->category(), ['price' => 100000]);
        $paidOrder = $this->salesOrder($customer, [
            'order_number' => 'KL-RECAP-PAID',
            'payment_status' => 'paid',
            'subtotal' => 200000,
            'total' => 218000,
            'paid_at' => now(),
        ]);
        OrderItem::create([
            'order_id' => $paidOrder->id,
            'product_id' => $product->id,
            'artisan_id' => $artisan->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 100000,
        ]);

        $unpaidOrder = $this->salesOrder($customer, ['order_number' => 'KL-RECAP-UNPAID']);
        OrderItem::create([
            'order_id' => $unpaidOrder->id,
            'product_id' => $product->id,
            'artisan_id' => $artisan->id,
            'product_name' => $product->name,
            'quantity' => 3,
            'unit_price' => 100000,
        ]);

        $admin = $this->user(User::ROLE_ADMIN);
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Rp218.000')
            ->assertSee('Rp200.000')
            ->assertSee($artisan->name);

        $this->actingAs($artisan)->get(route('artisan.dashboard'))
            ->assertOk()
            ->assertSee('Rp200.000')
            ->assertSee('Order dibayar')
            ->assertSee('Produk terjual');
    }

    private function user(string $role): User
    {
        static $sequence = 0;
        $sequence++;

        return User::create([
            'name' => ucfirst($role) . ' ' . $sequence,
            'email' => $role . $sequence . '@example.test',
            'password' => 'password123',
            'role' => $role,
            'status_akun' => 'aktif',
        ]);
    }

    private function salesOrder(User $customer, array $overrides = []): Order
    {
        static $sequence = 0;
        $sequence++;

        return Order::create(array_merge([
            'order_number' => 'KL-RECAP-' . $sequence,
            'customer_id' => $customer->id,
            'recipient_name' => $customer->name,
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Jalan Melati 10',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40123',
            'shipping_method' => 'regular',
            'status' => 'processing',
            'payment_method' => 'bank_transfer',
            'payment_status' => 'pending',
            'subtotal' => 100000,
            'shipping_cost' => 18000,
            'discount_total' => 0,
            'total' => 118000,
        ], $overrides));
    }

    private function category(): Category
    {
        static $sequence = 0;
        $sequence++;

        return Category::create([
            'name' => 'Kerajinan ' . $sequence,
            'slug' => 'kerajinan-' . $sequence,
            'is_active' => true,
        ]);
    }

    private function product(User $artisan, Category $category, array $overrides = []): Product
    {
        static $sequence = 0;
        $sequence++;
        $values = array_merge([
            'artisan_id' => $artisan->id,
            'category_id' => $category->id,
            'name' => 'Produk Uji ' . $sequence,
            'slug' => 'produk-uji-' . $sequence,
            'description' => 'Deskripsi produk uji.',
            'story' => 'Cerita karya uji.',
            'material' => 'Rotan',
            'technique' => 'Anyaman',
            'origin' => 'Cirebon',
            'price' => 100000,
            'stock' => 5,
            'fulfillment_type' => 'ready_stock',
            'images' => ['https://example.test/product.jpg'],
            'verification_status' => Product::STATUS_APPROVED,
            'craft_id' => null,
            'qr_active' => false,
        ], $overrides);

        return Product::create($values);
    }
}
