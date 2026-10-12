@extends('layouts.storefront')
@php($editing = isset($product))
@php($backRoute = $backRoute ?? route('artisan.products.index'))
@php($submitRoute = $submitRoute ?? ($editing ? route('artisan.products.update',$product) : route('artisan.products.store')))
@php($isAdminEdit = $isAdminEdit ?? false)
@php($backLabel = $isAdminEdit && parse_url($backRoute, PHP_URL_PATH) === parse_url(route('admin.saw.index'), PHP_URL_PATH) ? 'Kembali ke dashboard SPK' : ($isAdminEdit ? 'Kembali ke daftar QR' : 'Karya saya'))
@section('title', ($editing ? 'Edit Karya' : 'Tambah Karya') . ' — KaryaLokal.id')
@section('content')
<section class="shell" style="max-width:900px;padding:38px 0"><a class="muted" style="font-size:11px" href="{{ $backRoute }}">← {{ $backLabel }}</a><div class="eyebrow" style="margin-top:22px">Identitas karya</div><h1 class="page-title">{{ $editing ? 'Perbarui karya' : 'Tambah karya baru' }}</h1><p class="muted" style="font-size:12px">{{ $isAdminEdit ? 'Periksa dan sunting detail karya yang sudah terbit tanpa mengubah status approval.' : 'Lengkapi informasi karya dan ceritanya untuk dikirim ke kurator.' }}</p>
<form method="POST" enctype="multipart/form-data" action="{{ $submitRoute }}" style="display:grid;grid-template-columns:1fr 1fr;gap:13px;margin-top:20px">@csrf <input type="hidden" name="return_to" value="{{ request()->query('return_to', $backRoute) }}"> @if($editing)@method('PUT')@endif
    <label class="f">Nama produk<input name="name" value="{{ old('name',$product->name ?? '') }}" required maxlength="255"></label>
    <label class="f">Kategori<select name="category_id" required><option value="">Pilih kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id',$product->category_id ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select></label>
    <label class="f">SKU<input name="sku" value="{{ old('sku',$product->sku ?? '') }}"></label><label class="f">Harga (Rp)<input type="number" min="0" step="1000" name="price" value="{{ old('price',$product->price ?? '') }}" required></label>
    <label class="f">Stok<input type="number" min="0" name="stock" value="{{ old('stock',$product->stock ?? 0) }}" required></label><label class="f">Jenis pemenuhan<select name="fulfillment_type"><option value="ready_stock" @selected(old('fulfillment_type',$product->fulfillment_type ?? 'ready_stock')==='ready_stock')>Siap dikirim</option><option value="pre_order" @selected(old('fulfillment_type',$product->fulfillment_type ?? '')==='pre_order')>Pre-order</option></select></label>
    <label class="f">Estimasi produksi (hari)<input type="number" min="1" name="production_days" value="{{ old('production_days',$product->production_days ?? '') }}"></label><label class="f">Berat (gram)<input type="number" min="1" name="weight_grams" value="{{ old('weight_grams',$product->weight_grams ?? '') }}"></label>
    <label class="f">Material<input name="material" value="{{ old('material',$product->material ?? '') }}" required></label><label class="f">Teknik pembuatan<input name="technique" value="{{ old('technique',$product->technique ?? '') }}" required></label>
    <label class="f">Asal daerah<input name="origin" value="{{ old('origin',$product->origin ?? '') }}" placeholder="Bantul, Yogyakarta" required></label><label class="f">Dimensi<input name="dimensions" value="{{ old('dimensions',$product->dimensions ?? '') }}" placeholder="P × L × T"></label>
    <label class="f full">Deskripsi produk<textarea name="description" rows="3" required>{{ old('description',$product->description ?? '') }}</textarea></label>
    <label class="f full">Cerita dan inspirasi karya<textarea name="story" rows="5" required>{{ old('story',$product->story ?? '') }}</textarea></label>
    <label class="f full">{{ $editing ? 'Tambah foto produk' : 'Foto produk' }} (total maks. 6, masing-masing 5 MB)<input type="file" name="images[]" accept="image/*" multiple {{ $editing ? '' : 'required' }}></label>
    @if($editing && !empty($product->images))
        <div class="full">
            <p class="muted" style="font-size:10px;margin:0 0 8px">{{ count($product->images) }} foto tersimpan. Foto ini tetap disimpan; unggahan baru hanya menambahkan foto.</p>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                @foreach($product->images as $image)
                    <img src="{{ \Illuminate\Support\Str::startsWith($image, ['http://', 'https://']) ? $image : \Illuminate\Support\Facades\Storage::url($image) }}" alt="Foto tersimpan produk" style="width:88px;height:88px;object-fit:cover;background:#e8e5dc;border:1px solid var(--line)">
                @endforeach
            </div>
        </div>
    @endif
    <div class="full"><div class="eyebrow">Tahapan proses pembuatan</div><div class="process-grid">@foreach(old('process_steps', isset($product) ? $product->processSteps->map(fn($step)=>['title'=>$step->title,'description'=>$step->description,'duration_minutes'=>$step->duration_minutes])->all() : [['title'=>'','description'=>'','duration_minutes'=>'']]) as $i=>$step)<div class="process-item"><label class="f">Tahap {{ $i+1 }}<input name="process_steps[{{ $i }}][title]" value="{{ $step['title'] ?? '' }}" required></label><label class="f">Deskripsi<textarea name="process_steps[{{ $i }}][description]" rows="2">{{ $step['description'] ?? '' }}</textarea></label><label class="f">Durasi (menit)<input type="number" min="1" name="process_steps[{{ $i }}][duration_minutes]" value="{{ $step['duration_minutes'] ?? '' }}"></label></div>@endforeach</div><button type="button" id="addStep" class="signin" style="margin-top:8px">+ Tambah tahapan</button></div>
    <label class="full" style="font-size:11px"><input type="checkbox" name="is_customizable" value="1" @checked(old('is_customizable',$product->is_customizable ?? false))> Karya bisa dibuat custom</label>
    @if($isAdminEdit || ($editing && ($product->verification_status ?? null) === \App\Models\Product::STATUS_APPROVED))
        <div class="full" style="display:flex;gap:9px;justify-content:flex-end"><button class="primary" type="submit">Simpan perubahan</button></div>
    @else
        <div class="full" style="display:flex;gap:9px;justify-content:flex-end"><button class="signin" name="save_as" value="draft">Simpan draft</button><button class="primary" name="save_as" value="pending">Kirim ke kurator</button></div>
    @endif
