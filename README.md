<<<<<<< HEAD
# HttpX - Advanced PHP HTTP Client

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net/)
[![cURL Required](https://img.shields.io/badge/cURL-Required-green.svg)](https://curl.se/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A lightweight yet powerful HTTP client for PHP with retry logic, proxy support, concurrent requests, and logging — designed for performance and flexibility.

---

## 🚀 Features

- Automatic retry with exponential backoff  
- Proxy support with authentication  
- Cookie persistence  
- JSON request & response handling  
- Concurrent (multi) requests  
- Structured logging system  
- Configurable timeouts  
- Supports all HTTP methods (GET, POST, PUT, PATCH, DELETE)  

---

## 📦 Installation

=======
# HttpX - PHP HTTP Client

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net/)
[![cURL Required](https://img.shields.io/badge/cURL-Required-green.svg)](https://curl.se/)

A robust HTTP client with retry logic, proxy support, concurrent requests, and logging capabilities.

##   Why Choose HttpX? 
- Automatic retry with backoff
- Proxy & authentication support
- Cookie persistence
- JSON request/response handling
- Concurrent multi-requests
- Detailed logging
- Timeout configuration
- All HTTP methods support

## Installation 📦
>>>>>>> 44370528bf7840fdaa114d1e5582af51514a4e35
```php
require_once 'HttpX.php';
```

<<<<<<< HEAD
---

## ⚙️ Configuration

```php
// Proxy
HttpX::setProxy('proxy.example.com:3128', 'user:pass');

// Timeout (seconds)
HttpX::setTimeout(20);

// Retry config
HttpX::setRetryCount(5);
HttpX::setRetryStrategy(1, true);

// Logging
HttpX::setLogFile(__DIR__ . '/logs/httpx.log');
```

---

## 📌 Usage Examples

### 🔹 GET Request

```php
$response = HttpX::get('https://api.example.com/data');

echo $response->statusCode;
echo $response->body;
```

---

### 🔹 POST (Form + JSON)

```php
// Form
HttpX::post('https://api.example.com/login', [
    'username' => 'john',
    'password' => 'secret'
]);

// JSON
HttpX::sendJson(
    'https://api.example.com/users',
    'POST',
    ['name' => 'Alice']
);
```

---

### 🔹 PATCH Request

```php
HttpX::sendJson(
=======
## Configuration 
```php
// Set proxy with authentication
HttpX::setProxy('proxy.example.com:3128', 'user:pass');

// Configure timeouts (seconds)
HttpX::setTimeout(20);

// Set maximum retries
HttpX::setRetryCount(5);

// Enable logging
HttpX::setLogFile(__DIR__.'/http_logs.log');
```
## Usage Examples
<b>GET Request</b>
```php
$response = HttpX::send('https://api.example.com/data');
echo "Status: {$response->statusCode}\nBody: {$response->body}";
```
<b>POST Form Data</b>
```php
$response = HttpX::send(
    'https://api.example.com/login',
    'POST',
    ['username' => 'john', 'password' => 'secret']
);
// JSON
$response = HttpX::sendJson(
    'https://api.example.com/users',
    'POST',
    ['name' => 'Alice', 'email' => 'alice@example.com']
);
```
<b>PATCH Request</b>
```php
$response = HttpX::sendJson(
>>>>>>> 44370528bf7840fdaa114d1e5582af51514a4e35
    'https://api.example.com/users/123',
    'PATCH',
    ['status' => 'inactive']
);
```
<<<<<<< HEAD

---

### 🔹 Cookie-Based Session

```php
=======
<b>File Download with Cookies</b>
```php
// Login and save cookies
>>>>>>> 44370528bf7840fdaa114d1e5582af51514a4e35
HttpX::send(
    'https://example.com/login',
    'POST',
    ['user' => 'me', 'pass' => 'secret'],
    [],
    'cookies.txt'
);

<<<<<<< HEAD
=======
// Download protected content
>>>>>>> 44370528bf7840fdaa114d1e5582af51514a4e35
$response = HttpX::send(
    'https://example.com/dashboard',
    'GET',
    null,
    [],
    'cookies.txt'
);
```
<<<<<<< HEAD

---

### 🔹 Concurrent Requests

```php
$responses = HttpX::multiSend([
    'user' => ['url' => 'https://api.example.com/users/1'],
    'post' => [
=======
<b>Concurrent Requests</b>
```php
$requests = [
    'profile' => [
        'url' => 'https://api.example.com/users/1',
        'method' => 'GET'
    ],
    'create' => [
>>>>>>> 44370528bf7840fdaa114d1e5582af51514a4e35
        'url' => 'https://api.example.com/posts',
        'method' => 'POST',
        'data' => ['title' => 'Hello']
    ]
<<<<<<< HEAD
]);

echo $responses['user']->body;
```

---

## 📥 Response Object

```php
$response->statusCode;
$response->body;
$response->retryCount;
$response->headers;
$response->executionTime;

// Helpers
$response->isSuccess();
$response->isClientError();
$response->isServerError();
$response->json();
```

---

## 📝 Logging Example

```log
[2026-01-01 12:00:00] [INFO] Request to https://api.com [GET] returned 200
[2026-01-01 12:00:01] [WARN] Retrying in 2 seconds
```

---

## 📋 Requirements

- PHP 7.4+
- cURL extension
- OpenSSL extension

---

## 👤 Author

- GitHub: https://github.com/DevAniiii

---

## 📄 License

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
=======
];

$responses = HttpX::multiSend($requests);
echo $responses['profile']->body;
```

## Response Object
```php
class HttpXResponse {
    public int $statusCode;
    public string $body;
    public int $retryCount;
}

// Example usage:
if ($response->statusCode === 200) {
    $data = json_decode($response->body);
}
```
## Logging Format
```log
[Timestamp] Request to {URL} with method {METHOD} returned status {STATUS}
[Timestamp] Response: {RESPONSE_BODY}
[Timestamp] Retry Count: {COUNT}
```

## Requirements 
```
• PHP 7.4+
• cURL extension
• OpenSSL extension (for HTTPS)
```

## Author 👤 
- GitHub: [@DevAniiii](https://github.com/DevAniiii)  

## License 📄
This project is licensed under the MIT License
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
>>>>>>> 44370528bf7840fdaa114d1e5582af51514a4e35
