const products = [
    {
        id: 'gelang-anyaman-senja',
        name: 'Gelang Anyaman Senja',
        category: 'Aksesori',
        price: 185000,
        stock: 18,
        material: 'Benang katun dan manik kayu',
        origin: 'Bantul, Yogyakarta',
        image: 'assets/gelang-anyaman.jpg',
        tags: ['Pilihan kurator', 'Hadiah'],
    },
    {
        id: 'vas-bumi-edisi-pagi',
        name: 'Vas Bumi Edisi Pagi',
        category: 'Keramik',
        price: 320000,
        stock: 7,
        material: 'Tanah liat lokal, putar dan bakar alami',
        origin: 'Ubud, Bali',
        image: 'assets/vas-bumi.jpg',
        tags: ['Pilihan kurator'],
    },
    {
        id: 'selendang-lurik-purnama',
        name: 'Selendang Lurik Purnama',
        category: 'Fashion',
        price: 450000,
        stock: 6,
        material: 'Kapas pintal tangan, tenun ATBM',
        origin: 'Klaten, Jawa Tengah',
        image: 'assets/selendang-lurik.jpg',
        tags: ['Hadiah'],
    },
    {
        id: 'nampan-rotan-hutan',
        name: 'Nampan Rotan Hutan',
        category: 'Anyaman',
        price: 275000,
        stock: 10,
        material: 'Rotan alam, anyaman tangan',
        origin: 'Cirebon, Jawa Barat',
        image: 'assets/nampan-rotan.jpg',
        tags: ['Pilihan kurator'],
    },
];

const productGrid = document.getElementById('productGrid');
const searchInput = document.getElementById('searchInput');
const sortSelect = document.getElementById('sortSelect');
const resultCount = document.getElementById('resultCount');
const emptyState = document.getElementById('emptyState');
const cartDialog = document.getElementById('cartDialog');
const cartLines = document.getElementById('cartLines');
const cartCount = document.getElementById('cartCount');
const cartTotal = document.getElementById('cartTotal');
const toast = document.getElementById('toast');
const accountDialog = document.getElementById('accountDialog');
const accountContent = document.getElementById('accountContent');
const accountLabel = document.getElementById('accountLabel');
const rupiah = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
const storageKey = 'karyalokal-demo-cart';
const accountKey = 'karyalokal-demo-customer';
const wishlistKey = 'karyalokal-demo-wishlist';
const ordersKey = 'karyalokal-demo-orders';
const demoCustomer = { name: 'Alya Pratiwi', email: 'alya@demo.karyalokal.id', city: 'Jakarta Selatan' };
let activeCategory = 'Semua';
let activeTag = 'Semua';
let activeAccountTab = 'profil';
let pendingCheckout = false;
let toastTimer;

function loadArray(key) {
    try {
        const saved = JSON.parse(localStorage.getItem(key) || '[]');
        return Array.isArray(saved) ? saved : [];
    } catch {
        return [];
    }
}

function updateAccountHeader() {
    accountLabel.textContent = customerSignedIn ? 'Alya' : 'Masuk demo';
}

function saveValue(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
        return true;
    } catch {
        showToast('Data demo tidak dapat disimpan di browser ini.');
        return false;
    }
}

function loadCart() {
    try {
        const saved = JSON.parse(localStorage.getItem(storageKey) || '{}');
        return saved && typeof saved === 'object' ? saved : {};
    } catch {
        return {};
    }
}

let cart = loadCart();
let wishlist = loadArray(wishlistKey);
let orders = loadArray(ordersKey);
let customerSignedIn = localStorage.getItem(accountKey) === 'true';

function saveCart() {
    try {
        localStorage.setItem(storageKey, JSON.stringify(cart));
    } catch {
        showToast('Keranjang hanya tersimpan selama halaman ini terbuka.');
    }
}

function showToast(message) {
    toast.textContent = message;
    toast.classList.add('is-visible');
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(() => toast.classList.remove('is-visible'), 2200);
}

