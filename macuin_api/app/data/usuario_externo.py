from sqlalchemy import Column, Integer, String
from app.data.db import Base

class UsuarioExterno(Base):
    __tablename__ = "tb_clientes"
    
    id = Column(Integer, primary_key=True, index=True)
    nombre = Column(String(100), nullable=False)
    correo = Column(String(150), unique=True, index=True, nullable=False)
    password = Column(String(255), nullable=False)
    telefono = Column(String(20), nullable=True)