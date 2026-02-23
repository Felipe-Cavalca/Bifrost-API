<?php

namespace Bifrost\Integration;

use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Bifrost\Core\Settings;
use Bifrost\Interface\Storage as StorageInterface;

class S3Storage implements StorageInterface
{
    private S3Client $client;
    private string $bucket;

    public function __construct(array $config, ?S3Client $client = null)
    {
        self::assertSdkAvailable();

        $this->bucket = (string) ($config["bucket"] ?? "");
        if ($this->bucket === "") {
            throw new \InvalidArgumentException("S3 bucket is required.");
        }

        unset($config["bucket"]);

        $this->client = $client ?? new S3Client($config);
    }

    public static function fromSettings(?Settings $settings = null): static
    {
        $settings ??= new Settings();

        return new static(static::buildConfigFromSettings($settings));
    }

    protected static function buildConfigFromSettings(Settings $settings): array
    {
        $config = [
            "version" => "latest",
            "region" => $settings->BFR_API_S3_REGION ?? "us-east-1",
            "bucket" => $settings->BFR_API_S3_BUCKET,
            "credentials" => [
                "key" => $settings->BFR_API_S3_KEY,
                "secret" => $settings->BFR_API_S3_SECRET,
            ],
        ];

        if ($settings->BFR_API_S3_ENDPOINT) {
            $config["endpoint"] = $settings->BFR_API_S3_ENDPOINT;
        }

        if ($settings->BFR_API_S3_PATH_STYLE !== null) {
            $config["use_path_style_endpoint"] = filter_var(
                $settings->BFR_API_S3_PATH_STYLE,
                FILTER_VALIDATE_BOOL,
                FILTER_NULL_ON_FAILURE
            ) ?? false;
        }

        if (empty($config["credentials"]["key"]) || empty($config["credentials"]["secret"])) {
            unset($config["credentials"]);
        }

        return $config;
    }

    public function put(string $key, string $body, array $options = []): array
    {
        return $this->run("putObject", array_merge([
            "Bucket" => $this->bucket,
            "Key" => $key,
            "Body" => $body,
        ], $options));
    }

    public function get(string $key, array $options = []): array
    {
        return $this->run("getObject", array_merge([
            "Bucket" => $this->bucket,
            "Key" => $key,
        ], $options));
    }

    public function delete(string $key, array $options = []): array
    {
        return $this->run("deleteObject", array_merge([
            "Bucket" => $this->bucket,
            "Key" => $key,
        ], $options));
    }

    public function createPresignedUrl(string $key, string $expires = "+15 minutes", array $options = []): string
    {
        $command = $this->client->getCommand("GetObject", array_merge([
            "Bucket" => $this->bucket,
            "Key" => $key,
        ], $options));

        $request = $this->client->createPresignedRequest($command, $expires);

        return (string) $request->getUri();
    }

    public function getClient(): S3Client
    {
        return $this->client;
    }

    private static function assertSdkAvailable(): void
    {
        if (!class_exists(S3Client::class)) {
            throw new \RuntimeException(
                "AWS SDK for PHP not found. Install with: composer require aws/aws-sdk-php"
            );
        }
    }

    private function run(string $method, array $args): array
    {
        try {
            $result = $this->client->{$method}($args);

            return $result->toArray();
        } catch (AwsException $exception) {
            throw new \RuntimeException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }
    }
}