function productCard(product) {
    const article = document.createElement('article');
    const isSaved = wishlist.includes(product.id);
    article.className = 'product-card';
    article.dataset.productId = product.id;
    article.dataset.category = product.category;
    article.dataset.tags = product.tags.join('|');
    article.dataset.search = `${product.name} ${product.category} ${product.material} ${product.origin}`.toLocaleLowerCase('id');
    article.innerHTML = `
        <div class="product-photo">
            <img src="${product.image}" alt="${product.name}" loading="lazy">
            <span class="product-badge">Karya terverifikasi</span>
            <button class="wishlist-toggle ${isSaved ? 'is-saved' : ''}" type="button" data-wishlist="${product.id}" aria-pressed="${isSaved}" aria-label="${isSaved ? 'Hapus dari' : 'Simpan ke'} wishlist: ${product.name}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 8.8c0 5.2-8.8 10.3-8.8 10.3S3.2 14 3.2 8.8A4.3 4.3 0 0 1 12 6.5a4.3 4.3 0 0 1 8.8 2.3Z"></path></svg>
            </button>
        </div>
        <div class="product-info">
            <p class="artisan-name">Siti Rahma · ${product.origin}</p>
            <h3 class="product-title">${product.name}</h3>
            <div class="rating-line"><span class="rating-star" aria-hidden="true">★</span><span>4,6</span><span>·</span><span>5 ulasan contoh</span></div>
            <div class="product-bottom"><span class="product-price">${rupiah.format(product.price)}</span><span class="stock-label">${product.stock} tersedia</span></div>
            <p class="product-material">${product.material}</p>
            <button class="add-button" type="button" data-add="${product.id}" aria-label="Tambah ${product.name} ke keranjang demo">Tambah ke keranjang</button>
        </div>`;
    return article;
}

function visibleProducts() {
    const query = searchInput.value.trim().toLocaleLowerCase('id');
    return products.filter((product) => {
        const matchesQuery = `${product.name} ${product.category} ${product.material} ${product.origin}`.toLocaleLowerCase('id').includes(query);
        const matchesCategory = activeCategory === 'Semua' || product.category === activeCategory;
        const matchesTag = activeTag === 'Semua' || product.tags.includes(activeTag);
        return matchesQuery && matchesCategory && matchesTag;
    });
}

function renderProducts() {
    const visible = visibleProducts();
    const sorted = [...visible];
    if (sortSelect.value === 'price-low') sorted.sort((first, second) => first.price - second.price);
    if (sortSelect.value === 'price-high') sorted.sort((first, second) => second.price - first.price);
    productGrid.replaceChildren(...sorted.map(productCard));
    resultCount.textContent = `${sorted.length} karya`;
    emptyState.hidden = sorted.length > 0;
}

function renderCart() {
    const entries = Object.entries(cart)
        .map(([id, quantity]) => ({ product: products.find((item) => item.id === id), quantity: Number(quantity) }))
        .filter((entry) => entry.product && entry.quantity > 0);
    const totalCount = entries.reduce((sum, entry) => sum + entry.quantity, 0);
    const totalPrice = entries.reduce((sum, entry) => sum + entry.product.price * entry.quantity, 0);
    cartCount.textContent = String(totalCount);
    cartCount.setAttribute('aria-label', `${totalCount} karya di keranjang demo`);
    cartTotal.textContent = rupiah.format(totalPrice);
    document.getElementById('checkoutButton').disabled = totalCount === 0;

    if (!entries.length) {
        cartLines.innerHTML = '<p class="cart-empty">Keranjang demo masih kosong.</p>';
        return;
    }

    cartLines.innerHTML = entries.map(({ product, quantity }) => `
        <article class="cart-line" data-cart-id="${product.id}">
            <img src="${product.image}" alt="">
            <div>
                <h3>${product.name}</h3>
                <p>${rupiah.format(product.price)}</p>
                <div class="quantity-control" aria-label="Jumlah ${product.name}">
                    <button type="button" data-quantity="-1" data-id="${product.id}" aria-label="Kurangi jumlah">−</button>
                    <span>${quantity}</span>
                    <button type="button" data-quantity="1" data-id="${product.id}" aria-label="Tambah jumlah">+</button>
                </div>
            </div>
            <button class="remove-line" type="button" data-remove="${product.id}" aria-label="Hapus ${product.name}">Hapus</button>
        </article>`).join('');
}

