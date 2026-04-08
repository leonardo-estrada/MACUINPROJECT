import io
from fastapi import APIRouter, Depends, HTTPException
from fastapi.responses import StreamingResponse
from sqlalchemy.orm import Session
from app.data.db import get_db

# Importamos todos nuestros modelos para sacarles datos
from app.data.autoparte import Autoparte
from app.data.usuario_interno import UsuarioInterno
from app.data.usuario_externo import UsuarioExterno
from app.data.pedido import Pedido

# Librerías de fabricación de documentos
from reportlab.pdfgen import canvas
from openpyxl import Workbook
from docx import Document

router = APIRouter(
    prefix="/v1/reportes",
    tags=['Módulo de Reportes (PDF, Excel, Word)']
)

def extraer_datos(tipo: str, db: Session):
    """Función maestra que decide qué datos sacar de PostgreSQL"""
    if tipo == "inventario":
        return [f"[{p.codigo}] {p.nombre} - Marca: {p.marca} | Stock: {p.stock}" for p in db.query(Autoparte).all()]
    elif tipo == "empleados":
        return [f"Empleado: {e.nombre} | Depto: {e.departamento} | Activo: {e.activo}" for e in db.query(UsuarioInterno).all()]
    elif tipo == "clientes":
        return [f"Cliente: {c.nombre} | Correo: {c.correo} | Tel: {c.telefono}" for c in db.query(UsuarioExterno).all()]
    elif tipo == "pedidos":
        return [f"Folio: #{p.id} | Fecha: {p.fecha.strftime('%Y-%m-%d')} | Estatus: {p.estatus}" for p in db.query(Pedido).all()]
    return None

# --- EL ENDPOINT MAESTRO ---
@router.get("/{tipo}/{formato}")
async def descargar_reporte(tipo: str, formato: str, db: Session = Depends(get_db)):
    # 1. Validar y traer los 4 tipos de reportes
    datos = extraer_datos(tipo, db)
    if datos is None:
        raise HTTPException(status_code=404, detail="Reporte no válido. Usa: inventario, empleados, clientes, pedidos")
    
    if not datos:
        datos = ["No hay registros en la base de datos para este reporte."]

    buffer = io.BytesIO() # Nuestro disco duro temporal en la memoria RAM

    # 2. Fabricar en los 3 formatos solicitados
    if formato == "pdf":
        c = canvas.Canvas(buffer)
        c.drawString(100, 800, f"REPORTE OFICIAL MACUIN - {tipo.upper()}")
        y = 770
        for linea in datos:
            c.drawString(50, y, linea)
            y -= 20
            if y < 50: # Crear nueva página si se llena
                c.showPage()
                y = 800
        c.save()
        media_type = "application/pdf"
        
    elif formato == "xlsx":
        wb = Workbook()
        ws = wb.active
        ws.title = f"Reporte {tipo}"
        ws.append([f"REPORTE DE {tipo.upper()}"])
        for linea in datos:
            ws.append([linea])
        wb.save(buffer)
        media_type = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        
    elif formato == "docx":
        doc = Document()
        doc.add_heading(f'Reporte Oficial MACUIN - {tipo.upper()}', 0)
        for linea in datos:
            doc.add_paragraph(linea)
        doc.save(buffer)
        media_type = "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
        
    else:
        raise HTTPException(status_code=400, detail="Formato no válido. Usa: pdf, xlsx, docx")

    # 3. Empaquetar y enviar el archivo para descarga automática
    buffer.seek(0)
    return StreamingResponse(
        buffer, 
        media_type=media_type, 
        headers={"Content-Disposition": f"attachment; filename=Reporte_Macuin_{tipo}.{formato}"}
    )