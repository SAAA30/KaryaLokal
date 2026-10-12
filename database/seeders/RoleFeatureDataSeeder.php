<?php

namespace Database\Seeders;

use App\Models\ArtisanCustomerChat;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductProcessStep;
use App\Models\QrScan;
use App\Models\Review;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Wishlist;
use App\Services\CraftQrCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class RoleFeatureDataSeeder extends Seeder
{
    public function run(CraftQrCodeService $qrCode): void
    {
        if (!app()->environment('local')) {
            throw new RuntimeException('Operational feature sample records may only be seeded in the local environment.');
        }

        $customers = User::where('role', User::ROLE_CUSTOMER)->orderBy('id')->get();
        $artisans = User::where('role', User::ROLE_ARTISAN)->orderBy('id')->get();
        $customerServices = User::where('role', User::ROLE_CUSTOMER_SERVICE)->orderBy('id')->get();
        $categories = Category::where('is_active', true)->orderBy('id')->get();

        if ($customers->isEmpty() || $artisans->isEmpty() || $customerServices->isEmpty() || $categories->isEmpty()) {
            throw new RuntimeException('Seed the existing roles and categories before operational feature records.');
        }

        $styles = ['Anyaman', 'Tenun', 'Keramik', 'Ukiran', 'Serat Alam', 'Batik', 'Rotan', 'Kayu', 'Tekstil', 'Gerabah'];
        $materials = ['Rotan lokal', 'Kapas tenun', 'Tanah liat', 'Kayu mahoni', 'Serat pandan', 'Benang katun', 'Kayu jati', 'Pewarna alam', 'Bambu', 'Tanah Kasongan'];
        $approvedImages = Product::where('verification_status', Product::STATUS_APPROVED)->value('images') ?: [];
        $images = is_array($approvedImages) && $approvedImages !== []
            ? $approvedImages
            : ['https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=900&q=85'];

        foreach (range(1, 50) as $number) {
            if (Product::count() >= 50) {
                break;
            }

            $artisan = $artisans[($number - 1) % $artisans->count()];
            $category = $categories[($number - 1) % $categories->count()];
            $status = match (true) {
                $number <= 30 => Product::STATUS_APPROVED,
                $number <= 40 => Product::STATUS_PENDING,
                $number <= 45 => Product::STATUS_REVISION,
                $number <= 48 => Product::STATUS_DRAFT,
                default => Product::STATUS_REJECTED,
            };
            $slug = 'karya-lokal-' . str_pad((string) $number, 3, '0', STR_PAD_LEFT);
            $product = Product::firstOrCreate(
                ['slug' => $slug],
                [
                    'artisan_id' => $artisan->id,
                    'category_id' => $category->id,
                    'name' => $styles[($number - 1) % count($styles)] . ' Nusantara ' . str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                    'description' => 'Karya pilihan yang dibuat dengan teknik tangan dan bahan lokal.',
                    'story' => 'Bentuk dan warna karya ini terinspirasi dari tradisi serta alam di sekitar perajin.',
                    'material' => $materials[($number - 1) % count($materials)],
                    'technique' => $styles[($number - 1) % count($styles)] . ' tangan',
                    'origin' => $artisan->artisanProfile?->city ?: 'Yogyakarta',
                    'price' => 75000 + (($number * 17500) % 425000),
                    'stock' => 4 + ($number % 18),
                    'fulfillment_type' => $number % 5 === 0 ? 'pre_order' : 'ready_stock',
                    'production_days' => $number % 5 === 0 ? 7 : null,
                    'is_customizable' => $number % 4 === 0,
                    'images' => [$images[($number - 1) % count($images)]],
                    'verification_status' => $status,
                    'verification_note' => $status === Product::STATUS_REVISION ? 'Lengkapi informasi bahan dan proses pembuatan.' : null,
                    'verified_at' => $status === Product::STATUS_APPROVED ? now()->subDays($number % 25) : null,
                ]
            );

            foreach ([['Pemilihan bahan', 'Bahan dipilih sesuai karakter karya.'], ['Pembentukan karya', 'Perajin membentuk karya dengan teknik tangan.']] as $stepIndex => [$title, $description]) {
                ProductProcessStep::firstOrCreate(
                    ['product_id' => $product->id, 'step_number' => $stepIndex + 1],
                    ['title' => $title, 'description' => $description]
                );
            }

            if ($status === Product::STATUS_APPROVED && !$product->craft_id) {
                $qrCode->issue($product);
            }
        }

        $screenshotProducts = [
            24 => [
                'artisan_email' => 'maya.perajin@karyalokal.local',
                'category' => 'Kerajinan Kayu',
                'name' => 'Ukiran Nusantara 024',
                'material' => 'Kayu mahoni',
                'technique' => 'Ukiran tangan',
                'origin' => 'Ubud',
                'price' => 495000,
                'stock' => 10,
            ],
            25 => [
                'artisan_email' => 'siti@karyalokal.local',
                'category' => 'Anyaman',
                'name' => 'Serat Alam Nusantara 025',
                'material' => 'Serat pandan',
                'technique' => 'Serat Alam tangan',
                'origin' => 'Bantul',
                'price' => 87500,
                'stock' => 11,
            ],
            26 => [
                'artisan_email' => 'nabila.perajin@karyalokal.local',
                'category' => 'Batik',
                'name' => 'Batik Nusantara 026',
                'material' => 'Benang katun',
                'technique' => 'Batik tangan',
                'origin' => 'Bantul',
                'price' => 105000,
                'stock' => 12,
            ],
            27 => [
                'artisan_email' => 'dwiprasetyo.perajin@karyalokal.local',
                'category' => 'Anyaman',
                'name' => 'Rotan Nusantara 027',
                'material' => 'Kayu jati',
                'technique' => 'Rotan tangan',
                'origin' => 'Kasongan',
                'price' => 122500,
                'stock' => 13,
            ],
        ];

        foreach ($screenshotProducts as $number => $data) {
            $artisan = $artisans->firstWhere('email', $data['artisan_email']);
            $categoryId = $categories->firstWhere('name', $data['category'])?->id;
            if (!$artisan || !$categoryId) {
                continue;
            }

            $slug = 'karya-lokal-' . str_pad((string) $number, 3, '0', STR_PAD_LEFT);
            $product = Product::firstOrNew(['slug' => $slug]);
            $needsImage = !$product->exists || empty($product->images);
            $product->fill([
                'artisan_id' => $artisan->id,
                'category_id' => $categoryId,
                'name' => $data['name'],
                'description' => 'Karya pilihan yang dibuat dengan teknik tangan dan bahan lokal.',
                'story' => 'Bentuk dan warna karya ini terinspirasi dari tradisi serta alam di sekitar perajin.',
                'material' => $data['material'],
                'technique' => $data['technique'],
                'origin' => $data['origin'],
                'price' => $data['price'],
                'stock' => $data['stock'],
                'verification_status' => Product::STATUS_APPROVED,
                'verification_note' => null,
                'verified_at' => $product->verified_at ?? now(),
            ]);
            if ($needsImage) {
                $product->images = [$images[($number - 1) % count($images)]];
            } else {
                $storedImages = collect($product->images);
                $localImages = $storedImages->filter(fn ($image) => !\Illuminate\Support\Str::startsWith($image, ['http://', 'https://']))->values();
                $remoteImages = $storedImages->filter(fn ($image) => \Illuminate\Support\Str::startsWith($image, ['http://', 'https://']))->values();
                if ($localImages->isNotEmpty()) {
                    $product->images = $localImages->concat($remoteImages)->all();
                }
            }
            $product->save();

            foreach ([['Pemilihan bahan', 'Bahan dipilih sesuai karakter karya.'], ['Pembentukan karya', 'Perajin membentuk karya dengan teknik tangan.']] as $stepIndex => [$title, $description]) {
                ProductProcessStep::firstOrCreate(
                    ['product_id' => $product->id, 'step_number' => $stepIndex + 1],
                    ['title' => $title, 'description' => $description]
                );
            }

            if (!$product->craft_id || !$product->qr_active || !Storage::disk('public')->exists('craft-qr/' . $product->craft_id . '.svg')) {
                $qrCode->issue($product);
            }
        }

        $newSitiProducts = [
            [
                'slug' => 'keranjang-anyam-pesisir-baru',
                'name' => 'Keranjang Anyam Pesisir',
                'category' => 'Anyaman',
                'price' => 225000,
                'stock' => 12,
                'material' => 'Serat pandan dan rotan',
                'technique' => 'Anyaman silang tangan',
                'origin' => 'Bantul, Yogyakarta',
                'image' => 'Keranjang Anyam.jpg',
                'image_path' => 'products/keranjang-anyam-pesisir-baru.jpg',
                'description' => 'Keranjang serbaguna dengan pola anyaman rapat dan pegangan yang nyaman.',
                'story' => 'Anyaman terinspirasi garis pantai dan dibuat dari serat pilihan yang dirangkai perlahan.',
            ],
            [
                'slug' => 'vas-bumi-motif-pagi',
                'name' => 'Vas Bumi Motif Pagi',
                'category' => 'Keramik',
                'price' => 340000,
                'stock' => 8,
                'material' => 'Tanah liat lokal',
                'technique' => 'Putar dan bakar alami',
                'origin' => 'Ubud, Bali',
                'image' => 'Vas Motif Basic.jpg',
                'image_path' => 'products/vas-bumi-motif-pagi.jpg',
                'description' => 'Vas keramik bernuansa tanah untuk bunga segar dan sudut ruang yang hangat.',
                'story' => 'Bentuk sederhana dan glasir lembut terinspirasi cahaya pagi di bengkel perajin.',
            ],
            [
                'slug' => 'gelang-anyaman-rona',
                'name' => 'Gelang Anyaman Rona',
                'category' => 'Aksesori',
                'price' => 95000,
                'stock' => 20,
                'material' => 'Benang katun dan manik kayu',
                'technique' => 'Anyaman tangan',
                'origin' => 'Bantul, Yogyakarta',
                'image' => 'Gelang Serat Pelangi.jpg',
                'image_path' => 'products/gelang-anyaman-rona.jpg',
                'description' => 'Gelang ringan dengan paduan warna alami dan manik kayu yang halus.',
                'story' => 'Setiap simpul dirangkai dengan tangan dan memadukan warna yang terinspirasi alam.',
            ],
            [
                'slug' => 'tas-anyam-senja',
                'name' => 'Tas Anyam Senja',
                'artisan_email' => 'siti@karyalokal.local',
                'category' => 'Aksesori',
                'price' => 285000,
                'stock' => 9,
                'material' => 'Serat pandan dan benang katun',
                'technique' => 'Anyaman tangan',
                'origin' => 'Bantul, Yogyakarta',
                'image' => 'Tas Anyam bulat.jpg',
                'image_path' => 'products/tas-anyam-senja.jpg',
                'description' => 'Tas anyam ringan dengan ruang utama yang lapang untuk kegiatan harian.',
                'story' => 'Serat pandan dianyam dengan pola sederhana yang terinspirasi warna langit senja.',
            ],
            [
                'slug' => 'vas-kasongan-cahaya-pagi',
                'name' => 'Vas Kasongan Cahaya Pagi',
                'artisan_email' => 'dwiprasetyo.perajin@karyalokal.local',
                'category' => 'Keramik',
                'price' => 275000,
                'stock' => 8,
                'material' => 'Tanah liat Kasongan',
                'technique' => 'Putar dan glasir tangan',
                'origin' => 'Kasongan, Yogyakarta',
                'image' => 'Vas Motif Basic.jpg',
                'image_path' => 'products/vas-kasongan-cahaya-pagi.jpg',
                'description' => 'Vas keramik dengan siluet lembut dan glasir warna tanah.',
                'story' => 'Dibentuk dari tanah liat Kasongan dan diberi glasir yang menangkap nuansa pagi.',
            ],
            [
                'slug' => 'gelang-serat-pelangi',
                'name' => 'Gelang Serat Pelangi',
                'artisan_email' => 'siti@karyalokal.local',
                'category' => 'Aksesori',
                'price' => 110000,
                'stock' => 16,
                'material' => 'Benang katun dan manik kayu',
                'technique' => 'Anyaman tangan',
                'origin' => 'Bantul, Yogyakarta',
                'image' => 'Gelang Serat Pelangi.jpg',
                'image_path' => 'products/gelang-serat-pelangi.jpg',
                'description' => 'Gelang warna-warni dengan tekstur lembut dan manik kayu pilihan.',
                'story' => 'Setiap warna disusun satu per satu agar gelang nyaman dipakai sehari-hari.',
            ],
            [
                'slug' => 'selendang-batik-kembang-sore',
                'name' => 'Selendang Batik Kembang Sore',
                'artisan_email' => 'ayu.perajin@karyalokal.local',
                'category' => 'Batik',
                'price' => 385000,
                'stock' => 7,
                'material' => 'Katun dan pewarna alam',
                'technique' => 'Batik tulis tangan',
                'origin' => 'Solo, Jawa Tengah',
                'image' => 'Selendang Batik Kembang Sore.jpg',
                'image_path' => 'products/selendang-batik-kembang-sore.jpg',
                'description' => 'Selendang batik katun dengan motif bunga dan warna alami.',
                'story' => 'Motif kembang digambar dan diwarna bertahap, terinspirasi taman saat sore hari.',
            ],
            [
                'slug' => 'nampan-ukir-daun-cirebon',
                'name' => 'Nampan Ukir Daun Cirebon',
                'artisan_email' => 'raka.perajin@karyalokal.local',
                'category' => 'Kerajinan Kayu',
                'price' => 325000,
                'stock' => 6,
                'material' => 'Kayu mahoni',
                'technique' => 'Ukir dan amplas tangan',
                'origin' => 'Cirebon, Jawa Barat',
                'image' => 'nampan ukir.jpg',
                'image_path' => 'products/nampan-ukir-daun-cirebon.jpg',
                'description' => 'Nampan kayu dengan ukiran daun pada tepi dan lapisan pelindung alami.',
                'story' => 'Motif daun dipahat satu per satu untuk memberi aksen pada nampan fungsional ini.',
            ],
        ];

        $sitiRahma = $artisans->firstWhere('email', 'siti@karyalokal.local');
        foreach ($newSitiProducts as $data) {
            $artisan = isset($data['artisan_email'])
                ? $artisans->firstWhere('email', $data['artisan_email'])
                : $sitiRahma;
            $categoryId = $categories->firstWhere('name', $data['category'])?->id;
            if (!$artisan || !$categoryId) {
                continue;
            }

            $image = $data['image'];
            $imagePath = $data['image_path'];
            $sourceImagePath = base_path('img/' . $image);
            unset($data['image'], $data['image_path'], $data['category'], $data['artisan_email']);
            if (!File::exists($sourceImagePath)) {
                throw new RuntimeException('Missing product image: ' . $sourceImagePath);
            }
            Storage::disk('public')->put($imagePath, File::get($sourceImagePath));

            $product = Product::firstOrNew(['slug' => $data['slug']]);
            $existingImages = is_array($product->images) ? $product->images : [];
            $product->fill($data + [
                'artisan_id' => $artisan->id,
                'category_id' => $categoryId,
                'fulfillment_type' => 'ready_stock',
                'is_customizable' => false,
                'verification_status' => Product::STATUS_APPROVED,
                'verification_note' => null,
                'verified_at' => $product->verified_at ?? now(),
            ]);
            $product->images = array_values(array_unique(array_merge([$imagePath], $existingImages)));
            $product->save();

            foreach ([['Pemilihan bahan', 'Bahan dipilih dan disiapkan sesuai karakter karya.'], ['Pembentukan karya', 'Karya dibentuk dengan teknik tangan perajin.']] as $stepIndex => [$title, $description]) {
                ProductProcessStep::firstOrCreate(
                    ['product_id' => $product->id, 'step_number' => $stepIndex + 1],
                    ['title' => $title, 'description' => $description]
                );
            }

            if (!$product->craft_id || !$product->qr_active || !Storage::disk('public')->exists('craft-qr/' . $product->craft_id . '.svg')) {
                $qrCode->issue($product);
            }
        }

        $reviewComments = [
            'Bahannya bagus dan terasa kuat saat digunakan.',
            'Hasil kerjanya rapi, detailnya terlihat dibuat dengan teliti.',
            'Warnanya sesuai foto dan cocok untuk dipakai sehari-hari.',
            'Kualitasnya bagus, kemasannya juga aman saat diterima.',
            'Karya yang menarik dengan bahan lokal yang terasa berkualitas.',
        ];
        $reviewRatings = [5, 5, 4, 5, 4];
        $reviewCustomers = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->where('email', 'like', 'customer%@karyalokal.local')
            ->orderBy('email')
            ->get();

        if ($reviewCustomers->isEmpty()) {
            throw new RuntimeException('Seed KaryaLokal customer accounts before product reviews.');
        }

        foreach (Product::query()->orderBy('id')->get() as $productIndex => $product) {
            $publishedReviews = Review::query()
                ->where('product_id', $product->id)
                ->where('is_published', true)
                ->orderBy('id')
                ->get();

            for ($reviewIndex = $publishedReviews->count(); $reviewIndex < 5; $reviewIndex++) {
                $customer = $reviewCustomers[($productIndex * 5 + $reviewIndex) % $reviewCustomers->count()];
                $slot = ($productIndex + $reviewIndex) % count($reviewComments);
                $publishedReviews->push(Review::create([
                    'customer_id' => $customer->id,
                    'product_id' => $product->id,
                    'product_rating' => $reviewRatings[$slot],
                    'body' => $reviewComments[$slot],
                    'is_published' => true,
                ]));
            }

            foreach ($publishedReviews->values() as $reviewIndex => $review) {
                $customer = $reviewCustomers[($productIndex * 5 + $reviewIndex) % $reviewCustomers->count()];
                if ($review->customer_id !== $customer->id) {
                    $review->update(['customer_id' => $customer->id]);
                }
            }
        }

        $approvedProducts = Product::where('verification_status', Product::STATUS_APPROVED)->orderBy('id')->get();
        $orders = collect();

        foreach (range(1, 50) as $number) {
            if (Order::count() >= 50) {
                break;
            }

            $customer = $customers[($number - 1) % $customers->count()];
            $product = $approvedProducts[($number - 1) % $approvedProducts->count()];
            $quantity = 1 + ($number % 3);
            $subtotal = (float) $product->price * $quantity;
            $shipping = [18000, 30000, 50000][$number % 3];
            $orderNumber = 'KL-2026-' . str_pad((string) (880000 + $number), 6, '0', STR_PAD_LEFT);
            $order = Order::firstOrCreate(
                ['order_number' => $orderNumber],
                [
                    'customer_id' => $customer->id,
                    'recipient_name' => $customer->name,
                    'recipient_phone' => '0812345' . str_pad((string) $number, 5, '0', STR_PAD_LEFT),
                    'shipping_address' => 'Jl. Melati No. ' . ($number + 10),
                    'city' => ['Yogyakarta', 'Bandung', 'Surakarta', 'Denpasar'][$number % 4],
                    'province' => ['Yogyakarta', 'Jawa Barat', 'Jawa Tengah', 'Bali'][$number % 4],
                    'postal_code' => ['55122', '40123', '57111', '80571'][$number % 4],
                    'shipping_method' => ['regular', 'express', 'same_day'][$number % 3],
                    'status' => 'awaiting_payment',
                    'payment_method' => ['bank_transfer', 'virtual_account', 'qris'][$number % 3],
                    'payment_status' => 'pending',
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shipping,
                    'discount_total' => 0,
                    'total' => $subtotal + $shipping,
                ]
            );
            $orders->push($order);

            OrderItem::firstOrCreate(
                ['order_id' => $order->id, 'product_id' => $product->id],
                [
                    'artisan_id' => $product->artisan_id,
                    'product_name' => $product->name,
                    'craft_id' => $product->craft_id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'recipient_name' => $customer->name,
                    'sender_name' => $customer->name,
                    'gift_message' => $number % 4 === 0 ? 'Semoga karya ini membawa cerita baik.' : null,
                ]
            );

        }

        $orders = Order::query()->orderBy('id')->limit(50)->get();
        $verificationOrders = Order::query()
            ->whereBetween('order_number', ['KL-2026-880001', 'KL-2026-880050'])
            ->orderBy('order_number')
            ->limit(10)
            ->get();

        foreach ($verificationOrders as $index => $order) {
            $submittedAt = now()->subMinutes(($index + 1) * 7);
            $proofPath = 'payment-proofs/' . $order->order_number . '.svg';
            Storage::disk('local')->put($proofPath, $this->paymentProofSvg($order, $submittedAt));
            $order->update([
                'payment_status' => 'pending_verification',
                'payment_proof_path' => $proofPath,
                'payment_submitted_at' => $submittedAt,
                'payment_verified_at' => null,
                'payment_rejection_reason' => null,
            ]);
        }

        foreach (range(1, 50) as $number) {
            if (Wishlist::count() >= 50 && Cart::count() >= 50) {
                break;
            }

            $customer = $customers[($number - 1) % $customers->count()];
            $product = $approvedProducts[($number - 1) % $approvedProducts->count()];

            if (Wishlist::count() < 50) {
                Wishlist::firstOrCreate(['user_id' => $customer->id, 'product_id' => $product->id]);
            }

            if (Cart::count() < 50) {
                Cart::firstOrCreate(
                    ['user_id' => $customer->id, 'product_id' => $product->id],
                    ['quantity' => 1]
                );
            }
        }

        $ticketTopics = [
            ['shipping', 'Menanyakan status pengiriman'],
            ['product', 'Informasi bahan dan perawatan karya'],
            ['payment', 'Konfirmasi metode pembayaran'],
            ['qr', 'Bantuan membuka cerita QR'],
            ['custom', 'Permintaan karya sesuai ukuran'],
        ];

        foreach (range(1, 50) as $number) {
            if (SupportTicket::count() >= 50) {
                break;
            }

            $order = $orders[$number - 1];
            $customer = $customers->firstWhere('id', $order->customer_id);
            [$category, $subject] = $ticketTopics[($number - 1) % count($ticketTopics)];
            $status = ['open', 'in_progress', 'waiting_customer', 'resolved'][$number % 4];

            SupportTicket::firstOrCreate(
                ['ticket_number' => 'CS-261001-' . str_pad((string) (880000 + $number), 6, '0', STR_PAD_LEFT)],
                [
                    'customer_id' => $customer->id,
                    'order_id' => $order->id,
                    'assigned_to' => $customerServices[($number - 1) % $customerServices->count()]->id,
                    'category' => $category,
                    'subject' => $subject,
                    'description' => 'Customer meminta bantuan terkait pesanan ' . $order->order_number . '.',
                    'status' => $status,
                    'resolution' => $status === 'resolved' ? 'Informasi telah diberikan kepada customer.' : null,
                    'closed_at' => $status === 'resolved' ? now()->subDays($number % 20) : null,
                ]
            );
        }

        $customerTicketSamples = [
            ['shipping', 'Menanyakan status pengiriman', 'Mohon informasi perkiraan waktu pesanan tiba.', 'open'],
            ['product', 'Informasi bahan dan perawatan karya', 'Customer menanyakan cara merawat karya agar tetap awet.', 'in_progress'],
            ['payment', 'Konfirmasi pembayaran pesanan', 'Customer meminta bantuan untuk memastikan pembayaran telah diterima.', 'waiting_customer'],
        ];

        foreach ($customers as $customer) {
            foreach ($customerTicketSamples as $index => [$category, $subject, $description, $status]) {
                $ticketNumber = 'CS-261006-' . str_pad((string) $customer->id, 5, '0', STR_PAD_LEFT)
                    . '-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);

                SupportTicket::firstOrCreate(
                    ['ticket_number' => $ticketNumber],
                    [
                        'customer_id' => $customer->id,
                        'assigned_to' => $customerServices[($customer->id + $index) % $customerServices->count()]->id,
                        'category' => $category,
                        'subject' => $subject,
                        'description' => $description,
                        'status' => $status,
                    ]
                );
            }
        }

        $screenChatCustomer = $customers->firstWhere('email', 'intan.pelanggan@karyalokal.local')
            ?: $customers->first();
        $screenChatProduct = $approvedProducts->firstWhere('name', 'Rotan Nusantara 027')
            ?? $approvedProducts->first();
        if ($screenChatCustomer && $screenChatProduct) {
            $screenChat = ArtisanCustomerChat::firstOrCreate([
                'customer_id' => $screenChatCustomer->id,
                'artisan_id' => $screenChatProduct->artisan_id,
                'product_id' => $screenChatProduct->id,
            ]);
            $screenChat->messages()->firstOrCreate([
                'sender_id' => $screenChatCustomer->id,
                'body' => 'apakah ini cocok untuk ruang tamu?',
            ]);
            $screenChat->messages()->firstOrCreate([
                'sender_id' => $screenChatProduct->artisan_id,
                'body' => 'Cocok, bahan rotan kami dibuat dengan finishing tahan cuaca dan tekstur yang nyaman untuk penggunaan harian.',
            ]);
            $screenChat->update(['last_message_at' => \Illuminate\Support\Carbon::parse('2026-10-01 20:58:00')]);
        }

        foreach (range(1, 50) as $number) {
            if (ArtisanCustomerChat::count() >= 50) {
                break;
            }

            $product = $approvedProducts[($number - 1) % $approvedProducts->count()];
            $customer = $customers[($number - 1) % $customers->count()];
            $chat = ArtisanCustomerChat::firstOrCreate([
                'customer_id' => $customer->id,
                'artisan_id' => $product->artisan_id,
                'product_id' => $product->id,
            ]);
            $chat->messages()->firstOrCreate(
                ['sender_id' => $customer->id, 'body' => 'Halo, saya ingin bertanya tentang ' . $product->name . '.']
            );
            $chat->messages()->firstOrCreate(
                ['sender_id' => $product->artisan_id, 'body' => 'Halo, terima kasih sudah menghubungi. Saya siap menjelaskan detail karya ini.']
            );
            $chat->update(['last_message_at' => $chat->messages()->max('created_at')]);
        }

        $supplementalProducts = $approvedProducts
            ->reject(fn (Product $product) => $product->id === $screenChatProduct?->id)
            ->take(4)
            ->values();
        $supplementalMessages = [
            ['Apakah ukuran karya ini bisa disesuaikan?', 'Ukuran dapat disesuaikan, silakan sampaikan ukuran yang diinginkan.'],
            ['Bagaimana cara merawat karya ini?', 'Cukup bersihkan dengan kain lembut dan simpan di tempat kering.'],
            ['Apakah warna aslinya sama seperti di foto?', 'Warna aslinya serupa, dengan sedikit variasi karena proses buatan tangan.'],
            ['Berapa lama waktu pengirimannya?', 'Pesanan siap dikirim setelah dikemas dengan aman.'],
        ];

        foreach ($customers as $customer) {
            foreach ($supplementalProducts as $index => $product) {
                $chat = ArtisanCustomerChat::firstOrCreate([
                    'customer_id' => $customer->id,
                    'artisan_id' => $product->artisan_id,
                    'product_id' => $product->id,
                ]);
                [$question, $answer] = $supplementalMessages[$index];
                $chat->messages()->firstOrCreate(['sender_id' => $product->artisan_id, 'body' => $answer]);
                $chat->messages()->firstOrCreate(['sender_id' => $customer->id, 'body' => $question]);
                $chat->update([
                    'last_message_at' => \Illuminate\Support\Carbon::parse('2026-09-30 18:00:00')->subDays($index),
                ]);
            }
        }

        if ($screenChatCustomer && $screenChatProduct) {
            $screenChat = ArtisanCustomerChat::firstOrCreate([
                'customer_id' => $screenChatCustomer->id,
                'artisan_id' => $screenChatProduct->artisan_id,
                'product_id' => $screenChatProduct->id,
            ]);
            $screenChat->messages()->firstOrCreate([
                'sender_id' => $screenChatCustomer->id,
                'body' => 'apakah ini cocok untuk kulit sawo mateng',
            ]);
            $screenChat->update(['last_message_at' => \Illuminate\Support\Carbon::parse('2026-10-01 20:58:00')]);
        }

        foreach (range(1, 50) as $number) {
            if (QrScan::count() >= 50) {
                break;
            }

            $product = $approvedProducts[($number - 1) % $approvedProducts->count()];
            QrScan::firstOrCreate(
                ['visitor_hash' => hash('sha256', 'karyalokal-activity-' . $number)],
                [
                    'product_id' => $product->id,
                    'user_id' => $customers[($number - 1) % $customers->count()]->id,
                    'scanned_at' => now()->subDays($number % 30),
                    'user_agent' => 'Mozilla/5.0 (compatible; KaryaLokal/1.0)',
                ]
            );
        }
    }

    private function paymentProofSvg(Order $order, \Illuminate\Support\Carbon $submittedAt): string
    {
        $orderNumber = htmlspecialchars($order->order_number, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $total = number_format((float) $order->total, 0, ',', '.');
        $paymentMethod = htmlspecialchars(ucfirst(str_replace('_', ' ', $order->payment_method)), ENT_QUOTES | ENT_XML1, 'UTF-8');
        $submittedDate = $submittedAt->format('d M Y, H:i');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="720" height="460" viewBox="0 0 720 460">
  <rect width="720" height="460" fill="#f7f5ef"/>
  <rect x="28" y="28" width="664" height="404" rx="8" fill="#fffefa" stroke="#deded4"/>
  <path d="M28 36a8 8 0 0 1 8-8h648a8 8 0 0 1 8 8v82H28z" fill="#245a46"/>
  <text x="58" y="79" fill="#fffefa" font-family="Arial,sans-serif" font-size="24" font-weight="700">BUKTI PEMBAYARAN</text>
  <text x="58" y="158" fill="#d5633a" font-family="Arial,sans-serif" font-size="13" font-weight="700">DETAIL PEMBAYARAN</text>
  <text x="58" y="208" fill="#18352d" font-family="Arial,sans-serif" font-size="16">Nomor pesanan</text>
  <text x="662" y="208" text-anchor="end" fill="#18352d" font-family="Arial,sans-serif" font-size="16" font-weight="700">{$orderNumber}</text>
  <path d="M58 230h604" stroke="#deded4"/>
  <text x="58" y="270" fill="#69756d" font-family="Arial,sans-serif" font-size="15">Metode pembayaran</text>
  <text x="662" y="270" text-anchor="end" fill="#18352d" font-family="Arial,sans-serif" font-size="15">{$paymentMethod}</text>
  <text x="58" y="310" fill="#69756d" font-family="Arial,sans-serif" font-size="15">Jumlah</text>
  <text x="662" y="310" text-anchor="end" fill="#18352d" font-family="Arial,sans-serif" font-size="15">Rp{$total}</text>
  <text x="58" y="350" fill="#69756d" font-family="Arial,sans-serif" font-size="15">Dikirim pada</text>
  <text x="662" y="350" text-anchor="end" fill="#18352d" font-family="Arial,sans-serif" font-size="15">{$submittedDate}</text>
  <text x="58" y="397" fill="#69756d" font-family="Arial,sans-serif" font-size="12">Menunggu pemeriksaan customer service</text>
</svg>
SVG;
    }
}