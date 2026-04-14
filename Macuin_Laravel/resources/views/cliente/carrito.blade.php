@extends('layouts.app')

@php
    $articulos = collect($carrito)->sum('cantidad');
@endphp

@section('content')
<div class="mb-6 flex items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-semibold text-[#333]">Gestion de Pedido</h1>
        <p class="mt-1 text-sm text-[#666]">Ajusta cantidades y confirma la orden antes de enviarla.</p>
    </div>
    <a href="{{ route('catalogo') }}" class="rounded-[6px] bg-[#4A5568] px-4 py-2 text-sm text-white transition hover:bg-[#2D3748]">
        <i class="fas fa-arrow-left mr-2"></i>Volver al catalogo
    </a>
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

<div class="mb-6 grid gap-4 md:grid-cols-3">
    <div class="rounded-[10px] border-l-4 border-[#6B0F2A] bg-white p-4 shadow-sm">
        <div class="text-[12px] font-semibold uppercase text-[#666]">Lineas</div>
        <div class="mt-1 text-2xl font-bold text-[#333]">{{ count($carrito) }}</div>
    </div>
    <div class="rounded-[10px] border-l-4 border-[#3182CE] bg-white p-4 shadow-sm">
        <div class="text-[12px] font-semibold uppercase text-[#666]">Articulos</div>
        <div class="mt-1 text-2xl font-bold text-[#333]">{{ $articulos }}</div>
    </div>
    <div class="rounded-[10px] border-l-4 border-[#38A169] bg-white p-4 shadow-sm">
        <div class="text-[12px] font-semibold uppercase text-[#666]">Total</div>
        <div class="mt-1 text-2xl font-bold text-[#333]">${{ number_format($total, 2) }}</div>
    </div>
</div>

<div class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
    <section class="rounded-[10px] bg-white p-5 shadow-sm">
        @if(count($carrito) > 0)
            <div class="space-y-4">
                @foreach($carrito as $item)
                    <article class="rounded-[10px] border border-[#EDF2F7] bg-white p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div class="text-[12px] font-semibold uppercase text-[#666]">{{ $item['marca'] }}</div>
                                <h3 class="mt-1 text-lg font-semibold text-[#333]">{{ $item['nombre'] }}</h3>
                                <div class="mt-2 text-sm text-[#666]">Precio unitario: <span class="font-semibold text-[#333]">${{ number_format($item['precio'], 2) }}</span></div>
                            </div>

                            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                <form action="{{ route('carrito.actualizar') }}" method="POST" class="flex items-center gap-3">
                                    @csrf
                                    <input type="hidden" name="id_autoparte" value="{{ $item['id'] }}">
                                    <div class="inline-flex items-center rounded-[8px] border border-[#E2E8F0] bg-white px-2 py-2">
                                        <button type="button" class="qty-adjust h-8 w-8 rounded text-[#4A5568] hover:bg-[#EDF2F7]" data-action="decrease">-</button>
                                        <input type="number" name="cantidad" min="1" value="{{ $item['cantidad'] }}" class="qty-field w-10 border-0 bg-transparent text-center text-sm font-semibold outline-none">
                                        <button type="button" class="qty-adjust h-8 w-8 rounded text-[#4A5568] hover:bg-[#EDF2F7]" data-action="increase">+</button>
                                    </div>
                                    <button type="submit" class="rounded-[8px] bg-[#6B0F2A] px-4 py-3 text-sm font-medium text-white transition hover:bg-[#551022]">Actualizar</button>
                                </form>

                                <form action="{{ route('carrito.eliminar', $item['id']) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="rounded-[8px] border border-red-200 bg-white px-4 py-3 text-sm font-medium text-red-600 transition hover:bg-red-50">Eliminar</button>
                                </form>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between rounded-[8px] bg-[#f8fafc] px-4 py-3 text-sm text-[#666]">
                            <span>{{ $item['cantidad'] }} unidad(es) seleccionadas</span>
                            <span class="text-lg font-bold text-[#6B0F2A]">${{ number_format($item['precio'] * $item['cantidad'], 2) }}</span>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rounded-[10px] border border-dashed border-[#CBD5E0] bg-[#f8fafc] px-6 py-16 text-center text-[#666]">
                <i class="fas fa-shopping-basket text-5xl text-[#CBD5E0]"></i>
                <p class="mt-4 text-lg font-semibold">Tu carrito esta vacio.</p>
            </div>
        @endif
    </section>

    <aside class="space-y-4">
        <div class="rounded-[10px] bg-white p-5 shadow-sm">
            <div class="mb-4 border-b border-[#EDF2F7] pb-3 text-base font-semibold text-[#2D3748]">Resumen de compra</div>
            <div class="mb-3 flex items-center justify-between text-sm text-[#666]">
                <span>Subtotal</span>
                <span class="font-semibold text-[#333]">${{ number_format($total, 2) }}</span>
            </div>
            <div class="flex items-center justify-between border-t border-[#EDF2F7] pt-3 text-lg font-bold text-[#6B0F2A]">
                <span>Total</span>
                <span>${{ number_format($total, 2) }}</span>
            </div>

            <form action="{{ route('carrito.checkout') }}" method="POST" class="mt-5">
                @csrf
                <button type="submit" class="w-full rounded-[8px] bg-[#6B0F2A] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#551022]">
                    <i class="fas fa-credit-card mr-2"></i>Pagar y finalizar
                </button>
            </form>

            <form action="{{ route('carrito.vaciar') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="w-full rounded-[8px] border border-[#CBD5E0] bg-white px-5 py-3 text-sm font-medium text-[#4A5568] transition hover:bg-[#EDF2F7]">
                    <i class="fas fa-trash mr-2"></i>Vaciar carrito
                </button>
            </form>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.qty-adjust').forEach((button) => {
        button.addEventListener('click', () => {
            const input = button.parentElement.querySelector('.qty-field');
            const current = Number(input.value || 1);
            input.value = button.dataset.action === 'increase' ? current + 1 : Math.max(1, current - 1);
        });
    });
</script>
@endpush
