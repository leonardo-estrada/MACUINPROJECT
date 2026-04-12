from pydantic import BaseModel, Field
from typing import List

# Este modelo representa lo que va dentro del carrito
class ItemPedido(BaseModel):
    id_autoparte: int = Field(..., description="ID del producto a comprar")
    cantidad: int = Field(..., gt=0, description="Debe pedir al menos 1")

# Este modelo representa la orden completa que manda Laravel
class CrearPedido(BaseModel):
    id_cliente: int = Field(..., description="ID del usuario que hace la compra")
    productos: List[ItemPedido] = Field(..., min_length=1, description="Lista de 1 a N productos")

class EstatusUpdate(BaseModel):
    estatus: str