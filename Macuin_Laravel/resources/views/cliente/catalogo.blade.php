@extends('layouts.app')

@php
    $categorias = collect($catalogo)->pluck('categoria')->filter()->unique()->sort()->values();
    $stockTotal = collect($catalogo)->sum('stock');
    $precioPromedio = collect($catalogo)->avg('precio') ?? 0;
@endphp

@section('content')
<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <h1 class="text-2xl font-semibold text-[#333]">Catalogo de Autopartes</h1>
        <p class="mt-1 text-sm text-[#666]">Explora piezas disponibles y arma el pedido final del demo.</p>
    </div>
    <div class="grid grid-cols-3 gap-3 lg:w-[420px]">
        <div class="rounded-[10px] border-l-4 border-[#6B0F2A] bg-white p-4 shadow-sm">
            <div class="text-[12px] font-semibold uppercase text-[#666]">Productos</div>
            <div class="mt-1 text-2xl font-bold text-[#333]">{{ count($catalogo) }}</div>
        </div>
        <div class="rounded-[10px] border-l-4 border-[#38A169] bg-white p-4 shadow-sm">
            <div class="text-[12px] font-semibold uppercase text-[#666]">Stock</div>
            <div class="mt-1 text-2xl font-bold text-[#333]">{{ $stockTotal }}</div>
        </div>
        <div class="rounded-[10px] border-l-4 border-[#3182CE] bg-white p-4 shadow-sm">
            <div class="text-[12px] font-semibold uppercase text-[#666]">Promedio</div>
            <div class="mt-1 text-2xl font-bold text-[#333]">${{ number_format($precioPromedio, 2) }}</div>
        </div>
    </div>
</div>

@if(session('error_api'))
    <div class="mb-5 rounded-[10px] border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error_api') }}
    </div>
@endif

@if(session('success_cart'))
    <div class="mb-5 rounded-[10px] border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success_cart') }}
    </div>
@endif

<section class="rounded-[10px] bg-white p-5 shadow-sm">
    <div class="mb-5 grid gap-4 lg:grid-cols-[1.2fr_0.8fr_0.7fr]">
        <div>
            <label for="searchInput" class="mb-2 block text-[12px] font-semibold uppercase text-[#666]">Buscar</label>
            <div class="flex items-center rounded-[8px] border border-[#E2E8F0] bg-white px-3">
                <i class="fas fa-search text-[#666]"></i>
                <input id="searchInput" type="text" placeholder="Nombre, marca o categoria" class="w-full border-0 px-3 py-3 text-sm outline-none">
            </div>
        </div>
        <div>
            <label for="sortSelect" class="mb-2 block text-[12px] font-semibold uppercase text-[#666]">Ordenar</label>
            <select id="sortSelect" class="w-full rounded-[8px] border border-[#E2E8F0] px-3 py-3 text-sm outline-none">
                <option value="default">Orden original</option>
                <option value="price-asc">Precio menor</option>
                <option value="price-desc">Precio mayor</option>
                <option value="stock-desc">Mayor stock</option>
                <option value="name-asc">Nombre A-Z</option>
            </select>
        </div>
        <div class="rounded-[8px] bg-[#f8fafc] p-4">
            <div class="text-[12px] font-semibold uppercase text-[#666]">Resultados</div>
            <div id="resultCounter" class="mt-1 text-3xl font-bold text-[#6B0F2A]">{{ count($catalogo) }}</div>
        </div>
    </div>

    <div class="mb-6 flex flex-wrap gap-2">
        <button type="button" class="category-chip rounded-full bg-[#6B0F2A] px-4 py-2 text-[13px] font-medium text-white" data-category="all">Todas</button>
        @foreach($categorias as $categoria)
            <button type="button" class="category-chip rounded-full border border-[#E2E8F0] bg-white px-4 py-2 text-[13px] font-medium text-[#4A5568]" data-category="{{ strtolower($categoria) }}">{{ $categoria }}</button>
        @endforeach
    </div>

    <div id="productGrid" class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse($catalogo as $index => $pieza)
            <article class="product-card rounded-[10px] border border-[#EDF2F7] bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md" data-name="{{ strtolower($pieza['nombre']) }}" data-brand="{{ strtolower($pieza['marca']) }}" data-category="{{ strtolower($pieza['categoria']) }}" data-price="{{ $pieza['precio'] }}" data-stock="{{ $pieza['stock'] }}" data-index="{{ $index }}">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div class="flex h-14 w-14 items-center justify-center rounded-[10px] bg-[#f5f5f5] text-2xl text-[#6B0F2A]">
                        @if(str_contains(strtolower($pieza['categoria']), 'motor'))
                            <i class="fas fa-oil-can"></i>
                        @elseif(str_contains(strtolower($pieza['categoria']), 'freno'))
                            <i class="fas fa-truck-monster"></i>
                        @else
                            <i class="fas fa-box"></i>
                        @endif
                    </div>
                    <span class="rounded-full bg-[#EDF2F7] px-3 py-1 text-[11px] font-semibold uppercase text-[#4A5568]">{{ $pieza['categoria'] }}</span>
                </div>

                <div class="text-[12px] font-semibold uppercase tracking-wide text-[#666]">{{ $pieza['marca'] }}</div>
                <h3 class="mt-2 min-h-[56px] text-lg font-semibold text-[#333]">{{ $pieza['nombre'] }}</h3>

                <div class="mt-4 flex items-end justify-between">
                    <div>
                        <div class="text-[12px] uppercase text-[#666]">Precio</div>
                        <div class="text-3xl font-bold text-[#6B0F2A]">${{ number_format($pieza['precio'] ?? 0, 2) }}</div>
                    </div>
                    <div class="rounded-[8px] bg-[#f8fafc] px-3 py-2 text-right">
                        <div class="text-[11px] uppercase text-[#666]">Stock</div>
                        <div class="text-lg font-semibold text-[#333]">{{ $pieza['stock'] }}</div>
                    </div>
                </div>

                <form action="{{ route('carrito.agregar') }}" method="POST" class="mt-5 flex items-center gap-3">
                    @csrf
                    <input type="hidden" name="id_autoparte" value="{{ $pieza['id'] }}">
                    <div class="inline-flex items-center rounded-[8px] border border-[#E2E8F0] bg-white px-2 py-2">
                        <button type="button" class="qty-btn h-8 w-8 rounded text-[#4A5568] hover:bg-[#EDF2F7]" data-action="decrease">-</button>
                        <input type="number" name="cantidad" min="1" value="1" class="qty-input w-10 border-0 bg-transparent text-center text-sm font-semibold outline-none">
                        <button type="button" class="qty-btn h-8 w-8 rounded text-[#4A5568] hover:bg-[#EDF2F7]" data-action="increase">+</button>
                    </div>
                    <button type="submit" class="flex-1 rounded-[8px] bg-[#6B0F2A] px-4 py-3 text-sm font-medium text-white transition hover:bg-[#551022]">
                        <i class="fas fa-plus mr-2"></i>Agregar
                    </button>
                </form>
            </article>
        @empty
            <div class="col-span-full rounded-[10px] border border-dashed border-[#CBD5E0] bg-[#f8fafc] px-6 py-16 text-center text-[#666]">
                <i class="fas fa-box-open text-5xl text-[#CBD5E0]"></i>
                <p class="mt-4 text-lg font-semibold">No hay piezas disponibles en este momento.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection

