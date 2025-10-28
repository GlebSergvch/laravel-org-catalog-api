<?php
declare(strict_types=1);
namespace App\Interfaces;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

interface ApiResourceInterface
{
    /**
     * @return AnonymousResourceCollection
     */
    public function getCollection(): AnonymousResourceCollection;
}
