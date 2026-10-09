@if(!$shouldHideNav && !auth()->user()?->hasRole('Petugas Gate'))
@php
    $roleName = auth()->user()?->hasRole('Superadmin') ? 'Superadmin' : (auth()->user()?->hasRole('Penyedia Event') ? 'Organizer' : (auth()->user()?->hasRole('Petugas Loket') ? 'Petugas Loket' : 'Fan Zone'));
    $pageTitle = is_string($header ?? null) ? $header : (is_string($title ?? null) ? $title : 'Dashboard');
@endphp
<div class="px-6 py-6 md:px-8 md:py-8 bg-white border-b border-slate-200 mb-6">
    <div class="max-w-[1400px] mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight font-outfit">{{ $pageTitle }}</h2>
            <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">{{ $roleName }} / <span class="text-orange-600">{{ $pageTitle }}</span></p>
        </div>
        @if(isset($actions))
            <div class="flex flex-wrap items-center gap-3">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
@endif
