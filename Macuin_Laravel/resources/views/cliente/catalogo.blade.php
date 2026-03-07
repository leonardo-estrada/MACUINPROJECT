@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6 mb-8">
    <div class="flex items-center gap-4 mb-4 overflow-x-auto pb-2">
        <span class="text-gray-500 font-semibold whitespace-nowrap"><i class="fa-solid fa-filter mr-2"></i> Filtrar por Categoría</span>
        <a href="#" class="px-4 py-2 bg-macuin text-white rounded-full text-sm whitespace-nowrap">Todas las categorías</a>
        <a href="#" class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-full text-sm whitespace-nowrap">Motor</a>
        <a href="#" class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-full text-sm whitespace-nowrap">Frenos</a>
        <a href="#" class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-full text-sm whitespace-nowrap">Suspensión</a>
        <a href="#" class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-full text-sm whitespace-nowrap">Eléctrico</a>
    </div>
    <p class="text-sm text-gray-500">Mostrando 12 productos</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    
    <div class="bg-white rounded-xl shadow border border-gray-100 p-5 flex flex-col justify-between">
        <div>
            <p class="text-xs text-pink-400 font-medium mb-1 bg-pink-50 inline-block px-2 py-1 rounded">AutoTech</p>
            <h3 class="font-bold text-gray-800 text-lg mb-2">Filtro de Aceite Premium</h3>
            <p class="text-gray-500 text-sm mb-4 line-clamp-2">Filtro de aceite de alta eficiencia para motores de gasolina y diésel. Compatible con múltiples...</p>
            <p class="text-blue-700 font-bold text-2xl mb-3">$15.99</p>
            <p class="text-green-600 text-sm font-medium mb-4"><i class="fa-regular fa-clock"></i> Disponibilidad:<br><span class="ml-4">45 unidades</span></p>
        </div>
        <a href="{{ route('checkout') }}" class="block text-center w-full bg-macuin hover:bg-red-900 text-white font-medium py-2 rounded-lg transition">
            + Agregar al Pedido
        </a>
    </div>

    <div class="bg-white rounded-xl shadow border border-gray-100 p-5 flex flex-col justify-between opacity-75">
        <div>
            <p class="text-xs text-pink-400 font-medium mb-1 bg-pink-50 inline-block px-2 py-1 rounded">AirFlow</p>
            <h3 class="font-bold text-gray-800 text-lg mb-2">Filtro de Aire Deportivo</h3>
            <p class="text-gray-500 text-sm mb-4 line-clamp-2">Filtro de aire de alto flujo reutilizable. Aumenta el rendimiento del motor.</p>
            <p class="text-blue-700 font-bold text-2xl mb-3">$55.00</p>
            <p class="text-red-500 text-sm font-medium mb-4"><i class="fa-regular fa-clock"></i> Disponibilidad:<br><span class="ml-4">Sin stock</span></p>
        </div>
        <button class="w-full bg-gray-300 text-gray-500 font-medium py-2 rounded-lg cursor-not-allowed">
            + No Disponible
        </button>
    </div>

    </div>
@endsection