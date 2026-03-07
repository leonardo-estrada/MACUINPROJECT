from flask import Flask, render_template

app = Flask(__name__)

@app.route('/')
def login():
    return render_template('login.html')

@app.route('/dashboard')
def dashboard():
    return render_template('autopartes.html')

@app.route('/inventario')
def inventario():
    return render_template('inventario.html')

@app.route('/pedidos')
def pedidos():
    return render_template('GPedidos.html')

@app.route('/reportes')
def reportes():
    return render_template('reportes.html')

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)