# Sales Tracker - Gestión de Ventas de Restaurante

Aplicación en Laravel para registrar ventas, calcular márgenes de beneficio por receta (escandallo) y obtener estadísticas diarias de ventas.

---

## 🚀 Requisitos

-   PHP >= 8.1
-   Composer
-   SQLite
-   Laravel >= 11.0

---

## ⚙️ Instalación

1. Clona el repositorio:

```bash
git clone https://github.com/umigam3/sales-manager
cd sales-manager
```

2. Instala las dependencias

```bash
composer install
```

3. Crea archivo .env copiando el archivo .env.example

4. Ejecuta migraciones

```bash
php artisan migrate
```

5. Cargar los escandallos base

```bash
php artisan db:seed
```

6. Ejecutar comando para procesar las ventas

```bash
php artisan app:process-sales
```
