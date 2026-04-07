from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from app.data.db import get_db

# Importamos nuestro modelo físico y el validador
from app.data.usuario_externo import UsuarioExterno as UsuarioDB
from app.models.usuario_externo import CrearUsuarioExterno, ActualizarUsuarioExterno

router = APIRouter(
    prefix="/v1/clientes",
    tags=['CRUD Usuarios Externos (Clientes)']
)

# --- 1. LEER TODOS (GET) ---
@router.get("/")
async def obtener_clientes(db: Session = Depends(get_db)):
    clientes = db.query(UsuarioDB).all()
    return {"status": "200", "total": len(clientes), "data": clientes}

# --- 2. CREAR (POST) ---
@router.post("/", status_code=status.HTTP_201_CREATED)
async def registrar_cliente(cliente: CrearUsuarioExterno, db: Session = Depends(get_db)):
    # Verificamos que el correo no exista ya en la BD
    correo_existente = db.query(UsuarioDB).filter(UsuarioDB.correo == cliente.correo).first()
    if correo_existente:
        raise HTTPException(status_code=400, detail="Este correo ya está registrado")

    nuevo_cliente = UsuarioDB(
        nombre=cliente.nombre,
        correo=cliente.correo,
        password=cliente.password, # En el futuro idealmente la encriptaremos
        telefono=cliente.telefono
    )
    db.add(nuevo_cliente)
    db.commit()
    db.refresh(nuevo_cliente)
    
    return {"mensaje": "Cliente registrado correctamente", "status": "201", "data": nuevo_cliente}

# --- 3. ACTUALIZAR (PUT) - ¡Corregido a BD Real! ---
@router.put("/{id_cliente}")
async def actualizar_cliente(id_cliente: int, datos_nuevos: ActualizarUsuarioExterno, db: Session = Depends(get_db)):
    cliente_actual = db.query(UsuarioDB).filter(UsuarioDB.id == id_cliente).first()
    
    if not cliente_actual:
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
    
    # Actualizamos solo los campos que enviaron
    if datos_nuevos.nombre:
        cliente_actual.nombre = datos_nuevos.nombre
    if datos_nuevos.telefono:
        cliente_actual.telefono = datos_nuevos.telefono
        
    db.commit()
    db.refresh(cliente_actual)
    return {"mensaje": "Cliente actualizado", "status": "200", "data": cliente_actual}

# --- 4. ELIMINAR (DELETE) - ¡Corregido a BD Real! ---
@router.delete("/{id_cliente}")
async def eliminar_cliente(id_cliente: int, db: Session = Depends(get_db)):
    cliente_eliminar = db.query(UsuarioDB).filter(UsuarioDB.id == id_cliente).first()
    
    if not cliente_eliminar:
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
        
    db.delete(cliente_eliminar)
    db.commit()
    return {"mensaje": "Cliente eliminado correctamente", "status": "200"}