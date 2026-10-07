<?php

namespace App\Services\ResourceLibrary;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * A rule of the resource library was broken (not allowed, not found, would lose data...).
 * Rendered as JSON for the library UI: { success: false, message, code }.
 */
class LibraryException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status = 422,
        public readonly string $errorCode = 'invalid',
        public readonly array $extra = [],
    ) {
        parent::__construct($message);
    }

    public static function forbidden(string $message = 'ليس لديك صلاحية على هذا المحتوى.'): self
    {
        return new self($message, 403, 'forbidden');
    }

    public static function notFound(string $message = 'المحتوى غير موجود.'): self
    {
        return new self($message, 404, 'not_found');
    }

    public static function invalid(string $message, string $code = 'invalid', array $extra = []): self
    {
        return new self($message, 422, $code, $extra);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'code' => $this->errorCode,
        ] + $this->extra, $this->status);
    }
}
