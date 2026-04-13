from flask import Flask, render_template, request, redirect, url_for, send_file, Response, jsonify, session, flash
from datetime import datetime
from zoneinfo import ZoneInfo
from functools import wraps
import requests
import os
import io

# Respetamos tu configuración original para que carguen los CSS y Logos
app = Flask(__name__, 
            template_folder=os.path.join(os.path.dirname(__file__), 'templates'),
            static_folder=os.path.join(os.path.dirname(__file__), 'static'),
            static_url_path='/static')
app.secret_key = 'macuin_secret_key_pro'
def login_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        # Si no hay un usuario en la sesión, lo pateamos de vuelta al login
        if 'user_id' not in session:
            return redirect(url_for('login'))
        return f(*args, **kwargs)
    return decorated_function

# Esta es la URL interna de tu API gracias a la red de Docker
API_URL = "http://macuin-api:8000/v1"

# RUTAS DE AUTENTICACIÓN Y RECUPERACIÓN

@app.route('/', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        correo = request.form.get('correo')
        password = request.form.get('password')
        
        resp = requests.post(f"{API_URL}/empleados/login", json={
            "correo": correo, 
            "password": password
        })
        
        if resp.status_code == 200:
            user_data = resp.json().get('usuario')
            # Guardamos datos en la sesión de Flask
            session['user_id'] = user_data['id']
            session['user_name'] = user_data['nombre']
            session['user_role'] = user_data['rol']
            return redirect(url_for('reportes'))
        else:
            flash("Credenciales inválidas o cuenta inactiva", "error")
            
    return render_template('login.html')

# --- RUTA DE LOGOUT ---
@app.route('/logout')
def logout():
    session.clear() # Limpia toda la sesión
    return redirect(url_for('login'))

# --- FLUJO DE RECUPERACIÓN (3 PASOS) ---

@app.route('/recuperar-paso1', methods=['GET', 'POST'])
def pass1():
    if request.method == 'POST':
        correo = request.form.get('correo')
        session['recovery_email'] = correo
        
        # Disparamos la generación del código en la API
        requests.post(f"{API_URL}/empleados/recuperar/solicitar", json={"correo": correo})
        
        return redirect(url_for('pass2'))
    return render_template('Passowrd.html')

@app.route('/recuperar-paso2', methods=['GET', 'POST'])
def pass2():
    # Bloqueo de seguridad: Si no hay correo en sesión, lo regresamos al paso 1
    if 'recovery_email' not in session:
        return redirect(url_for('pass1'))
        
    if request.method == 'POST':
        codigo = request.form.get('codigo') # Asegúrate que tu input en Passowrd2.html se llame "codigo"
        correo = session.get('recovery_email')
        
        resp = requests.post(f"{API_URL}/empleados/recuperar/validar", json={"correo": correo, "token": codigo})
        
        if resp.status_code == 200:
            session['recovery_verified'] = True # Damos luz verde para cambiar contraseña
            return redirect(url_for('pass3'))
        else:
            flash("El código ingresado es incorrecto.", "error")
            
    return render_template('Passowrd2.html')

@app.route('/recuperar-paso3', methods=['GET', 'POST'])
def pass3():
    # Bloqueo de seguridad: Si no pasó por la validación del código, lo sacamos
    if not session.get('recovery_verified'):
        return redirect(url_for('pass1'))
        
    if request.method == 'POST':
        nueva_p = request.form.get('password')
        correo = session.get('recovery_email')
        
        resp = requests.post(f"{API_URL}/empleados/recuperar/reset", json={
            "correo": correo,
            "nueva_password": nueva_p
        })
        
        if resp.status_code == 200:
            # Limpiamos las variables temporales de sesión
            session.pop('recovery_email', None)
            session.pop('recovery_verified', None)
            flash("Contraseña restablecida con éxito. Ya puedes iniciar sesión.", "success")
            return redirect(url_for('login'))
            
    return render_template('Passowrd3.html')# RUTAS DEL PANEL ADMINISTRATIVO
@app.route('/')
@app.route('/reportes')
@login_required
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
@login_required
def pedidos():
    try:
        # Atrapamos el filtro desde la URL
        filtro = request.args.get('filtro', 'Todos')

        # Consumimos la API de Pedidos 
        resp_pedidos = requests.get(f"{API_URL}/pedidos/")
        todos_pedidos = resp_pedidos.json().get("data", []) if resp_pedidos.status_code == 200 else []

        zona_local = ZoneInfo("America/Mexico_City")

        for p in todos_pedidos:
            fecha_raw = p.get('fecha')
            if fecha_raw:
                try:
                    # 1. Parseamos la fecha ISO del servidor (ej. 2026-04-12T17:28:35+00:00)
                    fecha_utc = datetime.fromisoformat(fecha_raw.replace('Z', '+00:00'))
                    
                    # 2. Convertimos el tiempo UTC al tiempo local real
                    fecha_local = fecha_utc.astimezone(zona_local)
                    
                    # 3. Formateamos la salida visual
                    p['fecha_formateada'] = fecha_local.strftime("%d/%m/%Y")
                    
                    # Formateamos la hora a 12h y reemplazamos AM/PM por a.m./p.m.
                    hora_str = fecha_local.strftime("%I:%M %p").lower()
                    p['hora_formateada'] = hora_str.replace("am", "a.m.").replace("pm", "p.m.")
                    
                except Exception as e:
                    print(f"Error parseando fecha: {e}")
                    p['fecha_formateada'] = "Error de formato"
                    p['hora_formateada'] = ""
            else:
                p['fecha_formateada'] = "N/A"
                p['hora_formateada'] = ""
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
@login_required
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
@login_required
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
    filtro = request.args.get('filtro', 'todos')
    fecha_inicio = request.args.get('fecha_inicio', '')
    fecha_fin = request.args.get('fecha_fin', '')
    
    url_api = f"{API_URL}/reportes/{tipo}/{formato}"
    
    # Empaquetamos los parámetros de forma segura
    parametros = {"filtro": filtro}
    if fecha_inicio:
        parametros["fecha_inicio"] = fecha_inicio
    if fecha_fin:
        parametros["fecha_fin"] = fecha_fin
    
    # requests codifica los espacios automáticamente (ej. "En Proceso" -> "En%20Proceso")
    resp = requests.get(url_api, params=parametros)
    
    if resp.status_code == 200:
        return Response(
            resp.content,
            headers={
                "Content-Disposition": resp.headers.get("Content-Disposition", f"attachment; filename=Reporte_Macuin_{tipo}.{formato}"),
                "Content-Type": resp.headers.get("Content-Type")
            }
        )
    return "Error al generar reporte", 400

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)