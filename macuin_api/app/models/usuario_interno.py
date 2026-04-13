from pydantic import BaseModel, Field
from typing import Optional

class CrearUsuarioInterno(BaseModel):
    nombre: str = Field(..., min_length=3, max_length=100, example="Killjoy")
    correo: str = Field(..., min_length=5, max_length=150, example="killjoy@macuin.com")
    password: str = Field(..., min_length=6)
    departamento: str = Field(..., example="Sistemas")
    activo: bool = Field(True, description="True para Activo, False para Inactivo")

class ActualizarUsuarioInterno(BaseModel):
    nombre: Optional[str] = Field(None, min_length=3, max_length=100)
    departamento: Optional[str] = Field(None)
    activo: Optional[bool] = Field(None)

# --- ESQUEMAS DE AUTENTICACIÓN (Nivel Raíz, sin indentación) ---

class LoginSchema(BaseModel):
    correo: str = Field(
        ..., 
        min_length=5, 
        max_length=100, 
        description="Correo electrónico corporativo del empleado"
    )
    password: str = Field(
        ..., 
        min_length=1, 
        description="Contraseña de acceso"
    )

class ResetPasswordSchema(BaseModel):
    correo: str = Field(
        ..., 
        min_length=5, 
        max_length=100, 
        description="Correo electrónico registrado en el sistema"
    )
    nueva_password: str = Field(
        ..., 
        min_length=6, 
        max_length=50, 
        description="La nueva contraseña debe tener entre 6 y 50 caracteres"
    )

class SolicitarResetSchema(BaseModel):
    correo: str = Field(..., min_length=5)

class ValidarTokenSchema(BaseModel):
    correo: str = Field(..., min_length=5)
    token: str = Field(..., min_length=6, max_length=6)