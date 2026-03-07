@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6 flex items-center gap-2 text-gray-600">
        <a href="{{ route('catalogo') }}" class="hover:text-[#8b0000]"><i class="fa-solid fa-arrow-left"></i> Volver al catálogo</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa-solid fa-location-dot text-[#8b0000] mr-2"></i> Información de Entrega</h2>
                
                <form class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 mb-1">Nombre completo</label>
                        <input type="text" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:border-[#8b0000]" value="Usuario" readonly>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Teléfono</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fa-solid fa-phone"></i></span>
                            <input type="text" class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-3 focus:outline-none focus:border-[#8b0000]" placeholder="123-456-7890">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Correo electrónico</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fa-regular fa-envelope"></i></span>
                            <input type="email" class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-3 focus:outline-none focus:border-[#8b0000]" value="usuario@ejemplo.com" readonly>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 mb-1">Dirección de entrega</label>
                        <input type="text" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:border-[#8b0000]" placeholder="Calle, Número, Colonia">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 mb-1">Ciudad</label>
                        <input type="text" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:border-[#8b0000]" placeholder="Ciudad">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 mb-1">Notas adicionales (opcional)</label>
                        <div class="relative">
                            <span class="absolute top-3 left-3 text-gray-400"><i class="fa-regular fa-clipboard"></i></span>
                            <textarea class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-3 focus:outline-none focus:border-[#8b0000]" rows="3" placeholder="Instrucciones especiales de entrega..."></textarea>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa-solid fa-box-open text-[#8b0000] mr-2"></i> Productos en tu Pedido</h2>
                
                <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-gray-100 rounded flex items-center justify-center text-gray-400"><i class="fa-solid fa-image text-2xl"></i></div>
                        <div>
                            <h4 class="font-bold text-gray-800">Filtro de Aceite Premium</h4>
                            <p class="text-xs text-gray-500">AutoTech</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Cantidad: 1</p>
                        <p class="font-bold text-blue-700">$149.99</p>
                    </div>
                </div>
            </div>

        </div>

        <div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Resumen del Pedido</h2>
                
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-gray-600 text-sm">
                        <span>Total de productos:</span>
                        <span>1</span>
                    </div>
                    <div class="flex justify-between text-gray-600 text-sm">
                        <span>Subtotal:</span>
                        <span>$149.99</span>
                    </div>
                    <div class="flex justify-between text-gray-600 text-sm">
                        <span>Envío:</span>
                        <span class="text-green-600 font-medium">Gratis</span>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-gray-800 text-lg">Total:</span>
                        <span class="font-bold text-blue-700 text-2xl">$149.99</span>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 text-xs px-3 py-2 rounded mb-4 flex items-start gap-2">
                    <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                    <p>Complete todos los campos obligatorios para continuar.</p>
                </div>

                <a href="{{ route('historial') }}" class="block text-center w-full bg-[#8b0000] hover:bg-red-900 text-white font-medium py-3 rounded-md transition">
                    Confirmar Pedido
                </a>
        </div>

    </div>
</div>
@endsection