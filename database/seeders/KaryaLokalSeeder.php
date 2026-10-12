<?php

namespace Database\Seeders;

use App\Models\ArtisanProfile;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\CraftQrCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KaryaLokalSeeder extends Seeder
{
    public function run(CraftQrCodeService $qrCode): void
    {
        $accounts = [
            ['name' => 'Admin KaryaLokal', 'email' => 'admin@karyalokal.local', 'role' => User::ROLE_ADMIN],
            ['name' => 'Kurator KaryaLokal', 'email' => 'kurator@karyalokal.local', 'role' => User::ROLE_CURATOR],
            ['name' => 'Customer Service', 'email' => 'cs@karyalokal.local', 'role' => User::ROLE_CUSTOMER_SERVICE],
            ['name' => 'Siti Rahma', 'email' => 'siti@karyalokal.local', 'role' => User::ROLE_ARTISAN],
            ['name' => 'Pelanggan KaryaLokal', 'email' => 'pembeli@karyalokal.local', 'role' => User::ROLE_CUSTOMER],
        ];
        $users = [];
        foreach ($accounts as $account) {
            $users[$account['role']] = User::updateOrCreate(
                ['email' => $account['email']],
                $account + ['password' => Hash::make('password'), 'status_akun' => 'aktif']
            );
        }

        ArtisanProfile::updateOrCreate(
            ['user_id' => $users[User::ROLE_ARTISAN]->id],
            [
                'bio' => 'Perajin anyaman dan aksesori dari Bantul.',
                'maker_story' => 'Saya belajar merangkai benang dari ibu dan mengembangkan warna yang terinspirasi alam Yogyakarta.',
                'city' => 'Bantul',
                'province' => 'Yogyakarta',
                'specialty' => 'Anyaman dan aksesori',
                'started_year' => 2012,
                'verification_status' => 'approved',
                'verified_at' => now(),
            ]
        );

        $categories = [];
        foreach (['Aksesori', 'Dekorasi', 'Fashion', 'Kerajinan Kayu', 'Keramik', 'Batik', 'Anyaman', 'Suvenir', 'Personal Gift'] as $index => $name) {
            $categories[$name] = Category::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($name)],
                ['name' => $name, 'is_active' => true, 'sort_order' => $index]
            );
        }

        $examples = [
            [
                'name' => 'Gelang Anyaman Senja', 'slug' => 'gelang-anyaman-senja', 'category' => 'Aksesori',
                'price' => 185000, 'stock' => 18, 'material' => 'Benang katun dan manik kayu', 'technique' => 'Anyaman tangan',
                'origin' => 'Bantul, Yogyakarta', 'image' => 'https://images.unsplash.com/photo-1611652022419-a9419f74343d?auto=format&fit=crop&w=900&q=85',
                'description' => 'Gelang ringan dengan warna hangat dan manik kayu pilihan.',
                'story' => 'Terinspirasi warna senja di halaman rumah. Setiap simpul dirangkai perlahan dengan benang katun dan manik kayu dari pengrajin lokal.',
                'steps' => [['Pemilihan bahan', 'Benang dan manik diperiksa satu per satu.'], ['Perangkaian', 'Pola dianyam dan disesuaikan dengan ukuran.'], ['Finishing', 'Simpul dikunci dan karya diperiksa sebelum dikemas.']],
            ],
            [
                'name' => 'Vas Bumi Edisi Pagi', 'slug' => 'vas-bumi-edisi-pagi', 'category' => 'Keramik',
                'price' => 320000, 'stock' => 7, 'material' => 'Tanah liat lokal', 'technique' => 'Putar dan bakar alami',
                'origin' => 'Ubud, Bali', 'image' => 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=900&q=85',
                'description' => 'Vas keramik bertekstur lembut untuk bunga dan ruang kesayangan.',
                'story' => 'Permukaan bertekstur mengingatkan pada tanah basah sesudah hujan pagi di Ubud.',
                'steps' => [['Menyiapkan tanah', 'Tanah liat lokal dibersihkan dan diuleni.'], ['Pembentukan', 'Bentuk vas dibuat dengan teknik putar.'], ['Pembakaran', 'Vas dikeringkan, dibakar, lalu diperiksa.']],
            ],
            [
                'name' => 'Selendang Lurik Purnama', 'slug' => 'selendang-lurik-purnama', 'category' => 'Fashion',
                'price' => 450000, 'stock' => 6, 'material' => 'Kapas pintal tangan', 'technique' => 'Tenun ATBM',
                'origin' => 'Klaten, Jawa Tengah', 'image' => 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=900&q=85',
                'description' => 'Selendang lurik dengan warna alami dan tekstur tenun yang nyaman.',
                'story' => 'Motif garis sederhana merayakan tradisi menenun yang diwariskan lintas generasi.',
                'steps' => [['Pemintalan kapas', 'Benang disiapkan dan ditata sesuai motif.'], ['Penenunan', 'Kain ditenun perlahan menggunakan alat tenun bukan mesin.'], ['Pemeriksaan', 'Ujung kain dirapikan dan kualitas diperiksa.']],
            ],
            [
                'name' => 'Nampan Rotan Hutan', 'slug' => 'nampan-rotan-hutan', 'category' => 'Anyaman',
                'price' => 275000, 'stock' => 10, 'material' => 'Rotan alam', 'technique' => 'Anyaman tangan',
                'origin' => 'Cirebon, Jawa Barat', 'image' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=900&q=85',
                'description' => 'Nampan fungsional dari rotan dengan anyaman rapat dan tepi halus.',
                'story' => 'Dibuat dari rotan pilihan yang dirangkai dengan pola terinspirasi keranjang panen keluarga.',
                'steps' => [['Pemilihan rotan', 'Rotan dibersihkan, direndam, dan disortir.'], ['Pembentukan', 'Anyaman dibentuk mengikuti rangka nampan.'], ['Finishing', 'Tepi diamplas halus dan produk dikeringkan.']],
            ],
        ];

        foreach ($examples as $example) {
            $steps = $example['steps'];
            unset($example['steps']);
            $category = $example['category'];
            $image = $example['image'];
            unset($example['category'], $example['image']);
            $product = Product::updateOrCreate(
                ['slug' => $example['slug']],
                $example + [
                    'artisan_id' => $users[User::ROLE_ARTISAN]->id,
                    'category_id' => $categories[$category]->id,
                    'images' => [$image],
                    'verification_status' => Product::STATUS_APPROVED,
                    'verified_at' => now(),
                    'fulfillment_type' => 'ready_stock',
                    'is_customizable' => false,
                ]
            );
            $product->processSteps()->delete();
            foreach ($steps as $index => [$title, $description]) {
                $product->processSteps()->create([
                    'step_number' => $index + 1,
                    'title' => $title,
                    'description' => $description,
                ]);
            }
            $qrCode->issue($product);
        }

        $approvedProducts = Product::query()->where('verification_status', Product::STATUS_APPROVED)->get();
        if ($approvedProducts->isNotEmpty()) {
            $customerNames = [
                'Alya', 'Rizky', 'Nadia', 'Fajar', 'Salsa', 'Dimas', 'Citra', 'Bagas', 'Rina', 'Hadi',
                'Lina', 'Gilang', 'Putri', 'Arif', 'Sari', 'Kevin', 'Maya', 'Tito', 'Vina', 'Bima',
                'Nisa', 'Dewi', 'Raka', 'Anisa', 'Yuda', 'Zahra', 'Hanif', 'Reni', 'Maulana', 'Kiki',
                'Jihan', 'Seno', 'Ayu', 'Ilham', 'Tiara', 'Bayu', 'Desi', 'Iqbal', 'Intan', 'Omar',
                'Dinda', 'Pratama', 'Sinta', 'Wawan', 'Aisha', 'Rafi', 'Kirana', 'Yusuf', 'Eka', 'Luthfi', 'Mira',
            ];

            foreach ($customerNames as $index => $name) {
                User::updateOrCreate(
                    ['email' => sprintf('customer%02d@karyalokal.local', $index + 1)],
                    [
                        'name' => $name,
                        'role' => User::ROLE_CUSTOMER,
                        'password' => Hash::make('password'),
                        'status_akun' => 'aktif',
                    ]
                );
            }

            $customers = User::query()
                ->where('role', User::ROLE_CUSTOMER)
                ->where('email', 'like', 'customer%@karyalokal.local')
                ->orderBy('email')
                ->get();
            $comments = [
                'Produk ini terlihat bagus dan detailnya rapi sekali. Bahan terasa nyaman dipakai.',
                'Warna yang dipilih sangat cocok untuk ruang tamu. Saya suka bentuknya.',
                'Kualitas bahan terasa premium dan pengemasannya rapi. Sangat puas.',
                'Ukuran pas, hasilnya bagus, dan proses pengiriman juga cepat.',
                'Desainnya menarik dan terasa handmade dengan sentuhan yang hangat.',
                'Bahan kuat dan tahan lama. Saya merasa karya ini worth it untuk dibeli.',
                'Saya suka teksturnya, sangat natural dan cocok untuk dekorasi rumah.',
                'Produk datang sesuai foto dan kualitasnya konsisten. Terima kasih.',
                'Detail finishingnya bagus sekali, tampilan lebih mewah dari yang saya bayangkan.',
                'Sangat cocok untuk hadiah. Produk ini menjadikan ruang lebih hidup.',
                'Bentuknya simpel tapi tetap istimewa. Saya akan pesan lagi nanti.',
                'Materialnya terasa berkualitas dan pengirimannya juga aman sampai tujuan.',
                'Karya ini terlihat dibuat dengan hati. Saya suka sekali tiap goresannya.',
                'Saya merasa puas dengan hasilnya, warna dan bentuknya sangat nyaman.',
                'Ukuran dan warna sesuai deskripsi, sangat cocok dengan kebutuhan saya.',
            ];
            $replies = [
                'Terima kasih atas dukungan dan feedback yang sangat berarti untuk kami.',
                'Senang sekali karya ini diterima dengan baik. Terima kasih sudah memilih produk kami.',
                'Kami sangat bersyukur ulasan Anda membantu kami terus memperbaiki kualitas.',
                'Terima kasih atas apresiasinya. Semoga karya ini memberi manfaat untuk rumah Anda.',
                'Kami menghargai waktu Anda memberi masukan. Semoga karya ini bisa menemani hari-hari Anda.',
            ];

            $ratings = [5, 5, 4, 5, 4];
            foreach ($approvedProducts as $productIndex => $product) {
                $publishedReviews = Review::query()
                    ->where('product_id', $product->id)
                    ->where('is_published', true)
                    ->orderBy('id')
                    ->get();

                for ($reviewIndex = $publishedReviews->count(); $reviewIndex < 5; $reviewIndex++) {
                    $customer = $customers[($productIndex * 5 + $reviewIndex) % $customers->count()];
                    $slot = ($productIndex + $reviewIndex) % count($comments);
                    $reply = $reviewIndex % 2 === 0 ? $replies[$slot % count($replies)] : null;
                    $publishedReviews->push(Review::create([
                        'customer_id' => $customer->id,
                        'product_id' => $product->id,
                        'product_rating' => $ratings[$slot % count($ratings)],
                        'body' => $comments[$slot],
                        'artisan_reply' => $reply,
                        'replied_at' => $reply ? now()->subDays(($slot % 5) + 1) : null,
                        'is_published' => true,
                    ]));
                }

                foreach ($publishedReviews->take(5)->values() as $reviewIndex => $review) {
                    $customer = $customers[($productIndex * 5 + $reviewIndex) % $customers->count()];
                    if ($review->customer_id !== $customer->id) {
                        $review->update(['customer_id' => $customer->id]);
                    }
                }
            }
        }
    }
}
