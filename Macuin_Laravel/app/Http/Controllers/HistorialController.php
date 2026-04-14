<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HistorialController extends Controller
{
    public function index()
    {
        $clienteId = session('cliente_id');

        if (! $clienteId) {
            return redirect()->route('login')->with('error_api', 'Inicia sesion para consultar tu historial.');
        }

        try {
            $response = Http::timeout(5)->get(config('services.macuin_api.url') . '/pedidos/cliente/' . $clienteId);

            if (! $response->successful()) {
                return view('cliente.historial', ['pedidos' => collect([])])
                    ->with('error_api', 'No se pudo consultar el historial.');
            }

            $pedidos = collect($response->json('data') ?? [])->map(function ($pedido) {
                $fecha = isset($pedido['fecha']) ? \Carbon\Carbon::parse($pedido['fecha']) : null;

                return [
                    'id' => $pedido['id'] ?? null,
                    'fecha' => $fecha?->timezone(config('app.timezone')),
                    'total' => $pedido['total'] ?? 0,
                    'estatus' => $pedido['estatus'] ?? 'Pendiente',
                ];
            });

            return view('cliente.historial', ['pedidos' => $pedidos]);
        } catch (\Throwable $e) {
            Log::error('Error al consultar historial: ' . $e->getMessage());
            return view('cliente.historial', ['pedidos' => collect([])])
                ->with('error_api', 'No se pudo conectar con la API.');
        }
    }
}
