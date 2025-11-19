<?php
// Script untuk menghapus console.log dari semua file PHP
// Jalankan dengan: php clean_console_logs.php

$directory = __DIR__;
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($directory),
    RecursiveIteratorIterator::SELF_FIRST
);

$count = 0;
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filePath = $file->getPathname();

        // Skip file ini sendiri
        if (basename($filePath) === 'clean_console_logs.php') {
            continue;
        }

        $content = file_get_contents($filePath);
        $originalContent = $content;

        // Hapus baris console.log yang standalone
        $content = preg_replace('/^\s*console\.log\([^)]+\);\s*$/m', '', $content);

        // Hapus multiple newlines yang terbentuk
        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            $count++;
            echo "Cleaned: " . $file->getFilename() . "\n";
        }
    }
}

echo "\nTotal files cleaned: $count\n";