</form></section>
@endsection
@push('styles')<style>.f{display:grid;gap:6px;font-size:10px;font-weight:700;color:var(--muted)}.f input,.f select,.f textarea{width:100%;min-width:0;border:1px solid var(--line);background:var(--white);padding:10px;color:var(--ink);font:12px var(--sans)}.full{grid-column:1/-1}.process-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:12px}.process-item{background:#eeede5;padding:12px;display:grid;gap:8px}@media(max-width:680px){form[style*="grid-template-columns:1fr 1fr"]{grid-template-columns:1fr!important}.process-grid{grid-template-columns:1fr}}</style>@endpush
@push('scripts')<script>
let stepIndex = document.querySelectorAll('.process-item').length;
document.getElementById('addStep').addEventListener('click', () => {
    const template = `<div class="process-item"><label class="f">Tahap ${stepIndex + 1}<input name="process_steps[${stepIndex}][title]" required></label><label class="f">Deskripsi<textarea name="process_steps[${stepIndex}][description]" rows="2"></textarea></label><label class="f">Durasi (menit)<input type="number" min="1" name="process_steps[${stepIndex}][duration_minutes]"></label></div>`;
    document.querySelector('.process-grid').insertAdjacentHTML('beforeend', template);
    stepIndex++;
});
</script>@endpush
