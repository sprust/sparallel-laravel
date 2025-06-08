<?php

declare(strict_types=1);

namespace SParallelLaravel\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

readonly class ServerBinLoader
{
    private string $version;

    public function __construct(private string $path)
    {
        $this->version = 'latest';
    }

    public function fileExists(): bool
    {
        return file_exists($this->path);
    }

    /**
     * @throws GuzzleException
     */
    public function load(): void
    {
        $url = $this->makeUrl();

        $response = (new Client())->get($url, ['timeout' => 30]);
        $content  = $response->getBody()->getContents();

        $tmpFilePath = $this->path . '.tmp';

        file_put_contents($tmpFilePath, $content);

        rename($tmpFilePath, $this->path);

        chmod($this->path, 0755);
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    private function makeUrl(): string
    {
        return "https://github.com/sprust/sparallel-server/releases/download/$this->version/sparallel_server";
    }
}
