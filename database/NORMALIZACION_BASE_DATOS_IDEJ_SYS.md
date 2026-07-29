# NORMALIZACION_BASE_DATOS_IDEJ_SYS.md

## Objetivo
Se normalizó la carpeta `database/` para que las migraciones base creen la estructura final de las tablas, evitando migraciones posteriores que solo agregaban columnas, índices o llaves foráneas a tablas recién creadas.

## Regla aplicada
- Se conservaron migraciones de tablas independientes.
- Se fusionaron migraciones tipo `add_*` sobre tablas base cuando no aportaban historial necesario para una instalación limpia.
- Se mantuvieron separadas las migraciones de Portal Alumno, porque esa parte no debe tocarse salvo instrucción expresa.
- Se movieron algunas migraciones de creación para respetar llaves foráneas: `becas`, `planes_cargos_recurrentes`, `cortes_caja`, `prospectos` y `requisitos_documentales` ahora se crean antes de las tablas que las referencian.

## Uso obligatorio
Después de aplicar esta normalización, usar en local:

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan route:list
```

No usar esta normalización como migración incremental en producción. Es una reorganización de historial de migraciones para instalación limpia.

## Migraciones eliminadas por quedar fusionadas
Ver `MIGRACIONES_ELIMINADAS_NORMALIZACION.txt`.


## Orden normalizado de dependencias críticas
- `2026_01_08_204254_0_create_conceptos_pagos_table.php`
- `2026_01_08_204254_1_create_becas_table.php`
- `2026_01_08_204254_2_create_planes_cargos_recurrentes_table.php`
- `2026_01_08_204255_create_cargos_table.php`

Esto garantiza que `cargos` pueda referenciar `becas` y `planes_cargos_recurrentes` sin migraciones adicionales.
