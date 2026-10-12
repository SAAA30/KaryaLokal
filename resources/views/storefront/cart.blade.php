@extends('layouts.storefront')

@section('title', 'Keranjang — KaryaLokal.id')

@section('content')
<section class="shell" style="padding:42px 0 0">
    <div class="eyebrow">Simpan karya pilihanmu</div>
    <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
        <h1 class="page-title" style="margin:0">Keranjang karya</h1>
        <a href="{{ route('storefront.catalog') }}" class="muted" style="display:inline-flex;align-items:center;gap:8px;border:1px solid var(--line);padding:9px 14px;background:var(--white);font-size:11px;font-weight:700;text-decoration:none">
            <span aria-hidden="true">←</span> Jelajahi karya
        </a>
    </div>
    @if($items->isEmpty())
        <div style="padding:42px 0 70px;border-top:1px solid var(--line);margin-top:22px"><p class="muted" style="font-size:13px">Belum ada karya di keranjang.</p><a href="{{ route('storefront.catalog') }}" class="primary">Jelajahi karya</a></div>
    @else
        <div style="display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:36px;align-items:start;margin-top:22px">
            <div style="border-top:1px solid var(--line)">
                @foreach($items as $item)
                    <article style="display:grid;grid-template-columns:32px 94px minmax(0,1fr) auto;gap:15px;padding:16px 0;border-bottom:1px solid var(--line);align-items:center">
                        <label style="display:flex;align-items:center;justify-content:center">
                            <input type="checkbox" class="cart-item-select" value="{{ $item->id }}" data-price="{{ (float) $item->product->price }}" data-quantity="{{ (int) $item->quantity }}" checked aria-label="Pilih {{ $item->product->name }}">
                        </label>
                        @php($image = $item->product->images[0] ?? null)
                        <img src="{{ $image ? (\Illuminate\Support\Str::startsWith($image, ['http://', 'https://']) ? $image : \Illuminate\Support\Facades\Storage::url($image)) : 'https://images.unsplash.com/photo-1493106641515-6b5631de4bb9?auto=format&fit=crop&w=300&q=75' }}" alt="{{ $item->product->name }}" style="width:94px;height:110px;object-fit:cover">
                        <div><div class="artisan-caption">{{ $item->product->artisan->name }}</div><h2 class="product-name">{{ $item->product->name }}</h2><div class="product-price">Rp{{ number_format((float) $item->product->price, 0, ',', '.') }}</div>@if($item->gift_message)<div class="muted" style="font-size:10px;margin-top:6px">Pesan hadiah: {{ $item->gift_message }}</div>@endif
                            <div style="display:flex;align-items:center;gap:10px;margin-top:10px"><form method="POST" action="{{ route('cart.update', $item) }}" style="display:flex;gap:6px">@csrf @method('PUT')<input type="number" name="quantity" min="1" max="{{ $item->product->stock }}" value="{{ $item->quantity }}" aria-label="Jumlah {{ $item->product->name }}" style="width:58px;height:32px;border:1px solid var(--line);padding:0 7px"><button type="submit" class="muted" style="border:0;background:none;font-size:10px;cursor:pointer">Perbarui</button></form><form method="POST" action="{{ route('cart.destroy', $item) }}">@csrf @method('DELETE')<button type="submit" class="muted" style="border:0;background:none;font-size:10px;text-decoration:underline;cursor:pointer">Hapus</button></form></div>
                        </div><strong style="font-size:12px;white-space:nowrap">Rp{{ number_format((float) $item->product->price * $item->quantity, 0, ',', '.') }}</strong>
                    </article>
                @endforeach
            </div>
            <aside style="padding:18px;background:#eeede5"><div class="eyebrow">Ringkasan</div><div style="display:flex;justify-content:space-between;font-size:12px;margin:17px 0"><span>Subtotal</span><strong id="selected-subtotal">Rp{{ number_format($subtotal, 0, ',', '.') }}</strong></div><p class="muted" style="font-size:10px;line-height:1.6">Ongkir dihitung setelah alamat dan metode pengiriman dipilih.</p>@auth @if(auth()->user()->role === 'customer')<button type="button" id="checkout-selected" class="primary" style="width:100%">Lanjut checkout</button>@else<p class="muted" style="font-size:10px">Checkout tersedia untuk akun customer.</p>@endif @else<a href="{{ route('login') }}" class="primary" style="width:100%">Masuk untuk checkout</a>@endauth</aside>
        </div>
    @endif
</section>
@endsection

@push('scripts')
<script>
    (() => {
        const selectedButton = document.getElementById('checkout-selected');
        const subtotalElement = document.getElementById('selected-subtotal');
        const checkboxes = [...document.querySelectorAll('.cart-item-select')];

        const formatCurrency = (value) => new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(value).replace('Rp', 'Rp');

        const updateCheckoutUrl = () => {
            let subtotal = 0;
            const selectedIds = [];

            checkboxes.forEach((checkbox) => {
                if (checkbox.checked) {
                    const price = Number(checkbox.dataset.price || 0);
                    const quantity = Number(checkbox.dataset.quantity || 1);
                    subtotal += price * quantity;
                    selectedIds.push(checkbox.value);
                }
            });

            if (subtotalElement) {
                subtotalElement.textContent = formatCurrency(subtotal);
            }

            if (!selectedButton) return;

            if (selectedIds.length === 0) {
                selectedButton.disabled = true;
                selectedButton.style.opacity = '0.55';
                selectedButton.style.cursor = 'not-allowed';
                selectedButton.dataset.checkoutUrl = '';
                return;
            }

            const url = new URL('{{ route('checkout') }}');
            selectedIds.forEach((id) => url.searchParams.append('selected', id));

            selectedButton.disabled = false;
            selectedButton.style.opacity = '1';
            selectedButton.style.cursor = 'pointer';
            selectedButton.dataset.checkoutUrl = url.toString();
        };

        checkboxes.forEach((checkbox) => checkbox.addEventListener('change', updateCheckoutUrl));

        selectedButton?.addEventListener('click', () => {
            if (!selectedButton.dataset.checkoutUrl) {
                alert('Pilih minimal satu karya untuk lanjut checkout.');
                return;
            }

            window.location.href = selectedButton.dataset.checkoutUrl;
        });

        updateCheckoutUrl();
    })();
</script>
@endpush
