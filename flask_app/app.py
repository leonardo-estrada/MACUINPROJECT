from flask import Flask, render_template, request, redirect, url_for, send_file, Response, jsonify
import requests
import os
import io

# Respetamos tu configuración original para que carguen los CSS y Logos
app = Flask(__name__, 
            template_folder=os.path.join(os.path.dirname(__file__), 'templates'),
            static_folder=os.path.join(os.path.dirname(__file__), 'static'),
            static_url_path='/static')

# Esta es la URL interna de tu API gracias a la red de Docker
API_URL = "http://macuin-api:8000/v1"

# RUTAS DE AUTENTICACIÓN Y RECUPERACIÓN

@app.route('/')
def login():
    return render_template('login.html')

@app.route('/password-recovery')
def passwd1():
    return render_template('Passowrd.html')

@app.route('/password-verify')
def passwd2():
    return render_template('Passowrd2.html')

@app.route('/password-reset')
def passwd3():
    return render_template('Passowrd3.html')

# RUTAS DEL PANEL ADMINISTRATIVO
@app.route('/')
@app.route('/reportes')
def reportes():
    try:
        # Función auxiliar para no chocar si FastAPI devuelve Lista o Diccionario
        def extraer_lista(respuesta):
            if respuesta.status_code != 200 and respuesta.status_code != 201:
                return []
            datos = respuesta.json()
            return datos.get("data", []) if isinstance(datos, dict) else datos

        inventario = extraer_lista(requests.get(f"{API_URL}/inventario/"))
        empleados = extraer_lista(requests.get(f"{API_URL}/empleados/"))
        clientes = extraer_lista(requests.get(f"{API_URL}/clientes/"))
        pedidos = extraer_lista(requests.get(f"{API_URL}/pedidos/"))
        
        # Matemáticas para las Tarjetas
        total_piezas = sum([item.get('stock', 0) for item in inventario])
        
        # Procesar datos para Gráfica de Dona (Inventario)
        categorias_grafica = {}
        for item in inventario:
            cat = item.get('categoria', 'Otros')
            categorias_grafica[cat] = categorias_grafica.get(cat, 0) + item.get('stock', 0)
            
        # Procesar datos para Gráfica de Barras (Pedidos por Estatus)
        estatus_grafica = {}
        for ped in pedidos:
            # Si el pedido no trae 'estatus' de la base de datos, le ponemos 'Pendiente' por defecto
            est = ped.get('estatus', 'Pendiente')
            estatus_grafica[est] = estatus_grafica.get(est, 0) + 1

        stats = {
            "piezas": total_piezas,
            "empleados": len(empleados),
            "clientes": len(clientes),
            "pedidos": len(pedidos) # <--- AQUÍ VEMOS SI LLEGAN
        }
        
    except Exception as e:
        print(f"🔥 ERROR EN FLASK: {e}")
        stats = {"piezas": 0, "empleados": 0, "clientes": 0, "pedidos": 0}
        categorias_grafica = {}
        estatus_grafica = {}
        
    return render_template('reportes.html', 
                           stats=stats, 
                           labels_inv=list(categorias_grafica.keys()), 
                           data_inv=list(categorias_grafica.values()),
                           labels_ped=list(estatus_grafica.keys()),
                           data_ped=list(estatus_grafica.values()))