function renderAccount() {
    accountLabel.textContent = customerSignedIn ? 'Alya' : 'Masuk demo';
    if (!customerSignedIn) {
        accountContent.innerHTML = `
            <div class="demo-signin-panel">
                <div class="demo-avatar" aria-hidden="true">AP</div>
                <h3>Masuk sebagai customer demo</h3>
                <p>Jelajahi profil, wishlist, dan riwayat pesanan contoh tanpa membuat akun sungguhan.</p>
                <button class="demo-signin-button" type="button" data-demo-signin>Masuk sebagai customer demo</button>
                <p class="demo-caption">Tidak meminta email atau kata sandi. Status demo disimpan di browser ini.</p>
            </div>`;
        return;
    }

    accountContent.innerHTML = `
        <div class="customer-bar">
            <div class="demo-avatar" aria-hidden="true">AP</div>
            <div class="customer-details"><strong>${demoCustomer.name}</strong><span>Customer demo · data di browser ini</span></div>
            <button class="signout-button" type="button" data-demo-signout>Keluar</button>
        </div>
        <div class="account-tabs" role="tablist" aria-label="Menu akun customer">
            <button class="account-tab ${activeAccountTab === 'profil' ? 'is-active' : ''}" type="button" role="tab" aria-selected="${activeAccountTab === 'profil'}" data-account-tab="profil">Profil</button>
            <button class="account-tab ${activeAccountTab === 'pesanan' ? 'is-active' : ''}" type="button" role="tab" aria-selected="${activeAccountTab === 'pesanan'}" data-account-tab="pesanan">Pesanan (${orders.length})</button>
            <button class="account-tab ${activeAccountTab === 'wishlist' ? 'is-active' : ''}" type="button" role="tab" aria-selected="${activeAccountTab === 'wishlist'}" data-account-tab="wishlist">Wishlist (${wishlist.length})</button>
        </div>
        <div class="account-view" id="accountView" role="tabpanel"></div>`;
    renderAccountView();
}

function renderAccountView() {
    const view = document.getElementById('accountView');
    if (!view) return;

    if (activeAccountTab === 'pesanan') {
        if (!orders.length) {
            view.innerHTML = '<p class="account-empty">Belum ada pesanan demo. Isi keranjang lalu buat pesanan simulasi.</p>';
            return;
        }
        view.innerHTML = `<div class="order-list">${orders.map((order) => `
            <article class="order-entry">
                <div class="order-entry-head"><strong>${order.id}</strong><span>${order.date}</span></div>
                <p>${order.items.map((item) => `${item.name} × ${item.quantity}`).join('<br>')}</p>
                <p class="order-total">${rupiah.format(order.total)} · Simulasi</p>
            </article>`).join('')}</div>`;
        return;
    }

    if (activeAccountTab === 'wishlist') {
        const savedProducts = wishlist.map((id) => products.find((product) => product.id === id)).filter(Boolean);
        if (!savedProducts.length) {
            view.innerHTML = '<p class="account-empty">Wishlist demo masih kosong. Tekan ikon hati pada karya untuk menyimpannya.</p>';
            return;
        }
        view.innerHTML = `<div class="saved-list">${savedProducts.map((product) => `
            <article class="saved-entry">
                <img src="${product.image}" alt="">
                <div><strong>${product.name}</strong><span>${rupiah.format(product.price)}</span></div>
                <button type="button" data-saved-add="${product.id}">Tambah ke keranjang</button>
            </article>`).join('')}</div>`;
        return;
    }

    view.innerHTML = `
        <div class="profile-grid">
            <div class="profile-field"><span>Nama</span><strong>${demoCustomer.name}</strong></div>
            <div class="profile-field"><span>Role</span><strong>Customer demo</strong></div>
            <div class="profile-field"><span>Email contoh</span><strong>${demoCustomer.email}</strong></div>
            <div class="profile-field"><span>Kota contoh</span><strong>${demoCustomer.city}</strong></div>
        </div>
        <p class="account-note">Profil contoh ini hanya untuk melihat tampilan customer. Tidak terhubung ke akun atau data server.</p>`;
}

function updateWishlist(productId) {
    if (!customerSignedIn) {
        showToast('Masuk sebagai customer demo untuk menyimpan wishlist.');
        accountDialog.showModal();
        renderAccount();
        return;
    }
    wishlist = wishlist.includes(productId)
        ? wishlist.filter((id) => id !== productId)
        : [...wishlist, productId];
    saveValue(wishlistKey, wishlist);
    renderProducts();
    renderAccount();
}

function addToCart(productId) {
    const product = products.find((item) => item.id === productId);
    if (!product) return;
    cart[product.id] = Math.min((Number(cart[product.id]) || 0) + 1, product.stock);
    saveCart();
    renderCart();
    showToast(`${product.name} ditambahkan ke keranjang demo.`);
}

