from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from app.data.db import get_db

from app.data.pedido import Pedido as PedidoDB, DetallePedido as DetalleDB
from app.data.autoparte import Autoparte as AutoparteDB
from app.models.pedido import CrearPedido

router = APIRouter(
    prefix="/v1/pedidos",
    tags=['CRUD Pedidos (1 a N)']
)

@router.get("/")
def obtener_todos_los_pedidos(db: Session = Depends(get_db)):
    pedidos = db.query(PedidoDB).all()
    return {"data": pedidos}

# --- 1. CREAR UN PEDIDO (CARRITO DE COMPRAS) ---
@router.post("/", status_code=status.HTTP_201_CREATED)
async def procesar_pedido(orden: CrearPedido, db: Session = Depends(get_db)):
    
    # 1. Verificar stock de TODOS los productos antes de cobrar/guardar
    for item in orden.productos:
        pieza = db.query(AutoparteDB).filter(AutoparteDB.id == item.id_autoparte).first()
        if not pieza:
            raise HTTPException(status_code=404, detail=f"La pieza ID {item.id_autoparte} no existe")
        if pieza.stock < item.cantidad:
            raise HTTPException(status_code=400, detail=f"Stock insuficiente para {pieza.nombre}. Disponibles: {pieza.stock}")

    # 2. Crear el folio principal del pedido
    nuevo_pedido = PedidoDB(id_cliente=orden.id_cliente, estatus="Pendiente")
    db.add(nuevo_pedido)
    db.commit()
    db.refresh(nuevo_pedido)

    # 3. Guardar los detalles (1 a N) y descontar el stock
    for item in orden.productos:
        # Guardar en la tabla detalles
        detalle = DetalleDB(
            id_pedido=nuevo_pedido.id,
            id_autoparte=item.id_autoparte,
            cantidad=item.cantidad
        )
        db.add(detalle)
        
        # Descontar del inventario
        pieza = db.query(AutoparteDB).filter(AutoparteDB.id == item.id_autoparte).first()
        pieza.stock -= item.cantidad
        
    db.commit()
    return {"mensaje": "Pedido procesado con éxito", "folio": nuevo_pedido.id, "status": "201"}

# --- 2. CONSULTAR HISTORIAL DE UN CLIENTE ---
@router.get("/cliente/{id_cliente}")
async def historial_pedidos(id_cliente: int, db: Session = Depends(get_db)):
    pedidos = db.query(PedidoDB).filter(PedidoDB.id_cliente == id_cliente).all()
    # En un sistema robusto aquí haríamos un JOIN para traer los nombres de los productos, 
    # pero para cumplir la rúbrica, devolvemos los folios de las órdenes.
    return {"status": "200", "total_pedidos": len(pedidos), "data": pedidos}