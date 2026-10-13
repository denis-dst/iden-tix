@php
    $ticketCategoryName = $ticket->category?->name ?? $category->name;
    $homeInitial = collect(explode(' ', $event->name))->filter()->map(fn($word) => strtoupper(substr($word, 0, 1)))->take(3)->join('');
    $bandColor = $normalizeHex($ticket->category?->hex_color ?? $category->hex_color ?? '#d71920');
    $bandPalette = $contrastPalette($bandColor);
@endphp

@if(($activeMode ?? 'default') === 'custom' && isset($wristbandTemplate) && $wristbandTemplate)
    {{-- Custom Mode: wristband with custom uploaded background and dynamic columns --}}
    <div class="wristband-custom" style="{{ $wristbandTemplate->getBackgroundImageUrl() ? "background-image: url('{$wristbandTemplate->getBackgroundImageUrl()}');" : "background-color: #ffffff;" }}">
        @php $cols = $wristbandTemplate->getMergedColumnsConfig(); @endphp
        @foreach($cols as $cKey => $col)
            @if(!empty($col['enabled']))
                @php
                    $x = (float) str_replace(',', '.', (string)($col['x'] ?? 0));
                    $y = (float) str_replace(',', '.', (string)($col['y'] ?? 0));
                    $color = $col['color'] ?? '#111827';
                    $fontSize = $col['font_size'] ?? '6pt';
                    $fontWeight = $col['font_weight'] ?? '700';
                    $align = $col['align'] ?? 'left';
                @endphp

                @if($cKey === 'qr_code')
                    @php $qrSizeMm = (float) str_replace(',', '.', (string)($col['qr_size'] ?? 14)); @endphp
                    <div class="wb-element" style="left: {{ $x }}mm; top: {{ $y }}mm; width: {{ $qrSizeMm }}mm; height: {{ $qrSizeMm }}mm; display: flex; align-items: center; justify-content: center; background: #ffffff; padding: 0.5mm; border-radius: 1px; box-shadow: 0 0 1px rgba(0,0,0,0.15);">
                        {!! QrCode::size((int)($qrSizeMm * 3.78))->margin(0)->generate($ticket->ticket_code) !!}
                    </div>
                @elseif($cKey === 'wristband_code')
                    <div class="wb-element" style="left: {{ $x }}mm; top: {{ $y }}mm; font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }}; color: {{ $color }}; text-align: {{ $align }}; white-space: nowrap; line-height: 1;">
                        {{ $ticket->ticket_code }}
                    </div>
                @elseif($cKey === 'category_name')
                    <div class="wb-element" style="left: {{ $x }}mm; top: {{ $y }}mm; font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }}; color: {{ $color }}; text-align: {{ $align }}; white-space: nowrap; text-transform: uppercase; line-height: 1;">
                        {{ $ticketCategoryName }}
                    </div>
                @elseif($cKey === 'event_name')
                    <div class="wb-element" style="left: {{ $x }}mm; top: {{ $y }}mm; font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }}; color: {{ $color }}; text-align: {{ $align }}; white-space: nowrap; text-transform: uppercase; line-height: 1; max-width: 90mm; overflow: hidden; text-overflow: ellipsis;">
                        {{ $event->name }}
                    </div>
                @elseif($cKey === 'event_date')
                    <div class="wb-element" style="left: {{ $x }}mm; top: {{ $y }}mm; font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }}; color: {{ $color }}; text-align: {{ $align }}; white-space: nowrap; text-transform: uppercase; line-height: 1;">
                        {{ $event->event_start_date->format('d M Y') }}
                        @if($event->gate_open_at) • {{ $event->gate_open_at->format('H.i') }} WIB @endif
                    </div>
                @elseif($cKey === 'venue')
                    <div class="wb-element" style="left: {{ $x }}mm; top: {{ $y }}mm; font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }}; color: {{ $color }}; text-align: {{ $align }}; white-space: nowrap; text-transform: uppercase; line-height: 1; max-width: 90mm; overflow: hidden; text-overflow: ellipsis;">
                        {{ $event->venue }}{{ $event->city ? ', ' . $event->city : '' }}
                    </div>
                @elseif($cKey === 'seat_number')
                    <div class="wb-element" style="left: {{ $x }}mm; top: {{ $y }}mm; font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }}; color: {{ $color }}; text-align: {{ $align }}; white-space: nowrap; line-height: 1;">
                        #{{ sprintf('%04d', $ticket->index ?? 1) }}
                    </div>
                @elseif($cKey === 'price')
                    <div class="wb-element" style="left: {{ $x }}mm; top: {{ $y }}mm; font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }}; color: {{ $color }}; text-align: {{ $align }}; white-space: nowrap; line-height: 1;">
                        {{ $category->price > 0 ? 'Rp ' . number_format($category->price, 0, ',', '.') : 'FREE' }}
                    </div>
                @elseif($cKey === 'custom_text')
                    <div class="wb-element" style="left: {{ $x }}mm; top: {{ $y }}mm; font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }}; color: {{ $color }}; text-align: {{ $align }}; white-space: nowrap; line-height: 1;">
                        {{ $col['custom_value'] ?? 'NON-REFUNDABLE' }}
                    </div>
                @endif
            @endif
        @endforeach
    </div>
