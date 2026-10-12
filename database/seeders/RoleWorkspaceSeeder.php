<?php

namespace Database\Seeders;

use App\Models\ArtisanProfile;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductProcessStep;
use App\Models\QrScan;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Wishlist;
use App\Services\CraftQrCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class RoleWorkspaceSeeder extends Seeder
{
    public function run(CraftQrCodeService $qrCode): void
    {
        $accounts = [
            ['name' => 'Nadia Prameswari', 'email' => 'nadia.admin@karyalokal.local', 'role' => User::ROLE_ADMIN],
            ['name' => 'Ratna Wulandari', 'email' => 'ratna.kurator@karyalokal.local', 'role' => User::ROLE_CURATOR],
            ['name' => 'Bima Aditya', 'email' => 'bima.kurator@karyalokal.local', 'role' => User::ROLE_CURATOR],
            ['name' => 'Dewi Anggraini', 'email' => 'dewi.kurator@karyalokal.local', 'role' => User::ROLE_CURATOR],
            ['name' => 'Nisa Puspita', 'email' => 'nisa.cs@karyalokal.local', 'role' => User::ROLE_CUSTOMER_SERVICE],
            ['name' => 'Aris Kurniawan', 'email' => 'aris.cs@karyalokal.local', 'role' => User::ROLE_CUSTOMER_SERVICE],
            ['name' => 'Rani Permata', 'email' => 'rani.cs@karyalokal.local', 'role' => User::ROLE_CUSTOMER_SERVICE],
            ['name' => 'Nabila Putri', 'email' => 'nabila.perajin@karyalokal.local', 'role' => User::ROLE_ARTISAN],
            ['name' => 'Dwi Prasetyo', 'email' => 'dwiprasetyo.perajin@karyalokal.local', 'role' => User::ROLE_ARTISAN],
            ['name' => 'Ayu Lestari', 'email' => 'ayu.perajin@karyalokal.local', 'role' => User::ROLE_ARTISAN],
            ['name' => 'Raka Saputra', 'email' => 'raka.perajin@karyalokal.local', 'role' => User::ROLE_ARTISAN],
            ['name' => 'Maya Sari', 'email' => 'maya.perajin@karyalokal.local', 'role' => User::ROLE_ARTISAN],
            ['name' => 'Intan Maharani', 'email' => 'intan.pelanggan@karyalokal.local', 'role' => User::ROLE_CUSTOMER],
            ['name' => 'Fajar Nugroho', 'email' => 'fajar.pelanggan@karyalokal.local', 'role' => User::ROLE_CUSTOMER],
            ['name' => 'Laras Pertiwi', 'email' => 'laras.pelanggan@karyalokal.local', 'role' => User::ROLE_CUSTOMER],
            ['name' => 'Bagas Ramadhan', 'email' => 'bagas.pelanggan@karyalokal.local', 'role' => User::ROLE_CUSTOMER],
            ['name' => 'Putri Anindya', 'email' => 'putri.pelanggan@karyalokal.local', 'role' => User::ROLE_CUSTOMER],
            ['name' => 'Yoga Pratama', 'email' => 'yoga.pelanggan@karyalokal.local', 'role' => User::ROLE_CUSTOMER],
            ['name' => 'Salsa Kirana', 'email' => 'salsa.pelanggan@karyalokal.local', 'role' => User::ROLE_CUSTOMER],
            ['name' => 'Andi Firmansyah', 'email' => 'andi.pelanggan@karyalokal.local', 'role' => User::ROLE_CUSTOMER],
        ];

        $legacyDwi = User::where('email', 'dwi.perajin@karyalokal.local')->first();
        if ($legacyDwi && !User::where('email', 'dwiprasetyo.perajin@karyalokal.local')->exists()) {
            $legacyDwi->update(['email' => 'dwiprasetyo.perajin@karyalokal.local']);
        }

        $users = [];
        foreach ($accounts as $account) {
            $users[$account['email']] = User::firstOrCreate(
                ['email' => $account['email']],
                $account + ['password' => Hash::make('password'), 'status_akun' => 'aktif']
            );
        }

        $artisanDetails = [
            ['email' => 'nabila.perajin@karyalokal.local', 'city' => 'Bantul', 'province' => 'Yogyakarta', 'specialty' => 'Anyaman serat alam', 'story' => 'Nabila mengolah serat alam menjadi benda pakai yang ringan dan tahan lama.'],
            ['email' => 'dwiprasetyo.perajin@karyalokal.local', 'city' => 'Kasongan', 'province' => 'Yogyakarta', 'specialty' => 'Keramik dan gerabah', 'story' => 'Dwi menjaga karakter tanah lokal melalui bentuk sederhana dan glasir buatan sendiri.'],
            ['email' => 'ayu.perajin@karyalokal.local', 'city' => 'Solo', 'province' => 'Jawa Tengah', 'specialty' => 'Batik tulis', 'story' => 'Ayu mengembangkan motif batik yang terinspirasi dari tumbuhan di sekitar rumah.'],
            ['email' => 'raka.perajin@karyalokal.local', 'city' => 'Cirebon', 'province' => 'Jawa Barat', 'specialty' => 'Ukiran kayu', 'story' => 'Raka memilih kayu dengan cermat dan mengukir setiap detail secara manual.'],
            ['email' => 'maya.perajin@karyalokal.local', 'city' => 'Ubud', 'province' => 'Bali', 'specialty' => 'Tekstil tenun', 'story' => 'Maya menenun kain dengan palet warna alam dan teknik yang dipelajari lintas generasi.'],
        ];

        $categories = Category::query()->where('is_active', true)->pluck('id', 'name');
        $products = collect();

        $productSets = [
            [
                'name' => 'Kap Lampu Serat Pagi', 'slug' => 'kap-lampu-serat-pagi', 'category' => 'Dekorasi', 'status' => Product::STATUS_APPROVED,
                'price' => 385000, 'stock' => 8, 'material' => 'Serat pandan dan rotan', 'technique' => 'Anyaman tangan', 'origin' => 'Bantul, Yogyakarta',
                'image' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=900&q=85',
                'description' => 'Kap lampu dengan bayangan lembut untuk sudut baca dan ruang keluarga.',
                'story' => 'Serat pandan dikeringkan perlahan sebelum dianyam menjadi kap lampu yang menghadirkan suasana hangat.',
            ],
            [
                'name' => 'Keranjang Pesisir', 'slug' => 'keranjang-pesisir', 'category' => 'Anyaman', 'status' => Product::STATUS_PENDING,
                'price' => 245000, 'stock' => 12, 'material' => 'Pandan dan benang katun', 'technique' => 'Anyaman silang', 'origin' => 'Bantul, Yogyakarta',
                'image' => 'https://images.unsplash.com/photo-1593085260707-5377ba37f868?auto=format&fit=crop&w=900&q=85',
                'description' => 'Keranjang serbaguna dengan pegangan yang nyaman dan pola silang rapat.',
                'story' => 'Pola keranjang terinspirasi bentuk ombak yang terlihat dari kampung halaman perajin.',
            ],
            [
                'name' => 'Mangkuk Tanah Senja', 'slug' => 'mangkuk-tanah-senja', 'category' => 'Keramik', 'status' => Product::STATUS_APPROVED,
                'price' => 165000, 'stock' => 14, 'material' => 'Tanah liat Kasongan', 'technique' => 'Putar dan glasir', 'origin' => 'Kasongan, Yogyakarta',
                'image' => 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=900&q=85',
                'description' => 'Mangkuk keramik harian dengan glasir berwarna tanah yang lembut.',
                'story' => 'Warna glasirnya mengambil inspirasi dari cahaya sore yang menyentuh halaman bengkel keramik.',
            ],
            [
                'name' => 'Cangkir Awan Kasongan', 'slug' => 'cangkir-awan-kasongan', 'category' => 'Keramik', 'status' => Product::STATUS_REVISION,
                'price' => 135000, 'stock' => 10, 'material' => 'Tanah liat lokal', 'technique' => 'Bentuk tangan', 'origin' => 'Kasongan, Yogyakarta',
                'image' => 'https://images.unsplash.com/photo-1493106641515-6b5631de4bb9?auto=format&fit=crop&w=900&q=85',
                'description' => 'Cangkir dengan pegangan nyaman dan permukaan bertekstur.',
                'story' => 'Bentuk cangkir dibuat mengikuti gerak tangan agar terasa akrab saat digunakan.',
            ],
            [
                'name' => 'Kain Batik Taman Sore', 'slug' => 'kain-batik-taman-sore', 'category' => 'Batik', 'status' => Product::STATUS_APPROVED,
                'price' => 520000, 'stock' => 5, 'material' => 'Katun primisima', 'technique' => 'Batik tulis dan pewarna alam', 'origin' => 'Solo, Jawa Tengah',
                'image' => 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=900&q=85',
                'description' => 'Kain batik tulis dengan motif flora yang digambar dan diwarna secara bertahap.',
                'story' => 'Motif taman dibuat dari sketsa daun dan bunga yang tumbuh di halaman rumah perajin.',
            ],
            [
                'name' => 'Selendang Rona Daun', 'slug' => 'selendang-rona-daun', 'category' => 'Fashion', 'status' => Product::STATUS_PENDING,
                'price' => 310000, 'stock' => 7, 'material' => 'Katun tenun', 'technique' => 'Pewarnaan alami', 'origin' => 'Solo, Jawa Tengah',
                'image' => 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=900&q=85',
                'description' => 'Selendang ringan dengan warna hijau lembut dari bahan pewarna alami.',
                'story' => 'Warna daun muda dipilih untuk menghadirkan kesan tenang dan mudah dipadukan.',
            ],
            [
                'name' => 'Nampan Ukir Cirebon', 'slug' => 'nampan-ukir-cirebon', 'category' => 'Kerajinan Kayu', 'status' => Product::STATUS_APPROVED,
                'price' => 295000, 'stock' => 9, 'material' => 'Kayu mahoni', 'technique' => 'Ukir dan amplas tangan', 'origin' => 'Cirebon, Jawa Barat',
                'image' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=900&q=85',
                'description' => 'Nampan kayu dengan detail ukiran di tepi dan lapisan pelindung alami.',
                'story' => 'Garis ukir pada tepi nampan mengadaptasi motif yang biasa dijumpai pada pintu rumah lama.',
            ],
            [
                'name' => 'Kotak Kenang Kayu', 'slug' => 'kotak-kenang-kayu', 'category' => 'Suvenir', 'status' => Product::STATUS_REVISION,
                'price' => 225000, 'stock' => 6, 'material' => 'Kayu jati', 'technique' => 'Sambungan kayu dan ukir', 'origin' => 'Cirebon, Jawa Barat',
                'image' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=900&q=85',
                'description' => 'Kotak kecil untuk menyimpan perhiasan dan benda kenangan.',
                'story' => 'Kotak dirancang agar setiap benda kecil tersimpan rapi dan mudah ditemukan kembali.',
            ],
            [
                'name' => 'Tas Tenun Lembah', 'slug' => 'tas-tenun-lembah', 'category' => 'Aksesori', 'status' => Product::STATUS_APPROVED,
                'price' => 465000, 'stock' => 4, 'material' => 'Benang kapas', 'technique' => 'Tenun alat bukan mesin', 'origin' => 'Ubud, Bali',
                'image' => 'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=900&q=85',
                'description' => 'Tas tenun berukuran sedang dengan ruang utama dan tali bahu kuat.',
                'story' => 'Perpaduan warna tas mengambil inspirasi dari garis sawah dan perbukitan di sekitar desa.',
            ],
            [
                'name' => 'Dompet Tenun Pagi', 'slug' => 'dompet-tenun-pagi', 'category' => 'Aksesori', 'status' => Product::STATUS_PENDING,
                'price' => 185000, 'stock' => 11, 'material' => 'Kapas pintal lokal', 'technique' => 'Tenun dan jahit tangan', 'origin' => 'Ubud, Bali',
                'image' => 'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=900&q=85',
                'description' => 'Dompet ringan dengan beberapa ruang kartu dan lapisan kain yang kuat.',
                'story' => 'Sisa benang tenun dipadukan kembali agar setiap dompet memiliki komposisi warna yang khas.',
            ],
        ];

        $steps = [
            ['Pemilihan bahan', 'Bahan diperiksa dan disiapkan sesuai karakter karya.'],
            ['Pembentukan karya', 'Bentuk dan detail dibuat dengan teknik tangan perajin.'],
            ['Pemeriksaan akhir', 'Karya dirapikan dan diperiksa sebelum siap dikirim.'],
        ];

        foreach ($artisanDetails as $index => $artisanDetail) {
            $artisan = $users[$artisanDetail['email']];
            ArtisanProfile::updateOrCreate(
                ['user_id' => $artisan->id],
                [
                    'bio' => 'Perajin ' . strtolower($artisanDetail['specialty']) . ' dari ' . $artisanDetail['city'] . '.',
                    'maker_story' => $artisanDetail['story'],
                    'city' => $artisanDetail['city'],
                    'province' => $artisanDetail['province'],
                    'specialty' => $artisanDetail['specialty'],
                    'started_year' => 2014 + $index,
                    'verification_status' => 'approved',
                    'verified_at' => now()->subYears(2),
                ]
            );

            foreach (array_slice($productSets, $index * 2, 2) as $productData) {
                $categoryId = $categories[$productData['category']] ?? null;
                if (!$categoryId) {
                    continue;
                }

                $product = Product::updateOrCreate(
                    ['slug' => $productData['slug']],
                    [
                        'artisan_id' => $artisan->id,
                        'category_id' => $categoryId,
                        'name' => $productData['name'],
                        'description' => $productData['description'],
                        'story' => $productData['story'],
                        'material' => $productData['material'],
                        'technique' => $productData['technique'],
                        'origin' => $productData['origin'],
                        'price' => $productData['price'],
                        'stock' => $productData['stock'],
                        'fulfillment_type' => 'ready_stock',
                        'is_customizable' => false,
                        'images' => [$productData['image']],
                        'verification_status' => $productData['status'],
                        'verification_note' => $productData['status'] === Product::STATUS_REVISION ? 'Mohon lengkapi detail bahan dan foto proses pembuatan.' : null,
                        'verified_at' => $productData['status'] === Product::STATUS_APPROVED ? now()->subDays(12 + $index) : null,
                    ]
                );
                $products->push($product);

                foreach ($steps as $stepIndex => [$title, $description]) {
                    ProductProcessStep::updateOrCreate(
                        ['product_id' => $product->id, 'step_number' => $stepIndex + 1],
                        ['title' => $title, 'description' => $description]
                    );
                }

                if ($product->verification_status === Product::STATUS_APPROVED
                    && (!$product->craft_id || !$product->qr_active || !Storage::disk('public')->exists('craft-qr/' . $product->craft_id . '.svg'))) {
                    $qrCode->issue($product);
                }
            }
        }

        $customerEmails = array_column(array_filter($accounts, fn (array $account) => $account['role'] === User::ROLE_CUSTOMER), 'email');
        $customers = array_map(fn (string $email) => $users[$email], $customerEmails);
        $customerServiceEmails = ['nisa.cs@karyalokal.local', 'aris.cs@karyalokal.local', 'rani.cs@karyalokal.local'];
        $customerServices = array_map(fn (string $email) => $users[$email], $customerServiceEmails);
        $approvedProducts = Product::query()->where('verification_status', Product::STATUS_APPROVED)->orderBy('id')->get();

        if ($approvedProducts->isEmpty()) {
            return;
        }

        $orders = [];
        foreach ($customers as $index => $customer) {
            $product = $approvedProducts[$index % $approvedProducts->count()];
            $quantity = $index % 3 === 0 ? 2 : 1;
            $subtotal = (float) $product->price * $quantity;
            $shippingCost = [18000, 30000, 50000][$index % 3];
            $status = ['completed', 'processing', 'awaiting_payment', 'completed', 'processing', 'awaiting_payment', 'completed', 'processing'][$index];
            $paymentStatus = $status === 'awaiting_payment' ? 'pending' : 'paid';
            $orderNumber = 'KL-261001-' . str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT);
            $order = Order::updateOrCreate(
                ['order_number' => $orderNumber],
                [
                    'customer_id' => $customer->id,
                    'recipient_name' => $customer->name,
                    'recipient_phone' => '08123456' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'shipping_address' => 'Jl. Karya Lokal No. ' . ($index + 12),
                    'city' => ['Yogyakarta', 'Bandung', 'Surakarta', 'Denpasar'][$index % 4],
                    'province' => ['Yogyakarta', 'Jawa Barat', 'Jawa Tengah', 'Bali'][$index % 4],
                    'postal_code' => ['55122', '40123', '57111', '80571'][$index % 4],
                    'shipping_method' => ['regular', 'express', 'same_day'][$index % 3],
                    'status' => $status,
                    'payment_method' => ['bank_transfer', 'qris', 'virtual_account'][$index % 3],
                    'payment_status' => $paymentStatus,
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'discount_total' => 0,
                    'total' => $subtotal + $shippingCost,
                    'paid_at' => $paymentStatus === 'paid' ? now()->subDays($index + 1) : null,
                    'received_at' => $status === 'completed' ? now()->subDays($index + 1) : null,
                ]
            );
            $orders[$index] = $order;

            OrderItem::updateOrCreate(
                ['order_id' => $order->id, 'product_id' => $product->id],
                [
                    'artisan_id' => $product->artisan_id,
                    'product_name' => $product->name,
                    'craft_id' => $product->craft_id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'recipient_name' => $customer->name,
                    'sender_name' => $customer->name,
                    'gift_message' => $index % 2 === 0 ? 'Semoga karya ini membawa cerita baik.' : null,
                ]
            );

            Wishlist::firstOrCreate(['user_id' => $customer->id, 'product_id' => $approvedProducts[($index + 2) % $approvedProducts->count()]->id]);
            if ($index < 4) {
                Cart::firstOrCreate(
                    ['user_id' => $customer->id, 'product_id' => $approvedProducts[($index + 1) % $approvedProducts->count()]->id],
                    ['quantity' => 1]
                );
            }

            $ticketStatus = ['resolved', 'open', 'in_progress', 'waiting_customer', 'resolved', 'open', 'in_progress', 'waiting_customer'][$index];
            SupportTicket::updateOrCreate(
                ['ticket_number' => 'CS-261001-' . str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT)],
                [
                    'customer_id' => $customer->id,
                    'order_id' => $order->id,
                    'assigned_to' => $customerServices[$index % count($customerServices)]->id,
                    'category' => ['shipping', 'product', 'payment', 'qr'][$index % 4],
                    'subject' => ['Informasi pengiriman pesanan', 'Pertanyaan tentang bahan karya', 'Konfirmasi pembayaran', 'Bantuan membuka cerita QR'][$index % 4],
                    'description' => 'Pelanggan meminta bantuan untuk pesanan ' . $order->order_number . '.',
                    'status' => $ticketStatus,
                    'resolution' => $ticketStatus === 'resolved' ? 'Informasi telah disampaikan kepada pelanggan.' : null,
                    'closed_at' => $ticketStatus === 'resolved' ? now()->subDays($index + 1) : null,
                ]
            );
        }

        foreach (range(1, 20) as $scanIndex) {
            $product = $approvedProducts[($scanIndex - 1) % $approvedProducts->count()];
            $scan = QrScan::firstOrCreate(
                ['visitor_hash' => hash('sha256', 'karyalokal-visitor-' . $scanIndex)],
                [
                    'product_id' => $product->id,
                    'user_id' => $customers[($scanIndex - 1) % count($customers)]->id,
                    'scanned_at' => now()->subDays($scanIndex),
                    'user_agent' => 'Mozilla/5.0 (compatible; KaryaLokal/1.0)',
                ]
            );
            if ($scan->wasRecentlyCreated) {
                $product->increment('scan_count');
            }
        }
    }
}