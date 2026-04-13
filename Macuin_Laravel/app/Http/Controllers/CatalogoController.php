<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        $apiUrl = env('API_URL') . '/inventario/';
        $categoriaFiltro = $request->query('categoria'); // Por si luego quieres filtrar por URL

        try {
            // Hacemos la petición a FastAPI con un timeout de seguridad (3 segundos)
            $response = Http::timeout(3)->get($apiUrl);

            if ($response->successful()) {
                $todasLasPiezas = $response->json('data') ?? [];
                
                // Filtramos usando colecciones de Laravel: Solo stock mayor a 0
                $catalogo = collect($todasLasPiezas)->filter(function ($pieza) {
                    return $pieza['stock'] > 0;
                });

                // Si el usuario eligió una categoría, filtramos de nuevo
                if ($categoriaFiltro) {
                    $catalogo = $catalogo->filter(function ($pieza) use ($categoriaFiltro) {
                        return strtolower($pieza['categoria']) === strtolower($categoriaFiltro);
                    });
                }
            } else {
                $catalogo = collect([]);
                Log::error("API Error: Código " . $response->status());
            }

        } catch (\Exception $e) {
            // Si FastAPI está apagado, atrapamos la caída limpiamente
            $catalogo = collect([]);
            Log::error("Fallo de conexión con FastAPI: " . $e->getMessage());
            
            // Enviamos un mensaje a la vista para avisar del mantenimiento
            session()->flash('error_api', 'Estamos actualizando nuestro inventario. Por favor, intenta en unos minutos.');
        }

        return view('cliente.catalogo', compact('catalogo', 'categoriaFiltro'));
    }
}