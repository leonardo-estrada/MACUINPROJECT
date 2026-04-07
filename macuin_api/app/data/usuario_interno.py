from sqlalchemy import Column, Integer, String, Boolean
from app.data.db import Base

class UsuarioInterno(Base):
    __tablename__ = "tb_empleados"
    
    id = Column(Integer, primary_key=True, index=True)
    nombre = Column(String(100), nullable=False)
    correo = Column(String(150), unique=True, index=True, nullable=False)
    password = Column(String(255), nullable=False)
    departamento = Column(String(50), nullable=False) # ej: Sistemas, Ventas, Almacén
    activo = Column(Boolean, default=True) # True = Activo, False = Inactivo