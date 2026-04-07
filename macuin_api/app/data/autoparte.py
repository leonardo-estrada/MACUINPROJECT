from sqlalchemy import Column, Integer, String
from app.data.db import Base

class Autoparte(Base):
    __tablename__ = "tb_autopartes"
    
    id = Column(Integer, primary_key=True, index=True)
    codigo = Column(String(20), unique=True, index=True, nullable=False) # ej: FLT-001
    nombre = Column(String(150), nullable=False)
    marca = Column(String(50), nullable=False)
    categoria = Column(String(50), nullable=False) # ej: Filtros, Frenos, Eléctrico
    stock = Column(Integer, default=0, nullable=False)