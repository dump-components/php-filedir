<?php

namespace Dump\Filedir;

/**
 * PHP TmpFile
 * Generate and manipulate a temporary file, its auto deletion only happens at the end of the request
 */
class TmpFile
{
    private static array $filesToDelete = [];

    public readonly string $path;

    /** @var null|false|resource */
    private $handler = null;


    public function __construct()
    {
        $this->path = $this->newTemporaryPath();
        $this->createEmptyFile();

        self::$filesToDelete[$this->path] = $this;
    }

    public function __destruct()
    {
        $this->closeHandler();
        unset(self::$filesToDelete[$this->path]);
        unlink($this->path);
    }

    public static function new(): static
    {
        return new static();
    }

    /** @return false|resource */
    public function getHandler()
    {
        return $this->handler ?? fopen($this->path, 'a+');
    }

    public function getContent(): bool|string
    {
        $this->closeHandler();
        return file_get_contents($this->path);
    }

    public function write(string $data): void
    {
        fwrite($this->getHandler(), $data);
    }

    public function delete(): void
    {
        $this->__destruct();
    }

    private function closeHandler(): void
    {
        fclose($this->getHandler());
    }

    private function newTemporaryPath(): string
    {
        return $this->getTmpDir() . $this->randName();
    }

    private function randName(): string
    {
        return bin2hex(random_bytes(16));
    }

    private function createEmptyFile(): void
    {
        fclose(fopen($this->path, 'w+'));
    }

    private function getTmpDir(): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR;
    }
}
