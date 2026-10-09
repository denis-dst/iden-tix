<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Name -->
    <div class="col-span-1 md:col-span-2">
        <label for="name" class="block font-medium text-sm text-gray-700 mb-1">Organizer Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="name" value="{{ old('name', $tenant->name ?? '') }}" 
               class="w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm text-gray-900" 
               required placeholder="e.g. IdenTix Organization">
        @error('name')
            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Email -->
    <div>
        <label for="email" class="block font-medium text-sm text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
        <input type="email" name="email" id="email" value="{{ old('email', $tenant->email ?? '') }}" 
               class="w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm text-gray-900" 
               required placeholder="contact@organizer.com">
        @error('email')
            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Phone -->
    <div>
        <label for="phone" class="block font-medium text-sm text-gray-700 mb-1">Phone Number</label>
        <input type="text" name="phone" id="phone" value="{{ old('phone', $tenant->phone ?? '') }}" 
               class="w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm text-gray-900" 
               placeholder="+62 812 3456 7890">
        @error('phone')
            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Logo -->
    <div class="col-span-1 md:col-span-2">
        <label for="logo" class="block font-medium text-sm text-gray-700 mb-1">Organizer / Home Club Logo</label>
        @if(isset($tenant) && $tenant->logo)
            <div class="mb-3 h-20 w-32 rounded-lg bg-gray-50 border border-gray-100 flex items-center justify-center p-2">
                <img src="{{ Storage::url($tenant->logo) }}" class="max-h-full max-w-full object-contain">
            </div>
        @endif
        <input type="file" name="logo" id="logo"
               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 transition cursor-pointer">
        <p class="mt-1 text-xs text-gray-500">Dipakai sebagai default logo club tuan rumah pada layout wristband jika logo khusus kategori tidak diisi.</p>
        @error('logo')
            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Status -->
    <div class="col-span-1 md:col-span-2">
        <label for="status" class="block font-medium text-sm text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
        <select name="status" id="status" class="w-full md:w-1/2 border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm text-gray-900 font-medium" required>
            <option value="active" {{ old('status', $tenant->status ?? '') === 'active' ? 'selected' : '' }}>🟢 Active</option>
            <option value="inactive" {{ old('status', $tenant->status ?? '') === 'inactive' ? 'selected' : '' }}>⚪ Inactive</option>
            <option value="suspended" {{ old('status', $tenant->status ?? '') === 'suspended' ? 'selected' : '' }}>🔴 Suspended</option>
        </select>
        <p class="mt-1 text-xs text-gray-500">Only active organizers can create and manage events.</p>
        @error('status')
            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Address -->
    <div class="col-span-1 md:col-span-2">
        <label for="address" class="block font-medium text-sm text-gray-700 mb-1">Address</label>
        <textarea name="address" id="address" rows="3" 
                  class="w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm text-gray-900" 
                  placeholder="Full office or operating address...">{{ old('address', $tenant->address ?? '') }}</textarea>
        @error('address')
            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Initial Admin Password (Only on Create) -->
    @if(!isset($tenant))
    <div class="col-span-1 md:col-span-2 border-t border-gray-100 pt-4 mt-2">
        <h4 class="text-sm font-bold text-gray-800 mb-4">Initial Admin Account</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="password" class="block font-medium text-sm text-gray-700 mb-1">Admin Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" id="password" 
                       class="w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm text-gray-900" 
                       required placeholder="Minimum 8 characters">
                <p class="mt-1 text-xs text-gray-500">This password will be used to log into the organizer dashboard.</p>
                @error('password')
                    <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
    @endif
</div>
