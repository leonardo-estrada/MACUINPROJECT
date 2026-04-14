<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CarritoController extends Controller
{
    public function index()
    {
        $carrito = session()->get('carrito', []);
        $total = 0;

        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        return view('cliente.carrito', compact('carrito', 'total'));
    }

    public function agregar(Request $request)
    {
        $id = $request->input('id_autoparte');
        $cantidadSolicitada = max(1, (int) $request->input('cantidad', 1));
        $apiUrl = config('services.macuin_api.url') . '/inventario/';

        try {
            $response = Http::timeout(3)->get($apiUrl);

            if ($response->successful()) {
                $piezas = collect($response->json('data') ?? []);
                $pieza = $piezas->firstWhere('id', (int) $id) ?? $piezas->firstWhere('id', (string) $id);

                if ($pieza) {
                    $carrito = session()->get('carrito', []);

                    if (isset($carrito[$id])) {
                        $carrito[$id]['cantidad'] += $cantidadSolicitada;
                    } else {
                        $carrito[$id] = [
                            'id' => $pieza['id'],
                            'nombre' => $pieza['nombre'],
                            'marca' => $pieza['marca'],
                            'precio' => $pieza['precio'],
                            'cantidad' => $cantidadSolicitada,
                        ];
                    }

                    session()->put('carrito', $carrito);
                    return back()->with('success_cart', 'Pieza agregada al carrito.');
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error agregando al carrito: ' . $e->getMessage());
        }

        return back()->with('error_api', 'No se pudo agregar la pieza al carrito.');
    }

    public function actualizar(Request $request)
    {
        $datos = $request->validate([
            'id_autoparte' => ['required'],
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $carrito = session()->get('carrito', []);
        $id = $datos['id_autoparte'];

        if (isset($carrito[$id])) {
            $carrito[$id]['cantidad'] = (int) $datos['cantidad'];
            session()->put('carrito', $carrito);
        }

        return redirect()->route('carrito.index');
    }

    public function eliminar($id)
    {
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$id])) {
            unset($carrito[$id]);
            session()->put('carrito', $carrito);
        }

        return redirect()->route('carrito.index')->with('success_cart', 'Pieza eliminada del carrito.');
    }

    public function vaciar()
    {
        session()->forget('carrito');
        return redirect()->route('catalogo');
    }

    public function checkout()
    {
        $clienteId = session('cliente_id');

        if (! $clienteId) {
            return redirect()->route('login')->with('error_api', 'Debes iniciar sesion antes de finalizar tu compra.');
        }

        $carrito = session()->get('carrito', []);

        if (empty($carrito)) {
            return back()->with('error_api', 'El carrito esta vacio.');
        }

        $payload = [
            'id_cliente' => $clienteId,
            'productos' => array_values(array_map(function ($item) {
                return [
                    'id_autoparte' => $item['id'],
                    'cantidad' => $item['cantidad'],
                ];
            }, $carrito)),
        ];

        try {
            $response = Http::timeout(5)->post(config('services.macuin_api.url') . '/pedidos/', $payload);

            if ($response->successful()) {
                session()->forget('carrito');
                return redirect()->route('historial')
                    ->with('success_cart', 'Pedido procesado con exito. Tu folio es el #' . $response->json('folio', 'N/A'));
            }

            $mensaje = $response->json('detail') ?? 'Error al procesar el pedido.';
            return back()->with('error_api', $mensaje);
        } catch (\Throwable $e) {
            Log::error('Error en checkout: ' . $e->getMessage());
            return back()->with('error_api', 'Error al procesar el pedido.');
        }
    }
}
