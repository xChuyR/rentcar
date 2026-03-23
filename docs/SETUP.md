# RentCar v3 — Guía de Instalación con Laragon

## Stack
| Capa       | Tecnología                        |
|------------|-----------------------------------|
| Frontend   | Vue 3 + Vite + Pinia              |
| Backend    | PHP 8.2 (API REST pura)           |
| Base datos | MySQL 8 / MariaDB (Laragon)       |
| Email      | PHPMailer + SMTP (tú configuras)  |
| Mapas      | OpenStreetMap — sin API key       |
| Auth       | JWT + bcrypt (cost 12) + MFA real |

---

## Estructura de carpetas en Laragon

Laragon sirve los proyectos desde su carpeta `www`. La estructura correcta es:

```
C:\laragon\www\
└── rentcar\               ← carpeta raíz del proyecto
    ├── backend\           ← API PHP
    │   ├── api\
    │   ├── config\
    │   ├── middleware\
    │   ├── public\        ← DocumentRoot de Apache apunta aquí via Alias
    │   └── utils\
    ├── frontend\          ← Vue 3 (fuente)
    │   ├── dist\          ← Vue compilado (npm run build) → sirve Apache
    │   └── src\
    └── docs\
        ├── database.sql
        └── SETUP.md
```

Laragon asigna automáticamente el dominio: **http://rentcar.test**

---

## PASO 1 — Configurar Apache en Laragon

Laragon usa Virtual Hosts para cada proyecto. Necesitas configurar el tuyo para que:
- La raíz sirva el frontend Vue compilado (`frontend/dist/`)
- La ruta `/api` apunte al backend PHP (`backend/public/`)

### Abrir configuración de Virtual Hosts en Laragon:
**Laragon → Apache → sites-enabled → (tu dominio).conf**  
O ir a: `C:\laragon\etc\apache2\sites-enabled\rentcar.test.conf`

Contenido del archivo `.conf`:

```apache
<VirtualHost *:80>
    ServerName rentcar.test
    DocumentRoot "C:/laragon/www/rentcar/frontend/dist"

    # SPA fallback para Vue Router (history mode)
    <Directory "C:/laragon/www/rentcar/frontend/dist">
        Options -Indexes
        AllowOverride All
        Require all granted
        FallbackResource /index.html
    </Directory>

    # Alias para la API PHP
    Alias "/api" "C:/laragon/www/rentcar/backend/public"
    <Directory "C:/laragon/www/rentcar/backend/public">
        Options -Indexes
        AllowOverride All
        Require all granted
    </Directory>

    # Uploads públicos
    Alias "/uploads" "C:/laragon/www/rentcar/backend/public/uploads"
    <Directory "C:/laragon/www/rentcar/backend/public/uploads">
        Options -Indexes
        AllowOverride None
        Require all granted
    </Directory>

    ErrorLog "C:/laragon/log/apache2/rentcar-error.log"
    CustomLog "C:/laragon/log/apache2/rentcar-access.log" combined
</VirtualHost>
```

**Reiniciar Apache en Laragon** después de guardar el `.conf`.

---

## PASO 2 — Base de datos

### Opción A — HeidiSQL (recomendado con Laragon):
1. Abre **HeidiSQL** desde el menú de Laragon
2. Conecta con: Host `127.0.0.1` | Usuario `root` | Sin contraseña | Puerto `3306`
3. Clic en **"Nueva consulta"** → pega el contenido de `docs/database.sql` → Ejecutar

### Opción B — Línea de comandos:
```bash
# Desde la carpeta raíz del proyecto:
mysql -h 127.0.0.1 -u root rentcar < docs/database.sql
```
> Si MySQL no está en el PATH: usa la terminal de Laragon (botón Terminal).

---

## PASO 3 — Configurar backend

### 3.1 Editar `backend/config/env.php`

Los valores de DB ya están configurados para Laragon:
```php
define('DB_HOST', '127.0.0.1');  // ✅ ya configurado
define('DB_USER', 'root');        // ✅ ya configurado
define('DB_PASS', '');            // ✅ ya configurado (sin contraseña)
define('DB_NAME', 'rentcar');     // ✅ ya configurado
```

**Solo necesitas cambiar estas líneas:**

```php
// JWT — genera tu propio secret seguro:
define('JWT_SECRET', 'PON_AQUI_UN_STRING_LARGO_Y_ALEATORIO_MINIMO_32_CHARS');

// SMTP — tu proveedor de correo:
define('SMTP_USER', 'TU_CORREO@gmail.com');
define('SMTP_PASS', 'TU_APP_PASSWORD');   // Gmail App Password de 16 dígitos
```

### 3.2 Instalar PHPMailer

Desde la terminal de Laragon, dentro de la carpeta `backend/`:
```bash
cd C:\laragon\www\rentcar\backend
composer require phpmailer/phpmailer
```

