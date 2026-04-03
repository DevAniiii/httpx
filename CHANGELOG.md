# Changelog

All notable changes to the HttpX library will be documented in this file.

## [2.0.0] - 2025-04-03

### 🎉 Major Release - Complete Enhancement

### Added

#### Response Object Enhancements
- **Response headers parsing** - Full access to response headers via `$response->headers`
- **`getHeader($name)` method** - Easy access to specific headers (case-insensitive)
- **`json()` method** - Auto-decode JSON responses with error handling
- **Execution time tracking** - `$response->executionTime` for performance monitoring
- **Status helper methods**:
  - `isSuccess()` - Check for 2xx status codes
  - `isClientError()` - Check for 4xx status codes
  - `isServerError()` - Check for 5xx status codes
- **Error property** - Store error messages in response object

#### Convenience Methods
- **`get($url, $headers)`** - Simple GET requests
- **`post($url, $data, $headers)`** - Simple POST requests
- **`put($url, $data, $headers)`** - Simple PUT requests
- **`patch($url, $data, $headers)`** - Simple PATCH requests
- **`delete($url, $headers)`** - Simple DELETE requests

#### Advanced Retry System
- **Exponential backoff** - Configurable via `setRetryStrategy($delay, $exponential)`
- **Customizable retry delays** - Set base delay between retries
- **Configurable retryable status codes** - Define which status codes trigger retry
- **Better retry logging** - Enhanced logs showing retry attempts and delays

#### Request Lifecycle Hooks
- **`beforeRequest($callback)`** - Execute custom logic before each request
- **`afterRequest($callback)`** - Execute custom logic after each request
- **Request/response interception** - Modify or log requests in real-time

#### Configuration Options
- **Default headers** - `setDefaultHeaders($headers)` for all requests
- **SSL verification control** - `setVerifySsl($verify)` for development/testing
- **Redirect configuration** - `setFollowRedirects($follow, $maxRedirects)`
- **Individual request timeouts** - Per-request timeout in `multiSend()`

#### File Operations
- **`download($url, $destination, $progressCallback)`** - Download files with progress tracking
- **`upload($url, $filePath, $additionalFields, $headers)`** - Upload files via multipart/form-data
- **Progress callbacks** - Real-time download progress monitoring

#### Utility Features
- **`reset()` method** - Reset all configuration to defaults
- **Enhanced logging** - Structured logs with levels (INFO, WARN, ERROR)
- **Timestamp logging** - All logs include timestamps
- **Better error messages** - More descriptive error information

### Changed

#### Breaking Changes
- ✅ **None** - Fully backward compatible with v1.x

#### Improvements
- **Enhanced `multiSend()`** - Now includes response headers and execution time
- **Better header parsing** - More robust header extraction from responses
- **Improved error handling** - More specific error messages and better exception handling
- **Optimized retry logic** - Smarter retry decisions based on status codes
- **Enhanced logging** - More structured and informative log messages

#### Code Quality
- **Strict typing** - Added `declare(strict_types=1)` for type safety
- **Better documentation** - Comprehensive PHPDoc comments
- **Code organization** - Improved structure and readability
- **PSR compliance** - Better adherence to PHP standards

### Fixed
- **cURL error handling** - Better detection and reporting of network errors
- **Header parsing edge cases** - More robust header extraction
- **Retry count tracking** - Accurate retry count in responses
- **Memory efficiency** - Better handling of large responses

### Performance
- **Reduced overhead** - Optimized header parsing and request processing
- **Better concurrent handling** - Improved `multiSend()` performance
- **Efficient retries** - Smarter retry logic reduces unnecessary attempts

### Security
- **SSL verification** - Enabled by default (with option to disable)
- **Proxy authentication** - Secure handling of proxy credentials
- **Safe defaults** - Security-first default configuration

---

## [1.0.0] - 2024-05-03

### Initial Release

#### Features
- Basic HTTP request support (GET, POST, PUT, PATCH)
- Retry logic for failed requests
- Proxy support with authentication
- Cookie file support
- Timeout configuration
- JSON request helper
- Concurrent requests via `multiSend()`
- Basic logging support
- Configurable retry count

#### Core Classes
- `HttpXResponse` - Basic response object with status, body, retry count
- `HttpX` - Main HTTP client class

#### Configuration
- `setProxy()` - Proxy configuration
- `setTimeout()` - Request timeout
- `setRetryCount()` - Maximum retry attempts
- `setLogFile()` - Enable logging

#### Methods
- `send()` - Send HTTP request with retry logic
- `sendJson()` - Send JSON request
- `multiSend()` - Send multiple concurrent requests

---

## Migration Guide

### From v1.0 to v2.0

#### No Breaking Changes
All v1.0 code will continue to work without modifications.

#### Recommended Updates

```php
// v1.0 style (still works)
$response = HttpX::send('https://api.example.com', 'GET');
if ($response->statusCode === 200) {
    $data = json_decode($response->body, true);
}

// v2.0 style (recommended)
$response = HttpX::get('https://api.example.com');
if ($response->isSuccess()) {
    $data = $response->json();
}
```

#### New Features to Adopt

1. **Use convenience methods** for cleaner code
2. **Implement lifecycle callbacks** for logging and monitoring
3. **Configure retry strategy** with exponential backoff
4. **Set default headers** to avoid repetition
5. **Use response helpers** for cleaner status checking
6. **Enable structured logging** for better debugging

---

## Roadmap

### Planned for v2.1
- [ ] Request/Response middleware system
- [ ] Built-in rate limiting
- [ ] Response caching layer
- [ ] WebSocket support
- [ ] HTTP/2 support
- [ ] Async request support
- [ ] Request builder pattern
- [ ] Response streaming

### Planned for v3.0
- [ ] PSR-7 HTTP message interfaces
- [ ] PSR-18 HTTP client interface
- [ ] Dependency injection support
- [ ] Plugin system
- [ ] GraphQL support
- [ ] OAuth 2.0 helpers
- [ ] Request signing utilities

---

## Version History

| Version | Release Date | Major Changes |
|---------|--------------|---------------|
| 2.0.0 | 2025-04-03 | Complete enhancement with 30+ new features |
| 1.0.0 | 2024-05-03 | Initial release |

---

**Note:** This project follows [Semantic Versioning](https://semver.org/)
