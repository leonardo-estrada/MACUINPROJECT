from flask import Flask, render_template, request, redirect, url_for
import requests
import os

# Respetamos tu configuración original para que carguen los CSS y Logos
app = Flask(__name__, 
            template_folder=os.path.join(os.path.dirname(__file__), 'templates'),
            static_folder=os.path.join(os.path.dirname(__file__), 'static'),
            static_url_path='/static')

# Esta es la URL interna de tu API gracias a la red de Docker
API_URL = "http://macuin-api:8000/v1"

# ==========================================
# RUTAS DE AUTENTICACIÓN Y RECUPERACIÓN
# ==========================================
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

# ==========================================
# RUTAS DEL PANEL ADMINISTRATIVO (ESTÁTICAS POR AHORA)
# ==========================================
@app.route('/dashboard')
def dashboard():
    return render_template('autopartes.html')

@app.route('/pedidos')
def pedidos():
    return render_template('GPedidos.html')

@app.route('/reportes')
def reportes():
    return render_template('reportes.html')

@app.route('/stock-entrada')
def stock_entrada():
    return render_template('StockE.html')

@app.route('/stock-salida')
def stock_salida():
    return render_template('StockS.html')

# ==========================================
# MÓDULO 1: INVENTARIO (CONECTADO A FASTAPI)
# ==========================================
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
    # Recolectamos los datos que modificaron en el modal
    datos_actualizados = {
        "nombre": request.form['nombre'],
        "stock": int(request.form['stock'])
    }
    
    # Le disparamos el método PUT a FastAPI
    respuesta = requests.put(f"{API_URL}/inventario/{id}", json=datos_actualizados)
    
    if respuesta.status_code != 200:
        print("Error al actualizar:", respuesta.json())
        
    return redirect(url_for('inventario'))

@app.route('/inventario/eliminar/<int:id>')
def eliminar_autoparte(id):
    requests.delete(f"{API_URL}/inventario/{id}")
    return redirect(url_for('inventario'))

# ==========================================
# MÓDULO 2: EMPLEADOS (CONECTADO A FASTAPI)
# ==========================================
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

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)