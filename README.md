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

- git clone https://your-repo-here-url/
- cd (project-folder-name)
- composer install
- cp .env.example .env or copy .env.example .env
- Edit .env with your database credentials:
- php artisan key:generate
- $ composer require spatie/laravel-permission
- $ php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
- $ php artisan cache:table


Run core migrations (excluding Passport for now) with command
------------------------------------------------------------------------------
 php artisan migrate--path=database/migrations/*******migration file name*****
and that order
2025_04_11_102513_create_users_table.php
2025_04_15_092413_create_products_table.php
2025_04_15_113343_create_maintenance_records_table.php

(then migrate the cache table you created individually before next)
2025_04_14_131037_create_permission_tables.php

Then the passport tables
-----------------------------------------------------------
2025_04_11_101746_create_oauth_auth_codes_table.php
2025_04_11_101747_create_oauth_access_tokens_table.php
2025_04_11_101748_create_oauth_refresh_tokens_table.php
2025_04_11_101749_create_oauth_clients_table.php
2025_04_11_101750_create_oauth_personal_access_clients_table.php


Configure Environment:
----------------------------------------------------------------
generate .env with your local values from passport:

-  php artisan passport:client --personal   
-  php artisan passport:client --password

save the generated code in .env like this.....

PASSPORT_PERSONAL_ACCESS_CLIENT_ID=skjjiwhfffjksbf sdsaf4dff
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=jfnjfhaasFASF5F4ASSAFNSAFABSAJ

PASSPORT_PASSWORD_GRANT_CLIENT_ID=ALFAFSFN+F4+A7FF79F79F
PASSPORT_PASSWORD_GRANT_CLIENT_SECRET=afmakfnaA98SF8fsfF655a4s6f5afaf

next: run the RolesAndPermissionsSeeder
------------------------------------------------------
-  php artisan db:seed --class=RolesAndPermissionsSeeder
-  php artisan serve

next: run the test
------------------------------
- $ php artisan test--filter=AuthenticationTest
- $ php artisan test--filter=ProductManagementTest
- $ php artisan test--filter=RoleBasedAuthorizationTest

If some eg(login and logout ) test fails then run
# php artisan passport:install --force   and click yes

Next: Ignore the error message because the migrations already exist and
manually delete the last duplicated passport migrations table right below the cache table at the bottom..

Rerun the test again.
-  php artisan test--filter=AuthenticationTest
-  php artisan test--filter=ProductManagementTest
-  php artisan test--filter=RoleBasedAuthorizationTest
or just simply
php artisan test

 Authentication Flow (make sure the credential are the same )
------------------------------------------------------------------
POST http://localhost:8000/api/register \
   "Content-Type: application/json" \
   {"name": "#",
    "email": "#",
    "password": "#",
    "password_confirmation": "#",
    "role": "#",
    "phone": "#"
 }

POST http://localhost:8000/api/login \
   "Content-Type: application/json" \
   {
    "email": "#",  
    "password": "#", 
  }

Use Token
--------------------------------------------------------------------
http://localhost:8000/api/logout \
  "Content-Type: application/json"
  "Authorization: Bearer YOUR_TOKEN_Paste"

Refresh Token
--------------------------------------------------------------------
POST http://localhost:8000/api/refreshToken \
  "Authorization: Bearer EXPIRED_TOKEN_Paste"

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
