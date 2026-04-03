<?php

declare(strict_types=1);

class HttpXResponse
{
    public int $statusCode;
    public string $body;
    public int $retryCount;
    public array $headers;
    public float $executionTime;
    public ?string $error;

    public function __construct(
        int $statusCode,
        string $body,
        int $retryCount,
        array $headers = [],
        float $executionTime = 0.0,
        ?string $error = null
    ) {
        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->retryCount = $retryCount;
        $this->headers = $headers;
        $this->executionTime = $executionTime;
        $this->error = $error;
    }

    public function json(): ?array
    {
        $decoded = json_decode($this->body, true); // la
        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    /**
     * Check if response was successful (2xx status code)
     */
    public function isSuccess(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Check if response is a client error (4xx status code)
     */
    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }


    public function isServerError(): bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Get specific header value
     */
    public function getHeader(string $name): ?string
    {
        $name = strtolower($name);
        foreach ($this->headers as $key => $value) {
            if (strtolower($key) === $name) {
                return $value;
            }
        }
        return null;
    }
}

/**
 * HttpX - Advanced HTTP client with retry logic, proxy support, and concurrent requests
 */
class HttpX
{
    private static ?string $proxy = null;
    private static ?string $proxyAuth = null;
    private static int $timeout = 30;
    private static int $retryCount = 3;
    private static ?string $logFile = null;
    private static bool $followRedirects = true;
    private static int $maxRedirects = 5;
    private static bool $verifySsl = true;
    private static array $defaultHeaders = [];
    private static $beforeRequestCallback = null;
    private static $afterRequestCallback = null;
    private static array $retryableStatusCodes = [408, 429, 500, 502, 503, 504];
    private static int $retryDelay = 1; // seconds
    private static bool $exponentialBackoff = true;

    /**
     * Set proxy configuration
     */
    public static function setProxy(?string $proxy, ?string $proxyAuth = null): void
    {
        self::$proxy = $proxy;
        self::$proxyAuth = $proxyAuth;
    }

    /**
     * Set request timeout in seconds
     */
    public static function setTimeout(int $timeout): void
    {
        self::$timeout = $timeout;
    }

    /**
     * Set maximum retry attempts
     */
    public static function setRetryCount(int $retryCount): void
    {
        self::$retryCount = $retryCount;
    }

    /**
     * Set log file path
     */
    public static function setLogFile(?string $logFile): void
    {
        self::$logFile = $logFile;
    }

    /**
     * Enable/disable following redirects
     */
    public static function setFollowRedirects(bool $follow, int $maxRedirects = 5): void
    {
        self::$followRedirects = $follow;
        self::$maxRedirects = $maxRedirects;
    }

    /**
     * Enable/disable SSL certificate verification
     */
    public static function setVerifySsl(bool $verify): void
    {
        self::$verifySsl = $verify;
    }

    /**
     * Set default headers for all requests
     */
    public static function setDefaultHeaders(array $headers): void
    {
        self::$defaultHeaders = $headers;
    }

    /**
     * Set retryable HTTP status codes
     */
    public static function setRetryableStatusCodes(array $codes): void
    {
        self::$retryableStatusCodes = $codes;
    }

    /**
     * Set retry delay and enable/disable exponential backoff
     */
    public static function setRetryStrategy(int $delaySeconds, bool $exponentialBackoff = true): void
    {
        self::$retryDelay = $delaySeconds;
        self::$exponentialBackoff = $exponentialBackoff;
    }

    /**
     * Set callback to execute before each request
     */
    public static function beforeRequest(callable $callback): void
    {
        self::$beforeRequestCallback = $callback;
    }

    /**
     * Set callback to execute after each request
     */
    public static function afterRequest(callable $callback): void
    {
        self::$afterRequestCallback = $callback;
    }

    /**
     * Log message to file
     */
    private static function log(string $message, string $level = 'INFO'): void
    {
        if (self::$logFile) {
            $timestamp = date('Y-m-d H:i:s');
            $logMessage = "[{$timestamp}] [{$level}] {$message}";
            $dir = dirname(self::$logFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            file_put_contents(self::$logFile, $logMessage . PHP_EOL, FILE_APPEND);
        }
    }

    /**
     * Parse response headers
     */
    private static function parseHeaders(string $headerContent): array
    {
        $headers = [];
        $lines = explode("\r\n", $headerContent);

        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }

        return $headers;
    }

    /**
     * Calculate retry delay with optional exponential backoff
     */
    private static function calculateRetryDelay(int $attempt): int
    {
        if (self::$exponentialBackoff) {
            return self::$retryDelay * (2 ** $attempt);
        }
        return self::$retryDelay;
    }

