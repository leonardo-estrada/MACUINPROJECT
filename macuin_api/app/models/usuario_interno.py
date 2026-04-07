from pydantic import BaseModel, Field

class CrearUsuarioInterno(BaseModel):
    nombre: str = Field(..., min_length=3, max_length=100, example="Killjoy")
    correo: str = Field(..., min_length=5, max_length=150, example="killjoy@macuin.com")
    password: str = Field(..., min_length=6)
    departamento: str = Field(..., example="Sistemas")
    activo: bool = Field(True, description="True para Activo, False para Inactivo")

class ActualizarUsuarioInterno(BaseModel):
    nombre: str = Field(None, min_length=3, max_length=100)
    departamento: str = Field(None)
    activo: bool = Field(None)