<?php
echo "<pre>";
require_once 'HttpX.php';

function section($title) {
    echo "\n[$title]\n";
    echo str_repeat('-', 40) . "\n";
}

function row($key, $value) {
    printf("%-18s : %s\n", $key, $value);
}

echo "=== HttpX Enhanced Library - Examples ===\n";

// 1. BASIC GET
section("1. Basic GET Request");
$response = HttpX::get('https://jsonplaceholder.typicode.com/users/1');
row("Status", $response->statusCode);
row("Execution Time", $response->executionTime . "s");
row("Success", $response->isSuccess() ? 'Yes' : 'No');

// 2. JSON PARSE
section("2. JSON Response Parsing");
$data = $response->json();
row("Name", $data['name']);
row("Email", $data['email']);

// 3. POST JSON
section("3. POST Request (JSON)");
$response = HttpX::sendJson(
    'https://jsonplaceholder.typicode.com/posts',
    'POST',
    [
        'title' => 'Test',
        'body' => 'Hello',
        'userId' => 1
    ]
);
row("Status", $response->statusCode);

// 4. HEADERS
section("4. Response Headers");
$response = HttpX::get('https://jsonplaceholder.typicode.com/posts/1');
row("Content-Type", $response->getHeader('Content-Type'));

// 5. RETRY CONFIG
section("5. Retry Config");
HttpX::setRetryCount(3);
HttpX::setRetryStrategy(1, true);
row("Status", "Configured");

// 6. DEFAULT HEADERS
section("6. Default Headers");
HttpX::setDefaultHeaders([
    'User-Agent: HttpX',
    'Accept: application/json'
]);
row("Status", "Applied");

// 7. CALLBACKS
section("7. Callbacks");

HttpX::beforeRequest(function($url, $method) {
    echo "[BEFORE] $method $url\n";
});

HttpX::afterRequest(function($res, $url, $method) {
    echo "[AFTER ] $method $url -> {$res->statusCode}\n";
});

HttpX::get('https://jsonplaceholder.typicode.com/posts/1');

// 8. CONCURRENT
section("8. Concurrent Requests");

$responses = HttpX::multiSend([
    'u1' => ['url' => 'https://jsonplaceholder.typicode.com/users/1'],
    'u2' => ['url' => 'https://jsonplaceholder.typicode.com/users/2'],
    'p'  => ['url' => 'https://jsonplaceholder.typicode.com/posts/1']
]);

foreach ($responses as $k => $r) {
    printf("%-10s : %3d (%ss)\n", $k, $r->statusCode, round($r->executionTime, 3));
}

// 9. STATUS CHECK
section("9. Status Helpers");
$response = HttpX::get('https://jsonplaceholder.typicode.com/invalid-url');
row("Success", $response->isSuccess() ? 'Yes' : 'No');
row("Client Error", $response->isClientError() ? 'Yes' : 'No');

// 10. SSL
section("10. SSL");
HttpX::setVerifySsl(true);
row("Status", "Enabled");

// 11. REDIRECT
section("11. Redirect");
HttpX::setFollowRedirects(true, 5);
row("Status", "Enabled");

// 12. LOGGING
section("12. Logging");
HttpX::setLogFile(__DIR__ . '/logs/httpx.log');
HttpX::get('https://jsonplaceholder.typicode.com/posts/1');
row("Status", "Saved");
row("Path", "/logs/httpx.log");

// 13. DOWNLOAD
section("13. File Download");
HttpX::download(
    'https://picsum.photos/200',
    __DIR__ . '/downloads/test.jpg'
);
row("Status", "Completed");
row("Path", "/downloads/test.jpg");

// 14. TIMEOUT
section("14. Timeout");
HttpX::setTimeout(20);
row("Value", "20s");

// 15. PROXY
section("15. Proxy");
row("Status", "Supported (Not Used)");

// 16. METHODS
section("16. HTTP Methods");

row("GET", HttpX::get('https://jsonplaceholder.typicode.com/posts/1')->statusCode);
row("POST", HttpX::post(
    'https://jsonplaceholder.typicode.com/posts',
    ['title'=>'A']
)->statusCode);

row("PUT", HttpX::put(
    'https://jsonplaceholder.typicode.com/posts/1',
    ['title'=>'B']
)->statusCode);

row("PATCH", HttpX::patch(
    'https://jsonplaceholder.typicode.com/posts/1',
    ['title'=>'C']
)->statusCode);

row("DELETE", HttpX::delete(
    'https://jsonplaceholder.typicode.com/posts/1'
)->statusCode);

// 17. RESET
section("17. Reset");
HttpX::reset();
row("Status", "Done");

echo "\n" . str_repeat('=', 40) . "\n";
echo "DONE\n";
echo str_repeat('=', 40) . "\n";
echo "</pre>";