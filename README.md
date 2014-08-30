Seafito
=======
Seafito (sifito) es un Frontend para Seafile que utiliza la Web API. 
Horrendamente escrito en PHP y posiblemente inseguro...bah, que se yo.

Utiliza [Footable][1] para mostrar los datos en tabla.
Incluye paginación, filtros y ordenamiento de columnas.

Solo esta implementada la descarga de archivos.
por ahora...

Opciones
--------

Hay dos variables para configurar en el archivo **index.php** (por ahora)

**Habilitar paginación:**
$pagination_footable=1; # 1 enable / 0 disable

**Columnas por pagina:**
$pagination_footable_pages=30; # ignore if $pagination_footable=1

Requerimientos
--------------
 - php version 4.x 5.x (?)
 - php-curl

  [1]: https://github.com/bradvin/FooTable