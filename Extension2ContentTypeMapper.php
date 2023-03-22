<?php

declare(strict_types=1);

namespace App\YourNamespace;

class Extension2ContentTypeMapper
{
    private array $mapping;
    private const jsonPath = 'mapextension2mimetype.json';
    private const sourcePath = 'mime.types';

    public function __construct()
    {
        $this->mapping = json_decode(file_get_contents(static::jsonPath), true);
    }
    
    public function hasContentType(string $extension): bool
    {
        return isset($this->mapping[$extension]);
    }
    
    public function getContentType(string $extension): string
    {
        return $this->mapping[$extension];
    }

    public static function refreshSource(): void
    {
        file_put_contents(static::sourcePath, file_get_contents('https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types'));
    }

    public static function generate(): void
    {
        $mapping = [];
        $lines = explode("\n", file_get_contents(static::sourcePath));
        foreach ($lines as $line) {
            $line = trim(preg_replace('/#.*/', '', $line));
            $parts = $line ? array_values(array_filter(explode("\t", $line))) : [];
            if (count($parts) === 2) {
                $mime = trim($parts[0]);
                $extensions = explode(' ', $parts[1]);
                foreach ($extensions as $extension) {
                    $extension = trim($extension);
                    if ($mime && $extension !== '' && !isset($mapping[$extension])) {
                        $mapping[$extension] = $mime;
                    }
                }
            }
        }

        file_put_contents(static::jsonPath, json_encode($mapping));
    }
}
