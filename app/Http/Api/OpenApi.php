<?php

namespace App\Http\Api;

/**
 * @OA\Info(
 *     title="Organizations Catalog API",
 *     version="1.0.0",
 *     description="REST API для справочника организаций, зданий и деятельностей"
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description=""
 *  )
 *
 * @OA\SecurityScheme(
 *     securityScheme="ApiKeyAuth",
 *     type="apiKey",
 *     in="header",
 *     name="X-API-Key"
 * )
 */
class OpenApi {}
