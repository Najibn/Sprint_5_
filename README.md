# Fire Safety Management API

![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)
![Swagger](https://img.shields.io/badge/Docs-Swagger-brightgreen.svg)
![Passport](https://img.shields.io/badge/Passport-OAuth2-brightgreen.svg)


REST API for fire safety equipment management with **auto-generated Swagger documentation** and role-based access control.


## Quick Setup
-----------------------------------------------------------------
**Clone & Install**:

```bash
git clone https://github.com/your-repo-here/fire-safety-api.git
cd fire-safety-api
composer install
cp .env.example .env

Configure Environment:
----------------------------------------------------------------
Edit .env with your local values:

DB_DATABASE=your_database_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Generate these with:
# php artisan passport:install

PASSPORT_PERSONAL_ACCESS_CLIENT_ID=#
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=#
PASSPORT_PASSWORD_GRANT_CLIENT_ID=#
PASSPORT_PASSWORD_GRANT_CLIENT_SECRET=#

## API Documentation
Access live Swagger UI after running the server:
http://localhost:8000/api/documentation

Initialize
---------------------------------------------------------
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan passport:install
php artisan l5-swagger:generate
php artisan serve

 Authentication Flow
------------------------------------------------------------------
POST http://localhost:8000/api/login \
   "Content-Type: application/json" \
   '{"email":"admin@test.com","password":"AdminPass123!"}'

Use Token
--------------------------------------------------------------------
http://localhost:8000/api/products \
  "Authorization: Bearer YOUR_TOKEN"

Refresh Token
--------------------------------------------------------------------
POST http://localhost:8000/api/refreshToken \
  "Authorization: Bearer EXPIRED_TOKEN"

 Run Tests
-----------------------------------------------------------------------
php artisan test
# Tests covers:
# - Authentication (login/token refresh)
# - Role-based access control
# - Product/maintenance record CRUD

Key Features
-------------------------------------------------------------------------
Role System: Admin, Customer, Technician

Endpoints:

- Customers: View/update their products
- Technicians: Manage assigned maintenance
- Admins: Full CRUD access
- Token Security: 15-day expiry with refresh
- Interactive Swagger UI documentation


### Dependencies
---------------------------------------------------------------------------

- PHP 8.2+
- MySQL 5.7+
- Composer
- Laravel 12
- Laravel Passport (OAuth2
- Spatie Laravel-Permission
- Policy
- Swagger
- PHPUnit for TDD
