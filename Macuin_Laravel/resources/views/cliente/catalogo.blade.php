<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Autopartes - MACUIN</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #6B0F2A; --bg-light: #F7FAFC; --text-dark: #2D3748; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); color: var(--text-dark); margin: 0; padding: 0; }
        .navbar { background: var(--primary); padding: 1rem 5%; display: flex; justify-content: space-between; align-items: center; color: white; }
        .navbar-brand { font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .cart-icon { position: relative; font-size: 1.2rem; cursor: pointer; }
        .cart-badge { position: absolute; top: -8px; right: -10px; background: #E53E3E; font-size: 0.7rem; padding: 2px 6px; border-radius: 50%; font-weight: bold; }
        
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .header-catalogo { text-align: center; margin-bottom: 40px; }
        
        /* Mensaje de Error API */
        .alert-error { background: #FED7D7; color: #9B2C2C; padding: 15px; border-radius: 8px; text-align: center; margin-bottom: 20px; font-weight: 600; }

        .grid-productos { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .card-producto { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s; position: relative; border: 1px solid #E2E8F0; }
        .card-producto:hover { transform: translateY(-5px); box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
        
        .badge-categoria { position: absolute; top: 15px; right: 15px; background: #EDF2F7; color: #4A5568; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        
        /* Placeholder Icon para la pieza */
        .img-placeholder { height: 160px; background: #F7FAFC; border-radius: 8px; display: flex; justify-content: center; align-items: center; color: #A0AEC0; font-size: 3rem; margin-bottom: 15px; }
        
        .marca { color: #718096; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .nombre-pieza { font-size: 1.1rem; font-weight: 600; margin-bottom: 15px; color: #2D3748; min-height: 50px; }
        
        .footer-card { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #EDF2F7; padding-top: 15px; }
        .precio { font-size: 1.25rem; font-weight: 700; color: var(--primary); }
        
        .btn-add { background: white; border: 2px solid var(--primary); color: var(--primary); padding: 8px 15px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .btn-add:hover { background: var(--primary); color: white; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="navbar-brand">
            <i class="fas fa-cogs"></i> MACUIN
        </div>
        <a href="{{ route('carrito.index') }}" style="color: white; text-decoration: none;">
            <div class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-badge">{{ count(session('carrito', [])) }}</span>
            </div>
        </a>
    </nav>

    <div class="container">
        <div class="header-catalogo">
            <h1>Nuestro Catálogo</h1>
            <p>Encuentra las mejores autopartes con disponibilidad inmediata.</p>
        </div>

        @if(session('error_api'))
            <div class="alert-error">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error_api') }}
            </div>
        @endif

        <div class="grid-productos">
            @forelse($catalogo as $pieza)
                <div class="card-producto">
                    <span class="badge-categoria">{{ $pieza['categoria'] }}</span>
                    
                    <div class="img-placeholder">
                        @if(str_contains(strtolower($pieza['categoria']), 'motor'))
                            <i class="fas fa-oil-can"></i>
                        @elseif(str_contains(strtolower($pieza['categoria']), 'freno'))
                            <i class="fas fa-truck-monster"></i>
                        @else
                            <i class="fas fa-box"></i>
                        @endif
                    </div>

                    <div class="marca">{{ $pieza['marca'] }}</div>
                    <div class="nombre-pieza">{{ $pieza['nombre'] }}</div>
                    
                    <div class="footer-card">
                        <div class="precio">${{ number_format($pieza['precio'] ?? 0, 2) }}</div>
                        <form action="{{ route('carrito.agregar') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id_autoparte" value="{{ $pieza['id'] }}">
                            <button type="submit" class="btn-add"><i class="fas fa-plus"></i> Carrito</button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #718096;">
                    <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 15px;"></i>
                    <h3>No hay piezas disponibles en este momento.</h3>
                </div>
            @endforelse
        </div>
    </div>

</body>
</html>