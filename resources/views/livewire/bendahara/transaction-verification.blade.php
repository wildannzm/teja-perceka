<div>
    <div class="flex h-full w-full flex-col gap-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">Verifikasi Harian Bendahara</h1>
        </div>

        <!-- Filter Pencarian -->
        <div class="bg-white dark:bg-neutral-900 p-6 rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:select wire:model.live="unit_id" label="Pilih Unit Usaha yang Ingin Diverifikasi">
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                    @endforeach
                </flux:select>
                <flux:input wire:model.live="date" type="date" label="Pilih Tanggal Transaksi" />
            </div>
        </div>

        @if(!$journal_id)
            <div class="bg-yellow-50 dark:bg-yellow-900/30 p-6 rounded-xl border border-yellow-200 dark:border-yellow-700/50 text-center">
                <flux:icon.document-magnifying-glass class="w-12 h-12 text-yellow-500 mx-auto mb-3" />
                <h3 class="text-lg font-medium text-yellow-800 dark:text-yellow-500">Data Tidak Ditemukan</h3>
                <p class="text-yellow-600 dark:text-yellow-400 mt-1">Belum ada laporan harian yang diinput oleh Unit <b>{{ $selectedUnit->name ?? '' }}</b> pada tanggal ini.</p>
            </div>
        @else
            <!-- Form Dinamis -->
            <div class="bg-white dark:bg-neutral-900 p-6 rounded-xl border border-emerald-200 dark:border-emerald-700 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 bg-emerald-500 text-white px-4 py-1 rounded-bl-lg text-sm font-bold shadow-sm">
                    MODE VERIFIKASI
                </div>
                
                <h2 class="text-xl font-bold mb-6 text-neutral-800 dark:text-neutral-200">
                    Data Harian: <span class="text-emerald-600 dark:text-emerald-400">{{ $selectedUnit->name ?? '' }}</span>
                </h2>

                <div class="space-y-8">
                    <!-- SEKSI PEMASUKAN DINAMIS -->
                    <div>
                        <h3 class="text-lg font-bold text-emerald-700 dark:text-emerald-400 mb-4 border-b pb-2">A. RINCIAN PEMASUKAN</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            
                            @php 
                                $uName = strtolower($selectedUnit->name ?? '');
                            @endphp

                            {{-- SAWAH BENGKOK --}}
                            @if(str_contains($uName, 'sawah bengkok'))
                                <flux:input wire:model.live="form_data.tiket_dewasa" type="number" min="0" label="Tiket Dewasa @ Rp 10.000" />
                                <flux:input wire:model.live="form_data.tiket_anak" type="number" min="0" label="Tiket Anak @ Rp 5.000" />
                                <flux:input wire:model.live="form_data.parkir_mobil" type="number" min="0" label="Parkir Mobil @ Rp 5.000" />
                                <flux:input wire:model.live="form_data.parkir_motor" type="number" min="0" label="Parkir Motor @ Rp 2.000" />
                                <flux:input wire:model.live="form_data.sewa_ban" type="number" min="0" label="Sewa Ban @ Rp 10.000" />
                                <flux:input wire:model.live="form_data.sewa_plampung" type="number" min="0" label="Sewa Plampung @ Rp 10.000" />
                                <flux:input wire:model.live="form_data.kios" type="number" min="0" label="Kios @ Rp 150.000" />
                                <flux:input wire:model.live="form_data.kaki_lima" type="number" min="0" label="Kaki Lima @ Rp 20.000" />
                            @endif

                            {{-- SITU CIRANCA --}}
                            @if(str_contains($uName, 'situ ciranca'))
                                <flux:input wire:model.live="form_data.tiket_dewasa" type="number" min="0" label="Tiket Dewasa @ Rp 10.000" />
                                <flux:input wire:model.live="form_data.tiket_anak" type="number" min="0" label="Tiket Anak @ Rp 5.000" />
                                <flux:input wire:model.live="form_data.parkir_mobil" type="number" min="0" label="Parkir Mobil @ Rp 5.000" />
                                <flux:input wire:model.live="form_data.parkir_motor" type="number" min="0" label="Parkir Motor @ Rp 3.000" />
                                <flux:input wire:model.live="form_data.sewa_ban" type="number" min="0" label="Sewa Ban @ Rp 10.000" />
                                <flux:input wire:model.live="form_data.sewa_plampung" type="number" min="0" label="Sewa Plampung @ Rp 10.000" />
                                <flux:input wire:model.live="form_data.sewa_bebek" type="number" min="0" label="Sewa Bebek @ Rp 10.000" />
                            @endif

                            {{-- BUKIT SAMPORA --}}
                            @if(str_contains($uName, 'bukit sampora'))
                                <flux:input wire:model.live="form_data.tiket" type="number" min="0" label="Tiket Masuk @ Rp 15.000" />
                            @endif

                            {{-- BUPER CIRANCA --}}
                            @if(str_contains($uName, 'buper'))
                                <flux:input wire:model.live="form_data.tiket" type="number" min="0" label="Tiket Masuk @ Rp 20.000" />
                            @endif

                            {{-- TPS --}}
                            @if(str_contains($uName, 'tps'))
                                <flux:input wire:model.live="form_data.sampah_rumah" type="number" min="0" label="Sampah Rumah @ Rp 15.000" />
                                <flux:input wire:model.live="form_data.sampah_kegiatan" type="number" min="0" label="Kegiatan @ Rp 100.000" />
                                <flux:input wire:model.live="form_data.sampah_wisata_nominal" type="number" min="0" label="Sampah Wisata (Rp Custom)" placeholder="Contoh: 50000" />
                            @endif
                        </div>

                        <div class="mt-4 p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg text-right">
                            <span class="text-sm text-emerald-700 dark:text-emerald-400">Total Pemasukan Unit:</span>
                            <span class="text-2xl font-bold text-emerald-800 dark:text-emerald-300 block">Rp {{ number_format($total_pemasukan, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- SEKSI PENGELUARAN (Semua Unit sama) -->
                    <div>
                        <h3 class="text-lg font-bold text-red-600 dark:text-red-400 mb-4 border-b pb-2">B. RINCIAN PENGELUARAN (Rp)</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <flux:input wire:model.live="form_data.setoran_parkir" type="number" min="0" label="Setoran Parkir" />
                            <flux:input wire:model.live="form_data.setoran_lahan" type="number" min="0" label="Setoran Lahan" />
                            <flux:input wire:model.live="form_data.bagi_hasil" type="number" min="0" label="Bagi Hasil Tiket" />
                            <flux:input wire:model.live="form_data.nota_warung" type="number" min="0" label="Nota Warung" />
                            <flux:input wire:model.live="form_data.pengeluaran_lain" type="number" min="0" label="Pengeluaran Lain-lain" />
                        </div>

                        <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg text-right">
                            <span class="text-sm text-red-700 dark:text-red-400">Total Pengeluaran Unit:</span>
                            <span class="text-2xl font-bold text-red-800 dark:text-red-300 block">Rp {{ number_format($total_pengeluaran, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <hr class="border-neutral-200 dark:border-neutral-700">
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-sm text-neutral-500">Uang Fisik yang Diterima (Selisih):</span>
                            <div class="text-3xl font-black {{ $selisih >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">
                                Rp {{ number_format($selisih, 0, ',', '.') }}
                            </div>
                        </div>
                        <flux:button wire:click="simpanVerifikasi" variant="primary" icon="check-badge" class="px-8 py-3 shadow-lg">Validasi & Simpan Perubahan</flux:button>
                    </div>

                </div>
            </div>
        @endif
    </div>
</div>
