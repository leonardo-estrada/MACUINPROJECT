from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from typing import List
from app.data.db import get_db

from app.data.autoparte import Autoparte as AutoparteDB
from app.models.autoparte import CrearAutoparte, ActualizarAutoparte

router = APIRouter(
    prefix="/v1/inventario",
    tags=['CRUD Inventario (Autopartes)']
)

# --- 1. LEER TODOS (GET) ---
@router.get("/")
def obtener_autopartes(db: Session = Depends(get_db)):
    inventario = db.query(AutoparteDB).all()
    return {"status": "200", "total": len(inventario), "data": inventario}

# --- 2. CREAR (POST) ---
@router.post("/", status_code=status.HTTP_201_CREATED)
def registrar_autoparte(pieza: CrearAutoparte, db: Session = Depends(get_db)):
    # Verificamos que el código de producto no exista
    codigo_existente = db.query(AutoparteDB).filter(AutoparteDB.codigo == pieza.codigo).first()
    if codigo_existente:
        raise HTTPException(status_code=400, detail="Ya existe una autoparte con este código")

    nueva_pieza = AutoparteDB(
        codigo=pieza.codigo,
        nombre=pieza.nombre,
        marca=pieza.marca,
        categoria=pieza.categoria,
        stock=pieza.stock,
        precio=pieza.precio
    )
    db.add(nueva_pieza)
    db.commit()
    db.refresh(nueva_pieza)
    
    return {"mensaje": "Autoparte registrada en inventario", "status": "201", "data": nueva_pieza}

# --- 3. ACTUALIZAR (PUT) ---
@router.put("/{id_autoparte}")
def actualizar_autoparte(id_autoparte: int, datos_nuevos: ActualizarAutoparte, db: Session = Depends(get_db)):
    pieza_actual = db.query(AutoparteDB).filter(AutoparteDB.id == id_autoparte).first()
    
    if not pieza_actual:
        raise HTTPException(status_code=404, detail="Autoparte no encontrada")
    
    if datos_nuevos.nombre is not None:
        pieza_actual.nombre = datos_nuevos.nombre
    if datos_nuevos.marca is not None:
        pieza_actual.marca = datos_nuevos.marca
    if datos_nuevos.categoria is not None:
        pieza_actual.categoria = datos_nuevos.categoria
    if datos_nuevos.stock is not None:
        pieza_actual.stock = datos_nuevos.stock
    if datos_nuevos.precio is not None:
        pieza_actual.precio = datos_nuevos.precio
        
    db.commit()
    db.refresh(pieza_actual)
    return {"mensaje": "Inventario actualizado", "status": "200", "data": pieza_actual}

# --- 4. ELIMINAR (DELETE) ---
@router.delete("/{id_autoparte}")
def eliminar_autoparte(id_autoparte: int, db: Session = Depends(get_db)):
    pieza_eliminar = db.query(AutoparteDB).filter(AutoparteDB.id == id_autoparte).first()
    
    if not pieza_eliminar:
        raise HTTPException(status_code=404, detail="Autoparte no encontrada")
        
    db.delete(pieza_eliminar)
    db.commit()
    return {"mensaje": "Autoparte eliminada del catálogo", "status": "200"}

@router.post("/bulk", status_code=status.HTTP_201_CREATED)
def crear_multiples(piezas: List[CrearAutoparte], db: Session = Depends(get_db)):
    try:
        for p in piezas:
            # Verificación rápida para no chocar con códigos duplicados
            existe = db.query(AutoparteDB).filter(AutoparteDB.codigo == p.codigo).first()
            if not existe:
                # Usamos model_dump() que es el estándar de Pydantic V2
                nueva_pieza = AutoparteDB(**p.model_dump())
                db.add(nueva_pieza)
        
        db.commit()
        return {"mensaje": f"Se procesaron {len(piezas)} piezas correctamente.", "status": "201"}
    except Exception as e:
        db.rollback() # Si algo falla, deshacemos los cambios para no corromper la BD
        raise HTTPException(status_code=500, detail=f"Error al insertar bulk: {str(e)}")