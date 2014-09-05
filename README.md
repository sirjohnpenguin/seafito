Seafito
=======
Seafito (sifito) es un Frontend para Seafile que utiliza la Web API. 
Horrendamente escrito en PHP y posiblemente inseguro...bah, que se yo.

Utiliza [Bootstrap][1] para los estilos css y
[Footable][2] para mostrar los datos en tabla.
Incluye paginación, filtros y ordenamiento de columnas.
Los estilos css de Footable fueron modificados para usar los iconos [Glyphicons][3] que trae Bootstrap

Solo esta implementada la descarga de archivos y la lista de archivos/directorios compartidos públicamente (shared file links).
por ahora...

Requerimientos
--------------
 - php 5.x 
 - php5-curl
 
Opciones
--------
Hay un par de variables para configurar en el archivo **index.php** (por ahora)

**Habilitar paginación:**

$pagination_footable=1; # 1 enable / 0 disable


**Columnas por pagina:**

$pagination_footable_pages=30; # ignore if $pagination_footable=1


**Desconexion por inactividad.**

$max_idle_time=1440; #seconds


**Branding (?)**

$brand_name=''; # empty = current repo or section (For Menu)

$brand_name='Seafito'; # Brand 

$brand_url=''; # empty = current repo or section

$brand_url='index.php'; # Brand link

$brand_title=''; # empty = current repo/dir or section (For page title)

$brand_title='Seafito'; # Seafito - repo/dir



  [1]: http://getbootstrap.com/
  [2]: https://github.com/bradvin/FooTable
  [3]: http://glyphicons.com/
