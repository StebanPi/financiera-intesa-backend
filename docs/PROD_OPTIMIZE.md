# Optimización de Producción

Este documento describe los comandos de optimización de Laravel que deben ejecutarse en producción para mejorar el rendimiento de la aplicación.

## ⚠️ Importante: Solo en Producción

**Estos comandos SOLO deben ejecutarse en producción/staging. En desarrollo local, NO ejecutar estos comandos (o limpiar después con `optimize:clear`).**

## Comandos de Optimización

### Comandos Individuales

```bash
# Caché de configuración
php artisan config:cache

# Caché de rutas
php artisan route:cache

# Caché de vistas
php artisan view:cache

# Caché de eventos (si aplica)
php artisan event:cache
```

### Comando Todo-en-Uno

```bash
# Ejecuta config:cache, route:cache y view:cache
php artisan optimize
```

**Nota:** `optimize` NO incluye `event:cache` automáticamente. Si necesitas cachear eventos, ejecútalo por separado.

## Qué Hace Cada Comando

- **`config:cache`** — Genera `bootstrap/cache/config.php` con todas las configuraciones. En producción, las variables `.env` se leen una vez y se cachean, mejorando el rendimiento.
- **`route:cache`** — Genera `bootstrap/cache/routes-v7.php` con todas las rutas compiladas. Acelera significativamente el enrutamiento.
- **`view:cache`** — Compila y cachea todas las vistas Blade para renderizado más rápido.
- **`event:cache`** — Descubre y cachea los eventos y listeners de la aplicación, mejorando el rendimiento del sistema de eventos.
- **`optimize`** — Ejecuta `config:cache`, `route:cache` y `view:cache` en un solo comando.

## Automatización con Composer

El proyecto incluye un script `post-deploy` en `composer.json` que ejecuta automáticamente:

```json
"scripts": {
  "post-deploy": [
    "@php artisan migrate --force",
    "@php artisan storage:link",
    "@php artisan optimize"
  ]
}
```

Para ejecutar este script manualmente:

```bash
composer run-script post-deploy
```

O en un script de despliegue:

```bash
composer install --no-dev --optimize-autoloader
composer run-script post-deploy
```

## ⚠️ Revertir en Desarrollo Local

**IMPORTANTE:** Si ejecutaste estos comandos en local por error, o necesitas hacer cambios en `.env`, rutas o vistas, debes limpiar los cachés:

```bash
# Limpiar todos los cachés de optimización
php artisan optimize:clear
```

Este comando limpia:
- `config:clear` — Elimina el caché de configuración
- `route:clear` — Elimina el caché de rutas
- `view:clear` — Elimina el caché de vistas
- `event:clear` — Elimina el caché de eventos (si existe)

**Nota para desarrollo local:** En local, cuando cambies variables en `.env` o modifiques rutas, ejecuta `php artisan optimize:clear` para asegurar que los cambios se reflejen correctamente.

### Limpiar Cachés Individuales

Si solo necesitas limpiar un caché específico:

```bash
# Limpiar caché de configuración
php artisan config:clear

# Limpiar caché de rutas
php artisan route:clear

# Limpiar caché de vistas
php artisan view:clear

# Limpiar caché de eventos
php artisan event:clear
```

## Cuándo Limpiar y Re-cachear

### Después de Cambiar Variables en `.env`

```bash
php artisan config:clear
php artisan config:cache
```

### Después de Agregar/Modificar Rutas

```bash
php artisan route:clear
php artisan route:cache
```

### Después de Modificar Vistas Blade

```bash
php artisan view:clear
php artisan view:cache
```

### Después de Modificar Eventos/Listeners

```bash
php artisan event:clear
php artisan event:cache
```

### Si No Estás Seguro

```bash
# Limpiar todo
php artisan optimize:clear

# Re-cachear todo
php artisan optimize
php artisan event:cache  # Si aplica
```

## Verificación Post-Optimización

Después de ejecutar los comandos de optimización, verificar que la aplicación sigue funcionando:

1. **Health endpoint:**
   ```bash
   curl https://tu-dominio.com/api/v1/health
   ```

2. **Verificar que las rutas funcionan correctamente**

3. **Verificar que las vistas se renderizan correctamente**

## Flujo de Despliegue Recomendado

```bash
# 1. Instalar dependencias
composer install --no-dev --optimize-autoloader

# 2. Ejecutar migraciones
php artisan migrate --force

# 3. Crear enlace simbólico de storage
php artisan storage:link

# 4. Optimizar (cachear config, rutas, vistas)
php artisan optimize

# 5. Cachear eventos (si aplica)
php artisan event:cache

# 6. Verificar que todo funciona
curl https://tu-dominio.com/api/v1/health
```

## Notas Adicionales

- **En desarrollo local:** Normalmente NO se usa caché. Si por alguna razón ejecutaste `optimize`, recuerda ejecutar `optimize:clear` antes de continuar desarrollando. **Si cambias `.env` o rutas en local, ejecuta `optimize:clear` para que los cambios se reflejen.**
- **Cambios en `.env`:** En producción, siempre ejecutar `config:clear` y `config:cache` después de cambiar variables de entorno.
- **Cambios en rutas:** En producción, siempre ejecutar `route:clear` y `route:cache` después de modificar archivos de rutas.
- **Cambios en vistas:** En producción, siempre ejecutar `view:clear` y `view:cache` después de modificar vistas Blade.
- **Rollback rápido:** Si algo no funciona después de optimizar, ejecutar `php artisan optimize:clear` para revertir todos los cambios de optimización.
