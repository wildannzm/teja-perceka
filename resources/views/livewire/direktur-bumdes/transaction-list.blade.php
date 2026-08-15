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
    <flux:modal wire:model="showDeleteModal" class="min-w-[400px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Konfirmasi Hapus</flux:heading>
                <flux:subheading>
                    <p>Yakin ingin menghapus data ini?</p>
                    <p>Data yang dihapus tidak dapat dikembalikan dan mungkin mempengaruhi kalkulasi laporan.</p>
                </flux:subheading>
            </div>
            <div class="flex justify-end gap-2">
                <flux:button wire:click="$set('showDeleteModal', false)">Batal</flux:button>
                <flux:button variant="danger" wire:click="executeDelete">Ya, Hapus</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
