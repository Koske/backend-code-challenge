# OpenAPI\Client\DefaultApi

All URIs are relative to http://localhost:8000, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**messagesGet()**](DefaultApi.md#messagesGet) | **GET** /messages | Retrieve Messages |
| [**messagesPost()**](DefaultApi.md#messagesPost) | **POST** /messages | Send a Message |


## `messagesGet()`

```php
messagesGet($status): \OpenAPI\Client\Model\MessagesGet200Response
```

Retrieve Messages

Retrieves a list of messages, optionally filtered by status.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$status = 'status_example'; // string | Filter messages by their status.

try {
    $result = $apiInstance->messagesGet($status);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->messagesGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **status** | **string**| Filter messages by their status. | [optional] |

### Return type

[**\OpenAPI\Client\Model\MessagesGet200Response**](../Model/MessagesGet200Response.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `messagesPost()`

```php
messagesPost($messages_post_request)
```

Send a Message

Sends a new message with the provided text.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$messages_post_request = new \OpenAPI\Client\Model\MessagesPostRequest(); // \OpenAPI\Client\Model\MessagesPostRequest

try {
    $apiInstance->messagesPost($messages_post_request);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->messagesPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **messages_post_request** | [**\OpenAPI\Client\Model\MessagesPostRequest**](../Model/MessagesPostRequest.md)|  | |

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
