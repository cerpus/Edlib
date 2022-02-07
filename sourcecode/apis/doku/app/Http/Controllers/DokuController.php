<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DokuRequest;
use App\Models\Doku;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as Response;

final class DokuController extends Controller
{
    public function get(Doku $doku): array
    {
        return $doku->toArray();
    }

    public function getPaginated(): CursorPaginator
    {
        return Doku::orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->cursorPaginate(100);
    }

    public function create(DokuRequest $request): JsonResponse
    {
        $doku = Doku::make($request->validated());
//        $doku->creator_id = ...
        $doku->save();

        return new JsonResponse($doku, Response::HTTP_CREATED);
    }

    public function update(Doku $doku, DokuRequest $request): JsonResponse
    {
        $doku->fill($request->validated());
        $doku->save();

        return new JsonResponse($doku);
    }

    public function publish(Doku $doku): JsonResponse
    {
        $doku->draft = false;
        $doku->public = true;
        $doku->save();

        return new JsonResponse($doku);
    }

    public function unpublish(Doku $doku): JsonResponse
    {
        $doku->public = false;
        $doku->save();

        return new JsonResponse($doku);
    }
}