@else
    {{-- Default Mode: Standard IdenTix Wristband Layout --}}
    <div class="wristband">
        <div class="blank-space"></div>

        <div class="league-logo">
            @if($leagueLogo)
                <img src="{{ $leagueLogo }}" alt="League Logo">
            @else
                <div>SUPER<br>LEAGUE</div>
            @endif
        </div>

        <div class="ticket-band" style="--band-primary: {{ $bandColor }}; --band-dark: {{ $bandPalette['dark'] }}; --band-light: {{ $bandPalette['light'] }}; --band-edge: {{ $bandPalette['edge'] }}; --band-panel: {{ $bandPalette['panel'] }}; --band-text: {{ $bandPalette['text'] }}; --band-accent-text: {{ $bandPalette['accentText'] }}; --band-text-shadow: {{ $bandPalette['shadow'] }};">
            <div class="qr-section">
                {!! QrCode::size(62)->margin(0)->generate($ticket->ticket_code) !!}
                <div class="code">{{ $ticket->ticket_code }}</div>
            </div>

            <div class="category-section">{{ $ticketCategoryName }}</div>

            <div class="club-logo">
                @if($homeLogo)
                    <img src="{{ $homeLogo }}" alt="Home Club Logo">
                @else
                    <div class="club-placeholder">{{ $homeInitial ?: 'HOME' }}</div>
                @endif
            </div>

            <div class="event-section">
                <div class="league-title">{{ $leagueName }}</div>
                <div class="event-name">{{ $event->name }}</div>
                <div class="event-details">
                    {{ $event->event_start_date->format('l, d M Y') }}
                    @if($event->gate_open_at)
                        - KICK OFF {{ $event->gate_open_at->format('H.i') }} WIB
                    @endif
                    <br>{{ $event->venue }}{{ $event->city ? ', ' . $event->city : '' }}
                </div>
            </div>

            <div class="club-logo">
                @if($awayLogo)
                    <img src="{{ $awayLogo }}" alt="Away Club Logo">
                @else
                    <div class="club-placeholder">AWAY</div>
                @endif
            </div>

            <div class="sponsor-section">
                @forelse($sponsorLogos as $sponsorLogo)
                    @php $sponsorLogoUrl = $assetUrl($sponsorLogo); @endphp
                    @if($sponsorLogoUrl)
                        <div class="sponsor-item"><img src="{{ $sponsorLogoUrl }}" alt="Sponsor Logo"></div>
                    @endif
                @empty
                    @foreach($sponsorNames as $sponsorName)
                        <div class="sponsor-item">{{ $sponsorName }}</div>
                    @endforeach
                @endforelse
            </div>
        </div>

        <div class="right-blank"></div>
    </div>
@endif
