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
    <form method="POST" action="{{ route('logout') }}" class="inline">
        @csrf
        <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition" title="Keluar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4-4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
        </button>
    </form>
</div>
