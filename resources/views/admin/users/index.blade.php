@extends('layouts.storefront')

@section('title', 'Kelola Akun — KaryaLokal.id')

@section('content')
<section class="shell accounts-page">
	<a href="{{ route('admin.dashboard') }}" class="accounts-back">← Dashboard admin</a>
	<header class="accounts-heading">
		<div><div class="eyebrow">Administrasi marketplace</div><h1 class="page-title">Kelola akun</h1><p class="muted">Atur akses, role, dan status akun dalam satu tempat.</p></div>
		<div class="account-total"><strong>{{ number_format($totalAccounts) }}</strong><span>akun terdaftar</span></div>
	</header>

	<nav class="role-tabs" aria-label="Filter akun berdasarkan role">
		<a href="{{ route('admin.users.index', ['q' => request('q')]) }}" @class(['role-tab', 'is-active' => !request()->filled('role')]) aria-current="{{ !request()->filled('role') ? 'page' : 'false' }}">Semua <span>{{ number_format($totalAccounts) }}</span></a>
		@foreach($roles as $key => $label)
			<a href="{{ route('admin.users.index', ['role' => $key, 'q' => request('q')]) }}" @class(['role-tab', 'is-active' => request('role') === $key]) aria-current="{{ request('role') === $key ? 'page' : 'false' }}">{{ $label }} <span>{{ number_format($roleCounts[$key] ?? 0) }}</span></a>
		@endforeach
	</nav>

	<div class="accounts-toolbar">
		<form method="GET" action="{{ route('admin.users.index') }}" class="account-search">
			@if(request()->filled('role'))<input type="hidden" name="role" value="{{ request('role') }}">@endif
			<label for="account-search">Cari akun</label>
			<div><input id="account-search" name="q" value="{{ request('q') }}" placeholder="Nama atau email"><button class="primary" type="submit">Cari</button></div>
		</form>
		<details class="account-create">
			<summary>+ Buat akun baru</summary>
			<form method="POST" action="{{ route('admin.users.store') }}" class="user-create">
				@csrf
				<label>Nama<input name="name" required maxlength="255"></label>
				<label>Email<input type="email" name="email" required maxlength="255"></label>
				<label>Nomor HP<input name="phone" maxlength="30"></label>
				<label>Password sementara<input type="password" name="password" minlength="8" required></label>
				<label>Role<select name="role" required>@foreach($roles as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
				<button class="primary" type="submit">Buat akun</button>
			</form>
		</details>
	</div>

	@if(session('success'))<div class="account-notice" role="status">{{ session('success') }}</div>@endif
	@if(session('error'))<div class="account-notice is-error" role="alert">{{ session('error') }}</div>@endif

	<div class="account-table-wrap">
		<table class="account-table">
			<thead><tr><th>Akun</th><th>Role saat ini</th><th>Ubah role</th><th>Status</th><th>Aksi</th></tr></thead>
			<tbody>
				@forelse($users as $user)
					<tr>
						<td><strong>{{ $user->name }}</strong><span>{{ $user->email }}</span><small>{{ $user->phone ?: 'Tanpa nomor HP' }}</small></td>
						<td><span class="role-label">{{ $roles[$user->role] ?? $user->role }}</span></td>
						<td><form method="POST" action="{{ route('admin.users.role', $user) }}" class="role-form">@csrf @method('PUT')<select name="role" aria-label="Role {{ $user->name }}">@foreach($roles as $key => $label)<option value="{{ $key }}" @selected($user->role === $key)>{{ $label }}</option>@endforeach</select><button class="signin" type="submit">Simpan</button></form></td>
						<td><span class="account-status {{ $user->status_akun === 'aktif' ? 'is-active' : '' }}">{{ ucfirst($user->status_akun) }}</span></td>
						<td><form method="POST" action="{{ route('admin.users.toggle', $user) }}">@csrf<button class="signin" type="submit" {{ $user->id === auth()->id() ? 'disabled' : '' }}>{{ $user->status_akun === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}</button></form></td>
					</tr>
				@empty
					<tr><td colspan="5" class="account-empty">Tidak ada akun yang cocok.</td></tr>
				@endforelse
			</tbody>
		</table>
	</div>
	<div class="pagination">{{ $users->links() }}</div>
</section>
@endsection
@push('styles')
<style>
	.accounts-page{padding:28px 0 48px}
	.accounts-back{display:inline-block;margin-bottom:18px;color:var(--muted);font-size:11px}
	.accounts-heading{display:flex;justify-content:space-between;align-items:end;gap:20px;padding-bottom:22px;border-bottom:1px solid var(--line)}
	.accounts-heading .page-title{margin:8px 0 5px;font-size:34px}
	.accounts-heading p{margin:0;font-size:12px}
	.account-total{display:grid;gap:2px;text-align:right}
	.account-total strong{font:600 27px var(--serif);color:var(--green)}
	.account-total span{font-size:10px;color:var(--muted)}
	.role-tabs{display:flex;gap:20px;overflow-x:auto;border-bottom:1px solid var(--line);white-space:nowrap}
	.role-tab{display:flex;align-items:center;gap:7px;padding:14px 0 12px;border-bottom:2px solid transparent;color:var(--muted);font-size:11px}
	.role-tab span{font-size:10px;color:var(--ink)}
	.role-tab.is-active{border-color:var(--orange);color:var(--ink);font-weight:700}
	.accounts-toolbar{display:flex;justify-content:space-between;align-items:end;gap:20px;padding:18px 0}
	.account-search{display:grid;gap:6px;min-width:min(100%,360px);font-size:10px;color:var(--muted)}
	.account-search>div{display:flex;gap:7px}
	.account-search input,.user-create input,.user-create select,.role-form select{height:38px;min-width:0;border:1px solid var(--line);background:var(--white);padding:0 10px;color:var(--ink);font:11px var(--sans)}
	.account-search input{flex:1}
	.account-search .primary,.user-create .primary{min-height:38px}
	.account-create{position:relative}
	.account-create summary{list-style:none;cursor:pointer;padding:11px 13px;border:1px solid var(--green);color:var(--green);font-size:11px;font-weight:700}
	.account-create summary::-webkit-details-marker{display:none}
	.account-create[open]{z-index:2}
	.user-create{position:absolute;right:0;top:calc(100% + 8px);display:grid;grid-template-columns:repeat(2,minmax(150px,1fr));gap:11px;width:min(540px,calc(100vw - 32px));padding:16px;border:1px solid var(--line);background:var(--paper);box-shadow:0 12px 32px #18352d18}
	.user-create label{display:grid;gap:5px;color:var(--muted);font-size:10px}
	.account-notice{margin:10px 0;padding:11px 13px;background:#e6efdf;color:var(--green);font-size:11px}
	.account-notice.is-error{background:#fae8e3;color:#8b3325}
	.account-table-wrap{overflow-x:auto;border-top:1px solid var(--line)}
	.account-table{width:100%;min-width:860px;border-collapse:collapse;text-align:left}
	.account-table th{padding:11px 10px;color:var(--muted);font-size:9px;text-transform:uppercase}
	.account-table td{padding:12px 10px;border-top:1px solid var(--line);font-size:11px;vertical-align:middle}
	.account-table td:first-child{min-width:220px}
	.account-table td:first-child strong,.account-table td:first-child span,.account-table td:first-child small{display:block}
	.account-table td:first-child strong{font-size:12px}
	.account-table td:first-child span{margin-top:4px;color:var(--muted);font-size:10px}
	.account-table td:first-child small{margin-top:3px;color:var(--muted);font-size:9px}
	.role-label{color:var(--ink);font-size:10px}
	.role-form{display:flex;gap:6px}
	.role-form select{width:145px}
	.account-table .signin{padding:8px 10px;font-size:9px;white-space:nowrap}
	.account-table button:disabled{opacity:.45;cursor:not-allowed}
	.account-status{display:inline-block;padding:5px 8px;background:#eeeae2;color:#72695d;font-size:9px}
	.account-status.is-active{background:#e6efdf;color:var(--green)}
	.account-empty{padding:28px 10px!important;color:var(--muted);text-align:center}
	.accounts-page .pagination{margin-top:18px}
	.accounts-page .pagination nav{display:flex;align-items:center;justify-content:space-between;width:100%}
	.accounts-page .pagination nav>div:first-child{display:flex;align-items:center;justify-content:space-between;width:100%;gap:10px}
	.accounts-page .pagination nav>div:last-child{display:none}
	.accounts-page .pagination svg{display:block;flex:none;width:16px!important;height:16px!important}
	.accounts-page .pagination nav>div:last-child>div:last-child>span{display:inline-flex;align-items:center;white-space:nowrap}
	.accounts-page .pagination nav>div:last-child>div:last-child>span[aria-disabled]{display:inline-flex;align-items:center}
	.accounts-page .pagination a,.accounts-page .pagination span[aria-current="page"]{display:inline-flex;flex:none;align-items:center;justify-content:center;min-width:34px;height:36px;padding:0 9px;border:1px solid var(--line);color:var(--ink);font-size:11px;white-space:nowrap}
	.accounts-page .pagination span[aria-current="page"]{background:var(--green);color:#fff}
	@media(min-width:640px){.accounts-page .pagination nav>div:first-child{display:none}.accounts-page .pagination nav>div:last-child{display:flex;flex:1;align-items:center;justify-content:space-between;gap:14px}.accounts-page .pagination nav>div:last-child>div:last-child{display:flex;justify-content:flex-end;flex:none}}
	@media(max-width:760px){.accounts-page{padding-top:20px}.accounts-heading{align-items:start}.accounts-heading .page-title{font-size:29px}.account-total strong{font-size:23px}.role-tabs{gap:16px}.accounts-toolbar{align-items:stretch;flex-direction:column}.account-search{min-width:0}.account-create{align-self:flex-start}.user-create{left:0;right:auto}.account-total{padding-top:6px}}
	@media(max-width:420px){.accounts-heading{gap:8px}.accounts-heading p{max-width:230px}.account-total strong{font-size:20px}.user-create{grid-template-columns:1fr}}
</style>
@endpush