@app.route('/pedidos')
def pedidos():
    try:
        # Atrapamos el filtro desde la URL
        filtro = request.args.get('filtro', 'Todos')

        # Consumimos la API de Pedidos 
        resp_pedidos = requests.get(f"{API_URL}/pedidos/")
        todos_pedidos = resp_pedidos.json().get("data", []) if resp_pedidos.status_code == 200 else []
        
        # Necesitamos los clientes para vincular nombres en la tabla
        resp_clientes = requests.get(f"{API_URL}/clientes/")
        lista_clientes = resp_clientes.json().get("data", []) if resp_clientes.status_code == 200 else []
        clientes_dict = {cli['id']: cli for cli in lista_clientes}

        # Cálculo de estadísticas en tiempo real para las tarjetas
        stats = {
            "total": len(todos_pedidos),
            "pendientes": len([p for p in todos_pedidos if p.get('estatus') == 'Pendiente']),
            "en_proceso": len([p for p in todos_pedidos if p.get('estatus') == 'En Proceso']),
            "enviados": len([p for p in todos_pedidos if p.get('estatus') == 'Enviado']),
            "entregados": len([p for p in todos_pedidos if p.get('estatus') == 'Entregado']),
            "cancelados": len([p for p in todos_pedidos if p.get('estatus') == 'Cancelado'])
        }

        # Aplicamos el filtro a la lista de la tabla
        if filtro != 'Todos':
            lista_pedidos = [p for p in todos_pedidos if p.get('estatus') == filtro]
        else:
            lista_pedidos = todos_pedidos

    except Exception as e:
        print(f"Error en Gestión de Pedidos: {e}")
        lista_pedidos, clientes_dict = [], {}
        stats = {"total": 0, "pendientes": 0, "en_proceso": 0, "enviados": 0, "entregados": 0, "cancelados": 0}
        filtro = "Todos"

    return render_template('GPedidos.html', 
                           pedidos=lista_pedidos, 
                           clientes=clientes_dict, 
                           stats=stats,
                           filtro_actual=filtro) # Enviamos el filtro al front

# --- RUTAS DE ACCIÓN DE PEDIDOS ---

@app.route('/pedidos/<int:id>/estatus', methods=['POST'])
def cambiar_estatus_pedido(id):
    # El front nos enviará el 'nuevo_estatus' por formulario
    nuevo_estatus = request.form.get('nuevo_estatus')
    requests.patch(f"{API_URL}/pedidos/{id}/estatus", json={"estatus": nuevo_estatus})
    return redirect(url_for('pedidos'))

@app.route('/pedidos/<int:id>/detalle')
def detalle_pedido_json(id):
    # Endpoint para que el front lo consuma mediante Fetch API y llene un Modal
    resp = requests.get(f"{API_URL}/pedidos/{id}")
    if resp.status_code == 200:
        return jsonify(resp.json().get("data", {}))
    return jsonify({"error": "No se pudo cargar el detalle"}), 400

@app.route('/stock-entrada')
def stock_entrada():
    return render_template('StockE.html')

@app.route('/stock-salida')
def stock_salida():
    return render_template('StockS.html')

# MÓDULO 1: INVENTARIO

@app.route('/inventario')
def inventario():
    try:
        respuesta = requests.get(f"{API_URL}/inventario/")
        autopartes = respuesta.json().get("data", []) if respuesta.status_code == 200 else []
    except Exception as e:
        print(f"Error API Inventario: {e}")
        autopartes = []
    return render_template('inventario.html', inventario=autopartes)

@app.route('/inventario/agregar', methods=['POST'])
def agregar_autoparte():
    nueva_pieza = {
        "codigo": request.form['codigo'],
        "nombre": request.form['nombre'],
        "marca": request.form['marca'],
        "categoria": request.form['categoria'],
        "precio": float(request.form.get('precio', 0.0)),
        "stock": int(request.form['stock'])
    }
    requests.post(f"{API_URL}/inventario/", json=nueva_pieza)
    return redirect(url_for('inventario'))

@app.route('/inventario/editar/<int:id>', methods=['POST'])
def editar_autoparte(id):
    datos_actualizados = {
        "nombre": request.form['nombre'],
        "precio": float(request.form.get('precio', 0.0)),
        "stock": int(request.form['stock'])
    }
    
    respuesta = requests.put(f"{API_URL}/inventario/{id}", json=datos_actualizados)
    
    if respuesta.status_code != 200:
        print("Error al actualizar:", respuesta.json())
        
    return redirect(url_for('inventario'))

@app.route('/inventario/eliminar/<int:id>')
def eliminar_autoparte(id):
    requests.delete(f"{API_URL}/inventario/{id}")
    return redirect(url_for('inventario'))


# MÓDULO 2: EMPLEADOS

@app.route('/empleados')
def empleados():
    try:
        respuesta = requests.get(f"{API_URL}/empleados/")
        lista_empleados = respuesta.json().get("data", []) if respuesta.status_code == 200 else []
    except Exception as e:
        print(f"Error API Empleados: {e}")
        lista_empleados = []
    return render_template('empleados.html', empleados=lista_empleados)