    private static function formatHeaders(array $headers): array
    {
        $formatted = [];
        foreach ($headers as $key => $value) {
            if (is_string($key)) {
                $formatted[] = $key . ': ' . $value;
            } else {
                $formatted[] = $value;
            }
        }
        return $formatted;
    }

    /**
     * Send HTTP request with retry logic
     */
    public static function send(
        string $url,
        string $method = 'GET',
        $data = null,
        array $headers = [],
        ?string $cookieFile = null
    ): HttpXResponse {
        $attempt = 0;
        $allHeaders = self::formatHeaders(array_merge(self::$defaultHeaders, $headers));
        $startTime = microtime(true);

        while ($attempt < self::$retryCount) {
            if (self::$beforeRequestCallback) {
                call_user_func(self::$beforeRequestCallback, $url, $method, $data, $allHeaders);
            }

            $ch = curl_init();
            $options = [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $allHeaders,
                CURLOPT_TIMEOUT => self::$timeout,
                CURLOPT_CONNECTTIMEOUT => self::$timeout,
                CURLOPT_HEADER => true,
                CURLOPT_FOLLOWLOCATION => self::$followRedirects,
                CURLOPT_MAXREDIRS => self::$maxRedirects,
                CURLOPT_SSL_VERIFYPEER => self::$verifySsl,
                CURLOPT_SSL_VERIFYHOST => self::$verifySsl ? 2 : 0,
            ];

            if (self::$proxy) {
                $options[CURLOPT_PROXY] = self::$proxy;
                if (self::$proxyAuth) {
                    $options[CURLOPT_PROXYUSERPWD] = self::$proxyAuth;
                }
            }

            if ($cookieFile !== null) {
                $options[CURLOPT_COOKIEFILE] = $cookieFile;
                $options[CURLOPT_COOKIEJAR] = $cookieFile;
            }

            if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                if (is_array($data)) {
                    $options[CURLOPT_POSTFIELDS] = http_build_query($data);
                } else {
                    $options[CURLOPT_POSTFIELDS] = $data;
                }
            }

            curl_setopt_array($ch, $options);

            $response = curl_exec($ch);
            $requestStart = microtime(true);
            $executionTime = microtime(true) - $requestStart;

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                self::log("Client Error - {$error}", 'ERROR');
                curl_close($ch);

                $attempt++;
                if ($attempt < self::$retryCount) {
                    $delay = self::calculateRetryDelay($attempt);
                    self::log("Retrying in {$delay} seconds (attempt {$attempt}/" . self::$retryCount . ")", 'WARN');
                    sleep($delay);
                    continue;
                }

                throw new Exception("cURL Error: {$error}");
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

            $headerContent = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);
            $responseHeaders = self::parseHeaders($headerContent);

            curl_close($ch);

            if (in_array($httpCode, self::$retryableStatusCodes) && $attempt < self::$retryCount - 1) {
                $delay = self::calculateRetryDelay($attempt);
                self::log("Received retryable status {$httpCode}, retrying in {$delay} seconds", 'WARN');
                sleep($delay);
                $attempt++;
                continue;
            }

            $responseObj = new HttpXResponse($httpCode, $body, $attempt, $responseHeaders, $executionTime);

            self::log("Request to {$url} [{$method}] returned {$httpCode} in {$executionTime}s");

            if (self::$afterRequestCallback) {
                call_user_func(self::$afterRequestCallback, $responseObj, $url, $method);
            }

            return $responseObj;
        }

