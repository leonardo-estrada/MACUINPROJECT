from flask import Flask, render_template
import os

app = Flask(__name__, 
            template_folder=os.path.join(os.path.dirname(__file__), 'templates'),
            static_folder=os.path.join(os.path.dirname(__file__), 'static'),
            static_url_path='/static')

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

@app.route('/stock-entrada')
def stock_entrada():
    return render_template('StockE.html')

@app.route('/stock-salida')
def stock_salida():
    return render_template('StockS.html')

@app.route('/password-recovery')
def passwd1():
    return render_template('Passowrd.html')

@app.route('/password-verify')
def passwd2():
    return render_template('Passowrd2.html')

@app.route('/password-reset')
def passwd3():
    return render_template('Passowrd3.html')
@app.route('/empleados')
def empleados():
    return render_template('empleados.html')

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5002)