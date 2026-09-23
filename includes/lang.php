<?php
// FLF — bilingual strings (EN / ID)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$LANG = [
'en' => [
    // ---- Common ----
    'common.cancel'      => 'Cancel',
    'common.back'        => 'Back',

    // ---- Navbar ----
    'nav.home'           => 'Home',
    'nav.browse'         => 'Find Lancers',
    'nav.profile'        => 'My Profile',
    'nav.login'          => 'Log in',
    'nav.signup'         => 'Sign up',
    'nav.logout'         => 'Log out',
    'nav.lang_switch'    => 'Ganti ke Indonesia',

    // ---- Hero ----
    'hero.eyebrow'       => 'Any skill · Any freelancer',
    'hero.title'         => 'Find the right <span class="hl">Lancer</span> for whatever you need done.',
    'hero.lede'          => 'FLF — FreeLanceFinder connects you with freelancers across the country: developers, designers, writers, builders, photographers, musicians, and everything in between.',
    'hero.cta_browse'    => 'Browse Lancers',
    'hero.cta_join'      => 'Join as a Lancer',
    'hero.stat1_t'       => 'Every',
    'hero.stat1_s'       => 'category',
    'hero.stat2_t'       => 'Local',
    'hero.stat2_s'       => 'talent',
    'hero.stat3_t'       => 'Free',
    'hero.stat3_s'       => 'to join',

    // ---- Testimonials ----
    'reviews.heading'    => 'What people say about FLF',
    'reviews.sub'        => 'Real words from Lancers and the customers who hired them.',

    // ---- CTA ----
    'cta.heading'        => 'Ready to meet them?',
    'cta.text'           => 'Every kind of freelancer is already here. Take a look and find yours.',
    'cta.button'         => 'Check them out',

    // ---- Browse ----
    'browse.title'       => 'Find your Lancer',
    'browse.sub'         => "Developers, designers, writers, builders, photographers — whoever you need, they're here.",
    'browse.search_ph'   => 'Search for Lancers!',
    'browse.search_aria' => 'Search for Lancers',
    'browse.search_btn'  => 'Search',
    'browse.results_for' => ':count result(s) for “:q”',
    'browse.clear'       => 'clear',
    'browse.showing_all' => 'Showing all :count Lancers',
    'browse.empty_t'     => 'No Lancers found.',
    'browse.empty_s'     => 'Try a different keyword or :link.',
    'browse.everyone'    => 'browse everyone',
    'browse.rating'      => 'rating',

        'browse.filter_label' => 'Sort by',
    'browse.filter_all'   => 'All categories',
    'browse.filter_it'    => 'IT',
    'browse.filter_svc'   => 'Service',
    'browse.filter_other' => 'Others',

    'register.category'      => 'Category',
    'register.category_hint' => 'Pick the type of work you do.',
    'register.err_category'  => 'Please pick a category.',

    'profile.category'      => 'Category',
    'profile.cat_saved'     => 'Category updated.',
    'profile.cat_error'     => "Couldn't save the category. Please try again.",

    // ---- Login ----
    'login.title'        => 'Welcome back',
    'login.sub'          => 'Log in to your FLF account.',
    'login.email'        => 'Email',
    'login.password'     => 'Password',
    'login.submit'       => 'Log in',
    'login.no_account'   => 'New to FLF?',
    'login.create'       => 'Create an account',
    'login.err_email'    => 'Please enter a valid email.',
    'login.err_password' => 'Please enter your password.',
    'login.err_invalid'  => 'Incorrect email or password.',

    // ---- Register ----
    'register.title'        => 'Create your account',
    'register.sub'          => 'Join FLF as a customer looking for talent, or as a Lancer offering it.',
    'register.role_legend'  => 'I want to…',
    'register.role_cust'    => 'Hire a Lancer',
    'register.role_cust_s'  => "I'm a customer",
    'register.role_lanc'    => 'Offer my skills',
    'register.role_lanc_s'  => "I'm a Lancer",
    'register.name'         => 'Full name',
    'register.email'        => 'Email',
    'register.password'     => 'Password',
    'register.password_c'   => 'Confirm password',
    'register.title_lbl'    => 'Title',
    'register.title_ph'     => 'e.g. UI Designer',
    'register.skill'        => 'Main skill / category',
    'register.skill_ph'     => 'e.g. Design',
    'register.location'     => 'Location',
    'register.location_ph'  => 'e.g. Jakarta',
    'register.bio'          => 'Short bio',
    'register.bio_ph'       => 'Tell customers what you do and who you help.',
    'register.submit'       => 'Create account',
    'register.have'         => 'Already have an account?',
    'register.login_link'   => 'Log in',
    'register.err_name'     => 'Please enter your name.',
    'register.err_email'    => 'Please enter a valid email.',
    'register.err_password' => 'Password must be at least 6 characters.',
    'register.err_pw_match' => 'Passwords do not match.',
    'register.err_title'    => 'Please add a short title (e.g. "UI Designer").',
    'register.err_skill'    => 'Please add your main skill or category.',
    'register.err_email_used' => 'That email is already registered.',
    'register.err_general'  => 'Something went wrong. Please try again.',

    // ---- Profile ----
    'profile.customer'      => 'Customer on FLF',
    'profile.joined'        => 'Joined',
    'profile.about'         => 'About',
    'profile.no_bio'        => 'No bio yet.',
    'profile.customer_no'   => "This customer hasn't shared public details.",
    'profile.details'       => 'Details',
    'profile.role'          => 'Role',
    'profile.rating'        => 'Rating',
    'profile.rate'          => 'Rate',
    'profile.skill'         => 'Skill',
    'profile.location'      => 'Location',
    'profile.member_since'  => 'Member since',
    'profile.hire_h'        => 'Hire this Lancer',
    'profile.hire_text'     => "Start a secure sandbox payment via Tripay. The amount is pre-filled with :name's rate — you can change it before paying.",
    'profile.hire_amount'   => 'Amount',
    'profile.pay_btn'       => 'Pay this Lancer →',
    'profile.quick'         => 'Quick actions',
    'profile.browse_btn'    => 'Browse Lancers',
    'profile.not_found'     => 'Profile not found.',
    'profile.back'          => 'Back to Lancers',

    // ---- Payment — form ----
    'pay.step_profile'   => 'Profile',
    'pay.step_payment'   => 'Payment',
    'pay.step_done'      => 'Done',
    'pay.step_now'       => 'Pay now',
    'pay.title'          => 'Pay this Lancer',
    'pay.sub'            => 'Secure sandbox payment for :name.',
    'pay.amount_due'     => 'Amount due',
    'pay.sum_lancer'     => 'Lancer',
    'pay.sum_skill'      => 'Skill',
    'pay.sum_location'   => 'Location',
    'pay.sum_rate'       => 'Base rate',
    'pay.method'         => 'Payment method',
    'pay.inactive'       => '(inactive)',
    'pay.amount_lbl'     => 'Amount (IDR)',
    'pay.amount_hint'    => "Pre-filled from the Lancer's rate. Change it if you agreed on a different price.",
    'pay.donate_t'       => 'Add a donation to FLF',
    'pay.donate_s'       => 'Support platform development. Optional, you can skip it.',
    'pay.donate_lbl'     => 'Donation amount (IDR)',
    'pay.donate_hint'    => 'e.g. 2000, 5000, 10000. You can also leave it empty.',
    'pay.total'          => 'Total to pay',
    'pay.your_name'      => 'Your name',
    'pay.guest'          => 'Guest',
    'pay.phone'          => 'Phone',
    'pay.your_email'     => 'Your email',
    'pay.continue'       => 'Continue to payment →',
    'pay.err_channels'   => 'Could not load payment channels. Check your Tripay API key.',
    'pay.err_curl'       => 'cURL error: :msg',

    // ---- Payment — instructions ----
    'pi.title'           => 'Payment instructions',
    'pi.paying'          => 'Paying :name — reference :ref',
    'pi.err_curl_t'      => "Couldn't reach Tripay",
    'pi.err_fail_t'      => 'Payment failed',
    'pi.err_unknown'     => 'Unknown error from Tripay.',
    'pi.sum_status'      => 'Status',
    'pi.sum_ref'         => 'Reference',
    'pi.sum_mref'        => 'Merchant ref',
    'pi.sum_amount'      => 'Amount',
    'pi.sum_donation'    => 'Donation',
    'pi.sum_fee'         => 'Fee',
    'pi.sum_expires'     => 'Expires',
    'pi.incl_donation'   => 'including donation Rp :amt',
    'pi.code_lbl'        => 'Payment code / VA',
    'pi.copy'            => 'Copy code',
    'pi.copied'          => '✓ Copied',
    'pi.qr_lbl'          => 'Scan to pay',
    'pi.qr_alt'          => 'Payment QR code',
    'pi.how_lbl'         => 'How to pay',
    'pi.back_profile'    => '← Back to profile',
    'pi.open_checkout'   => 'Open Tripay Checkout →',

    // ---- Payment — return ----
    'pr.thanks_t'        => "Thanks! We're checking your payment.",
    'pr.thanks_s'        => 'You came back from Tripay. Final status is confirmed via webhook or dashboard.',
    'pr.back_browse'     => 'Back to Lancers',
    'pr.open_profile'    => 'Go to profile',

    // ---- Payment — test ----
    'pt.title'           => 'Sandbox test payment',
    'pt.sub'             => 'Use this page to test Tripay without going through a Lancer profile.',
    'pt.method'          => 'Payment method',
    'pt.amount'          => 'Amount (IDR)',
    'pt.cust_name'       => 'Customer name',
    'pt.cust_default'    => 'Test Customer',
    'pt.phone'           => 'Phone',
    'pt.email'           => 'Email',
    'pt.submit'          => 'Create sandbox transaction →',
    'pt.back_site'       => '← Back to site',

        'profile.rate_label'  => 'Starting rate',
    'profile.rate_save'   => 'Save rate',
    'profile.rate_hint'   => 'This is the price customers will see when they hire you. You can change it any time.',
    'profile.rate_saved'  => 'Rate updated.',
    'profile.rate_error'  => "Couldn't save the rate. Please try again.",

    // ---- Footer ----
    'footer.copy'        => '© :year FLF — FreeLanceFinder. Built for freelancers everywhere.',
],

