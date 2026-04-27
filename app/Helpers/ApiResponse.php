<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Membentuk respons JSON sukses dengan format data yang konsisten.
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Berhasil.',
        int $statusCode = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Membentuk respons JSON khusus untuk data yang baru berhasil dibuat.
     */
    protected function createdResponse(mixed $data, string $message = 'Data berhasil dibuat.'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Membentuk respons JSON error dengan pesan dan kode status yang sesuai.
     */
    protected function errorResponse(string $message = 'Terjadi kesalahan.', int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }

    /**
     * Membentuk respons JSON saat data yang diminta tidak ditemukan.
     */
    protected function notFoundResponse(string $message = 'Data tidak ditemukan.'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Membentuk respons JSON ketika akses ke resource ditolak.
     */
    protected function forbiddenResponse(string $message = 'Akses ditolak.'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Membentuk respons JSON untuk data yang dikembalikan secara paginasi.
     */
    protected function paginatedResponse(mixed $paginator, string $message = 'Berhasil.'): JsonResponse
    {
        return response()->json([
            'success'    => true,
            'message'    => $message,
            'data'       => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
        ]);
    }
}
