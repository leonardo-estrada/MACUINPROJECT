# Usamos una imagen oficial y ligera de Python
FROM python:3.10-slim

# Establecemos el directorio de trabajo dentro del contenedor
WORKDIR /app

# Evita que Python genere archivos .pyc y asegura que la salida de consola no se bloquee
ENV PYTHONDONTWRITEBYTECODE 1
ENV PYTHONUNBUFFERED 1

# Copiamos primero el archivo de requerimientos para aprovechar el caché de Docker
COPY requirements.txt /app/

# Instalamos las dependencias
RUN pip install --upgrade pip && \
    pip install --no-cache-dir -r requirements.txt

# Copiamos el resto del código de la aplicación
COPY . /app/

# Exponemos el puerto en el que correrá Flask
EXPOSE 5000

# Comando para ejecutar la aplicación
CMD ["flask", "run", "--host=0.0.0.0"]