> **Sin Composer:** Descarga PHPMailer desde https://github.com/PHPMailer/PHPMailer/releases  
> Extrae y coloca la carpeta `src/` en `backend/vendor/phpmailer/phpmailer/src/`

### 3.3 Carpeta de uploads
Crea la carpeta manualmente:
```
C:\laragon\www\rentcar\backend\public\uploads\
```
(Ya viene en el ZIP, solo asegúrate de que exista)

---

## PASO 4 — Inicializar usuarios (OBLIGATORIO)

Abre en tu navegador:
```
http://rentcar.test/setup.php?key=rentcar-setup-2025
```

Este script:
- ✅ Verifica que todas las tablas existan
- ✅ Crea el usuario **admin** con contraseña correctamente hasheada
- ✅ Crea el usuario **cliente** demo
- ✅ Inserta los 6 autos de ejemplo
- ✅ Muestra resumen del estado de la BD

**Después de ejecutarlo, elimina el archivo `backend/public/setup.php`** por seguridad.

---

## PASO 5 — Frontend Vue

### Desarrollo (hot reload):
```bash
cd C:\laragon\www\rentcar\frontend
npm install
npm run dev
```
Abre: **http://localhost:5173**  
El proxy de Vite redirige `/api` → `http://rentcar.test/api` automáticamente.

### Producción (servir desde Laragon):
```bash
npm run build
```
Esto genera `frontend/dist/` que Apache sirve en `http://rentcar.test`.

---

## PASO 6 — Verificar que todo funciona

### Test de conexión a la API:
Abre en el navegador:
```
http://rentcar.test/api/ping
```
Respuesta esperada: `{"ok":true,"message":"OK","data":{"pong":true,"time":"..."}}`

### Test de autos:
```
http://rentcar.test/api/cars
```
Debe devolver los 6 autos de ejemplo en JSON.

### Si algo falla — checklist:
- [ ] ¿Apache está corriendo en Laragon? (botón verde)
- [ ] ¿MySQL está corriendo en Laragon? (botón verde)
- [ ] ¿El archivo `.conf` se guardó en `sites-enabled`?
- [ ] ¿Reiniciaste Apache después de editar el `.conf`?
- [ ] ¿Ejecutaste `setup.php`?
- [ ] ¿`env.php` tiene el `DB_HOST = '127.0.0.1'`?

---

## Credenciales tras ejecutar setup.php

| Rol     | Email                | Contraseña    |
|---------|----------------------|---------------|
| Admin   | admin@rentcar.mx     | Admin2025!    |
| Cliente | cliente@rentcar.mx   | Cliente2025!  |

El código MFA de 6 dígitos llega al correo real configurado en SMTP.

---

## Problemas frecuentes

### Error: "Database connection failed"
→ Verifica que MySQL esté corriendo en Laragon  
→ Verifica `DB_HOST = '127.0.0.1'` (no `localhost`)  
→ Verifica `DB_USER = 'root'` y `DB_PASS = ''`  

### Error 404 en rutas de la API
→ El `.conf` de Apache no tiene el `Alias /api` correcto  
→ Reinicia Apache en Laragon después de editar el `.conf`  
→ Verifica que `mod_rewrite` esté habilitado (en Laragon viene habilitado por defecto)

### El modo oscuro no cambia
→ Asegúrate de que el archivo `main.css` esté cargado (abre DevTools → Sources)  
→ Las variables CSS se aplican a `body.dark` — el toggle llama `document.body.classList.toggle('dark')`

### El cambio de idioma no traduce todo
→ El sistema de traducción está en `App.vue` (función `t(key)`)  
→ Las vistas individuales (Servicios, Contacto, etc.) están en español fijo  
→ Para traducir completamente, pasar `lang` como prop o usar Pinia store global

### La búsqueda no encuentra autos del catálogo
→ La búsqueda del header usa un índice estático de páginas  
→ Para buscar autos específicos, usa los filtros en `/servicios`

---

## Configuración SMTP (Gmail)

1. En tu cuenta Google → **Seguridad** → **Verificación en dos pasos** → activar
2. Ir a **Contraseñas de aplicaciones** → Nueva app → selecciona "Correo"
3. Google genera un código de 16 caracteres — ese va en `SMTP_PASS`
4. En `env.php`: `SMTP_USER = 'tu_correo@gmail.com'`

> **Alternativas a Gmail:** SendGrid (gratis hasta 100/día), Mailtrap (solo pruebas)

---

## Ubicación en el mapa

**Dirección real del negocio:** 314 Bertha B. de La Peña, Piedras Negras, Coahuila  
**Coordenadas:** 28.7006° N, 100.5234° W  
El mapa en `/contacto` ya apunta a esta ubicación exacta vía OpenStreetMap.
