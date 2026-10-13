<x-app-layout>
    <x-slot name="title">Edit Event: {{ $event->name }}</x-slot>

    <div class="bg-white rounded-sm border-t-4 border-purple-600 shadow-md overflow-hidden max-w-5xl mx-auto">
        <div class="px-6 py-4 border-b border-[#dee2e6] flex justify-between items-center bg-white">
            <h3 class="text-lg font-bold text-gray-800">Edit Event</h3>
            <a href="{{ route('superadmin.events.index') }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded shadow-sm transition">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
            </a>
        </div>
        
        <div class="p-6">
            <form action="{{ route('superadmin.events.update', $event) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                @include('superadmin.events.form')
                
                <div class="mt-8 flex justify-end gap-3 border-t border-gray-100 pt-5">
                    <a href="{{ route('superadmin.events.index') }}" class="px-5 py-2.5 bg-orange-50 border border-orange-200 text-orange-700 font-bold rounded-lg hover:bg-orange-100 transition shadow-sm">Cancel</a>
                    <button type="submit" class="px-5 py-2.5 bg-orange-600 text-white font-bold rounded-lg hover:bg-orange-700 transition shadow-md flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        Update Event
                    </button>
                </div>
            </form>

            @if(isset($event))
            <!-- Ticket Categories Section -->
            <div class="mt-12 border-t border-gray-100 pt-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800">Ticket Categories</h3>
                    <a href="{{ route('organizer.events.categories.create', $event) }}" class="px-4 py-2 bg-orange-600 text-white rounded-lg font-bold hover:bg-orange-700 transition text-sm">
                        + Add Category
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($event->ticketCategories as $category)
                    <div class="p-4 border border-gray-100 rounded-xl flex items-center justify-between bg-gray-50/50">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-10 rounded-full" style="background-color: {{ $category->hex_color ?? '#6366F1' }}"></div>
                            <div>
                                <h4 class="font-bold text-gray-800">{{ $category->name }}</h4>
                                <p class="text-xs text-gray-500">Rp {{ number_format($category->price, 0, ',', '.') }} | {{ $category->quota }} Quota</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('organizer.categories.print-wristbands', $category) }}" target="_blank" class="p-1.5 bg-blue-50 text-blue-600 rounded-md border border-blue-100 hover:bg-blue-100" title="Print Wristbands (F4 & A3+)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            </a>
                            <a href="{{ route('organizer.categories.export-wristbands-docx', ['category' => $category, 'paper' => 'f4']) }}" class="p-1.5 bg-sky-50 text-sky-600 rounded-md border border-sky-100 hover:bg-sky-100" title="Export Word (.docx) F4 & A3+">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </a>
                            <a href="{{ route('organizer.events.categories.edit', [$event, $category]) }}" class="p-1.5 bg-gray-50 text-gray-600 rounded-md border border-gray-200 hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
