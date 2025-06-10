<?php


namespace App\Http\Controllers;


/**
 * 
 * 
 * @OA\Info(
 *     title="Sellaz Engine API",
 *     version="1.0.0",
 *     description=".",
 *     @OA\Contact(
 *         name="Sellaz Support Team",
 *         email="support@sellaz.co.tz"
 *     )
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     description="Use a valid bearer token",
 *     name="Authorization",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="bearerAuth"
 * )
 * 
 *  * @OA\Response(
 *     response=401,
 *     description="Unauthorized",
 *     @OA\JsonContent(
 *         @OA\Property(property="status", type="boolean", example=false),
 *         @OA\Property(property="message", type="string", example="Unauthorized"),
 *         @OA\Property(property="code", type="integer", example=401)
 *     )
 * ),
 * 
 * @OA\Response(
 *     response=422,
 *     description="Unprocessable Content",
 *     @OA\JsonContent(
 *         @OA\Property(property="status", type="boolean", example=false),
 *         @OA\Property(property="message", type="string", example="Failed to validate fields"),
 *         @OA\Property(property="code", type="integer", example=422),
 *         * @OA\Property(
 *              property="errors",
 *              type="object",
 *              description="Validation error messages, where each field contains an array of error messages.",
 *              additionalProperties=@OA\Schema(
 *              type="array",
 *                  @OA\Items(type="string", example="The field is required.")
 *              ),
 *              example={
 *                  "email": {"The email field is required."},
 *                  "password": {"The password must be at least 8 characters."}
 *              }
 *          )
 *     )
 * ),
 * @OA\Schema(
 *     schema="Company",
 *     type="object",
 *     title="Company",
 *     description="Company model structure",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Sellaz Ltd"),
 *     @OA\Property(property="abbr", type="string", example="SZL"),
 *     @OA\Property(property="logo", type="string", example="https://example.com/images/logo.png"),
 *     @OA\Property(property="description", type="string", example="Leading distributor in the region."),
 *     @OA\Property(property="primary_color", type="string", example="#ff9900"),
 *     @OA\Property(property="secondary_color", type="string", example="#0066cc"),
 *     @OA\Property(property="background_color", type="string", example="#ffffff"),
 *     @OA\Property(property="text_color", type="string", example="#000000"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-06T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-06T13:00:00Z")
 * ), 
 * 
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     description="Product Model structure",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Master Sports"),
 *     @OA\Property(property="brand", type="string", example="SM"),
 *     @OA\Property(property="image", type="string", example="https://example.com/images/logo.png"),
 *     @OA\Property(property="company_id", type="integer", example=1),
 *     @OA\Property(property="company_price", type="integer", example=12000),
 * ), 
 * 
 * @OA\Schema(
 *     schema="OtherUsers",
 *     type="object",
 *     title="Superdealer or Bikers",
 *     description="User model representing an application user (super dealer, biker).",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="company_id", type="integer", nullable=true, example=2),
 *     @OA\Property(property="super_dealer_id", type="integer", nullable=true, example=3),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", nullable=true, example="john@example.com"),
 *     @OA\Property(property="phone", type="string", example="255712345678"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2024-01-15T10:00:00Z"),
 *     @OA\Property(property="role", type="string", example="biker or super_dealer"),
 *     @OA\Property(property="sex", type="string", enum={"male", "female"}, example="male"),
 *     @OA\Property(property="profile_picture", type="string", format="url", nullable=true, example="https://example.com/images/profile.jpg"),
 *     @OA\Property(property="created_by", type="integer", nullable=true, example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-20T14:30:00Z"),
 *     @OA\Property(
 *         property="company",
 *         ref="#/components/schemas/Company",
 *         nullable=true
 *     )
 * ), 
 * @OA\Schema(
 *     schema="SuperAdmin",
 *     type="object",
 *     title="Superadmin",
 *     description="User model representing an application user (super admin).",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", nullable=true, example="john@example.com"),
 *     @OA\Property(property="phone", type="string", example="255712345678"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2024-01-15T10:00:00Z"),
 *     @OA\Property(property="role", type="string", example="super_admin"),
 *     @OA\Property(property="sex", type="string", enum={"male", "female"}, example="male"),
 *     @OA\Property(property="profile_picture", type="string", format="url", nullable=true, example="https://example.com/images/profile.jpg"),
 *     @OA\Property(property="created_by", type="integer", nullable=true, example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-20T14:30:00Z"),
 * )
 */



abstract class Controller
{
    //
}
