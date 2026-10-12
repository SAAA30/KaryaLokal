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
const rupiah = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
const storageKey = 'karyalokal-demo-cart';
let activeCategory = 'Semua';
let activeTag = 'Semua';
let toastTimer;

function loadCart() {
    try {
        const saved = JSON.parse(localStorage.getItem(storageKey) || '{}');
        return saved && typeof saved === 'object' ? saved : {};
    } catch {
        return {};
    }
}

let cart = loadCart();

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
    article.className = 'product-card';
    article.dataset.productId = product.id;
    article.dataset.category = product.category;
    article.dataset.tags = product.tags.join('|');
    article.dataset.search = `${product.name} ${product.category} ${product.material} ${product.origin}`.toLocaleLowerCase('id');
    article.innerHTML = `
        <div class="product-photo">
            <img src="${product.image}" alt="${product.name}" loading="lazy">
            <span class="product-badge">Karya terverifikasi</span>
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
    const button = event.target.closest('[data-add]');
    if (!button) return;
    const product = products.find((item) => item.id === button.dataset.add);
    if (!product) return;
    cart[product.id] = Math.min((Number(cart[product.id]) || 0) + 1, product.stock);
    saveCart();
    renderCart();
    showToast(`${product.name} ditambahkan ke keranjang demo.`);
});

document.getElementById('cartTrigger').addEventListener('click', () => cartDialog.showModal());
document.getElementById('closeCart').addEventListener('click', () => cartDialog.close());

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

renderProducts();
renderCart();