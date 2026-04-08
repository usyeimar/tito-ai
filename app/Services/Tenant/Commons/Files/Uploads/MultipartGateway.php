<?php

namespace App\Services\Tenant\Commons\Files\Uploads;

interface MultipartGateway
{
    public function initiate(string $disk, string $objectKey, string $mimeType, string $originalFilename): string;

    /**
     * @return array{url:string,headers:array<string, string>,expires_at:string}
     */
    public function signPart(string $disk, string $objectKey, string $uploadId, int $partNumber, int $expiresInSeconds): array;

    /**
     * @param  array<int, array{part_number:int,etag:string}>  $parts
     */
    public function complete(string $disk, string $objectKey, string $uploadId, array $parts): void;

    public function abort(string $disk, string $objectKey, string $uploadId): void;

    /**
     * @return array<int, array{part_number:int,etag:string,size:int}>
     */
    public function listUploadedParts(string $disk, string $objectKey, string $uploadId): array;
}
