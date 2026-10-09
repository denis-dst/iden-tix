<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Keanggotaan Suporter {{ $tenant->name }} - IdenTix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #090d16; }
        .font-outfit { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="min-h-screen text-slate-100 flex items-center justify-center p-4 sm:p-6 md:p-8 relative">

    <div class="max-w-xl w-full space-y-6">

        <!-- Club Identity Header -->
        <div class="text-center space-y-3">
            <div class="inline-flex p-3 rounded-2xl bg-slate-900 border border-slate-800 shadow-md mb-1">
                @if($tenant->logo)
                    <img src="{{ asset('storage/' . $tenant->logo) }}" alt="{{ $tenant->name }}" class="h-14 w-auto object-contain">
                @else
                    <div class="w-14 h-14 bg-orange-600 rounded-xl flex items-center justify-center font-black text-2xl text-white font-outfit shadow-sm">
                        {{ strtoupper(substr($tenant->name, 0, 1)) }}
                    </div>
                @endif
            </div>

            <h1 class="text-2xl sm:text-3xl font-black text-white font-outfit uppercase tracking-tight">
                {{ $tenant->name }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 max-w-md mx-auto leading-relaxed">
                Pendaftaran resmi kartu suporter klub. Dapatkan prioritas tiket pertandingan kandang, potongan harga, dan kumpulkan poin loyalitas.
            </p>
        </div>

        <!-- Registration Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl space-y-6">

            @if ($errors->any())
                <div class="p-4 bg-rose-950/60 border border-rose-800 rounded-xl text-rose-200 text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <p class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                            {{ $error }}
                        </p>
                    @endforeach
                </div>
            @endif

            <form action="{{ url()->current() }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-1.5">
                        Nama Lengkap (Sesuai KTP) <span class="text-orange-400">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Nama lengkap Anda" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500 font-medium">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="nik" class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-1.5">
                            Nomor NIK KTP (16 Digit) <span class="text-orange-400">*</span>
                        </label>
                        <input type="text" id="nik" name="nik" value="{{ old('nik') }}" maxlength="16" minlength="16" placeholder="3201xxxxxxxxxxxx" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500 font-mono font-bold">
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-1.5">
                            No. WhatsApp Aktif <span class="text-orange-400">*</span>
                        </label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500 font-medium">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-1.5">
                        Alamat Email <span class="text-orange-400">*</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500 font-medium">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-1.5">
                            Kata Sandi <span class="text-orange-400">*</span>
                        </label>
                        <input type="password" id="password" name="password" placeholder="Minimal 8 karakter" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500 font-medium">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-1.5">
                            Konfirmasi Kata Sandi <span class="text-orange-400">*</span>
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi kata sandi" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500 font-medium">
                    </div>
                </div>

                @if($tiers->isNotEmpty())
                    <div class="pt-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-2">Pilih Kategori Keanggotaan</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                            @foreach($tiers as $tier)
                                <label class="flex items-center gap-3 p-3 bg-slate-950 border border-slate-800 rounded-xl cursor-pointer hover:border-orange-500 transition min-h-[44px]">
                                    <input type="radio" name="membership_tier_id" value="{{ $tier->id }}" class="text-orange-600 focus:ring-orange-500 bg-slate-900 border-slate-700">
                                    <div class="text-xs">
                                        <p class="font-bold text-white">{{ $tier->name }}</p>
                                        <p class="text-orange-400 font-semibold text-xs">{{ $tier->price > 0 ? 'Rp ' . number_format($tier->price, 0, ',', '.') : 'Gratis' }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="pt-4">
                    <button type="submit" class="w-full min-h-[48px] py-3.5 bg-orange-600 hover:bg-orange-700 text-white font-black text-sm uppercase tracking-wider rounded-xl shadow-lg transition duration-150 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 focus:ring-offset-slate-900">
                        Daftar Sebagai Member Resmi
                    </button>
                </div>
            </form>

            <div class="text-center pt-3 border-t border-slate-800">
                <p class="text-xs text-slate-400">
                    Sudah memiliki akun suporter? 
                    <a href="{{ route('login') }}" class="text-orange-400 font-bold hover:underline focus:outline-none focus:ring-2 focus:ring-orange-500 rounded px-1">Masuk di sini</a>
                </p>
            </div>
        </div>

        <!-- Footer Notice -->
        <p class="text-xs text-slate-500 text-center font-medium">
            Platform Manajemen Tiket & Komunitas Klub Sepakbola IdenTix
        </p>
    </div>

</body>
</html>
