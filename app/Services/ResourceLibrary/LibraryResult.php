<?php

namespace App\Services\ResourceLibrary;

/**
 * What a library action reports back to the UI: a message, the undo token (when the action
 * can be reverted) and any extra data the caller wants to forward.
 */
final class LibraryResult
{
    /** @param  array<string, mixed>  $data */
    public function __construct(
        public readonly string $message,
        public readonly ?string $undoToken = null,
        public readonly array $data = [],
    ) {}

    public function with(array $data): self
    {
        return new self($this->message, $this->undoToken, [...$this->data, ...$data]);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $payload = ['success' => true, 'message' => $this->message] + $this->data;

        if ($this->undoToken) {
            $payload['undo'] = [
                'token' => $this->undoToken,
                'seconds' => (int) config('resource_library.undo.toast_seconds', 5),
            ];
        }

        return $payload;
    }
}