        throw new Exception('Max retry attempts reached');
    }

    /**
     * Send JSON request
     */
    public static function sendJson(
        string $url,
        string $method = 'GET',
        $data = null,
        array $headers = [],
        ?string $cookieFile = null
    ): HttpXResponse {
        $headers[] = 'Content-Type: application/json';

        if (is_array($data)) {
            $data = json_encode($data);
        }

        return self::send($url, $method, $data, $headers, $cookieFile);
    }

    /**
     * Send multiple concurrent requests
     */
    public static function multiSend(array $requests): array
    {
        $multiHandle = curl_multi_init();
        $handles = [];
        $responses = [];
        $startTime = microtime(true);

        foreach ($requests as $key => $request) {
            $ch = curl_init();
            $allHeaders = self::formatHeaders(array_merge(
                self::$defaultHeaders,
                $request['headers'] ?? []
            ));

            $options = [
                CURLOPT_URL => $request['url'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $request['method'] ?? 'GET',
                CURLOPT_HTTPHEADER => $allHeaders,
                CURLOPT_TIMEOUT => $request['timeout'] ?? self::$timeout,
                CURLOPT_CONNECTTIMEOUT => $request['timeout'] ?? self::$timeout,
                CURLOPT_HEADER => true,
                CURLOPT_FOLLOWLOCATION => self::$followRedirects,
                CURLOPT_MAXREDIRS => self::$maxRedirects,
                CURLOPT_SSL_VERIFYPEER => self::$verifySsl,
                CURLOPT_SSL_VERIFYHOST => self::$verifySsl ? 2 : 0,
            ];

            if (self::$proxy) {
                $options[CURLOPT_PROXY] = self::$proxy;
                if (self::$proxyAuth) {
                    $options[CURLOPT_PROXYUSERPWD] = self::$proxyAuth;
                }
            }

            if (isset($request['cookieFile'])) {
                $options[CURLOPT_COOKIEFILE] = $request['cookieFile'];
                $options[CURLOPT_COOKIEJAR] = $request['cookieFile'];
            }

            $method = $request['method'] ?? 'GET';
            if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE']) && isset($request['data'])) {
                if (is_array($request['data'])) {
                    $options[CURLOPT_POSTFIELDS] = http_build_query($request['data']);
                } else {
                    $options[CURLOPT_POSTFIELDS] = $request['data'];
                }
            }

            curl_setopt_array($ch, $options);
            curl_multi_add_handle($multiHandle, $ch);
            $handles[$key] = $ch;
        }

        $running = null;
        do {
            curl_multi_exec($multiHandle, $running);
            curl_multi_select($multiHandle);
        } while ($running > 0);

        foreach ($handles as $key => $ch) {
            $response = curl_multi_getcontent($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

            $headerContent = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);
            $responseHeaders = self::parseHeaders($headerContent);

            $executionTime = microtime(true) - $startTime;

            $responses[$key] = new HttpXResponse($httpCode, $body, 0, $responseHeaders, $executionTime);
            curl_multi_remove_handle($multiHandle, $ch);
        }

        curl_multi_close($multiHandle);

        return $responses;
    }

    /**
     * Convenience method for GET requests
     */
    public static function get(string $url, array $headers = []): HttpXResponse
    {
        return self::send($url, 'GET', null, $headers);
    }

    /**
     * Convenience method for POST requests
     */
    public static function post(string $url, $data = null, array $headers = []): HttpXResponse
    {
        return self::send($url, 'POST', $data, $headers);
    }

    /**
     * Convenience method for PUT requests
     */
    public static function put(string $url, $data = null, array $headers = []): HttpXResponse
    {
        return self::send($url, 'PUT', $data, $headers);
    }

    /**
     * Convenience method for PATCH requests
     */
    public static function patch(string $url, $data = null, array $headers = []): HttpXResponse
    {
        return self::send($url, 'PATCH', $data, $headers);
    }

    /**
     * Convenience method for DELETE requests
     */
    public static function delete(string $url, array $headers = []): HttpXResponse
    {
        return self::send($url, 'DELETE', null, $headers);
    }

    /**
     * Download file from URL
     */
    public static function download(string $url, string $destination, callable $progressCallback = null): bool
    {


        $dir = dirname($destination);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $fp = fopen($destination, 'w+');

        if (!$fp) {
            throw new Exception("Cannot open file: {$destination}");
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::$timeout);

        if ($progressCallback) {
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, $progressCallback);
        }

        if (self::$proxy) {
            curl_setopt($ch, CURLOPT_PROXY, self::$proxy);
            if (self::$proxyAuth) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, self::$proxyAuth);
            }
        }

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        fclose($fp);

        if (!$result || $httpCode >= 400) {
            unlink($destination);
            return false;
        }

        self::log("Downloaded {$url} to {$destination}");
        return true;
    }

    /**
     * Upload file via multipart/form-data
     */
    public static function upload(string $url, string $filePath, array $additionalFields = [], array $headers = []): HttpXResponse
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: {$filePath}");
        }

        $cfile = new CURLFile($filePath);
        $data = array_merge(['file' => $cfile], $additionalFields);

        return self::send($url, 'POST', $data, $headers);
    }

    /**
     * Reset all static configuration to defaults
     */
    public static function reset(): void
    {
        self::$proxy = null;
        self::$proxyAuth = null;
        self::$timeout = 30;
        self::$retryCount = 3;
        self::$logFile = null;
        self::$followRedirects = true;
        self::$maxRedirects = 5;
        self::$verifySsl = true;
        self::$defaultHeaders = [];
        self::$beforeRequestCallback = null;
        self::$afterRequestCallback = null;
        self::$retryableStatusCodes = [408, 429, 500, 502, 503, 504];
        self::$retryDelay = 1;
        self::$exponentialBackoff = true;
    }
}



/**
 * made by github.com/DevAniiii 
 */
