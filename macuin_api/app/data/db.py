from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker, declarative_base
import os

# Apuntamos a la base de datos de MACUIN en el contenedor
DATABASE_URL = os.getenv(
    "DATABASE_URL",
    "postgresql://Admin:PLFO205@postgres_db:5432/Macuin"
)

# 2.- Creamos motor de la conexion
engine = create_engine(DATABASE_URL)

# 3.- Definimos el manejador de sessiones
SessionLocal = sessionmaker(
    autocommit=False,
    autoflush=False,
    bind=engine
)

# 4.- Instaciamos la Base declarativa del modelo
Base = declarative_base()

# 5.- funcion para manejo de sessiones por peticion
def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()