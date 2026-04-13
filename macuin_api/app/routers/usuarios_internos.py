from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from app.data.db import get_db
import random

from app.data.usuario_interno import UsuarioInterno as EmpleadoDB
from app.models.usuario_interno import CrearUsuarioInterno, ActualizarUsuarioInterno, LoginSchema,ResetPasswordSchema, SolicitarResetSchema, ValidarTokenSchema

router = APIRouter(
    prefix="/v1/empleados",
    tags=['CRUD Usuarios Internos (Personal)']
)
@router.post("/login")
def login_empleado(credenciales: LoginSchema, db: Session = Depends(get_db)):
    # Buscamos al empleado por correo
    empleado = db.query(EmpleadoDB).filter(EmpleadoDB.correo == credenciales.correo).first()
    
    if not empleado or empleado.password != credenciales.password:
        raise HTTPException(status_code=401, detail="Correo o contraseña incorrectos")
    
    if not empleado.activo:
        raise HTTPException(status_code=403, detail="Esta cuenta ha sido desactivada")
        
    return {
        "status": "200",
        "usuario": {
            "id": empleado.id,
            "nombre": empleado.nombre,
            "rol": empleado.departamento
        }
    }

# --- ENDPOINT DE RECUPERACIÓN (SIMPLIFICADO PARA LA RÚBRICA) ---
import random

# PASO 1: Generar el código
@router.post("/recuperar/solicitar")
def solicitar_recuperacion(datos: SolicitarResetSchema, db: Session = Depends(get_db)):
    empleado = db.query(EmpleadoDB).filter(EmpleadoDB.correo == datos.correo).first()
    
    # Por seguridad, siempre devolvemos "Ok" para no revelar si un correo existe o no a los hackers
    if empleado:
        # Generamos un código de 6 dígitos
        codigo = str(random.randint(100000, 999999))
        empleado.token_recuperacion = codigo
        db.commit()
        
        # ⚠️ MODO DESARROLLO: Imprimimos el código en la consola de Docker
        # En producción, aquí iría tu código de smtplib para enviar un correo real
        print(f"🛑 [MACUIN SECURITY] CÓDIGO DE RECUPERACIÓN PARA {empleado.correo}: {codigo} 🛑")
        
    return {"status": "200", "mensaje": "Si el correo existe, se envió un código"}

# PASO 2: Validar el código
@router.post("/recuperar/validar")
def validar_codigo(datos: ValidarTokenSchema, db: Session = Depends(get_db)):
    empleado = db.query(EmpleadoDB).filter(EmpleadoDB.correo == datos.correo).first()
    
    if not empleado or empleado.token_recuperacion != datos.token:
        raise HTTPException(status_code=400, detail="Código inválido o expirado")
        
    return {"status": "200", "mensaje": "Código verificado correctamente"}

# PASO 3: Cambiar la contraseña de forma segura
@router.post("/recuperar/reset")
def ejecutar_reset(datos: ResetPasswordSchema, db: Session = Depends(get_db)):
    empleado = db.query(EmpleadoDB).filter(EmpleadoDB.correo == datos.correo).first()
    
    # Doble validación: Asegurarnos de que tenga un token activo antes de permitir el cambio
    if not empleado or not empleado.token_recuperacion:
        raise HTTPException(status_code=403, detail="Proceso de recuperación no autorizado")
        
    empleado.password = datos.nueva_password
    empleado.token_recuperacion = None # Destruimos el código para que no se reuse
    db.commit()
    
    return {"status": "200", "mensaje": "Contraseña actualizada exitosamente"}
# --- 1. LEER TODOS (GET) ---
@router.get("/")
def obtener_empleados(db: Session = Depends(get_db)):
    empleados = db.query(EmpleadoDB).all()
    return {"status": "200", "total": len(empleados), "data": empleados}

# --- 2. CREAR (POST) ---
@router.post("/", status_code=status.HTTP_201_CREATED)
def registrar_empleado(empleado: CrearUsuarioInterno, db: Session = Depends(get_db)):
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
def actualizar_empleado(id_empleado: int, datos_nuevos: ActualizarUsuarioInterno, db: Session = Depends(get_db)):
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

@router.delete("/{empleado_id}")
def dar_de_baja_empleado(empleado_id: int, db: Session = Depends(get_db)):
    empleado = db.query(EmpleadoDB).filter(EmpleadoDB.id == empleado_id).first()
    
    if not empleado:
        raise HTTPException(status_code=404, detail="Empleado no encontrado")
    empleado.activo = False
    db.commit()
    db.refresh(empleado)
    return {
        "mensaje": "Baja lógica procesada exitosamente", 
        "data": {"id": empleado.id, "nombre": empleado.nombre, "activo": empleado.activo}
    }

@router.patch("/{empleado_id}/reactivar")
def reactivar_empleado(empleado_id: int, db: Session = Depends(get_db)):
    # 1. Buscamos al empleado
    empleado = db.query(EmpleadoDB).filter(EmpleadoDB.id == empleado_id).first()
    
    if not empleado:
        raise HTTPException(status_code=404, detail="Empleado no encontrado")
    
    # 2. Reversibilidad pura: lo regresamos a la vida
    empleado.activo = True
    
    db.commit()
    db.refresh(empleado)
    
    return {
        "mensaje": "Empleado reactivado exitosamente", 
        "data": {"id": empleado.id, "nombre": empleado.nombre, "activo": empleado.activo}
    }