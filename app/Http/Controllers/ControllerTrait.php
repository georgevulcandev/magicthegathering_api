<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use DesServices\BaseComponents\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as Response;

trait ControllerTrait
{
    public function respondCreated(string $route, array $args): JsonResponse
    {
        return response()->json(null, Response::HTTP_CREATED, ['Location' => route($route, $args)]);
    }

    public function respondNoContent(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function respondNotFound(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NOT_FOUND);
    }

    public function respondSuccess(array $data = []): JsonResponse
    {
        return response()->json($data, Response::HTTP_OK);
    }

    public function respondError(array $messages, int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json(['errors' => $messages], $status);
    }
}