@push('scripts')
<script>
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortSelect');
    const chips = document.querySelectorAll('.category-chip');
    const grid = document.getElementById('productGrid');
    const cards = Array.from(document.querySelectorAll('.product-card'));
    const counter = document.getElementById('resultCounter');
    let activeCategory = 'all';

    function applyFilters() {
        const term = (searchInput?.value || '').toLowerCase().trim();
        const mode = sortSelect?.value || 'default';

        cards.forEach((card) => {
            const matchesCategory = activeCategory === 'all' || card.dataset.category === activeCategory;
            const haystack = `${card.dataset.name} ${card.dataset.brand} ${card.dataset.category}`;
            const matchesSearch = !term || haystack.includes(term);
            card.style.display = matchesCategory && matchesSearch ? '' : 'none';
        });

        const visibleCards = cards.filter((card) => card.style.display !== 'none');
        visibleCards.sort((a, b) => {
            if (mode === 'price-asc') return Number(a.dataset.price) - Number(b.dataset.price);
            if (mode === 'price-desc') return Number(b.dataset.price) - Number(a.dataset.price);
            if (mode === 'stock-desc') return Number(b.dataset.stock) - Number(a.dataset.stock);
            if (mode === 'name-asc') return a.dataset.name.localeCompare(b.dataset.name);
            return Number(a.dataset.index) - Number(b.dataset.index);
        });

        visibleCards.forEach((card) => grid.appendChild(card));
        counter.textContent = visibleCards.length;
    }

    searchInput?.addEventListener('input', applyFilters);
    sortSelect?.addEventListener('change', applyFilters);
    chips.forEach((chip) => {
        chip.addEventListener('click', () => {
            activeCategory = chip.dataset.category;
            chips.forEach((item) => {
                item.classList.remove('bg-[#6B0F2A]', 'text-white');
                item.classList.add('border', 'border-[#E2E8F0]', 'bg-white', 'text-[#4A5568]');
            });
            chip.classList.add('bg-[#6B0F2A]', 'text-white');
            chip.classList.remove('border', 'border-[#E2E8F0]', 'bg-white', 'text-[#4A5568]');
            applyFilters();
        });
    });

    document.querySelectorAll('form').forEach((form) => {
        const input = form.querySelector('.qty-input');
        form.querySelectorAll('.qty-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                if (!input) return;
                const value = Number(input.value || 1);
                input.value = btn.dataset.action === 'increase' ? value + 1 : Math.max(1, value - 1);
            });
        });
    });
</script>
@endpush
