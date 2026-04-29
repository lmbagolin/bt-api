<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    /**
     * Upload a file to the specified disk and create a File record.
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param string $disk
     * @return File
     */
    public function upload(UploadedFile $file, string $folder, string $disk = 's3'): File
    {
        $filename = Str::uuid() . '.' . $file->extension();
        $path = Storage::disk($disk)->putFileAs($folder, $file, $filename);

        if ($path === false) {
            throw new \Exception("Erro ao fazer upload do arquivo para o disco: {$disk}");
        }

        return File::create([
            'path' => $path,
            'disk' => $disk,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    /**
     * Delete a file from storage and remove its record.
     *
     * @param File $file
     * @return bool
     */
    public function delete(File $file): bool
    {
        if (Storage::disk($file->disk)->exists($file->path)) {
            Storage::disk($file->disk)->delete($file->path);
        }
        return $file->delete();
    }
}
