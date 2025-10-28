<?php

namespace App\Resources;

final class NotationsOA
    /**
     * @OA\Schema(
     *     schema="PaginationMeta",
     *     type="object",
     *     @OA\Property(property="current_page", type="integer", example=1),
     *     @OA\Property(property="last_page", type="integer", example=1),
     *     @OA\Property(property="per_page", type="integer", example=15),
     *     @OA\Property(property="total", type="integer", example=20)
     * )
     *
     * @OA\Schema(
     *     schema="Response",
     *     type="object",
     *     @OA\Property(property="success", type="boolean", example=true),
     *     @OA\Property(
     *         property="body",
     *         type="object",
     *         @OA\Property(
     *             property="data",
     *             type="array",
     *             @OA\Items()
     *         ),
     *         @OA\Property(ref="#/components/schemas/PaginationMeta"),
     *         @OA\Property(
     *             property="related",
     *             type="array",
     *             @OA\Items(),
     *             example="[]"
     *         )
     *     ),
     *     @OA\Property(property="message", type="string", example="ok")
     * )
     */
{

}
