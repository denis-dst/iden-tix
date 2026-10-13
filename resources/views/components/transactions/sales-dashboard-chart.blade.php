@props([
    'totalQuota' => 0,
    'totalSold' => 0,
    'totalUnsold' => 0,
    'totalRevenue' => 0,
    'categoryStats' => collect(),
    'throughputChart' => []
])

<div style="background-color: #1a1a22 !important; border: 1.5px solid rgba(249, 115, 22, 0.45) !important; border-radius: 28px !important; padding: 28px !important; box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.6) !important; color: #ffffff !important; position: relative; overflow: hidden; margin-bottom: 24px;">

    <!-- Top Row: 4 Metric Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; position: relative; z-index: 2;">
        
        <!-- Card 1: KUOTA -->
        <div style="background-color: #24252f !important; border: 1.5px solid rgba(249, 115, 22, 0.5) !important; border-radius: 18px !important; padding: 18px 22px !important; box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3) !important;">
            <div style="color: #94a3b8 !important; font-size: 11px !important; font-weight: 800 !important; letter-spacing: 0.18em !important; text-transform: uppercase !important; margin-bottom: 8px !important;">
                KUOTA
            </div>
            <div style="color: #ffffff !important; font-size: 28px !important; font-weight: 900 !important; font-family: 'Outfit', sans-serif !important; line-height: 1.2 !important;">
                {{ number_format($totalQuota, 0, ',', '.') }}
            </div>
        </div>

        <!-- Card 2: TERJUAL -->
        <div style="background-color: #24252f !important; border: 1.5px solid rgba(249, 115, 22, 0.5) !important; border-radius: 18px !important; padding: 18px 22px !important; box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3) !important;">
            <div style="color: #94a3b8 !important; font-size: 11px !important; font-weight: 800 !important; letter-spacing: 0.18em !important; text-transform: uppercase !important; margin-bottom: 8px !important;">
                TERJUAL
            </div>
            <div style="color: #ffffff !important; font-size: 28px !important; font-weight: 900 !important; font-family: 'Outfit', sans-serif !important; line-height: 1.2 !important;">
                {{ number_format($totalSold, 0, ',', '.') }}
            </div>
        </div>

        <!-- Card 3: BELUM TERJUAL -->
        <div style="background-color: #24252f !important; border: 1.5px solid rgba(249, 115, 22, 0.5) !important; border-radius: 18px !important; padding: 18px 22px !important; box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3) !important;">
            <div style="color: #94a3b8 !important; font-size: 11px !important; font-weight: 800 !important; letter-spacing: 0.18em !important; text-transform: uppercase !important; margin-bottom: 8px !important;">
                BELUM TERJUAL
            </div>
            <div style="color: #ffffff !important; font-size: 28px !important; font-weight: 900 !important; font-family: 'Outfit', sans-serif !important; line-height: 1.2 !important;">
                {{ number_format($totalUnsold, 0, ',', '.') }}
            </div>
        </div>

        <!-- Card 4: TOTAL PENDAPATAN -->
        <div style="background-color: #24252f !important; border: 1.5px solid rgba(249, 115, 22, 0.5) !important; border-radius: 18px !important; padding: 18px 22px !important; box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3) !important;">
            <div style="color: #94a3b8 !important; font-size: 11px !important; font-weight: 800 !important; letter-spacing: 0.18em !important; text-transform: uppercase !important; margin-bottom: 8px !important;">
                TOTAL PENDAPATAN
            </div>
            <div style="color: #ffffff !important; font-size: 28px !important; font-weight: 900 !important; font-family: 'Outfit', sans-serif !important; line-height: 1.2 !important; white-space: nowrap !important; overflow: hidden; text-overflow: ellipsis;" title="Rp {{ number_format($totalRevenue, 0, ',', '.') }}">
                Rp,{{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
        </div>

    </div>

    <!-- Bottom Section: Sales Throughput Chart + Kategori Tiket Breakdown -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 28px; margin-top: 28px; padding-top: 24px; border-top: 1px solid rgba(255, 255, 255, 0.1) !important; align-items: start; position: relative; z-index: 2;">
        
        <!-- Left: Sales Throughput Chart -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <span style="color: #ffffff !important; font-size: 15px !important; font-weight: 800 !important; font-family: 'Outfit', sans-serif !important; letter-spacing: 0.02em !important;">
                    Sales throughput
                </span>
                <span style="color: #64748b !important; font-size: 11px !important; font-weight: 700 !important; text-transform: uppercase !important; letter-spacing: 0.1em !important;">
                    Aktivitas Penjualan
                </span>
            </div>

            <!-- Chart Box -->
            <div style="background-color: #15151c !important; border: 1px solid rgba(255, 255, 255, 0.08) !important; border-radius: 18px !important; padding: 20px 16px 14px 16px !important;">
                <div style="height: 160px; display: flex; align-items: flex-end; justify-content: space-between; gap: 8px; padding-bottom: 8px; border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
                    @forelse($throughputChart as $bar)
                        <div class="throughput-bar-group" style="flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; justify-content: flex-end; position: relative;">
                            <!-- Floating Tooltip on hover -->
                            <div class="throughput-tooltip" style="position: absolute; top: -38px; left: 50%; transform: translateX(-50%); background-color: #0f172a; border: 1px solid #38bdf8; color: #ffffff; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; white-space: nowrap; z-index: 30; pointer-events: none; opacity: 0; transition: opacity 0.2s, transform 0.2s; box-shadow: 0 8px 16px rgba(0,0,0,0.5);">
                                <span style="color: #38bdf8 !important;">{{ number_format($bar['count'], 0, ',', '.') }} Tiket</span>
                                <div style="font-size: 9px; color: #94a3b8;">{{ $bar['label'] }}</div>
                            </div>

                            <!-- Bar with vibrant cyan-blue electric gradient -->
                            <div style="width: 100%; max-width: 32px; height: {{ $bar['height_pct'] }}%; min-height: 14px; background: linear-gradient(180deg, #00d4ff 0%, #0066ff 100%) !important; border-radius: 8px 8px 3px 3px !important; box-shadow: 0 0 12px rgba(0, 160, 255, 0.5) !important; cursor: pointer; transition: all 0.25s ease;"
                                 onmouseover="this.style.boxShadow='0 0 20px rgba(0, 212, 255, 0.9)'; this.style.filter='brightness(1.25)';"
                                 onmouseout="this.style.boxShadow='0 0 12px rgba(0, 160, 255, 0.5)'; this.style.filter='none';">
                            </div>

                            <!-- Date Label -->
                            <span style="color: #94a3b8 !important; font-size: 10px !important; font-weight: 700 !important; margin-top: 8px !important; text-align: center; width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $bar['label'] }}
                            </span>
                        </div>
                    @empty
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 12px; font-weight: 700;">
                            Belum ada aktivitas penjualan
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Kategori Tiket Breakdown Table -->
        <div>
            <!-- Table Header -->
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 10px; color: #94a3b8 !important; font-size: 11px !important; font-weight: 800 !important; letter-spacing: 0.12em !important; text-transform: uppercase !important; padding-bottom: 12px !important; border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;">
                <div>KATEGORI TIKET</div>
                <div style="text-align: right;">KUOTA</div>
                <div style="text-align: right;">TERJUAL</div>
                <div style="text-align: right;">BELUM TERJUAL</div>
            </div>

            <!-- Table Rows List -->
            <div class="gentix-dark-scroll" style="max-height: 190px; overflow-y: auto; padding-top: 6px; padding-right: 4px;">
                @forelse($categoryStats as $cat)
                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 10px; align-items: center; padding: 10px 4px; border-bottom: 1px solid rgba(255, 255, 255, 0.04); transition: background-color 0.2s;"
                         onmouseover="this.style.backgroundColor='rgba(255,255,255,0.05)';"
                         onmouseout="this.style.backgroundColor='transparent';">
                        
                        <!-- Category Name + Colored Indicator Dot -->
                        <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #0088ff !important; box-shadow: 0 0 10px #0088ff !important; flex-shrink: 0;"></span>
                            <span style="color: #ffffff !important; font-size: 13px !important; font-weight: 800 !important; text-transform: uppercase !important; letter-spacing: 0.04em !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $cat->name }}">
                                {{ $cat->name }}
                            </span>
                        </div>

                        <!-- Kuota -->
                        <div style="color: #38bdf8 !important; font-size: 13px !important; font-weight: 800 !important; font-family: monospace !important; text-align: right;">
                            {{ number_format($cat->quota, 0, ',', '.') }}
                        </div>

                        <!-- Terjual -->
                        <div style="color: #38bdf8 !important; font-size: 13px !important; font-weight: 800 !important; font-family: monospace !important; text-align: right;">
                            {{ number_format($cat->sold, 0, ',', '.') }}
                        </div>

                        <!-- Belum Terjual -->
                        <div style="color: #38bdf8 !important; font-size: 13px !important; font-weight: 800 !important; font-family: monospace !important; text-align: right;">
                            {{ number_format($cat->unsold, 0, ',', '.') }}
                        </div>
                    </div>
                @empty
                    <div style="padding: 30px; text-align: center; color: #64748b; font-size: 12px; font-weight: 700;">
                        Tidak ada kategori tiket yang sesuai filter.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

<style>
.throughput-bar-group:hover .throughput-tooltip {
    opacity: 1 !important;
    transform: translateX(-50%) translateY(-4px) !important;
}
.gentix-dark-scroll::-webkit-scrollbar {
    width: 6px;
}
.gentix-dark-scroll::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.04);
    border-radius: 8px;
}
.gentix-dark-scroll::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
}
.gentix-dark-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.35);
}
</style>
