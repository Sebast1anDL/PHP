# El Buen Comer 🍽️

Sistema de gestión de restaurante desarrollado en PHP y MySQL. Permite a los clientes explorar el menú, gestionar favoritos y realizar pedidos mediante un carrito de compras, con un panel de administración para gestionar ítems y usuarios.

---

## Características

- **Menú interactivo** — visualización por categorías con ordenamiento por nombre y precio
- **Autenticación** — registro e inicio de sesión con control de acceso por roles (Cliente / Administrador)
- **Favoritos** — agregar y quitar platos favoritos con actualización en tiempo real (AJAX)
- **Carrito de compras** — agregar ítems, modificar cantidades, eliminar y confirmar pedido
- **Email de confirmación** — envío automático al correo del usuario al completar un pedido
- **Panel de administración** — CRUD completo de ítems del menú y gestión de usuarios
- **Diseño responsive** — adaptado para desktop, tablet y mobile

---

## Tecnologías

| Capa | Tecnología |
|------|-----------|
| Backend | PHP 8+ |
| Base de datos | MySQL / MariaDB |
| Frontend | HTML5, CSS3, JavaScript (Fetch API) |
| UI framework | Bootstrap 5.3 |
| Tipografía | Google Fonts — Inter |
| Email | PHPMailer + Gmail SMTP |
| Servidor local | XAMPP |

---

## Estructura del proyecto

```
PHP/
├── index.php                  # Página principal — menú por categorías
├── header.php                 # Cabecera global (nav, sesión, badge carrito)
├── footer.php                 # Pie de página global
├── register.php               # Registro de nuevos clientes
├── favorites.php              # Página de favoritos del usuario
├── toggle_favorite.php        # Endpoint AJAX — agregar/quitar favorito
├── cart.php                   # Página del carrito de compras
├── cart_action.php            # Endpoint AJAX — operaciones del carrito
├── db.php                     # Conexión a la base de datos
├── mailer.php                 # Envío de emails con PHPMailer
├── mail_config.php            # Configuración SMTP (credenciales)
├── styles.css                 # Estilos globales
├── nueva_bd.txt               # Schema SQL de la base de datos
├── datos.txt                  # Datos de ejemplo (seed)
├── images/
│   └── Logo_final.png
├── PHPMailer/
│   └── src/                   # Librería PHPMailer (instalación manual)
│       ├── PHPMailer.php
│       ├── SMTP.php
│       └── Exception.php
└── administrador/
    ├── login.php              # Formulario de inicio de sesión
    ├── auth.php               # Manejador de autenticación (POST)
    ├── logout.php             # Cierre de sesión
    ├── admin_index.php        # CRUD de ítems del menú
    ├── admin_users.php        # Gestión de usuarios
    └── template/
        ├── header.php         # Cabecera del panel admin
        └── footer.php         # Pie del panel admin
```

---

## Instalación

### Requisitos previos

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL)
- PHP 8.0 o superior
- Navegador moderno

### Pasos

**1. Clonar o copiar el proyecto**

Colocar la carpeta dentro de `C:\xampp\htdocs\`:

```bash
git clone <url-del-repo> C:\xampp\htdocs\PHP
```

O crear un enlace simbólico desde la ubicación actual (PowerShell como Administrador):

```powershell
New-Item -ItemType SymbolicLink -Path "C:\xampp\htdocs\PHP" -Target "C:\ruta\al\proyecto"
```

**2. Iniciar XAMPP**

Abrir el XAMPP Control Panel y activar **Apache** y **MySQL**.

**3. Crear la base de datos**

Abrir [phpMyAdmin](http://localhost/phpmyadmin) y ejecutar en orden:

```sql
CREATE DATABASE restaurante;
USE restaurante;

