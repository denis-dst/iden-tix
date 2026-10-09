<div class="flex items-center gap-4">
    <div class="text-right hidden md:block">
        <div class="text-sm font-black text-slate-800 leading-none">{{ Auth::user()?->name ?? 'Pengguna' }}</div>
        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">{{ Auth::user()?->getRoleNames()?->first() ?? 'User' }}</div>
    </div>
    <div class="w-10 h-10 rounded-lg bg-orange-500 text-white flex items-center justify-center font-bold border-2 border-orange-100 shadow-sm overflow-hidden">
        @if(Auth::user()?->avatar)
            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()?->name ?? 'Avatar' }}" class="w-full h-full object-cover">
        @else
            {{ substr(Auth::user()?->name ?? 'U', 0, 1) }}
        @endif
    </div>
</div>
