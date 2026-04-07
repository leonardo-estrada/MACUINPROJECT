from pydantic import BaseModel, Field

class CrearUsuarioExterno(BaseModel):
    nombre: str = Field(..., min_length=3, max_length=100, example="Juan Pérez")
    correo: str = Field(..., min_length=5, max_length=150, example="juan@correo.com")
    password: str = Field(..., min_length=6, description="Contraseña mínimo 6 caracteres")
    telefono: str = Field(None, max_length=20, example="4421234567")

class ActualizarUsuarioExterno(BaseModel):
    nombre: str = Field(None, min_length=3, max_length=100)
    telefono: str = Field(None, max_length=20)