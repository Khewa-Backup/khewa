INSTALACIóN
-----------------
El proceso de instalación estándar, común para todos los módulos de Prestashop se aplica al “Generador de Descuentos”. Si usted está utilizando “Multitienda”, pase a la “Configuración” del módulo (el botón “Configurar”) y marque la casilla “Activar el módulo para este contexto de tienda: todas las tiendas”. Esto le permitirá generar descuentos para cualquiera de las tiendas individualmente o para todas las tiendas a la vez. Si necesita dejar de usar el módulo por un período de tiempo, use el botón “Desactivar”.

Importante: si por alguna razón usted desea eliminar por completo el módulo “Generador de Descuentos”, primero DESINSTÁLELO y luego elimíneLO. Este procedimiento es importante para limpiar las tablas de la base de datos relacionadas al módulo.

Importante: Ya que el módulo “Generador de Descuentos” amplía las funciones integradas de las reglas de la cesta, es necesario deshabilitar las opciones “Desactivar todos los métodos overrides” y “Desactivar los módulos no nativos de PrestaShop” en la sección “Parámetros Avanzados” > “Rendimiento” > “Modo depuración”.

DESCRIPCIóN
-----------------
El módulo “Generador de Descuentos” permite generar muchos descuentos (reglas de cesta) con códigos de promoción únicos. Usted puede generar descuentos para un producto específico, un grupo de productos seleccionados, una categoría específica o un pedido completo, o especificar cualquier otra condición disponible para las reglas estándar de la cesta.

Usted puede determinar:
- cuántos descuentos con códigos únicos quiere generar
- estructura de códigos únicos (combinación de números y letras)
- cuántas veces el cliente puede usar el descuento, etc.

AJUSTES DE MÓDULO
-----------------
Este módulo se instala directamente en la pestaña Catálogo > Descuentos y amplía las funciones básicas.

1. En Prestashop 1.7 pase a la pestaña “Catálogo” > “Descuentos” y haga clic en “Añadir una nueva regla de carrito”. En Prestashop 1.5 - 1.6, pase a la pestaña del menú “Reglas de Descuentos” > “Reglas del carrito” y haga clic en “Añadir una nueva regla de carrito”.
2. Para crear descuentos utilizando el “Generador de Descuentos”, marque la casilla “Generar muchos descuentos únicos” y rellene los nuevos campos que aparecerán en la página.
3. Rellene los nuevos campos obligatorios:
    - Número total de descuentos únicos: número de descuentos para generación.
    - Configuración del código:
        - Prefijo: se permite cualquier letra o número. Los siguientes símbolos no están permitidos: ^!,;? = + () @ "° {} _ $%. El prefijo es una parte estable de su código, es común para todos los descuentos.
        - Máscara: una secuencia de X y/o Y que define la estructura del código. X significa cualquier número, Y es cualquier letra del alfabeto latino. X e Y se generarán en el modo aleatorio para que sus códigos se hagan únicos. Ejemplo: si Prefix = TEST- y Mask = XXYY, el módulo generará tales códigos como TEST-96FA, TEST-27ME, etc.
4. Asegúrese de que todos los demás campos obligatorios están rellenados correctamente.
5. Guarde el formulario. El módulo generará la cantidad de descuentos que usted ha indicado.

EXPORTACIÓN de las LISTAS
-----------------
Todos los descuentos generados se reflejarán en la tabla “Historial del módulo” que se encuentra en la parte inferior de la página de configuración del módulo.

Hay tres tipos de las listas cargadas como archivos CSV:

- “Todos”: reflejará todos los descuentos generados, las fechas de inicio y finalización, el tipo de descuento. Esta lista se genera a la hora de crear un descuento y no cambia.
- “Usados”: reflejará solo los descuentos que han sido utilizados por los clientes, con especificación del nombre y del correo electrónico. Esta lista es dinámica y se actualiza durante una descarga.
- “No usados”: reflejará solo los descuentos que no han sido utilizados todavía. Esta lista es dinámica y se actualiza durante una descarga.

CONTACTOS
-----------------
Soporte: Por favor, para contactar utilice la cuenta Addons, para ayudarnos a determinar el ID del pedido: https://addons.prestashop.com/en/order-history.