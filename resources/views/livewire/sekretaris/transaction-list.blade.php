<div>
    <div class="flex h-full w-full flex-col gap-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-neutral-900">Kelola Jurnal Transaksi</h1>
        </div>

        <div class="bg-white p-6 rounded-xl border border-neutral-200 flex-1">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-neutral-600 border border-neutral-200">
                    <thead class="text-xs text-neutral-700 uppercase bg-neutral-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 border-b">Tanggal</th>
                            <th scope="col" class="px-4 py-3 border-b">Nomor Bukti</th>
                            <th scope="col" class="px-4 py-3 border-b">Unit Usaha</th>
                            <th scope="col" class="px-4 py-3 border-b">Keterangan</th>
                            <th scope="col" class="px-4 py-3 border-b">Akun (COA)</th>
                            <th scope="col" class="px-4 py-3 text-right border-b">Debit</th>
                            <th scope="col" class="px-4 py-3 text-right border-b">Kredit</th>
                            <th scope="col" class="px-4 py-3 text-center border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($journals as $nomorBukti => $group)
                            @foreach ($group as $jurnal)
                                <tr class="bg-white hover:bg-neutral-50 :bg-neutral-800 border-b">
                                    @if ($loop->first)
                                        <td rowspan="{{ $group->count() }}"
                                            class="px-4 py-4 border-r align-top whitespace-nowrap">
                                            {{ $jurnal->tanggal->translatedFormat('d F Y') }}</td>
                                        <td rowspan="{{ $group->count() }}"
                                            class="px-4 py-4 border-r align-top whitespace-nowrap font-mono">
                                            {{ $jurnal->nomor_bukti }}</td>
                                        <td rowspan="{{ $group->count() }}" class="px-4 py-4 border-r align-top">
                                            {{ $jurnal->unitWisata->nama ?? '-' }}</td>
                                        <td rowspan="{{ $group->count() }}" class="px-4 py-4 border-r align-top">
                                            {{ $jurnal->keterangan }}</td>
                                    @endif
                                    <td class="px-4 py-4">
                                        {{ $jurnal->kodeAkun->kode ?? '-' }} -
                                        {{ $jurnal->kodeAkun->nama ?? 'Akun Tidak Ditemukan' }}
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        {{ $jurnal->debet > 0 ? number_format($jurnal->debet, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        {{ $jurnal->kredit > 0 ? number_format($jurnal->kredit, 0, ',', '.') : '-' }}
                                    </td>
                                    @if ($loop->first)
                                        <td rowspan="{{ $group->count() }}"
                                            class="px-4 py-4 border-l align-middle text-center">
                                            <flux:button wire:click="confirmDelete({{ $jurnal->id }})"
                                                variant="danger" size="sm" icon="trash">Hapus</flux:button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-neutral-500">
                                    Belum ada data transaksi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div wire:click="$set('showDeleteModal', false)" class="absolute inset-0 bg-zinc-900/40 backdrop-blur-sm transition-opacity"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col gap-0 overflow-hidden z-10">
                <div class="px-6 py-5 flex flex-col gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900">Konfirmasi Hapus</h2>
                        <div class="mt-2 text-sm text-zinc-600">
                            <p>Yakin ingin menghapus data ini?</p>
                            <p>Data yang dihapus tidak dapat dikembalikan dan mungkin mempengaruhi kalkulasi laporan.</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showDeleteModal', false)" class="px-4 py-2 rounded-xl border border-zinc-200 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 transition-colors">
                            Batal
                        </button>
                        <button type="button" wire:click="executeDelete" class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition-colors">
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
