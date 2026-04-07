from flask import Flask, render_template, request, redirect, url_for
import requests

app = Flask(__name__)

# Esta es la URL interna de tu API gracias a la red de Docker
API_URL = "http://macuin-api:8000/v1"

# --- 1. LEER INVENTARIO (GET) ---
@app.route('/inventario')
def inventario():
    try:
        # Flask le pide los datos a FastAPI
        respuesta = requests.get(f"{API_URL}/inventario/")
        if respuesta.status_code == 200:
            datos = respuesta.json()
            autopartes = datos.get("data", [])
        else:
            autopartes = []
    except Exception as e:
        print(f"Error de conexión con la API: {e}")
        autopartes = []
        
    # Le pasamos los datos reales a tu vista HTML
    return render_template('inventario.html', inventario=autopartes)

# --- 2. AGREGAR AUTOPARTE (POST) ---
@app.route('/inventario/agregar', methods=['POST'])
def agregar_autoparte():
    # Recolectamos lo que el usuario escribió en el modal
    nueva_pieza = {
        "codigo": request.form['codigo'],
        "nombre": request.form['nombre'],
        "marca": request.form['marca'],
        "categoria": request.form['categoria'],
        "stock": int(request.form['stock'])
    }
    # Se lo mandamos a FastAPI para que lo guarde en PostgreSQL
    requests.post(f"{API_URL}/inventario/", json=nueva_pieza)
    
    # Recargamos la página
    return redirect(url_for('inventario'))

# --- 3. ELIMINAR AUTOPARTE (DELETE) ---
@app.route('/inventario/eliminar/<int:id>')
def eliminar_autoparte(id):
    requests.delete(f"{API_URL}/inventario/{id}")
    return redirect(url_for('inventario'))

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)