<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClienteAuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function registroForm()
    {
        return view('auth.registro');
    }

    public function recuperarForm()
    {
        return view('auth.recuperar');
    }

    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'correo' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $response = Http::timeout(5)->get(config('services.macuin_api.url') . '/clientes/');

            if (! $response->successful()) {
                return back()->withInput()->with('error_api', 'No se pudo validar la cuenta en este momento.');
            }

            $cliente = collect($response->json('data') ?? [])->first(function ($item) use ($credenciales) {
                return ($item['correo'] ?? null) === $credenciales['correo']
                    && ($item['password'] ?? null) === $credenciales['password'];
            });

            if (! $cliente) {
                return back()->withInput()->with('error_api', 'Credenciales incorrectas.');
            }

            session([
                'cliente_id' => $cliente['id'],
                'cliente_nombre' => $cliente['nombre'],
                'cliente_correo' => $cliente['correo'],
            ]);

            return redirect()->route('catalogo');
        } catch (\Throwable $e) {
            Log::error('Error al iniciar sesion de cliente: ' . $e->getMessage());
            return back()->withInput()->with('error_api', 'No se pudo conectar con la API.');
        }
    }

    public function registro(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'min:3', 'max:100'],
            'correo' => ['required', 'email'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        try {
            $response = Http::timeout(5)->post(config('services.macuin_api.url') . '/clientes/', [
                'nombre' => $datos['nombre'],
                'correo' => $datos['correo'],
                'telefono' => $datos['telefono'] ?? '',
                'password' => $datos['password'],
            ]);

            if (! $response->successful()) {
                $mensaje = $response->json('detail') ?? 'No se pudo completar el registro.';
                return back()->withInput()->with('error_api', $mensaje);
            }

            $cliente = $response->json('data');

            session([
                'cliente_id' => $cliente['id'],
                'cliente_nombre' => $cliente['nombre'],
                'cliente_correo' => $cliente['correo'],
            ]);

            return redirect()->route('catalogo')->with('success_cart', 'Registro completado correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error al registrar cliente: ' . $e->getMessage());
            return back()->withInput()->with('error_api', 'No se pudo conectar con la API.');
        }
    }

    public function logout()
    {
        session()->forget(['cliente_id', 'cliente_nombre', 'cliente_correo', 'carrito']);
        return redirect()->route('login');
    }

    public function recuperar(Request $request)
    {
        $datos = $request->validate([
            'correo' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        try {
            $response = Http::timeout(5)->post(config('services.macuin_api.url') . '/clientes/recuperar/reset', [
                'correo' => $datos['correo'],
                'nueva_password' => $datos['password'],
            ]);

            if (! $response->successful()) {
                $mensaje = $response->json('detail') ?? 'No se pudo restablecer la contrasena.';
                return back()->withInput()->with('error_api', $mensaje);
            }

            return redirect()->route('login')->with('success_reset', 'Contrasena restablecida correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error al recuperar contrasena de cliente: ' . $e->getMessage());
            return back()->withInput()->with('error_api', 'No se pudo conectar con la API.');
        }
    }
}
