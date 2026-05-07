<?php

class Upload {
    private array $allowedMimes = [
        'image' => ['image/jpeg','image/png','image/gif','image/webp','image/svg+xml'],
        'doc'   => ['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'csv'   => ['text/csv','application/vnd.ms-excel','text/plain'],
    ];

    private string $disk = 'public';
    private string $directory = 'uploads';
    private int $maxSizeKb = 5120; // 5MB
    private array $allowedTypes = [];
    private bool $generateUniqueName = true;

    public function disk(string $disk): self { $this->disk = $disk; return $this; }
    public function to(string $dir): self { $this->directory = $dir; return $this; }
    public function maxSize(int $kb): self { $this->maxSizeKb = $kb; return $this; }
    public function types(array $types): self { $this->allowedTypes = $types; return $this; }

    public function handle(array $file): array {
        $this->validateFile($file);
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $this->generateUniqueName ? $this->uniqueName($ext) : $file['name'];
        $destDir  = $this->destinationDir();
        $destPath = $destDir . '/' . $filename;

        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new RuntimeException("Failed to move uploaded file");
        }

        // Generate thumbnail for images
        $thumbnail = null;
        if (str_starts_with($file['type'], 'image/') && $file['type'] !== 'image/svg+xml') {
            $thumbnail = $this->createThumbnail($destPath, $destDir, $filename);
        }

        $relativePath = trim($this->directory, '/') . '/' . $filename;

        return [
            'path'      => $destPath,
            'url'       => '/storage/' . $relativePath,
            'filename'  => $filename,
            'original'  => $file['name'],
            'mime'      => $file['type'],
            'size'      => $file['size'],
            'ext'       => $ext,
            'thumbnail' => $thumbnail,
        ];
    }

    public function handleMultiple(array $files): array {
        $results = [];
        foreach ($files as $file) {
            $results[] = $this->handle($file);
        }
        return $results;
    }

    private function validateFile(array $file): void {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException("Upload error code: {$file['error']}");
        }
        if ($file['size'] > $this->maxSizeKb * 1024) {
            throw new RuntimeException("File size exceeds maximum of {$this->maxSizeKb}KB");
        }
        if (!empty($this->allowedTypes)) {
            $allowedMimes = [];
            foreach ($this->allowedTypes as $type) {
                $allowedMimes = array_merge($allowedMimes, $this->allowedMimes[$type] ?? [$type]);
            }
            if (!in_array($file['type'], $allowedMimes)) {
                throw new RuntimeException("File type {$file['type']} is not allowed");
            }
        }
        // Check for executable content
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['php','php5','phtml','exe','sh','bat','cmd','js'])) {
            throw new RuntimeException("File type not allowed for security reasons");
        }
    }

    private function destinationDir(): string {
        if ($this->disk === 'public') {
            return PUBLIC_PATH . '/storage/' . trim($this->directory, '/');
        }
        return STORAGE_PATH . '/' . trim($this->directory, '/');
    }

    private function uniqueName(string $ext): string {
        return uniqid('', true) . '_' . time() . '.' . $ext;
    }

    private function createThumbnail(string $sourcePath, string $destDir, string $filename): ?string {
        if (!extension_loaded('gd')) return null;
        try {
            $info = getimagesize($sourcePath);
            if (!$info) return null;
            [$w, $h, $type] = $info;
            $thumbW = 300;
            $thumbH = (int)($h * $thumbW / $w);
            $src    = match ($type) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
                IMAGETYPE_PNG  => imagecreatefrompng($sourcePath),
                IMAGETYPE_GIF  => imagecreatefromgif($sourcePath),
                IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
                default        => null,
            };
            if (!$src) return null;
            $dst  = imagecreatetruecolor($thumbW, $thumbH);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $thumbW, $thumbH, $w, $h);
            $thumbName = 'thumb_' . $filename;
            $thumbPath = $destDir . '/' . $thumbName;
            match ($type) {
                IMAGETYPE_JPEG => imagejpeg($dst, $thumbPath, 85),
                IMAGETYPE_PNG  => imagepng($dst, $thumbPath, 8),
                IMAGETYPE_GIF  => imagegif($dst, $thumbPath),
                IMAGETYPE_WEBP => imagewebp($dst, $thumbPath, 85),
                default        => null,
            };
            imagedestroy($src);
            imagedestroy($dst);
            return '/storage/' . trim($this->directory, '/') . '/' . $thumbName;
        } catch (Throwable) {
            return null;
        }
    }

    public static function delete(string $path): bool {
        $fullPath = str_starts_with($path, '/') ? $_SERVER['DOCUMENT_ROOT'] . $path : $path;
        return file_exists($fullPath) && unlink($fullPath);
    }
}
