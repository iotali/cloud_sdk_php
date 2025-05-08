<?php
/**
 * IoTSdk - 使用应用凭证初始化客户端示例
 */

// 包含自动加载文件
require __DIR__ . '/../vendor/autoload.php';

// 使用SDK命名空间
use function IoTSdk\createClientFromCredentials;
use function IoTSdk\createDeviceManager;

// 配置应用凭证
$baseUrl = 'https://deviot.know-act.com';
$appId = 'app-6808aa3';
$appSecret = '6808aa30614c4c9f32386dc4';

echo "===== 使用应用凭证初始化客户端示例 =====\n";
echo "使用应用凭证自动获取token...\n";

try {
    // 使用应用凭证初始化客户端
    $client = createClientFromCredentials($baseUrl, $appId, $appSecret);
    
    echo "\n客户端初始化成功!\n";
    echo "Base URL: " . $client->getBaseUrl() . "\n";
    echo "Token: " . substr($client->getToken(), 0, 10) . "...\n"; // 只显示token的前10个字符
    
    // 测试客户端基本功能
    echo "\n===== 测试客户端功能 =====\n";
    
    // 创建设备管理器
    $deviceManager = createDeviceManager($client);
    
    // 查询设备状态
    $deviceName = '32test'; // 替换为您的设备名称
    $response = $deviceManager->getDeviceStatus($deviceName);
    
    // 检查结果
    if ($client->checkResponse($response)) {
        echo "\n使用客户端测试成功!\n";
        $statusData = $response['data'] ?? [];
        $status = $statusData['status'] ?? 'N/A';
        echo "设备状态: $status\n";
    } else {
        echo "\n操作失败，但客户端初始化成功!\n";
    }
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}

// 执行其他操作的示例
echo "\n您可以使用以下方式在各个示例中使用同一个客户端：\n";
echo "1. 创建一个全局客户端实例\n";
echo "2. 将客户端实例传递给各个功能函数\n";
echo "3. 在函数中使用传递的客户端实例，而不是每次都重新创建\n";

echo "\n示例代码：\n";
echo '
// 初始化
$client = createClientFromCredentials($baseUrl, $appId, $appSecret);

// 各种操作函数
function registerDevice($client, $productKey, $deviceName) {
    $deviceManager = createDeviceManager($client);
    return $deviceManager->registerDevice($productKey, $deviceName);
}

function getDeviceStatus($client, $deviceName) {
    $deviceManager = createDeviceManager($client);
    return $deviceManager->getDeviceStatus($deviceName);
}

// 调用示例
$result1 = registerDevice($client, "productKey1", "device1");
$result2 = getDeviceStatus($client, "device1");
';

echo "\n===== 示例完成 =====\n"; 