function placeDemoOrder() {
    if (!customerSignedIn) {
        pendingCheckout = true;
        cartDialog.close();
        renderAccount();
        accountDialog.showModal();
        showToast('Masuk demo dulu untuk membuat pesanan simulasi.');
        return;
    }

    const items = Object.entries(cart)
        .map(([id, quantity]) => ({ product: products.find((item) => item.id === id), quantity: Number(quantity) }))
        .filter((entry) => entry.product && entry.quantity > 0);
    if (!items.length) return;

    const total = items.reduce((sum, item) => sum + item.product.price * item.quantity, 0);
    const order = {
        id: `KL-DEMO-${String(Date.now()).slice(-6)}`,
        date: new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }),
        total,
        items: items.map(({ product, quantity }) => ({ name: product.name, quantity })),
    };
    orders = [order, ...orders];
    saveValue(ordersKey, orders);
    cart = {};
    saveCart();
    renderCart();
    cartDialog.close();
    showToast(`Pesanan simulasi ${order.id} tercatat di browser.`);
}

function updateCategory(category) {
    activeCategory = category;
    document.querySelectorAll('.category-chip').forEach((button) => {
        button.classList.toggle('is-active', button.dataset.category === category);
    });
    renderProducts();
}

document.getElementById('categoryList').addEventListener('click', (event) => {
    const button = event.target.closest('[data-category]');
    if (!button) return;
    updateCategory(button.dataset.category);
    document.getElementById('karya').scrollIntoView({ behavior: 'smooth' });
});

document.querySelector('.filter-row').addEventListener('click', (event) => {
    const button = event.target.closest('[data-filter]');
    if (!button) return;
    activeTag = button.dataset.filter;
    document.querySelectorAll('.filter-button').forEach((item) => item.classList.toggle('is-active', item === button));
    renderProducts();
});

searchInput.addEventListener('input', renderProducts);
sortSelect.addEventListener('change', renderProducts);

productGrid.addEventListener('click', (event) => {
    const wishlistButton = event.target.closest('[data-wishlist]');
    if (wishlistButton) {
        updateWishlist(wishlistButton.dataset.wishlist);
        return;
    }
    const button = event.target.closest('[data-add]');
    if (!button) return;
    addToCart(button.dataset.add);
});

document.getElementById('cartTrigger').addEventListener('click', () => cartDialog.showModal());
document.getElementById('closeCart').addEventListener('click', () => cartDialog.close());
document.getElementById('closeAccount').addEventListener('click', () => accountDialog.close());
document.getElementById('accountTrigger').addEventListener('click', () => {
    renderAccount();
    accountDialog.showModal();
});
document.getElementById('checkoutButton').addEventListener('click', placeDemoOrder);

accountContent.addEventListener('click', (event) => {
    if (event.target.closest('[data-demo-signin]')) {
        customerSignedIn = true;
        try { localStorage.setItem(accountKey, 'true'); } catch { showToast('Status demo hanya berlaku selama halaman terbuka.'); }
        activeAccountTab = 'profil';
        renderAccount();
        showToast('Masuk sebagai customer demo.');
        if (pendingCheckout) {
            pendingCheckout = false;
            accountDialog.close();
            cartDialog.showModal();
        }
        return;
    }
    if (event.target.closest('[data-demo-signout]')) {
        customerSignedIn = false;
        try { localStorage.removeItem(accountKey); } catch {}
        renderAccount();
        showToast('Keluar dari customer demo.');
        return;
    }
    const tab = event.target.closest('[data-account-tab]');
    if (tab) {
        activeAccountTab = tab.dataset.accountTab;
        renderAccount();
        return;
    }
    const savedAdd = event.target.closest('[data-saved-add]');
    if (savedAdd) {
        addToCart(savedAdd.dataset.savedAdd);
        cartDialog.showModal();
    }
});

cartLines.addEventListener('click', (event) => {
    const removeButton = event.target.closest('[data-remove]');
    const quantityButton = event.target.closest('[data-quantity]');
    if (removeButton) {
        delete cart[removeButton.dataset.remove];
    } else if (quantityButton) {
        const id = quantityButton.dataset.id;
        const product = products.find((item) => item.id === id);
        const nextQuantity = (Number(cart[id]) || 0) + Number(quantityButton.dataset.quantity);
        if (nextQuantity < 1) delete cart[id];
        else cart[id] = Math.min(nextQuantity, product.stock);
    } else {
        return;
    }
    saveCart();
    renderCart();
});

updateAccountHeader();
renderProducts();
renderCart();