from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from app.data.db import get_db

from app.data.pedido import Pedido as PedidoDB, DetallePedido as DetalleDB
from app.data.autoparte import Autoparte as AutoparteDB
from app.data.usuario_externo import UsuarioExterno as ClienteDB # Importamos el modelo del cliente
from app.models.pedido import CrearPedido, EstatusUpdate

router = APIRouter(
    prefix="/v1/pedidos",
    tags=['CRUD Pedidos (1 a N)']
)

@router.get("/")
def obtener_todos_los_pedidos(db: Session = Depends(get_db)): # Eliminamos el 'async'
    pedidos = db.query(PedidoDB).all()
    return {"data": pedidos}

# --- 1. CREAR UN PEDIDO (CARRITO DE COMPRAS) ---
@router.post("/", status_code=status.HTTP_201_CREATED)
def procesar_pedido(orden: CrearPedido, db: Session = Depends(get_db)): # Eliminamos el 'async'
    
    # --- FIX DEL ERROR: Validar que el cliente realmente exista ---
    cliente = db.query(ClienteDB).filter(ClienteDB.id == orden.id_cliente).first()
    if not cliente:
        raise HTTPException(status_code=404, detail=f"El cliente con ID {orden.id_cliente} no está registrado en la base de datos.")

    # --- OPTIMIZACIÓN N+1: Traer todos los productos en una sola consulta ---
    ids_solicitados = [item.id_autoparte for item in orden.productos]
    piezas_db = db.query(AutoparteDB).filter(AutoparteDB.id.in_(ids_solicitados)).all()
    
    # Convertimos a diccionario para búsqueda en memoria (súper rápido)
    catalogo = {pieza.id: pieza for pieza in piezas_db}

    # Validar stock en memoria antes de afectar la base de datos
    for item in orden.productos:
        if item.id_autoparte not in catalogo:
            raise HTTPException(status_code=404, detail=f"La pieza ID {item.id_autoparte} no existe en el inventario.")
        
        pieza = catalogo[item.id_autoparte]
        if pieza.stock < item.cantidad:
            raise HTTPException(status_code=400, detail=f"Stock insuficiente para {pieza.nombre}. Solicitado: {item.cantidad}, Disponible: {pieza.stock}")

    # --- CREACIÓN DEL PEDIDO ---
    nuevo_pedido = PedidoDB(id_cliente=orden.id_cliente, estatus="Pendiente", total=0.0)
    db.add(nuevo_pedido)
    db.flush() 

    total_calculado = 0.0 # Acumulador para la orden completa

    # 2. Guardar detalles, descontar stock y calcular precios
    for item in orden.productos:
        pieza_bd = catalogo[item.id_autoparte]
        
        # Matemáticas de la orden
        subtotal = pieza_bd.precio * item.cantidad
        total_calculado += subtotal
        
        detalle = DetalleDB(
            id_pedido=nuevo_pedido.id,
            id_autoparte=item.id_autoparte,
            cantidad=item.cantidad,
            precio_unitario=pieza_bd.precio # <--- CONGELAMOS EL PRECIO ACTUAL AQUÍ
        )
        db.add(detalle)
        
        # Descontamos el stock
        pieza_bd.stock -= item.cantidad
        
    # 3. Asignamos el total real al maestro antes de sellar
    nuevo_pedido.total = total_calculado
    db.commit() 
    
    return {"mensaje": "Pedido procesado", "folio": nuevo_pedido.id, "total": total_calculado, "status": "201"}

# --- 2. CONSULTAR HISTORIAL DE UN CLIENTE ---
@router.get("/cliente/{id_cliente}")
def historial_pedidos(id_cliente: int, db: Session = Depends(get_db)): # Eliminamos el 'async'
    pedidos = db.query(PedidoDB).filter(PedidoDB.id_cliente == id_cliente).all()
    return {"status": "200", "total_pedidos": len(pedidos), "data": pedidos}

# --- 3. VER DETALLE DE UN PEDIDO (MAESTRO-DETALLE) ---
@router.get("/{id_pedido}")
def ver_detalle_pedido(id_pedido: int, db: Session = Depends(get_db)):
    # Buscamos el maestro
    pedido = db.query(PedidoDB).filter(PedidoDB.id == id_pedido).first()
    if not pedido:
        raise HTTPException(status_code=404, detail="Pedido no encontrado")
        
    # Buscamos los detalles (N productos)
    detalles = db.query(DetalleDB).filter(DetalleDB.id_pedido == id_pedido).all()
    
    # Armamos la lista de productos consultando el nombre de cada pieza
    productos_lista = []
    for det in detalles:
        pieza = db.query(AutoparteDB).filter(AutoparteDB.id == det.id_autoparte).first()
        productos_lista.append({
            "id_autoparte": det.id_autoparte,
            "nombre_pieza": pieza.nombre if pieza else "Pieza eliminada/desconocida",
            "cantidad": det.cantidad,
            "precio_unitario": det.precio_unitario
        })
        
    return {
        "status": "200", 
        "data": {
            "folio": pedido.id,
            "id_cliente": pedido.id_cliente,
            "estatus": pedido.estatus,
            "fecha": pedido.fecha.strftime('%Y-%m-%d') if pedido.fecha else "Sin fecha",
            "productos": productos_lista
        }
    }

# --- 4. ACTUALIZAR ESTATUS DEL PEDIDO ---
@router.patch("/{id_pedido}/estatus")
def actualizar_estatus(id_pedido: int, payload: EstatusUpdate, db: Session = Depends(get_db)):
    pedido = db.query(PedidoDB).filter(PedidoDB.id == id_pedido).first()
    if not pedido:
        raise HTTPException(status_code=404, detail="Pedido no encontrado")
        
    pedido.estatus = payload.estatus
    db.commit()
    db.refresh(pedido)
    return {"mensaje": f"Estatus actualizado a {payload.estatus}", "status": "200"}