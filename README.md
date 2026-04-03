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

```php
require_once 'HttpX.php';
```

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
    'https://api.example.com/users/123',
    'PATCH',
    ['status' => 'inactive']
);
```

---

### 🔹 Cookie-Based Session

```php
HttpX::send(
    'https://example.com/login',
    'POST',
    ['user' => 'me', 'pass' => 'secret'],
    [],
    'cookies.txt'
);

$response = HttpX::send(
    'https://example.com/dashboard',
    'GET',
    null,
    [],
    'cookies.txt'
);
```

---

### 🔹 Concurrent Requests

```php
$responses = HttpX::multiSend([
    'user' => ['url' => 'https://api.example.com/users/1'],
    'post' => [
        'url' => 'https://api.example.com/posts',
        'method' => 'POST',
        'data' => ['title' => 'Hello']
    ]
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