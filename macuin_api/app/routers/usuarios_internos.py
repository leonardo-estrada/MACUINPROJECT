from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from app.data.db import get_db

from app.data.usuario_interno import UsuarioInterno as EmpleadoDB
from app.models.usuario_interno import CrearUsuarioInterno, ActualizarUsuarioInterno

router = APIRouter(
    prefix="/v1/empleados",
    tags=['CRUD Usuarios Internos (Personal)']
)

# --- 1. LEER TODOS (GET) ---
@router.get("/")
async def obtener_empleados(db: Session = Depends(get_db)):
    empleados = db.query(EmpleadoDB).all()
    return {"status": "200", "total": len(empleados), "data": empleados}

# --- 2. CREAR (POST) ---
@router.post("/", status_code=status.HTTP_201_CREATED)
async def registrar_empleado(empleado: CrearUsuarioInterno, db: Session = Depends(get_db)):
    correo_existente = db.query(EmpleadoDB).filter(EmpleadoDB.correo == empleado.correo).first()
    if correo_existente:
        raise HTTPException(status_code=400, detail="Este correo ya pertenece a un empleado")

    nuevo_empleado = EmpleadoDB(
        nombre=empleado.nombre,
        correo=empleado.correo,
        password=empleado.password,
        departamento=empleado.departamento,
        activo=empleado.activo
    )
    db.add(nuevo_empleado)
    db.commit()
    db.refresh(nuevo_empleado)
    
    return {"mensaje": "Empleado dado de alta", "status": "201", "data": nuevo_empleado}

# --- 3. ACTUALIZAR (PUT) ---
@router.put("/{id_empleado}")
async def actualizar_empleado(id_empleado: int, datos_nuevos: ActualizarUsuarioInterno, db: Session = Depends(get_db)):
    empleado_actual = db.query(EmpleadoDB).filter(EmpleadoDB.id == id_empleado).first()
    
    if not empleado_actual:
        raise HTTPException(status_code=404, detail="Empleado no encontrado")
    
    if datos_nuevos.nombre is not None:
        empleado_actual.nombre = datos_nuevos.nombre
    if datos_nuevos.departamento is not None:
        empleado_actual.departamento = datos_nuevos.departamento
    if datos_nuevos.activo is not None:
        empleado_actual.activo = datos_nuevos.activo
        
    db.commit()
    db.refresh(empleado_actual)
    return {"mensaje": "Datos del empleado actualizados", "status": "200", "data": empleado_actual}

# --- 4. ELIMINAR / DAR DE BAJA (DELETE) ---
@router.delete("/{id_empleado}")
async def eliminar_empleado(id_empleado: int, db: Session = Depends(get_db)):
    empleado_eliminar = db.query(EmpleadoDB).filter(EmpleadoDB.id == id_empleado).first()
    
    if not empleado_eliminar:
        raise HTTPException(status_code=404, detail="Empleado no encontrado")
        
    db.delete(empleado_eliminar)
    db.commit()
    return {"mensaje": "Registro de empleado eliminado del sistema", "status": "200"}