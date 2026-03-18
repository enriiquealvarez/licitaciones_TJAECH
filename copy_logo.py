import os
import shutil

src = r"C:\Users\enriquealvarez\Desktop\evaluaciones.tjaech.gob.mx\public\assets\img\logo_tjaech.png"
dst_dir = r"C:\Users\enriquealvarez\Desktop\licitaciones_TJAECH\public\assets"
os.makedirs(dst_dir, exist_ok=True)
shutil.copy2(src, os.path.join(dst_dir, "logo_tjaech.png"))
print("Done copying")
