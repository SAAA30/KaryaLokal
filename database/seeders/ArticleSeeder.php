<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::query()->where('role', User::ROLE_ADMIN)->orderBy('id')->firstOrFail();

        $articles = [
            [
                'memilih-anyaman-untuk-rumah',
                'Memilih Anyaman yang Tepat untuk Ruang di Rumah',
                'Kenali fungsi, ukuran, dan karakter bahan sebelum memilih keranjang atau dekorasi anyaman.',
                'Anyaman dapat menjadi tempat penyimpanan sekaligus aksen yang memberi tekstur pada ruangan. Sebelum memilih, ukur area yang tersedia dan tentukan benda apa yang akan disimpan. Keranjang tinggi cocok untuk selimut, sedangkan wadah dangkal lebih mudah dijangkau untuk benda sehari-hari.\n\nPerhatikan bahan dan kerapatan pola. Rotan terasa kokoh untuk penggunaan rutin, sementara serat pandan memberi tampilan lebih ringan. Perbedaan kecil pada warna dan susunan serat adalah bagian dari proses tangan, bukan cacat produksi.',
            ],
            [
                'merawat-rotan-tetap-awet',
                'Cara Merawat Rotan agar Tetap Awet',
                'Kebiasaan sederhana untuk menjaga warna dan kekuatan karya rotan.',
                'Bersihkan rotan secara berkala menggunakan kain lembut atau sikat berbulu halus. Untuk debu di sela anyaman, gunakan kuas kecil dengan gerakan mengikuti arah serat. Hindari merendam rotan karena kelembapan berlebih dapat melemahkan bahan dan memicu jamur.\n\nLetakkan karya di tempat yang memiliki sirkulasi udara baik dan tidak terkena matahari langsung sepanjang hari. Jika terkena air, segera lap dan keringkan secara alami. Perawatan lembut dan rutin membantu rotan mempertahankan bentuknya lebih lama.',
            ],
            [
                'mengenal-proses-batik-tulis',
                'Mengenal Tahapan dalam Proses Batik Tulis',
                'Dari menggambar pola hingga pelorodan, setiap tahap membentuk karakter kain batik.',
                'Batik tulis dimulai dengan menggambar pola pada kain. Perajin kemudian mengalirkan malam menggunakan canting untuk menutup bagian yang tidak ingin terkena warna. Ketelitian diperlukan agar garis tetap menyambung dan motif terbaca dengan baik.\n\nKain dicelup atau diwarna bertahap, lalu malam dilepas melalui proses pelorodan. Satu kain dapat melewati beberapa putaran tutup dan celup untuk menghasilkan warna berlapis. Karena dikerjakan manual, detail dan nuansa setiap kain memiliki ciri tersendiri.',
            ],
            [
                'membedakan-tenun-dan-batik',
                'Tenun dan Batik: Dua Teknik, Dua Cerita',
                'Keduanya menghasilkan kain bermotif, tetapi proses pembuatannya berbeda.',
                'Pada tenun, motif terbentuk dari susunan benang lungsi dan pakan yang saling menyilang. Perajin mengatur warna dan urutan benang sebelum atau saat kain ditenun. Perubahan kecil pada susunan dapat menghasilkan pola yang berbeda.\n\nPada batik, motif dibuat dengan menghalangi pewarna pada bagian tertentu menggunakan malam. Keduanya membutuhkan keterampilan dan waktu, tetapi meninggalkan tekstur visual yang khas. Memahami teknik membantu kita memilih kain sesuai kebutuhan dan menghargai proses di baliknya.',
            ],
            [
                'keramik-handmade-dan-karakternya',
                'Mengapa Keramik Buatan Tangan Tidak Selalu Seragam?',
                'Bekas putaran, glasir, dan pembakaran membuat setiap keramik punya karakter unik.',
                'Keramik handmade dibentuk dan diselesaikan melalui beberapa tahap, mulai dari menyiapkan tanah hingga pembakaran. Suhu tungku, komposisi glasir, dan posisi benda dapat memengaruhi hasil akhir. Karena itu, warna dapat sedikit berubah dan bentuk tidak selalu identik.\n\nKetidaksamaan kecil tersebut menunjukkan proses pembuatannya, selama fungsi dan kualitas karya tetap sesuai. Untuk memilih keramik, periksa permukaan, kestabilan dasar, dan keterangan penggunaan. Pastikan pula apakah benda aman untuk makanan atau hanya ditujukan sebagai dekorasi.',
            ],
            [
                'memilih-vas-keramik',
                'Panduan Memilih Vas Keramik untuk Bunga',
                'Sesuaikan tinggi leher, berat, dan ukuran vas dengan jenis rangkaian yang ingin dibuat.',
                'Vas dengan leher sempit membantu menahan beberapa tangkai agar tetap tegak. Bentuk berleher lebar memberi ruang untuk rangkaian yang lebih menyebar. Perhatikan tinggi vas dibandingkan meja atau rak agar komposisi terlihat seimbang.\n\nJika vas akan diisi air, tanyakan kepada perajin apakah bagian dalamnya sudah kedap. Pilih alas yang cukup lebar untuk menjaga kestabilan, terutama untuk rangkaian tinggi. Vas tanpa bunga pun dapat menjadi aksen ruang, jadi pilih warna dan tekstur yang menyatu dengan suasana rumah.',
            ],
            [
                'memilih-kayu-untuk-perabot-kecil',
                'Mengenal Kayu untuk Perabot dan Aksesori Kecil',
                'Setiap jenis kayu memiliki pola serat, bobot, dan kebutuhan perawatan berbeda.',
                'Kayu jati dikenal kuat dan memiliki serat yang tegas, sedangkan mahoni sering dipilih untuk warna yang lebih hangat. Kayu pinus relatif ringan dengan pola mata kayu yang terlihat. Pemilihan bahan bergantung pada fungsi, ukuran, serta lingkungan penggunaan.\n\nTanyakan jenis finishing yang digunakan dan cara membersihkannya. Hindari menempatkan benda kayu di area lembap atau terkena panas langsung dalam waktu lama. Memahami asal bahan juga membantu pembeli mengetahui karakter alami yang mungkin muncul pada tiap potongan.',
            ],
            [
                'finishing-alami-pada-karya-kayu',
                'Finishing Alami dan Tampilan Karya Kayu',
                'Lapisan akhir melindungi permukaan sekaligus menentukan kesan yang terlihat dan terasa.',
                'Finishing minyak biasanya mempertahankan rasa alami serat kayu, sementara lapisan pelindung lain dapat memberi kilap dan perlindungan berbeda. Tidak ada satu jenis finishing yang cocok untuk semua benda; pilihan mengikuti fungsi dan paparan penggunaan.\n\nSebelum membeli, tanyakan bahan pelapis dan petunjuk perawatannya. Gunakan kain lembut untuk membersihkan permukaan, dan hindari cairan pembersih keras yang belum tentu sesuai. Perawatan yang mengikuti rekomendasi perajin membantu menjaga lapisan akhir tetap baik.',
            ],
            [
                'pewarna-alam-dan-prosesnya',
                'Pewarna Alam: Warna dari Tumbuhan dan Lingkungan',
                'Daun, kulit kayu, dan bahan alam lain dapat memberi palet warna dengan nuansa khas.',
                'Pewarna alam berasal dari bahan seperti daun, akar, kulit kayu, atau bagian tumbuhan lainnya. Perajin menyiapkan bahan, mengekstrak warna, lalu menguji hasilnya pada serat yang akan digunakan. Jenis bahan, waktu, dan kondisi air dapat memengaruhi intensitas warna.\n\nHasilnya sering memiliki gradasi lembut dan tidak selalu sama persis di setiap proses. Saat merawat kain berwarna alam, ikuti petunjuk pencucian dari perajin. Menjemur di tempat teduh dan menggunakan sabun lembut dapat membantu menjaga warnanya.',
            ],
            [
                'dari-bahan-lokal-menjadi-karya',
                'Dari Bahan Lokal Menjadi Karya yang Siap Digunakan',
                'Kualitas karya dimulai dari pemilihan bahan dan keputusan kecil di meja kerja perajin.',
                'Sebelum membentuk sebuah karya, perajin menilai bahan berdasarkan kekuatan, tekstur, dan kegunaannya. Serat yang cocok untuk keranjang belum tentu sesuai untuk aksesori. Kayu juga dipilih dengan mempertimbangkan arah serat dan ukuran benda yang akan dibuat.\n\nTahap persiapan sering tidak terlihat oleh pembeli, tetapi menentukan kenyamanan dan daya tahan produk. Dengan membaca deskripsi bahan dan proses, pembeli dapat memahami alasan di balik bentuk maupun harga sebuah karya.',
            ],
            [
                'cerita-di-balik-motif-daerah',
                'Membaca Cerita di Balik Motif Daerah',
                'Motif dapat menyimpan ingatan tempat, kebiasaan, dan cara pandang masyarakat.',
                'Motif kerajinan sering tumbuh dari lingkungan sekitar: bentuk tanaman, garis pantai, kegiatan harian, atau cerita yang diwariskan. Maknanya dapat berbeda antarwilayah dan keluarga perajin. Karena itu, penjelasan langsung dari pembuat karya menjadi sumber yang penting.\n\nSaat memilih produk bermotif, tanyakan nama motif, asal inspirasi, dan bagaimana motif itu diterapkan. Dengan begitu, kita tidak hanya membawa pulang benda, tetapi juga memahami konteks budaya yang menyertainya.',
            ],
            [
                'kemasan-aman-untuk-karya-rapuh',
                'Bagaimana Karya Rapuh Dipersiapkan untuk Dikirim?',
                'Keramik dan dekorasi perlu perlindungan berlapis agar perjalanan lebih aman.',
                'Perajin biasanya membungkus benda rapuh dengan bahan pelindung, mengisi ruang kosong, lalu memilih kotak yang ukurannya sesuai. Bagian yang menonjol seperti gagang atau ujung vas perlu perlindungan tambahan. Kemasan yang baik menjaga benda tetap tidak bergerak selama perjalanan.\n\nKetika paket tiba, buka dengan hati-hati dan simpan bahan pembungkus jika diperlukan untuk pemindahan berikutnya. Jika barang mengalami kerusakan, dokumentasikan kondisi paket dan produk sesegera mungkin lalu hubungi layanan pelanggan dengan menyertakan nomor pesanan.',
            ],
            [
                'mengapa-produk-handmade-berbeda',
                'Mengapa Produk Handmade Memiliki Detail yang Berbeda?',
                'Perbedaan kecil adalah bagian dari proses produksi yang melibatkan tangan manusia.',
                'Pada produk handmade, perajin mengukur, membentuk, menjahit, atau menyelesaikan benda secara manual. Perbedaan tekanan tangan, bahan alami, serta proses pengeringan dapat memengaruhi hasil akhir. Karena itu, dua produk dari model yang sama mungkin memiliki detail yang sedikit berbeda.\n\nPerbedaan tersebut tetap perlu berada dalam batas kualitas yang dijelaskan. Periksa ukuran, fungsi, dan foto produk sebelum membeli. Jika ada kebutuhan khusus, gunakan fitur pesan kepada perajin untuk memastikan detail pesanan.',
            ],
            [
                'memilih-hadiah-kerajinan-lokal',
                'Memilih Hadiah Kerajinan Lokal yang Berkesan',
                'Hadiah terasa lebih personal ketika fungsi, cerita, dan kebutuhan penerimanya dipertimbangkan.',
                'Mulailah dari kebiasaan dan kebutuhan penerima. Aksesori kecil cocok untuk hadiah ringan, sedangkan benda dekorasi dapat dipilih jika kita memahami gaya ruangnya. Periksa ukuran, bahan, pilihan warna, dan perkiraan waktu produksi sebelum memesan.\n\nBanyak perajin menyediakan opsi pesan hadiah atau personalisasi. Sampaikan permintaan dengan jelas dan beri waktu yang cukup untuk proses pembuatan. Sertakan kartu ucapan bila tersedia agar cerita dari pembuat dan pemberi hadiah dapat tersampaikan bersama.',
            ],
            [
                'merawat-kain-tenun',
                'Panduan Merawat Kain Tenun',
                'Penyimpanan dan pencucian yang tepat membantu menjaga serat serta warna kain.',
                'Sebelum mencuci kain tenun, periksa petunjuk dari perajin karena jenis serat dan pewarna menentukan cara perawatannya. Gunakan air bersuhu normal dan gerakan lembut bila pencucian tangan dianjurkan. Hindari memeras kain terlalu kuat karena dapat mengubah bentuk serat.\n\nKeringkan di tempat teduh dengan posisi yang tidak menarik kain. Untuk penyimpanan lama, pastikan kain benar-benar kering dan lipat dengan rapi. Jika kain memiliki detail rapuh, konsultasikan perawatan profesional sebelum membersihkannya.',
            ],
            [
                'menata-ruang-dengan-kerajinan',
                'Menata Ruang dengan Kerajinan tanpa Terasa Penuh',
                'Pilih beberapa benda yang punya fungsi atau cerita agar dekorasi tetap terasa seimbang.',
                'Mulailah dengan satu benda utama, misalnya vas atau keranjang, lalu pilih elemen pendamping dengan warna dan tekstur yang saling melengkapi. Menyisakan ruang kosong membuat tiap karya lebih mudah terlihat dan digunakan.\n\nPertimbangkan juga kebutuhan sehari-hari. Keranjang di dekat pintu dapat menyimpan barang kecil, sementara tekstil dapat memberi aksen pada kursi. Menata karya berdasarkan fungsi membantu ruangan tetap rapi sekaligus memberi ruang bagi cerita pembuatnya.',
            ],
            [
                'mendukung-perajin-dengan-pembelian-sadar',
                'Pembelian Sadar untuk Mendukung Perajin Lokal',
                'Memilih karya dengan memahami bahan dan proses memberi dukungan yang lebih bermakna.',
                'Pembelian sadar dimulai dengan membaca informasi produk, memahami waktu produksi, dan memilih barang yang benar-benar akan digunakan. Harga karya mencakup bahan, keterampilan, waktu, serta proses penyelesaian. Bertanya dengan sopan membantu pembeli dan perajin memastikan kecocokan sebelum transaksi.\n\nSetelah menerima produk, ulasan yang jujur dan spesifik dapat membantu calon pembeli lain. Ceritakan fungsi, kualitas, dan pengalaman penggunaan tanpa membandingkan karya handmade dengan produk pabrik secara tidak sepadan.',
            ],
            [
                'proses-pesanan-custom',
                'Apa yang Perlu Disiapkan untuk Pesanan Custom?',
                'Ukuran, warna, bahan, dan tenggat waktu sebaiknya dibicarakan sejak awal.',
                'Pesanan custom biasanya membutuhkan diskusi tambahan. Siapkan ukuran yang jelas, contoh warna atau referensi bentuk, jumlah produk, dan tanggal barang dibutuhkan. Jelaskan bagian yang wajib dipenuhi dan bagian yang masih bisa disesuaikan oleh perajin.\n\nPerajin akan mengonfirmasi bahan, estimasi waktu, harga, dan batas perubahan desain. Kesepakatan tertulis membantu kedua pihak menghindari salah pengertian. Ingat bahwa produk custom tetap dibuat dengan teknik tangan, sehingga hasil akhir dapat memiliki variasi kecil.',
            ],
            [
                'membaca-deskripsi-produk-handmade',
                'Cara Membaca Deskripsi Produk Handmade',
                'Informasi bahan, ukuran, teknik, asal, dan perawatan membantu memastikan produk sesuai kebutuhan.',
                'Mulailah dari ukuran dan bahan karena keduanya menentukan fungsi dan kecocokan produk. Deskripsi teknik memberi gambaran bagaimana benda dibuat, sedangkan asal produk menghubungkan karya dengan tempat dan pembuatnya. Periksa pula jumlah stok serta opsi pesanan khusus.\n\nJika informasi yang dibutuhkan belum tersedia, hubungi perajin sebelum membeli. Pertanyaan tentang ukuran aktual, variasi warna, atau cara merawat lebih mudah dijawab sebelum transaksi dibanding setelah produk diterima.',
            ],
            [
                'qr-digital-craft-story',
                'Mengenal Cerita Karya melalui QR Digital',
                'QR pada produk dapat menghubungkan pembeli dengan informasi tentang pembuat dan prosesnya.',
                'Sebagian karya dilengkapi QR yang membuka cerita digital. Halaman tersebut dapat memuat identitas karya, daerah asal, bahan, dan langkah pembuatannya. Informasi ini membantu pembeli mengenal konteks produk setelah membawanya pulang.\n\nSimpan atau pindai QR dengan kamera ponsel, lalu baca cerita yang tersedia. Jika QR tidak dapat dibuka, pastikan koneksi internet aktif dan coba pindai ulang dengan pencahayaan cukup. QR menjadi pelengkap, sedangkan komunikasi langsung dengan perajin tetap menjadi cara terbaik untuk bertanya.',
            ],
            [
                'merawat-kerajinan-sebagai-bagian-harian',
                'Menjadikan Kerajinan Bagian dari Keseharian',
                'Karya yang digunakan dan dirawat dengan baik akan terus membawa manfaat setelah transaksi selesai.',
                'Kerajinan tidak harus disimpan sebagai pajangan. Gunakan sesuai fungsinya, ikuti panduan perawatan, dan letakkan di tempat yang mendukung daya tahannya. Wadah, kain, dan aksesori yang dipakai secara rutin dapat mengingatkan kita pada keterampilan di balik pembuatannya.\n\nJika suatu saat produk membutuhkan perbaikan, tanyakan apakah perajin menyediakan layanan perawatan atau reparasi. Merawat dan memperbaiki barang dapat memperpanjang masa guna sekaligus menjaga hubungan antara pembeli dan pembuat karya.',
            ],
        ];

        foreach ($articles as $index => [$slug, $title, $excerpt, $body]) {
            Article::firstOrCreate(
                ['slug' => $slug],
                [
                    'author_id' => $author->id,
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'body' => $body,
                    'is_published' => true,
                    'published_at' => now()->subDays(count($articles) - $index),
                ]
            );
        }

        $this->command?->info('20 artikel KaryaLokal siap dibaca pembeli.');
    }
}