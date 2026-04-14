<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MACUIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="mx-auto flex min-h-screen max-w-5xl items-center justify-center px-4 py-8">
        <div class="grid w-full items-center gap-8 md:grid-cols-[0.9fr_1.1fr]">
            <div class="text-center">
                <img src="{{ asset('img/logo.png') }}" alt="Logo MACUIN" class="mx-auto h-28 w-auto">
                <p class="mt-4 text-sm text-[#666]">Sistema de Gestion de Autopartes</p>
            </div>

            <div class="rounded-[10px] bg-white p-8 shadow-lg">
                <div class="mb-6 text-center">
                    <h2 class="text-2xl font-semibold text-[#333]">Iniciar Sesion</h2>
                    <p class="mt-1 text-sm text-[#666]">Acceso para clientes externos</p>
                </div>

                <form action="{{ route('login') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-2 block text-sm font-medium text-[#4A5568]">Correo Electronico</label>
                        <input type="email" name="correo" value="{{ old('correo') }}" class="w-full rounded-[6px] border border-[#CBD5E0] px-4 py-3 text-sm outline-none focus:border-[#8B1538] focus:ring-2 focus:ring-[#8B1538]/10" placeholder="ejemplo@macuin.com" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-[#4A5568]">Contrasena</label>
                        <input type="password" name="password" class="w-full rounded-[6px] border border-[#CBD5E0] px-4 py-3 text-sm outline-none focus:border-[#8B1538] focus:ring-2 focus:ring-[#8B1538]/10" placeholder="••••••••" required>
                    </div>

                    @if(session('success_reset'))
                        <div class="rounded-[6px] bg-green-50 px-4 py-3 text-sm font-semibold text-green-600">
                            {{ session('success_reset') }}
                        </div>
                    @endif

                    @if($errors->any() || session('error_api'))
                        <div class="rounded-[6px] bg-red-50 px-4 py-3 text-sm font-semibold text-red-600">
                            {{ $errors->first() ?: session('error_api') }}
                        </div>
                    @endif

                    <button type="submit" class="w-full rounded-[6px] bg-[#6B0F2A] px-4 py-3 text-sm font-medium text-white transition hover:bg-[#551022]">
                        Iniciar Sesion
                    </button>
                </form>

                <div class="mt-5 space-y-3 text-center text-sm">
                    <div>
                        <a href="{{ route('password.request') }}" class="text-[#6B0F2A] hover:underline">¿Olvidaste tu contrasena?</a>
                    </div>
                    <div>
                        <a href="{{ route('registro') }}" class="text-[#6B0F2A] hover:underline">Crear cuenta</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
