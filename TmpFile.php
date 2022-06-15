<?php

namespace Dump\Filedir;


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

    public function remove(): void
    {
        $this->__destruct();
    }

    private function closeHandler(): void
    {
        fclose($this->getHandler());
    }

    private function newTemporaryPath(): string
    {
        return sprintf('/tmp/%s', $this->randUuid());
    }

    private function randUuid(): string
    {
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
    }

    private function createEmptyFile(): void
    {
        fclose(fopen($this->path, 'a+'));
    }
}
