@extends('layouts.app')

@php
    $totalPedidos = collect($pedidos)->count();
    $totalImporte = collect($pedidos)->sum('total');
@endphp

@section('content')
<div class="mb-6 flex items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-semibold text-[#333]">Historial de Pedidos</h1>
        <p class="mt-1 text-sm text-[#666]">Consulta y filtra los pedidos generados por el cliente activo.</p>
    </div>
</div>

@if(session('success_cart'))
    <div class="mb-5 rounded-[10px] border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success_cart') }}
    </div>
@endif

@if(session('error_api'))
    <div class="mb-5 rounded-[10px] border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error_api') }}
    </div>
@endif

<div class="mb-6 grid gap-4 md:grid-cols-2">
    <div class="rounded-[10px] border-l-4 border-[#6B0F2A] bg-white p-4 shadow-sm">
        <div class="text-[12px] font-semibold uppercase text-[#666]">Pedidos registrados</div>
        <div class="mt-1 text-2xl font-bold text-[#333]">{{ $totalPedidos }}</div>
    </div>
    <div class="rounded-[10px] border-l-4 border-[#3182CE] bg-white p-4 shadow-sm">
        <div class="text-[12px] font-semibold uppercase text-[#666]">Importe acumulado</div>
        <div class="mt-1 text-2xl font-bold text-[#333]">${{ number_format($totalImporte, 2) }}</div>
    </div>
</div>

<section class="rounded-[10px] bg-white p-5 shadow-sm">
    <div class="mb-5 grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
        <div>
            <label for="historySearch" class="mb-2 block text-[12px] font-semibold uppercase text-[#666]">Buscar</label>
            <div class="flex items-center rounded-[8px] border border-[#E2E8F0] bg-white px-3">
                <i class="fas fa-search text-[#666]"></i>
                <input id="historySearch" type="text" class="w-full border-0 px-3 py-3 text-sm outline-none" placeholder="Buscar por folio o estatus">
            </div>
        </div>
        <div class="rounded-[8px] bg-[#f8fafc] p-4">
            <div class="text-[12px] font-semibold uppercase text-[#666]">Coincidencias</div>
            <div id="historyCounter" class="mt-1 text-3xl font-bold text-[#6B0F2A]">{{ $totalPedidos }}</div>
        </div>
    </div>

    <div class="mb-5 flex flex-wrap gap-2">
        <button type="button" class="history-filter rounded-full bg-[#6B0F2A] px-4 py-2 text-[13px] font-medium text-white" data-status="all">Todos</button>
        @foreach(collect($pedidos)->pluck('estatus')->unique()->values() as $estatus)
            <button type="button" class="history-filter rounded-full border border-[#E2E8F0] bg-white px-4 py-2 text-[13px] font-medium text-[#4A5568]" data-status="{{ strtolower($estatus) }}">{{ $estatus }}</button>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-[10px] border border-[#EDF2F7]">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] border-collapse">
                <thead class="bg-[#f8fafc]">
                    <tr class="text-left text-[12px] font-semibold uppercase tracking-wide text-[#666]">
                        <th class="px-5 py-4">Pedido</th>
                        <th class="px-5 py-4">Fecha</th>
                        <th class="px-5 py-4">Total</th>
                        <th class="px-5 py-4">Estatus</th>
                    </tr>
                </thead>
                <tbody class="bg-white text-sm text-[#333]">
                    @forelse($pedidos as $pedido)
                        <tr class="history-row border-t border-[#EDF2F7] hover:bg-[#f8fafc]" data-status="{{ strtolower($pedido['estatus']) }}" data-search="ped-{{ str_pad($pedido['id'], 8, '0', STR_PAD_LEFT) }} {{ strtolower($pedido['estatus']) }}">
                            <td class="px-5 py-4 font-semibold text-[#333]">PED-{{ str_pad($pedido['id'], 8, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-5 py-4">
                                <div>{{ optional($pedido['fecha'])->format('d/m/Y') ?? 'N/A' }}</div>
                                <div class="text-xs text-[#666]">{{ optional($pedido['fecha'])->format('h:i a') ?? '' }}</div>
                            </td>
                            <td class="px-5 py-4 font-bold text-[#6B0F2A]">${{ number_format($pedido['total'], 2) }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full bg-[#EDF2F7] px-3 py-2 text-[11px] font-semibold uppercase text-[#4A5568]">{{ $pedido['estatus'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-[#666]">No hay pedidos registrados para este cliente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    const historySearch = document.getElementById('historySearch');
    const historyFilters = document.querySelectorAll('.history-filter');
    const historyRows = Array.from(document.querySelectorAll('.history-row'));
    const historyCounter = document.getElementById('historyCounter');
    let activeStatus = 'all';

    function filterHistory() {
        const term = (historySearch?.value || '').toLowerCase().trim();
        let visible = 0;

        historyRows.forEach((row) => {
            const matchesStatus = activeStatus === 'all' || row.dataset.status === activeStatus;
            const matchesTerm = !term || row.dataset.search.includes(term);
            const show = matchesStatus && matchesTerm;
            row.style.display = show ? '' : 'none';
            if (show) visible += 1;
        });

        historyCounter.textContent = visible;
    }

    historySearch?.addEventListener('input', filterHistory);
    historyFilters.forEach((button) => {
        button.addEventListener('click', () => {
            activeStatus = button.dataset.status;
            historyFilters.forEach((item) => {
                item.classList.remove('bg-[#6B0F2A]', 'text-white');
                item.classList.add('border', 'border-[#E2E8F0]', 'bg-white', 'text-[#4A5568]');
            });
            button.classList.add('bg-[#6B0F2A]', 'text-white');
            button.classList.remove('border', 'border-[#E2E8F0]', 'bg-white', 'text-[#4A5568]');
            filterHistory();
        });
    });
</script>
@endpush
