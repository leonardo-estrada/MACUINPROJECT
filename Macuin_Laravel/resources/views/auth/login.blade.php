<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - MACUIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-[#f8f9fc] flex flex-col justify-center items-center min-h-screen">

    <div class="mb-6 text-center">
        <img src="{{ asset('img/logo.png') }}" alt="MACUIN Logo" class="h-20 mx-auto mb-2">
        <h1 class="text-2xl font-serif text-[#8b0000] tracking-widest">MACUIN</h1>
        <p class="text-gray-500 text-sm italic">Sistema de Gestión de Autopartes</p>
    </div>

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border border-gray-100">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6 font-serif">Iniciar Sesión</h2>

        <form action="{{ route('catalogo') }}" method="GET">
            <div class="mb-4">
                <label class="block text-gray-600 text-sm mb-2">Correo electrónico o Usuario</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fa-regular fa-user"></i>
                    </span>
                    <input type="text" class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-3 focus:outline-none focus:border-[#8b0000] focus:ring-1 focus:ring-[#8b0000]" placeholder="usuario@ejemplo.com">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-600 text-sm mb-2">Contraseña</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-3 focus:outline-none focus:border-[#8b0000] focus:ring-1 focus:ring-[#8b0000]" placeholder="••••••••">
                </div>
            </div>

            <div class="bg-red-50 border border-red-200 text-red-600 text-xs px-3 py-2 rounded mb-4">
                Error: Credenciales incorrectas, Intente nuevamente.
            </div>

            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center text-sm text-gray-600">
                    <input type="checkbox" class="mr-2 rounded text-[#8b0000] focus:ring-[#8b0000]"> Recordarme
                </label>
                <a href="#" class="text-sm text-[#8b0000] hover:underline">¿Olvidó su contraseña?</a>
            </div>

            <button type="submit" class="w-full bg-[#8b0000] hover:bg-red-900 text-white font-medium py-2 rounded-md transition flex justify-center items-center gap-2">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Iniciar Sesión
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-gray-500">
            <p class="mb-2">El rol del usuario se asigna automáticamente según sus credenciales.</p>
            <p>¿Nuevo cliente? <a href="{{ route('registro') }}" class="text-[#8b0000] font-medium hover:underline border border-gray-200 px-2 py-1 rounded ml-1">Crear cuenta</a></p>
        </div>
    </div>
</body>
</html>