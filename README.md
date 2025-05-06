# IoT云平台SDK (PHP)

这是一个用于连接和管理IoT设备的PHP SDK，提供了与IoT云平台交互的简便方法。

## SDK结构

SDK采用模块化设计，主要包含以下组件：

- **IoTClient**: 核心客户端类，处理API请求、认证和基础通信
- **DeviceManager**: 设备管理模块，提供设备相关的所有操作
- **Utils**: 工具函数集，提供格式化、数据处理等辅助功能

## 功能特性

- 设备管理
  - 设备注册
  - 设备详情查询
  - 设备状态查询
  - 批量设备状态查询
- 远程控制
  - RRPC消息发送

## 安装要求

1. PHP 7.4 或更高版本
2. 安装依赖库：

```bash
composer install
```

## 快速开始

### 1. 创建客户端和设备管理器

```php
<?php
require 'vendor/autoload.php';

use IoTSdk\Client;
use IoTSdk\Device;

// 创建IoT客户端
$client = Client::create(
    "http://your-iot-platform-url",
    "your-auth-token"
);

// 创建设备管理器
$deviceManager = Device::create($client);
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

// 或者通过设备ID列表查询
$deviceIds = ["id1", "id2", "id3"];
$response = $deviceManager->batchGetDeviceStatus(deviceIdList: $deviceIds);
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
```

## 示例代码

参见 `examples` 目录下的示例文件，展示了SDK的具体用法。

## 异常处理

SDK提供了统一的异常处理机制：

```php
<?php
try {
    $response = $deviceManager->getDeviceStatus(deviceName: "your-device-name");
    if ($client->checkResponse($response)) {
        // 处理成功响应
    } else {
        // 处理API错误
        $errorMsg = $response["errorMessage"] ?? "未知错误";
        echo "API调用失败: " . $errorMsg . "\n";
    }
} catch (Exception $e) {
    // 处理网络或其他异常
    echo "发生异常: " . $e->getMessage() . "\n";
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
    "http://your-iot-platform-url",
    "your-auth-token",
    $logger
);
```

## 注意事项

- 使用前请确保已获取正确的认证令牌和产品密钥
- 所有API调用都会返回完整的响应内容，便于进一步处理和分析

## 贡献

欢迎提交问题和改进建议，也欢迎通过Pull Request来提交代码贡献。

## 许可证

MIT License 