@app.route('/empleados/agregar', methods=['POST'])
def agregar_empleado():
    nuevo_empleado = {
        "nombre": request.form['nombre'],
        "correo": request.form['correo'],
        "password": request.form['password'],
        "departamento": request.form['departamento'],
        "activo": True  # Por defecto el usuario se crea como Activo
    }
    requests.post(f"{API_URL}/empleados/", json=nuevo_empleado)
    return redirect(url_for('empleados'))

@app.route('/empleados/editar/<int:id>', methods=['POST'])
def editar_empleado(id):
    datos_actualizados = {
        "nombre": request.form['nombre'],
        "departamento": request.form['departamento']
    }
    requests.put(f"{API_URL}/empleados/{id}", json=datos_actualizados)
    return redirect(url_for('empleados')) 

@app.route('/empleados/<int:id>/baja', methods=['POST'])
def dar_de_baja_empleado_flask(id):
    try:
        # Hacemos la petición DELETE hacia FastAPI
        url_api = f"{API_URL}/empleados/{id}"
        resp = requests.delete(url_api)
        
        if resp.status_code == 200:
            print(f"Empleado {id} dado de baja (lógica) con éxito.")
        else:
            print(f"Error al dar de baja empleado {id}: {resp.text}")
            
    except Exception as e:
        print(f"🔥 Error de conexión en Flask: {e}")
        
    # Recargamos la página para ver cómo el botón cambia a verde
    return redirect('/empleados')

@app.route('/empleados/<int:id>/reactivar', methods=['POST'])
def reactivar_empleado(id):
    try:
        # Usamos requests.patch para comunicarnos con el nuevo endpoint
        url_api = f"{API_URL}/empleados/{id}/reactivar"
        resp = requests.patch(url_api)
        
        if resp.status_code == 200:
            print(f"Empleado {id} reactivado con éxito.")
        else:
            print(f"Error al reactivar empleado {id}: {resp.text}")
            
    except Exception as e:
        print(f"Error de conexión en Flask: {e}")
        
    # Recargamos la página de empleados para ver el cambio
    return redirect('/empleados')

@app.route('/descargar-reporte/<tipo>/<formato>')
def descargar_reporte(tipo, formato):
    # 1. Atrapamos los parámetros (si no vienen, quedan vacíos)
    filtro = request.args.get('filtro', 'todos')
    fecha_inicio = request.args.get('fecha_inicio', '')
    fecha_fin = request.args.get('fecha_fin', '')
    
    # 2. Construimos la URL base
    url_api = f"{API_URL}/reportes/{tipo}/{formato}?filtro={filtro}"
    
    # 3. Le pegamos las fechas a la URL solo si el usuario las seleccionó
    if fecha_inicio:
        url_api += f"&fecha_inicio={fecha_inicio}"
    if fecha_fin:
        url_api += f"&fecha_fin={fecha_fin}"
    
    resp = requests.get(url_api)
    
    if resp.status_code == 200:
        return Response(
            resp.content,
            headers={
                "Content-Disposition": resp.headers.get("Content-Disposition", f"attachment; filename=Reporte_Macuin_{tipo}.{formato}"),
                "Content-Type": resp.headers.get("Content-Type")
            }
        )
    return "Error al generar reporte", 400

@app.route('/ventas')
def ventas():
    try:
        # 1. Obtenemos datos base
        resp_pedidos = requests.get(f"{API_URL}/pedidos/")
        pedidos = resp_pedidos.json().get("data", [])
        
        resp_inv = requests.get(f"{API_URL}/inventario/")
        inventario = {p['id']: p for p in resp_inv.json().get("data", [])}

        # 2. Lógica de Ventas: Solo pedidos que ya generaron ingreso
        ventas_reales = [p for p in pedidos if p.get('estatus') in ['Enviado', 'Entregado']]
        
        ingreso_total = 0
        por_categoria = {}

        for v in ventas_reales:
            # Aquí deberíamos consultar el detalle de cada pedido para sumar precios
            # Por ahora, simulamos el cálculo basado en el total del pedido
            ingreso_total += v.get('total_venta', 0) 

        return render_template('ventas.html', 
                               ventas=ventas_reales, 
                               ingreso=ingreso_total,
                               top_categorias=por_categoria)
    except Exception as e:
        return redirect(url_for('dashboard'))

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)