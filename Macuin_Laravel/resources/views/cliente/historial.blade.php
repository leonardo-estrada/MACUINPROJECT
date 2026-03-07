@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto">
    
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 font-serif border-b pb-2"><i class="fa-solid fa-clock-rotate-left text-[#8b0000] mr-2"></i> Mi Historial de Pedidos</h2>
        <p class="text-sm text-gray-500 mt-2">Consulta el estado actual de tus compras y descarga tus comprobantes.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b border-gray-200">
                        <th class="p-4 font-semibold">N° Pedido</th>
                        <th class="p-4 font-semibold">Fecha</th>
                        <th class="p-4 font-semibold">Total</th>
                        <th class="p-4 font-semibold text-center">Estatus</th>
                        <th class="p-4 font-semibold text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700">
                    
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                        <td class="p-4 font-medium text-gray-900">PED-00012346</td>
                        <td class="p-4">12/02/2026<br><span class="text-xs text-gray-400">10:30 a.m.</span></td>
                        <td class="p-4 font-bold text-blue-700">$124.40</td>
                        <td class="p-4 text-center">
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">En Proceso</span>
                        </td>
                        <td class="p-4 text-center">
                            <button class="text-[#8b0000] hover:text-red-900" title="Descargar Documento"><i class="fa-solid fa-file-pdf text-lg"></i></button>
                        </td>
                    </tr>

                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                        <td class="p-4 font-medium text-gray-900">PED-00012348</td>
                        <td class="p-4">30/01/2026<br><span class="text-xs text-gray-400">02:15 p.m.</span></td>
                        <td class="p-4 font-bold text-blue-700">$293.00</td>
                        <td class="p-4 text-center">
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">Entregado</span>
                        </td>
                        <td class="p-4 text-center">
                            <button class="text-[#8b0000] hover:text-red-900" title="Descargar Documento"><i class="fa-solid fa-file-pdf text-lg"></i></button>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection