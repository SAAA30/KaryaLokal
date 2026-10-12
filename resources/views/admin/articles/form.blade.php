@extends('layouts.storefront')
@php($editing = isset($article))
@section('title', ($editing ? 'Edit Artikel' : 'Tulis Artikel') . ' — KaryaLokal.id')
@section('content')
<section class="shell" style="max-width:850px;padding:38px 0">
    <a href="{{ route('admin.articles.index') }}" class="muted" style="font-size:11px">← Kembali ke artikel</a>
    <div class="eyebrow" style="margin-top:18px">Konten KaryaLokal</div>
    <h1 class="page-title">{{ $editing ? 'Edit artikel' : 'Tulis artikel' }}</h1>
    <form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.articles.update', $article) : route('admin.articles.store') }}" style="display:grid;gap:16px;margin-top:22px">
        @csrf
        @if($editing) @method('PUT') @endif
        <label style="display:grid;gap:6px;font-size:11px;font-weight:700">Judul artikel
            <input name="title" value="{{ old('title', $article->title ?? '') }}" required maxlength="255" style="padding:11px;border:1px solid var(--line);background:var(--white)">
            @error('title')<span role="alert" style="color:#a33">{{ $message }}</span>@enderror
        </label>
        <label style="display:grid;gap:6px;font-size:11px;font-weight:700">Ringkasan
            <textarea name="excerpt" rows="3" maxlength="500" style="padding:11px;border:1px solid var(--line);background:var(--white);resize:vertical">{{ old('excerpt', $article->excerpt ?? '') }}</textarea>
            @error('excerpt')<span role="alert" style="color:#a33">{{ $message }}</span>@enderror
        </label>
        <label style="display:grid;gap:6px;font-size:11px;font-weight:700">Isi artikel
            <textarea name="body" rows="14" required maxlength="50000" style="padding:11px;border:1px solid var(--line);background:var(--white);resize:vertical;line-height:1.8">{{ old('body', $article->body ?? '') }}</textarea>
            @error('body')<span role="alert" style="color:#a33">{{ $message }}</span>@enderror
        </label>
        <label style="display:grid;gap:7px;font-size:11px;font-weight:700">Foto artikel
            @if($editing && $article->image_path)<img src="{{ \Illuminate\Support\Facades\Storage::url($article->image_path) }}" alt="Foto {{ $article->title }}" style="width:min(320px,100%);aspect-ratio:16/9;object-fit:cover">@endif
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" style="font-size:11px">
            <span class="muted" style="font-weight:400">JPG, PNG, atau WebP, maksimal 5 MB. Kosongkan jika tidak mengganti foto.</span>
            @error('image')<span role="alert" style="color:#a33">{{ $message }}</span>@enderror
        </label>
        <label style="display:flex;align-items:center;gap:9px;font-size:12px;font-weight:700">
            <input type="checkbox" name="is_published" value="1" @checked((bool) old('is_published', $article->is_published ?? false))>
            Terbitkan artikel agar dapat dibaca pembeli
        </label>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <button type="submit" class="primary">{{ $editing ? 'Simpan perubahan' : 'Simpan artikel' }}</button>
            <a href="{{ route('admin.articles.index') }}" class="signin">Batal</a>
        </div>
    </form>
</section>
@endsection
