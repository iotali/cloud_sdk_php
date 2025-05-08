# IoT云平台SDK (PHP)

这是一个用于连接和管理IoT设备的PHP SDK，提供了与IoT云平台交互的简便方法。

## SDK结构

SDK采用模块化设计，主要包含以下组件：

- **IoTClient**: 核心客户端类，处理API请求、认证和基础通信
- **DeviceManager**: 设备管理模块，提供设备相关的所有操作
- **Utils**: 工具函数集，提供格式化、数据处理等辅助功能

## 功能特性

- 认证管理
  - 通过token直接认证
  - **新增：** 通过应用凭证(appId/appSecret)自动获取token
- 设备管理
  - 设备注册
  - 设备详情查询
  - 设备状态查询
  - 批量设备状态查询
- 远程控制
  - RRPC消息发送
  - 自定义指令下发（异步）

## 安装要求

1. PHP 7.4 或更高版本
2. 安装依赖库：

```bash
composer install
```

## 快速开始

### 1. 创建客户端和设备管理器

#### 方式一：使用token创建客户端（传统方式）

```php
<?php
require 'vendor/autoload.php';

use IoTSdk\Client;
use IoTSdk\Device;

// 创建IoT客户端
$client = Client::create(
    "https://your-iot-platform-url",
    "your-auth-token"
);

// 创建设备管理器
$deviceManager = Device::create($client);
```

#### 方式二：使用应用凭证创建客户端（推荐方式）

```php
<?php
require 'vendor/autoload.php';

use function IoTSdk\createClientFromCredentials;
use function IoTSdk\createDeviceManager;

// 使用应用凭证自动获取token并创建客户端
$client = createClientFromCredentials(
    "https://your-iot-platform-url",
    "your-app-id",
    "your-app-secret"
);

// 创建设备管理器
$deviceManager = createDeviceManager($client);
```

### 2. 设备注册

```php
<?php
// 注册设备
$response = $deviceManager->registerDevice(
    "your-product-key",
    "your-device-name",  // 可选
    "设备显示名称"  // 可选
);

// 检查结果
if ($client->checkResponse($response)) {
    $deviceInfo = $response["data"];
    echo "设备ID: " . $deviceInfo['deviceId'] . "\n";
    echo "设备密钥: " . $deviceInfo['deviceSecret'] . "\n";
}
```

### 3. 查询设备详情

```php
<?php
// 通过设备名称查询
$response = $deviceManager->getDeviceDetail(deviceName: "your-device-name");

// 或通过设备ID查询
$response = $deviceManager->getDeviceDetail(deviceId: "your-device-id");

// 处理结果
if ($client->checkResponse($response)) {
    $deviceInfo = $response["data"];
    echo "设备状态: " . $deviceInfo['status'] . "\n";
}
```

### 4. 查询设备状态

```php
<?php
// 查询设备在线状态
$response = $deviceManager->getDeviceStatus(deviceName: "your-device-name");

// 处理结果
if ($client->checkResponse($response)) {
    $statusData = $response["data"];
    echo "设备状态: " . $statusData['status'] . "\n";
    echo "状态时间戳: " . $statusData['timestamp'] . "\n";
}
```

### 5. 批量查询设备状态

```php
<?php
// 批量查询多个设备状态
$deviceNames = ["device1", "device2", "device3"];
$response = $deviceManager->batchGetDeviceStatus(deviceNameList: $deviceNames);

// 处理结果
if ($client->checkResponse($response)) {
    $devicesData = $response["data"];
    foreach ($devicesData as $deviceInfo) {
        echo "设备: " . $deviceInfo['deviceName'] . "\n";
        echo "状态: " . $deviceInfo['status'] . "\n";
        echo "最后在线时间: " . date('Y-m-d H:i:s', $deviceInfo['lastOnlineTime']/1000) . "\n";
        echo "-------------------\n";
    }
}
```

### 6. 发送RRPC消息

```php
<?php
// 向设备发送RRPC消息
$response = $deviceManager->sendRrpcMessage(
    "your-device-name",
    "your-product-key",
    "Hello Device",
    5000  // 超时时间(毫秒)
);

// 检查结果
if ($client->checkResponse($response)) {
    // 处理返回的payloadBase64Byte字段
    if (isset($response['payloadBase64Byte'])) {
        $decodedContent = base64_decode($response['payloadBase64Byte']);
        echo "设备响应: " . $decodedContent . "\n";
    }
}
```

### 7. 发送自定义指令（异步）

```php
<?php
// 向设备发送自定义指令
// 注意：设备需要已订阅/{productKey}/{deviceName}/user/get主题
$messageContent = json_encode([
    'command' => 'set_mode',
    'params' => [
        'mode' => 2,
        'duration' => 30
    ]
]);

$response = $deviceManager->sendCustomCommand(
    "your-device-name",
    $messageContent  // 将被自动Base64编码
);

if ($client->checkResponse($response)) {
    echo "自定义指令下发成功!\n";
}
```

## 完整示例

### 使用应用凭证并重用客户端

```php
<?php
require 'vendor/autoload.php';

use function IoTSdk\createClientFromCredentials;
use function IoTSdk\createDeviceManager;

// 配置参数
$baseUrl = 'https://your-iot-platform-url';
$appId = 'your-app-id';
$appSecret = 'your-app-secret';
$productKey = 'your-product-key';

// 初始化客户端（仅一次）
try {
    $client = createClientFromCredentials($baseUrl, $appId, $appSecret);
    echo "客户端初始化成功，Token: " . substr($client->getToken(), 0, 10) . "...\n";
    
    // 创建设备管理器
    $deviceManager = createDeviceManager($client);
    
    // 执行多个操作，复用同一个客户端
    $deviceName = "test-device-1";
    
    // 查询设备状态
    $statusResponse = $deviceManager->getDeviceStatus($deviceName);
    if ($client->checkResponse($statusResponse)) {
        $status = $statusResponse['data']['status'] ?? 'unknown';
        echo "设备状态: $status\n";
    }
    
    // 发送指令
    $commandJson = json_encode(['command' => 'refresh']);
    $cmdResponse = $deviceManager->sendCustomCommand($deviceName, $commandJson);
    
    // 其他操作...
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
```

## 自定义日志

SDK支持自定义日志记录器：

```php
<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// 创建自定义日志记录器
$logger = new Logger('my-iot-app');
$logger->pushHandler(new StreamHandler('path/to/your.log', Logger::DEBUG));

// 创建带自定义日志的客户端
$client = Client::create(
    "https://your-iot-platform-url",
    "your-auth-token",
    $logger
);

// 或使用应用凭证创建
$client = createClientFromCredentials(
    "https://your-iot-platform-url",
    "your-app-id",
    "your-app-secret",
    $logger
);
```

## 注意事项

- **认证方式**：推荐使用应用凭证方式自动获取token
- **客户端复用**：创建一次客户端实例后在应用程序中复用，避免重复获取token
- 使用前请确保已获取正确的认证令牌/应用凭证和产品密钥
- 所有API调用都会返回完整的响应内容，便于进一步处理和分析
- 自定义指令下发需要设备已订阅相应的主题

## 贡献

欢迎提交问题和改进建议，也欢迎通过Pull Request来提交代码贡献。

## 许可证

MIT License 