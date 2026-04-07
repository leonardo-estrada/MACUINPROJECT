<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito y Checkout - MACUIN</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #334155; }
        
        /* Navbar Simple */
        .navbar { background-color: #6B0F2A; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; color: white; }
        .logo-text { font-size: 20px; font-weight: bold; letter-spacing: 1px; }
        .btn-back { color: white; text-decoration: none; font-size: 14px; display: flex; align-items: center; gap: 8px; }
        .btn-back:hover { color: #e2e8f0; }

        /* Contenedor Principal */
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }

        /* Sección del Carrito */
        .cart-section { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .cart-title { font-size: 24px; font-weight: 600; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px; }
        
        /* Items del Carrito */
        .cart-item { display: flex; align-items: center; justify-content: space-between; padding: 20px 0; border-bottom: 1px solid #f1f5f9; }
        .item-info { display: flex; gap: 20px; align-items: center; width: 45%; }
        .item-img { width: 80px; height: 80px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 24px; }
        .item-details h4 { font-size: 16px; color: #0f172a; margin-bottom: 4px; }
        .item-details p { font-size: 13px; color: #64748b; }
        
        /* Controles de Cantidad */
        .quantity-controls { display: flex; align-items: center; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; }
        .btn-qty { background: #f8fafc; border: none; width: 32px; height: 32px; font-size: 16px; cursor: pointer; color: #475569; transition: background 0.2s; }
        .btn-qty:hover { background: #e2e8f0; }
        .qty-input { width: 40px; text-align: center; border: none; font-size: 14px; font-weight: 500; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; outline: none; }
        
        .item-price { font-weight: 600; color: #0f172a; width: 100px; text-align: right; }
        .btn-remove { color: #ef4444; background: none; border: none; cursor: pointer; font-size: 18px; padding: 8px; transition: color 0.2s; }
        .btn-remove:hover { color: #dc2626; }

        /* Resumen de Compra */
        .summary-section { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); height: fit-content; }
        .summary-title { font-size: 18px; font-weight: 600; margin-bottom: 20px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; color: #475569; }
        .summary-total { display: flex; justify-content: space-between; margin-top: 20px; padding-top: 20px; border-top: 2px solid #f1f5f9; font-size: 18px; font-weight: 700; color: #0f172a; }
        
        /* Botones Finales */
        .btn-checkout { width: 100%; background: #38A169; color: white; border: none; padding: 14px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 25px; display: flex; justify-content: center; align-items: center; gap: 8px; transition: background 0.2s; }
        .btn-checkout:hover { background: #2f855a; }
        
        .btn-cancel { width: 100%; background: white; color: #ef4444; border: 1px solid #ef4444; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; margin-top: 15px; transition: all 0.2s; }
        .btn-cancel:hover { background: #fef2f2; }

    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo-text">MACUIN - Cliente</div>
        <a href="{{ url('/catalogo') }}" class="btn-back"><i class="fas fa-arrow-left"></i> Seguir Comprando</a>
    </nav>

    <div class="container">
        <div class="cart-section">
            <h2 class="cart-title">Tu Carrito (2 Artículos)</h2>
            
            <div class="cart-item">
                <div class="item-info">
                    <div class="item-img"><i class="fas fa-cogs"></i></div>
                    <div class="item-details">
                        <h4>Filtro de Aceite Premium</h4>
                        <p>Código: FLT-001 | Marca: AutoTech</p>
                    </div>
                </div>
                <div class="quantity-controls">
                    <button class="btn-qty" onclick="restar('qty1')">-</button>
                    <input type="text" id="qty1" class="qty-input" value="2" readonly>
                    <button class="btn-qty" onclick="sumar('qty1')">+</button>
                </div>
                <div class="item-price">$350.00</div>
                <button class="btn-remove" title="Quitar del carrito"><i class="fas fa-trash-alt"></i></button>
            </div>

            <div class="cart-item">
                <div class="item-info">
                    <div class="item-img"><i class="fas fa-car-battery"></i></div>
                    <div class="item-details">
                        <h4>Batería 12V 70Ah</h4>
                        <p>Código: BAT-075 | Marca: PowerStart</p>
                    </div>
                </div>
                <div class="quantity-controls">
                    <button class="btn-qty" onclick="restar('qty2')">-</button>
                    <input type="text" id="qty2" class="qty-input" value="1" readonly>
                    <button class="btn-qty" onclick="sumar('qty2')">+</button>
                </div>
                <div class="item-price">$1,850.00</div>
                <button class="btn-remove" title="Quitar del carrito"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>

        <div class="summary-section">
            <h3 class="summary-title">Resumen de tu Pedido</h3>
            <div class="summary-row">
                <span>Subtotal</span>
                <span>$2,550.00</span>
            </div>
            <div class="summary-row">
                <span>Envío</span>
                <span>$150.00</span>
            </div>
            <div class="summary-row" style="color: #38A169;">
                <span>Descuento de Temporada</span>
                <span>-$200.00</span>
            </div>
            
            <div class="summary-total">
                <span>Total a Pagar</span>
                <span>$2,500.00</span>
            </div>

            <form action="{{ route('checkout') }}" style="margin-top: 10px;">
                <button type="submit" class="btn-checkout">
                    <i class="fas fa-check-circle"></i> Confirmar y Pagar
                </button>
            </form>

            <form action="#" method="POST">
                <button type="button" class="btn-cancel" onclick="confirmarCancelacion()">
                    <i class="fas fa-times-circle"></i> Cancelar Pedido Completo
                </button>
            </form>
        </div>
    </div>

    <script>
        function sumar(id) {
            let input = document.getElementById(id);
            input.value = parseInt(input.value) + 1;
            // Aquí en producción mandarías una petición fetch() a tu API
        }

        function restar(id) {
            let input = document.getElementById(id);
            if(parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
                // Aquí en producción mandarías una petición fetch() a tu API
            }
        }

        function confirmarCancelacion() {
            if(confirm('¿Estás seguro de que deseas vaciar tu carrito y cancelar el pedido?')) {
                // Aquí en producción redirigirías a la ruta de limpieza de carrito
                alert('Pedido cancelado correctamente.');
                window.location.href = '/catalogo'; 
            }
        }
    </script>
</body>
</html>