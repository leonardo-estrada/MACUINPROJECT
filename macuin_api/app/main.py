from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

# 1. Importamos la conexión y TODOS los modelos físicos
from app.data.db import engine, Base
from app.data.usuario_externo import UsuarioExterno
from app.data.usuario_interno import UsuarioInterno 
from app.data.autoparte import Autoparte
from app.data.pedido import Pedido, DetallePedido

# 2. Importamos los enrutadores 
from app.routers import usuarios_externos, usuarios_internos, autopartes, pedidos, reportes

# 3. ¡Magia pura! Esta línea va a PostgreSQL y crea las tablas si no existen
Base.metadata.create_all(bind=engine)

# 4. Inicializamos la aplicación FastAPI
app = FastAPI(
    title="API Central MACUIN",
    description="Microservicio backend para la gestión de clientes, personal e inventario",
    version="1.0.0"
)

# 5. Configuración de CORS 
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"], # En producción aquí pondremos las IPs exactas de los contenedores
    allow_credentials=True,
    allow_methods=["*"], # Permite GET, POST, PUT, DELETE
    allow_headers=["*"],
)

# 6. Enchufamos los routers a la aplicación principal
app.include_router(usuarios_externos.router)
app.include_router(usuarios_internos.router)
app.include_router(autopartes.router)
app.include_router(pedidos.router)
app.include_router(reportes.router)

# 7. Ruta raíz para comprobar que el servidor está vivo (Health Check)
@app.get("/", tags=["Estado del Servidor"])
async def root():
    return {
        "status": "online",
        "mensaje": "API de MACUIN operativa y conectada a PostgreSQL",
        "tecnologias": ["FastAPI", "SQLAlchemy", "PostgreSQL"]
    }