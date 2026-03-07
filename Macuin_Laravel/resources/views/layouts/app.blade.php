<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MACUIN - Sistema de Gestión de Autopartes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .bg-macuin { background-color: #8B0000; }
        .text-macuin { color: #8B0000; }
    </style>
</head>
<body class="bg-gray-50">

    <nav class="bg-macuin text-white py-3 px-6 flex justify-between items-center shadow-md">
        <a href="{{ route('catalogo') }}" class="flex items-center gap-4 hover:opacity-80 transition">
            <img src="{{ asset('img/logo.png') }}" alt="MACUIN Logo" class="h-12 bg-white rounded-full p-1">
            <div>
                <h1 class="text-xl font-bold tracking-widest">MACUIN</h1>
                <p class="text-xs italic text-gray-200">Catálogo de Autopartes</p>
            </div>
        </a>
        
        <div class="flex-1 max-w-2xl mx-8 hidden md:block">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                </span>
                <input type="text" class="w-full bg-white/10 border border-white/30 rounded-md py-2 pl-10 pr-4 text-white placeholder-gray-300 focus:outline-none focus:bg-white focus:text-gray-900" placeholder="Buscar autopartes...">
            </div>
        </div>

        <div class="flex items-center gap-6">
            <a href="{{ route('login') }}" class="hover:text-gray-300 transition">
                <i class="fa-regular fa-user mr-1"></i> Usuario
            </a>
            <a href="{{ route('checkout') }}" class="hover:text-gray-300 transition">
                <i class="fa-solid fa-cart-shopping mr-1"></i> Mi Pedido
            </a>
            <a href="#" class="hover:text-gray-300 transition">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </a>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-8">
        @yield('content')
    </main>

</body>
</html>