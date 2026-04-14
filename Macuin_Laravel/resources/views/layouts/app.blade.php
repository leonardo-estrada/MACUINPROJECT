<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MACUIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e0dbdb;
            color: #333;
        }
    </style>
    @stack('head')
</head>
<body>
    <header class="bg-[#6B0F2A] text-white">
        <div class="mx-auto flex h-[60px] max-w-[1400px] items-center justify-between px-4 md:px-6">
            <div class="flex items-center gap-4">
                <img src="{{ asset('img/logo.png') }}" alt="Logo MACUIN" class="h-10 w-auto rounded">
                <span class="text-sm font-medium">Panel Comercial</span>
            </div>

            <nav class="hidden items-center gap-1 md:flex">
                <a href="{{ route('catalogo') }}" class="rounded-lg px-4 py-2 text-[13px] text-white/80 transition hover:bg-white/10 hover:text-white {{ request()->routeIs('catalogo') ? 'bg-white/10 text-white' : '' }}">
                    <i class="fas fa-box mr-2"></i>Catalogo
                </a>
                <a href="{{ route('carrito.index') }}" class="rounded-lg px-4 py-2 text-[13px] text-white/80 transition hover:bg-white/10 hover:text-white {{ request()->routeIs('carrito.*') ? 'bg-white/10 text-white' : '' }}">
                    <i class="fas fa-shopping-cart mr-2"></i>Pedido
                </a>
                <a href="{{ route('historial') }}" class="rounded-lg px-4 py-2 text-[13px] text-white/80 transition hover:bg-white/10 hover:text-white {{ request()->routeIs('historial') ? 'bg-white/10 text-white' : '' }}">
                    <i class="fas fa-clock-rotate-left mr-2"></i>Historial
                </a>
            </nav>

            <div class="flex items-center gap-3">
                @if(session('cliente_id'))
                    <div class="hidden text-right md:block">
                        <div class="text-[11px] text-white/70">Cliente activo</div>
                        <div class="text-sm font-semibold">{{ session('cliente_nombre') }}</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="rounded-lg p-2 text-white transition hover:bg-white/10">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg px-4 py-2 text-[13px] text-white/90 transition hover:bg-white/10">
                        <i class="fas fa-user mr-2"></i>Ingresar
                    </a>
                @endif
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-[1400px] px-4 py-8 md:px-6">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
