# Validaciones para agregar productos al catálogo

Este documento detalla las validaciones necesarias para garantizar que un producto sea aceptado y mostrado en el catálogo.

## Requisitos para agregar productos

1. **Precio sugerido mayor a 0**
   - No se aceptan productos con un precio sugerido igual a 0. Asegúrate de establecer un valor positivo.

2. **Imágenes del producto**
   - Los productos deben tener al menos una imagen agregada. No se permiten productos sin imágenes.

3. **Asociación a un grupo**
   - Los productos deben estar asociados a un grupo válido. Los productos sin grupo no se mostrarán.

4. **Estado activo**
   - Solo se mostrarán productos con un estado activo. Los productos marcados como "borrados" serán ignorados.

5. **Tipo de producto recurrente**
   - Únicamente los productos de tipo "recurrente" son soportados por el catálogo. Otros tipos no serán mostrados.

## Notas adicionales

Verifica que cada producto cumpla con todos estos criterios antes de intentar agregarlo al catálogo para evitar errores y garantizar su correcta visualización.
