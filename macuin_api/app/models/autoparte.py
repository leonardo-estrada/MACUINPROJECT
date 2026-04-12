from pydantic import BaseModel, Field
from typing import Optional

class CrearAutoparte(BaseModel):
    codigo: str = Field(..., min_length=3, max_length=20, example="FLT-001")
    nombre: str = Field(..., min_length=3, max_length=150, example="Filtro de Aceite Premium")
    marca: str = Field(..., min_length=2, max_length=50, example="AutoTech")
    categoria: str = Field(..., min_length=3, max_length=50, example="Filtros")
    precio: float = Field(..., gt=0.0, description="El precio debe ser mayor a 0")
    stock: int = Field(0, ge=0, description="El stock no puede ser negativo")

class ActualizarAutoparte(BaseModel):
    nombre: str = Field(None, min_length=3, max_length=150)
    marca: str = Field(None, min_length=2, max_length=50)
    categoria: str = Field(None, min_length=3, max_length=50)
    precio: Optional[float] = Field(None, gt=0.0, description="El precio debe ser mayor a 0")
    stock: int = Field(None, ge=0)
    