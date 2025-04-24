<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     title="Maintenance Management API",
 *     version="1.0.0",
 *     description="API for managing maintenance products and records with different user roles (admin, customer, technician)",
 *     @OA\Contact(
 *         email="support@maintenance-api.com",
 *         name="API Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="oauth2",
 *     description="OAuth2 Bearer Token for authentication",
 *     @OA\Flow(
 *         flow="implicit",
 *         authorizationUrl="http://localhost:8000/oauth/authorize", 
 *         tokenUrl="http://localhost:8000/oauth/token",
 *         scopes={
 *             "read": "Read access",
 *             "write": "Write access"
 *         }
 *     )
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for user authentication"
 * )
 * 
 * @OA\Tag(
 *     name="Users",
 *     description="API Endpoints for managing users (admin only)"
 * )
 * 
 * @OA\Tag(
 *     name="Products",
 *     description="API Endpoints for managing products (admin only)"
 * )
 * 
 * @OA\Tag(
 *     name="Maintenance Records",
 *     description="API Endpoints for managing maintenance records (admin only)"
 * )
 * 
 * @OA\Tag(
 *     name="Customer",
 *     description="API Endpoints for customer operations"
 * )
 * 
 * @OA\Tag(
 *     name="Technician",
 *     description="API Endpoints for technician operations"
 * )
 */

class ApiDocBaseController extends Controller
{
    // This controller is created only for Swagger annotations
}
