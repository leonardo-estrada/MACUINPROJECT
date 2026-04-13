<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CarritoController extends Controller
{
    // 1. Mostrar la vista del carrito
    public function index()
    {
        $carrito = session()->get('carrito', []);
        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }
        return view('cliente.carrito', compact('carrito', 'total'));
    }

    // 2. Agregar al carrito de forma segura
    public function agregar(Request $request)
    {
        $id = $request->input('id_autoparte');
        $apiUrl = env('API_URL') . '/inventario/';

        try {
            $response = Http::timeout(3)->get($apiUrl);
            if ($response->successful()) {
                $piezas = collect($response->json('data') ?? []);
                $pieza = $piezas->firstWhere('id', (int)$id) ?? $piezas->firstWhere('id', (string)$id);

                if ($pieza) {
                    $carrito = session()->get('carrito', []);
                    
                    if (isset($carrito[$id])) {
                        $carrito[$id]['cantidad']++; // Si ya existe, sumamos 1
                    } else {
                        $carrito[$id] = [
                            'id' => $pieza['id'],
                            'nombre' => $pieza['nombre'],
                            'marca' => $pieza['marca'],
                            'precio' => $pieza['precio'],
                            'cantidad' => 1
                        ];
                    }
                    session()->put('carrito', $carrito);
                    return back()->with('success_cart', '¡Pieza agregada al carrito!');
                }
            }
        } catch (\Exception $e) {
            Log::error("Error agregando al carrito: " . $e->getMessage());
        }
        return back()->with('error_api', 'No se pudo agregar la pieza al carrito.');
    }

    // 3. Vaciar el carrito
    public function vaciar()
    {
        session()->forget('carrito');
        return redirect()->route('catalogo');
    }

    // 4. Procesar el Checkout hacia FastAPI
    public function checkout(Request $request)
    {
        $carrito = session()->get('carrito', []);
        if (empty($carrito)) {
            return back()->with('error_api', 'El carrito está vacío.');
        }

        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        // NOTA TECH LEAD: Como aún no hacemos el Login del cliente, 
        // usaremos el ID 1 temporalmente para que tu API no rechace el pedido.
        $payload = [
            'id_cliente' => 1, 
            'total' => $total,
            'estatus' => 'Pendiente'
        ];

        try {
            $response = Http::timeout(5)->post(env('API_URL') . '/pedidos/', $payload);

            if ($response->successful()) {
                session()->forget('carrito');
                // En un flujo real, aquí lo mandarías a su historial de compras
                return redirect()->route('catalogo')->with('success_cart', '¡Pedido procesado con éxito! Tu folio es el #' . $response->json('id', 'N/A'));
            }
        } catch (\Exception $e) {
            Log::error("Error en Checkout: " . $e->getMessage());
        }

        return back()->with('error_api', 'Error al procesar el pago. Intenta de nuevo.');
    }
}