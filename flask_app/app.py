from flask import Flask, render_template, request, redirect, url_for, send_file, Response
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
@app.route('/dashboard')
def dashboard():
    return render_template('autopartes.html')

@app.route('/pedidos')
def pedidos():
    return render_template('GPedidos.html')

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
        "stock": int(request.form['stock'])
    }
    requests.post(f"{API_URL}/inventario/", json=nueva_pieza)
    return redirect(url_for('inventario'))

@app.route('/inventario/editar/<int:id>', methods=['POST'])
def editar_autoparte(id):
    datos_actualizados = {
        "nombre": request.form['nombre'],
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

@app.route('/empleados/eliminar/<int:id>')
def eliminar_empleado(id):
    requests.delete(f"{API_URL}/empleados/{id}")
    return redirect(url_for('empleados'))

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

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)