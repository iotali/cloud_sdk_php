<?php
/**
 * 设备注册示例
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IoTSdk\Client;
use IoTSdk\Device;
use IoTSdk\Utils;

// 配置参数
$baseUrl = 'http://your-iot-platform-url'; // 替换为实际的API地址
$token = 'your-auth-token';                // 替换为实际的认证令牌
$productKey = 'your-product-key';          // 替换为实际的产品密钥
$deviceName = 'device' . time();           // 生成一个唯一的设备名称
$nickName = '测试设备-' . date('Ymd-His');   // 设备显示名称

echo "===== IoT云平台SDK - 设备注册示例 =====\n\n";

try {
    // 创建客户端和设备管理器
    $client = Client::create($baseUrl, $token);
    $deviceManager = Device::create($client);
    
    echo "正在注册设备...\n";
    echo "产品密钥: {$productKey}\n";
    echo "设备名称: {$deviceName}\n";
    echo "显示名称: {$nickName}\n\n";
    
    // 执行设备注册
    $response = $deviceManager->registerDevice(
        $productKey, 
        $deviceName, 
        $nickName
    );
    
    // 检查响应结果
    if ($client->checkResponse($response)) {
        $deviceInfo = $response['data'];
        
        echo "设备注册成功!\n";
        echo "-------------------------------------\n";
        echo "产品密钥: {$deviceInfo['productKey']}\n";
        echo "设备名称: {$deviceInfo['deviceName']}\n";
        echo "设备ID: {$deviceInfo['deviceId']}\n";
        echo "设备密钥: {$deviceInfo['deviceSecret']}\n";
        echo "-------------------------------------\n";
        
        // 输出完整的设备信息（美化格式）
        echo "\n完整的设备信息:\n";
        Utils::prettyPrintJson($deviceInfo);
    } else {
        $errorMsg = $response['errorMessage'] ?? '未知错误';
        echo "设备注册失败: {$errorMsg}\n";
    }
    
} catch (\Exception $e) {
    echo "发生错误: " . $e->getMessage() . "\n";
}

echo "\n示例运行完成\n"; 