'id' => [
    // ---- Common ----
    'common.cancel'      => 'Batal',
    'common.back'        => 'Kembali',

    // ---- Navbar ----
    'nav.home'           => 'Beranda',
    'nav.browse'         => 'Cari Lancer',
    'nav.profile'        => 'Profil Saya',
    'nav.login'          => 'Masuk',
    'nav.signup'         => 'Daftar',
    'nav.logout'         => 'Keluar',
    'nav.lang_switch'    => 'Switch to English',

    // ---- Hero ----
    'hero.eyebrow'       => 'Semua keahlian · Semua freelancer',
    'hero.title'         => 'Temukan <span class="hl">Lancer</span> yang tepat untuk semua kebutuhan Anda.',
    'hero.lede'          => 'FLF — FreeLanceFinder menghubungkan Anda dengan freelancer di seluruh negeri: developer, desainer, penulis, tukang, fotografer, musisi, dan masih banyak lagi.',
    'hero.cta_browse'    => 'Jelajahi Lancer',
    'hero.cta_join'      => 'Gabung sebagai Lancer',
    'hero.stat1_t'       => 'Semua',
    'hero.stat1_s'       => 'kategori',
    'hero.stat2_t'       => 'Lokal',
    'hero.stat2_s'       => 'talenta',
    'hero.stat3_t'       => 'Gratis',
    'hero.stat3_s'       => 'untuk gabung',

    // ---- Testimonials ----
    'reviews.heading'    => 'Apa kata mereka tentang FLF',
    'reviews.sub'        => 'Kata-kata asli dari Lancer dan pelanggan yang menyewa mereka.',

    // ---- CTA ----
    'cta.heading'        => 'Siap bertemu mereka?',
    'cta.text'           => 'Semua jenis freelancer sudah ada di sini. Lihat dan temukan milik Anda.',
    'cta.button'         => 'Lihat mereka',

    // ---- Browse ----
    'browse.title'       => 'Temukan Lancer Anda',
    'browse.sub'         => 'Developer, desainer, penulis, tukang, fotografer — siapa pun yang Anda butuhkan, semua ada di sini.',
    'browse.search_ph'   => 'Cari Lancer!',
    'browse.search_aria' => 'Cari Lancer',
    'browse.search_btn'  => 'Cari',
    'browse.results_for' => ':count hasil untuk “:q”',
    'browse.clear'       => 'hapus',
    'browse.showing_all' => 'Menampilkan semua :count Lancer',
    'browse.empty_t'     => 'Tidak ada Lancer ditemukan.',
    'browse.empty_s'     => 'Coba kata kunci lain atau :link.',
    'browse.everyone'    => 'jelajahi semua',
    'browse.rating'      => 'peringkat',

        'browse.filter_label' => 'Urutkan berdasarkan',
    'browse.filter_all'   => 'Semua kategori',
    'browse.filter_it'    => 'IT',
    'browse.filter_svc'   => 'Layanan',
    'browse.filter_other' => 'Lainnya',

    'register.category'      => 'Kategori',
    'register.category_hint' => 'Pilih jenis pekerjaan Anda.',
    'register.err_category'  => 'Silakan pilih kategori.',

    'profile.category'      => 'Kategori',
    'profile.cat_saved'     => 'Kategori diperbarui.',
    'profile.cat_error'     => 'Gagal menyimpan kategori. Silakan coba lagi.',

    // ---- Login ----
    'login.title'        => 'Selamat datang kembali',
    'login.sub'          => 'Masuk ke akun FLF Anda.',
    'login.email'        => 'Email',
    'login.password'     => 'Kata sandi',
    'login.submit'       => 'Masuk',
    'login.no_account'   => 'Baru di FLF?',
    'login.create'       => 'Buat akun',
    'login.err_email'    => 'Masukkan email yang valid.',
    'login.err_password' => 'Masukkan kata sandi Anda.',
    'login.err_invalid'  => 'Email atau kata sandi salah.',

    // ---- Register ----
    'register.title'        => 'Buat akun Anda',
    'register.sub'          => 'Gabung FLF sebagai pelanggan yang mencari talenta, atau sebagai Lancer yang menawarkan jasa.',
    'register.role_legend'  => 'Saya ingin…',
    'register.role_cust'    => 'Merekrut Lancer',
    'register.role_cust_s'  => 'Saya pelanggan',
    'register.role_lanc'    => 'Menawarkan keahlian saya',
    'register.role_lanc_s'  => 'Saya Lancer',
    'register.name'         => 'Nama lengkap',
    'register.email'        => 'Email',
    'register.password'     => 'Kata sandi',
    'register.password_c'   => 'Konfirmasi kata sandi',
    'register.title_lbl'    => 'Gelar',
    'register.title_ph'     => 'mis. UI Designer',
    'register.skill'        => 'Keahlian utama / kategori',
    'register.skill_ph'     => 'mis. Desain',
    'register.location'     => 'Lokasi',
    'register.location_ph'  => 'mis. Jakarta',
    'register.bio'          => 'Bio singkat',
    'register.bio_ph'       => 'Beri tahu pelanggan apa yang Anda lakukan dan siapa yang Anda bantu.',
    'register.submit'       => 'Buat akun',
    'register.have'         => 'Sudah punya akun?',
    'register.login_link'   => 'Masuk',
    'register.err_name'     => 'Masukkan nama Anda.',
    'register.err_email'    => 'Masukkan email yang valid.',
    'register.err_password' => 'Kata sandi minimal 6 karakter.',
    'register.err_pw_match' => 'Kata sandi tidak cocok.',
    'register.err_title'    => 'Tambahkan gelar singkat (mis. "UI Designer").',
    'register.err_skill'    => 'Tambahkan keahlian atau kategori utama Anda.',
    'register.err_email_used' => 'Email tersebut sudah terdaftar.',
    'register.err_general'  => 'Terjadi kesalahan. Silakan coba lagi.',

    // ---- Profile ----
    'profile.customer'      => 'Pelanggan FLF',
    'profile.joined'        => 'Bergabung',
    'profile.about'         => 'Tentang',
    'profile.no_bio'        => 'Belum ada bio.',
    'profile.customer_no'   => 'Pelanggan ini belum membagikan detail publik.',
    'profile.details'       => 'Detail',
    'profile.role'          => 'Peran',
    'profile.rating'        => 'Peringkat',
    'profile.rate'          => 'Tarif',
    'profile.skill'         => 'Keahlian',
    'profile.location'      => 'Lokasi',
    'profile.member_since'  => 'Anggota sejak',
    'profile.hire_h'        => 'Rekrut Lancer ini',
    'profile.hire_text'     => 'Mulai pembayaran sandbox aman via Tripay. Jumlah sudah diisi otomatis dari tarif :name — Anda bisa mengubahnya sebelum membayar.',
    'profile.hire_amount'   => 'Jumlah',
    'profile.pay_btn'       => 'Bayar Lancer ini →',
    'profile.quick'         => 'Tindakan cepat',
    'profile.browse_btn'    => 'Jelajahi Lancer',
    'profile.not_found'     => 'Profil tidak ditemukan.',
    'profile.back'          => 'Kembali ke Lancer',

    // ---- Payment — form ----
    'pay.step_profile'   => 'Profil',
    'pay.step_payment'   => 'Pembayaran',
    'pay.step_done'      => 'Selesai',
    'pay.step_now'       => 'Bayar sekarang',
    'pay.title'          => 'Bayar Lancer ini',
    'pay.sub'            => 'Pembayaran sandbox aman untuk :name.',
    'pay.amount_due'     => 'Total tagihan',
    'pay.sum_lancer'     => 'Lancer',
    'pay.sum_skill'      => 'Keahlian',
    'pay.sum_location'   => 'Lokasi',
    'pay.sum_rate'       => 'Tarif dasar',
    'pay.method'         => 'Metode pembayaran',
    'pay.inactive'       => '(tidak aktif)',
    'pay.amount_lbl'     => 'Jumlah (IDR)',
    'pay.amount_hint'    => 'Sudah diisi otomatis dari tarif Lancer. Ubah jika Anda menyepakati harga lain.',
    'pay.donate_t'       => 'Tambahkan donasi untuk FLF',
    'pay.donate_s'       => 'Dukung pengembangan platform. Opsional, bisa dilewati.',
    'pay.donate_lbl'     => 'Jumlah donasi (IDR)',
    'pay.donate_hint'    => 'Contoh: 2000, 5000, 10000. Bisa juga dikosongkan.',
    'pay.total'          => 'Total yang akan dibayar',
    'pay.your_name'      => 'Nama Anda',
    'pay.guest'          => 'Tamu',
    'pay.phone'          => 'Nomor telepon',
    'pay.your_email'     => 'Email Anda',
    'pay.continue'       => 'Lanjut ke pembayaran →',
    'pay.err_channels'   => 'Gagal memuat metode pembayaran. Periksa API key Tripay Anda.',
    'pay.err_curl'       => 'Kesalahan cURL: :msg',

    // ---- Payment — instructions ----
    'pi.title'           => 'Instruksi pembayaran',
    'pi.paying'          => 'Membayar :name — referensi :ref',
    'pi.err_curl_t'      => 'Tidak dapat menghubungi Tripay',
    'pi.err_fail_t'      => 'Pembayaran gagal',
    'pi.err_unknown'     => 'Kesalahan tidak diketahui dari Tripay.',
    'pi.sum_status'      => 'Status',
    'pi.sum_ref'         => 'Referensi',
    'pi.sum_mref'        => 'Referensi merchant',
    'pi.sum_amount'      => 'Jumlah',
    'pi.sum_donation'    => 'Donasi',
    'pi.sum_fee'         => 'Biaya',
    'pi.sum_expires'     => 'Kedaluwarsa',
    'pi.incl_donation'   => 'termasuk donasi Rp :amt',
    'pi.code_lbl'        => 'Kode pembayaran / VA',
    'pi.copy'            => 'Salin kode',
    'pi.copied'          => '✓ Tersalin',
    'pi.qr_lbl'          => 'Pindai untuk membayar',
    'pi.qr_alt'          => 'Kode QR pembayaran',
    'pi.how_lbl'         => 'Cara membayar',
    'pi.back_profile'    => '← Kembali ke profil',
    'pi.open_checkout'   => 'Buka Checkout Tripay →',

    // ---- Payment — return ----
    'pr.thanks_t'        => 'Terima kasih! Kami sedang memeriksa pembayaran Anda.',
    'pr.thanks_s'        => 'Anda baru kembali dari Tripay. Status akhir akan dikonfirmasi melalui webhook atau dashboard.',
    'pr.back_browse'     => 'Kembali ke daftar Lancer',
    'pr.open_profile'    => 'Buka profil saya',

    // ---- Payment — test ----
    'pt.title'           => 'Pembayaran uji sandbox',
    'pt.sub'             => 'Gunakan halaman ini untuk menguji Tripay tanpa melalui profil Lancer.',
    'pt.method'          => 'Metode pembayaran',
    'pt.amount'          => 'Jumlah (IDR)',
    'pt.cust_name'       => 'Nama pelanggan',
    'pt.cust_default'    => 'Pelanggan Uji',
    'pt.phone'           => 'Nomor telepon',
    'pt.email'           => 'Email',
    'pt.submit'          => 'Buat transaksi sandbox →',
    'pt.back_site'       => '← Kembali ke situs',

    // ---- Footer ----
    'footer.copy'        => '© :year FLF — FreeLanceFinder. Dibuat untuk freelancer di mana pun.',

        'profile.rate_label'  => 'Tarif mulai dari',
    'profile.rate_save'   => 'Simpan tarif',
    'profile.rate_hint'   => 'Ini harga yang dilihat pelanggan saat menyewa Anda. Bisa diubah kapan saja.',
    'profile.rate_saved'  => 'Tarif diperbarui.',
    'profile.rate_error'  => 'Gagal menyimpan tarif. Silakan coba lagi.',
],
];

// --- Default language ---
if (!isset($_SESSION['lang']) || !array_key_exists($_SESSION['lang'], $LANG)) {
    $_SESSION['lang'] = 'en';
}

function current_lang(): string {
    return $_SESSION['lang'] ?? 'en';
}

function other_lang(): string {
    return current_lang() === 'en' ? 'id' : 'en';
}

/** Translate a key, with optional :placeholder replacements. */
function t(string $key, array $replace = []): string {
    global $LANG;
    $lang = current_lang();
    $str  = $LANG[$lang][$key] ?? $LANG['en'][$key] ?? $key;
    foreach ($replace as $k => $v) {
        $str = str_replace(':' . $k, (string)$v, $str);
    }
    return $str;
}