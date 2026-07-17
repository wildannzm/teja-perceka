<div>
    <div class="flex h-full w-full flex-col gap-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">Kelola Jurnal Transaksi</h1>
            <flux:button variant="primary" icon="document-check" :href="route('sekretaris.verifikasi')" wire:navigate>Verifikasi Transaksi</flux:button>
        </div>

        <div class="bg-white dark:bg-neutral-900 p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 flex-1">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400 border border-neutral-200 dark:border-neutral-700">
                    <thead class="text-xs text-neutral-700 uppercase bg-neutral-50 dark:bg-neutral-800 dark:text-neutral-300">
                        <tr>
                            <th scope="col" class="px-4 py-3 border-b dark:border-neutral-700">Tanggal</th>
                            <th scope="col" class="px-4 py-3 border-b dark:border-neutral-700">Unit Usaha</th>
                            <th scope="col" class="px-4 py-3 border-b dark:border-neutral-700">Keterangan</th>
                            <th scope="col" class="px-4 py-3 border-b dark:border-neutral-700">Akun (COA)</th>
                            <th scope="col" class="px-4 py-3 text-right border-b dark:border-neutral-700">Debet</th>
                            <th scope="col" class="px-4 py-3 text-right border-b dark:border-neutral-700">Kredit</th>
                            <th scope="col" class="px-4 py-3 text-center border-b dark:border-neutral-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($journals as $journal)
                            @foreach($journal->details as $detail)
                            <tr class="bg-white dark:bg-neutral-900 hover:bg-neutral-50 dark:hover:bg-neutral-800 border-b dark:border-neutral-700">
                                @if($loop->first)
                                    <td rowspan="{{ $journal->details->count() }}" class="px-4 py-4 border-r dark:border-neutral-700 align-top whitespace-nowrap">{{ \Carbon\Carbon::parse($journal->date)->format('d/m/Y') }}</td>
                                    <td rowspan="{{ $journal->details->count() }}" class="px-4 py-4 border-r dark:border-neutral-700 align-top">{{ $journal->unit->name }}</td>
                                    <td rowspan="{{ $journal->details->count() }}" class="px-4 py-4 border-r dark:border-neutral-700 align-top">{{ $journal->description }}</td>
                                @endif
                                <td class="px-4 py-4">
                                    {{ $detail->account->code }} - {{ $detail->account->name }}
                                </td>
                                <td class="px-4 py-4 text-right">
                                    {{ $detail->debit > 0 ? number_format($detail->debit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-4 text-right">
                                    {{ $detail->credit > 0 ? number_format($detail->credit, 0, ',', '.') : '-' }}
                                </td>
                                @if($loop->first)
                                    <td rowspan="{{ $journal->details->count() }}" class="px-4 py-4 border-l dark:border-neutral-700 align-middle text-center">
                                        <flux:button wire:click="delete({{ $journal->id }})" wire:confirm="Yakin ingin menghapus transaksi ini? Data yang terhapus akan mempengaruhi saldo." variant="danger" size="sm" icon="trash">Hapus</flux:button>
                                    </td>
                                @endif
                            </tr>
                            @endforeach
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-neutral-500">
                                Belum ada data transaksi.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
