<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - MACUIN</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #6B0F2A; --bg-light: #F7FAFC; --text-dark: #2D3748; --border: #E2E8F0; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); color: var(--text-dark); margin: 0; padding: 0; }
        .navbar { background: var(--primary); padding: 1rem 5%; display: flex; justify-content: space-between; align-items: center; color: white; }
        .navbar-brand a { color: white; text-decoration: none; font-size: 1.5rem; font-weight: 700; }
        
        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        
        .cart-card { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid var(--border); }
        .cart-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .cart-table th { text-align: left; padding: 15px; border-bottom: 2px solid var(--border); color: #718096; text-transform: uppercase; font-size: 0.85rem; }
        .cart-table td { padding: 15px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        
        .item-name { font-weight: 600; color: var(--text-dark); }
        .item-brand { font-size: 0.85rem; color: #718096; }
        
        .summary-section { margin-top: 30px; display: flex; justify-content: flex-end; }
        .summary-box { background: #F8FAFC; padding: 25px; border-radius: 8px; border: 1px solid var(--border); width: 300px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 1.1rem; }
        .total-row { font-size: 1.4rem; font-weight: 700; color: var(--primary); border-top: 2px solid var(--border); padding-top: 15px; }
        
        .btn { padding: 12px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; text-align: center; border: none; width: 100%; box-sizing: border-box;}
        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { background-color: #500b1f; }
        .btn-outline { background-color: white; border: 1px solid #CBD5E0; color: #4A5568; margin-bottom: 10px;}
        .btn-outline:hover { background-color: #EDF2F7; }
        
        .empty-cart { text-align: center; padding: 50px 20px; color: #718096; }
        .empty-cart i { font-size: 4rem; color: #CBD5E0; margin-bottom: 20px; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="navbar-brand">
            <a href="{{ route('catalogo') }}"><i class="fas fa-cogs"></i> MACUIN</a>
        </div>
    </nav>

    <div class="container">
        <h2><i class="fas fa-shopping-cart"></i> Tu Carrito</h2>

        @if(session('error_api'))
            <div style="background: #FED7D7; color: #9B2C2C; padding: 15px; border-radius: 8px; text-align: center; margin-bottom: 20px; font-weight: 600;">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error_api') }}
            </div>
        @endif

        <div class="cart-card">
            @if(count($carrito) > 0)
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio Unitario</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($carrito as $item)
                        <tr>
                            <td>
                                <div class="item-name">{{ $item['nombre'] }}</div>
                                <div class="item-brand">{{ $item['marca'] }}</div>
                            </td>
                            <td>${{ number_format($item['precio'], 2) }}</td>
                            <td>{{ $item['cantidad'] }}</td>
                            <td style="font-weight: 600;">${{ number_format($item['precio'] * $item['cantidad'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="summary-section">
                    <div class="summary-box">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>${{ number_format($total, 2) }}</span>
                        </div>
                        <div class="summary-row total-row">
                            <span>Total</span>
                            <span>${{ number_format($total, 2) }}</span>
                        </div>
                        
                        <form action="{{ route('carrito.checkout') }}" method="POST" style="margin-top: 20px;">
                            @csrf
                            <button type="submit" class="btn btn-primary"><i class="fas fa-credit-card"></i> Pagar y Finalizar</button>
                        </form>
                        
                        <form action="{{ route('carrito.vaciar') }}" method="POST" style="margin-top: 10px;">
                            @csrf
                            <button type="submit" class="btn btn-outline"><i class="fas fa-trash"></i> Vaciar Carrito</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="empty-cart">
                    <i class="fas fa-shopping-basket"></i>
                    <h3>Tu carrito está vacío</h3>
                    <p>Parece que aún no has agregado ninguna autoparte.</p>
                    <a href="{{ route('catalogo') }}" class="btn btn-primary" style="width: auto; margin-top: 20px;">Volver al Catálogo</a>
                </div>
            @endif
        </div>
    </div>

</body>
</html>