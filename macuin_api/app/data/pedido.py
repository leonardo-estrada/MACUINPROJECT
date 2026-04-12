from sqlalchemy import Column, Integer, String, ForeignKey, DateTime, Float
from sqlalchemy.sql import func
from app.data.db import Base

class Pedido(Base):
    __tablename__ = "tb_pedidos"
    
    id = Column(Integer, primary_key=True, index=True)
    # Llave foránea que conecta con el cliente externo que compró
    id_cliente = Column(Integer, ForeignKey("tb_clientes.id"), nullable=False)
    fecha = Column(DateTime(timezone=True), server_default=func.now())
    estatus = Column(String(50), default="Pendiente")
    total = Column(Float, default=0.0)
class DetallePedido(Base):
    __tablename__ = "tb_pedido_detalles"
    
    id = Column(Integer, primary_key=True, index=True)
    # Llave foránea que conecta con el folio del pedido
    id_pedido = Column(Integer, ForeignKey("tb_pedidos.id"), nullable=False)
    # Llave foránea que conecta con la pieza del inventario
    id_autoparte = Column(Integer, ForeignKey("tb_autopartes.id"), nullable=False)
    cantidad = Column(Integer, nullable=False)
    precio_unitario = Column(Float, default=0.0)