CREATE TABLE Rol (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);
```

Luego pegar y ejecutar el contenido de [`nueva_bd.txt`](nueva_bd.txt) (resto de tablas).

Finalmente, pegar y ejecutar [`datos.txt`](datos.txt) para cargar los datos de ejemplo.

**4. Acceder a la app**

```
http://localhost/PHP/
```

---

## Base de datos

### Esquema

```
Rol              Usuario           Categoria
───────          ───────────────   ─────────
id               id                id
nombre           nombre            nombre
                 contrasena
                 email
                 rol_id ──────────► Rol.id

MenuItems        Carrito           CarritoItems       Favoritos
─────────        ───────────────   ────────────────   ──────────────
id               id                carrito_id ──────► Carrito.id
nombre           dueno_id ────────► Usuario.id        menu_id ──────► MenuItems.id
precio           total             menu_id ─────────► MenuItems.id   usuario_id ──► Usuario.id
categoria_id     fecha_compra      cantidad
imagen           comprado
creador_id
```

---

## Credenciales de prueba

| Rol | Email | Contraseña |
|-----|-------|-----------|
| Administrador | `admin@restaurante.com` | `pass123` |
| Cliente | `juan@gmail.com` | `pass456` |

> El login está en `http://localhost/PHP/administrador/login.php`

---

## Configuración de email (opcional)

El sistema envía un email de confirmación al usuario al completar un pedido.

### 1. Instalar PHPMailer manualmente

Descargar el [último release de PHPMailer](https://github.com/PHPMailer/PHPMailer/releases/latest), descomprimir y copiar la carpeta `src/` a:

```
PHP/PHPMailer/src/
```

### 2. Configurar credenciales SMTP

Editar `mail_config.php`:

```php
define('SMTP_USER', 'tu-correo@gmail.com');
define('SMTP_PASS', 'xxxx xxxx xxxx xxxx'); // App Password de Gmail
```

Para obtener una App Password de Gmail:
1. Activar la verificación en dos pasos en tu cuenta Google
2. Ir a **Seguridad → Contraseñas de aplicación**
3. Generar una clave para "Correo"

> Si PHPMailer no está instalado o el SMTP falla, el pedido igual se procesa correctamente — el envío de email es opcional y no bloquea el flujo de compra.

---

## Flujo de la aplicación

```
[Visitante]
    │
    ├─ Ver menú (index.php)
    ├─ Registrarse (register.php)
    └─ Iniciar sesión (administrador/login.php)
            │
            ├─ [Cliente]
            │       ├─ Ver menú + agregar al carrito
            │       ├─ Gestionar favoritos
            │       ├─ Ver carrito (cart.php)
            │       │       ├─ Modificar cantidades
            │       │       ├─ Eliminar ítems
            │       │       └─ Confirmar pedido → email de confirmación
            │       └─ Cerrar sesión
            │
            └─ [Administrador]
                    ├─ CRUD de ítems del menú (admin_index.php)
                    ├─ Gestión de usuarios (admin_users.php)
                    └─ Cerrar sesión
```

---

## Funcionalidades AJAX

| Acción | Archivo | Método |
|--------|---------|--------|
| Agregar/quitar favorito | `toggle_favorite.php` | GET |
| Agregar ítem al carrito | `cart_action.php` | POST (`action=add`) |
| Actualizar cantidad | `cart_action.php` | POST (`action=update`) |
| Eliminar ítem | `cart_action.php` | POST (`action=remove`) |
| Confirmar pedido | `cart_action.php` | POST (`action=checkout`) |

Todas las respuestas son JSON. El badge del carrito en el header se actualiza en tiempo real sin recargar la página.

---

## Notas de seguridad

> Este proyecto fue desarrollado con fines educativos. Para un entorno de producción se recomienda:

- **Contraseñas:** migrar a `password_hash()` / `password_verify()` en lugar de texto plano
- **CSRF:** agregar tokens de validación en los formularios POST
- **Credenciales:** mover `db.php` y `mail_config.php` fuera del web root o usar variables de entorno
- **SQL:** la búsqueda en `admin_index.php` usa interpolación directa — reemplazar por prepared statement

---

## Autor

**Ignacio González** — [@NachoGonzalez2001](https://github.com/NachoGonzalez2001)
