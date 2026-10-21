<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Event Name -->
    <div class="col-span-1 md:col-span-2">
        <label for="name" class="block font-medium text-sm text-gray-700 mb-1">Event Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="name" value="{{ old('name', $event->name ?? '') }}" 
               class="w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm text-gray-900" 
               required placeholder="e.g. Summer Music Festival 2026">
        @error('name')
            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <script>
        (function(){
            // Sync Trix editors to their hidden inputs before the surrounding form submits
            var script = document.currentScript;
            var form = script && script.closest('form') || document.querySelector('form');
            if (!form) return;

            form.addEventListener('submit', function () {
                var editors = form.querySelectorAll('trix-editor');
                editors.forEach(function (editor) {
                    var inputId = editor.getAttribute('input');
                    var input = inputId ? document.getElementById(inputId) : null;
                    if (input && editor.editor) {
                        input.value = editor.editor.getDocument().toString().trim() ? editor.value : '';
                    }
                });
            });
        })();
    </script>

    <!-- Event Description -->
    <!-- Event Description -->
    <div class="col-span-1 md:col-span-2">
        <label for="description" class="block font-medium text-sm text-gray-700 mb-1">Event Description</label>
        <input id="description_input" type="hidden" name="description" value="{{ old('description', $event->description ?? '') }}">
        <trix-editor input="description_input" class="trix-content min-h-[150px] bg-white rounded-lg border-gray-300 focus-within:border-purple-500 focus:ring-purple-500 shadow-sm text-gray-900 transition-all px-4 py-3" placeholder="Tell us about the event..."></trix-editor>
        @error('description')
            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Organizer (Tenant) -->
    <div>
        <label for="tenant_id" class="block font-medium text-sm text-gray-700 mb-1">Organizer <span class="text-red-500">*</span></label>
        <select name="tenant_id" id="tenant_id" class="w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm text-gray-900 font-medium" required>
            <option value="">-- Select Organizer --</option>
            @foreach($tenants as $tenant)
                <option value="{{ $tenant->id }}" {{ old('tenant_id', $event->tenant_id ?? '') == $tenant->id ? 'selected' : '' }}>
                    {{ $tenant->name }}
                </option>
            @endforeach
        </select>
        @error('tenant_id')
            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Status -->
    <div>
        <label for="status" class="block font-medium text-sm text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
        <select name="status" id="status" class="w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm text-gray-900 font-medium" required>
            <option value="draft" {{ old('status', $event->status ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="published" {{ old('status', $event->status ?? '') === 'published' ? 'selected' : '' }}>Published</option>
            <option value="private" {{ old('status', $event->status ?? '') === 'private' ? 'selected' : '' }}>Private</option>
            <option value="finished" {{ old('status', $event->status ?? '') === 'finished' ? 'selected' : '' }}>Finished</option>
            <option value="cancelled" {{ old('status', $event->status ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
        @error('status')
            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Venue -->
    <div>
        <label for="venue" class="block font-medium text-sm text-gray-700 mb-1">Venue <span class="text-red-500">*</span></label>
        <input type="text" name="venue" id="venue" value="{{ old('venue', $event->venue ?? '') }}" 
               class="w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm text-gray-900" 
               required placeholder="e.g. GBK Stadium">
        @error('venue')
            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- City -->
    <div>
        <label for="city" class="block font-medium text-sm text-gray-700 mb-1">City <span class="text-red-500">*</span></label>
        <input type="text" name="city" id="city" value="{{ old('city', $event->city ?? '') }}" 
               class="w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm text-gray-900" 
               required placeholder="e.g. Jakarta">
        @error('city')
            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <div class="col-span-1 md:col-span-2">
        <label for="google_maps_url" class="block font-medium text-sm text-gray-700 mb-1">Google Maps Link</label>
        <input type="url" name="google_maps_url" id="google_maps_url" value="{{ old('google_maps_url', $event->google_maps_url ?? '') }}"
               class="w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm text-gray-900"
               placeholder="https://maps.google.com/...">
        @error('google_maps_url')
            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Dates Section -->
    <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-100 pt-4 mt-2">
        <div>
            <label for="event_start_date" class="block font-medium text-sm text-gray-700 mb-1">Event Start <span class="text-red-500">*</span></label>
            <input type="datetime-local" name="event_start_date" id="event_start_date" 
                   value="{{ old('event_start_date', (isset($event) && $event->event_start_date) ? $event->event_start_date->format('Y-m-d\TH:i') : '') }}" 
                   class="w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm text-gray-900" 
                   required>
            @error('event_start_date')
                <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="event_end_date" class="block font-medium text-sm text-gray-700 mb-1">Event End <span class="text-red-500">*</span></label>
            <input type="datetime-local" name="event_end_date" id="event_end_date" 
                   value="{{ old('event_end_date', (isset($event) && $event->event_end_date) ? $event->event_end_date->format('Y-m-d\TH:i') : '') }}" 
                   class="w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm text-gray-900" 
                   required>
            @error('event_end_date')
                <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="gate_open_at" class="block font-medium text-sm text-gray-700 mb-1">Gate Open <span class="text-red-500">*</span></label>
            <input type="datetime-local" name="gate_open_at" id="gate_open_at" 
                   value="{{ old('gate_open_at', (isset($event) && $event->gate_open_at) ? $event->gate_open_at->format('Y-m-d\TH:i') : '') }}" 
                   class="w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm text-gray-900" 
                   required>
            @error('gate_open_at')
                <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="gate_close_at" class="block font-medium text-sm text-gray-700 mb-1">Gate Close <span class="text-red-500">*</span></label>
            <input type="datetime-local" name="gate_close_at" id="gate_close_at" 
                   value="{{ old('gate_close_at', (isset($event) && $event->gate_close_at) ? $event->gate_close_at->format('Y-m-d\TH:i') : '') }}" 
                   class="w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm text-gray-900" 
                   required>
            @error('gate_close_at')
                <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Banner Image -->
    <div class="col-span-1 md:col-span-2 border-t border-gray-100 pt-4 mt-2">
        <label for="banner_image" class="block font-medium text-sm text-gray-700 mb-1">Banner Image</label>
        <div class="flex items-start gap-4">
            <div class="w-32 h-20 bg-slate-950 rounded-lg border border-dashed border-gray-300 flex items-center justify-center overflow-hidden relative">
                @if(isset($event) && ($event->banner_image || $event->background_image))
                    @php $formBanner = $event->banner_image ?? $event->background_image; @endphp
                    <img src="{{ asset('storage/' . $formBanner) }}" alt="" aria-hidden="true" class="absolute inset-0 w-full h-full object-cover blur-xs scale-110 opacity-50 brightness-75 pointer-events-none">
                    <img src="{{ asset('storage/' . $formBanner) }}" alt="Banner" class="relative z-10 max-w-full max-h-full object-contain">
                @else
                    <i class="fas fa-cloud-upload-alt text-gray-400 text-xl"></i>
                @endif
            </div>
            <div class="flex-1">
                <input type="file" name="banner_image" id="banner_image" class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                <p class="mt-1 text-xs text-gray-500 italic">Recommended size: 1200x600px. Max: 2MB.</p>
            </div>
        </div>
        @error('banner_image')
            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Syarat & Ketentuan -->
    <div class="col-span-1 md:col-span-2 border-t border-gray-100 pt-4 mt-2">
        <label for="terms_conditions" class="block font-medium text-sm text-gray-700 mb-1">Syarat & Ketentuan (S&K)</label>
        
        <input id="terms_conditions_input" type="hidden" name="terms_conditions" value="{{ old('terms_conditions', $event->terms_conditions ?? '') }}">
        <trix-editor input="terms_conditions_input" class="trix-content min-h-[250px] bg-white rounded-lg border-gray-300 focus-within:border-purple-500 focus:ring-purple-500 shadow-sm text-gray-900 transition-all px-4 py-3" placeholder="Tuliskan S&K khusus event ini..."></trix-editor>
        
        <p class="mt-1 text-xs text-gray-500 italic">Kosongkan jika ingin menggunakan S&K Global Tenant. S&K ini akan muncul di e-voucher.</p>
        @error('terms_conditions')
            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Evoucher Info -->
    <div class="col-span-1 md:col-span-2 border-t border-gray-100 pt-4 mt-2">
        <label for="evoucher_info" class="block font-medium text-sm text-gray-700 mb-1">Informasi E-Voucher Tambahan (Dinamis)</label>
        <textarea name="evoucher_info" id="evoucher_info" rows="3" 
                  class="w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm text-gray-900" 
                  placeholder="e.g. Gabung ke Group Whatsapp Peserta melalui link berikut: https://chat.whatsapp.com/...">{{ old('evoucher_info', $event->evoucher_info ?? '') }}</textarea>
        <p class="mt-1 text-xs text-gray-500 italic">Informasi ini akan ditampilkan di bagian paling atas halaman E-Voucher peserta. Link URL akan otomatis menjadi link klik.</p>
        @error('evoucher_info')
            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <div class="col-span-1 md:col-span-2">
        @include('organizer.events._wristband_designer', ['event' => $event ?? null])
    </div>
</div>
