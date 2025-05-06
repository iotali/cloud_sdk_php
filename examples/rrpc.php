<?php
/**
 * RRPC消息发送示例
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IoTSdk\Client;
use IoTSdk\Device;
use IoTSdk\Utils;

// 配置参数
$baseUrl = 'http://your-iot-platform-url'; // 替换为实际的API地址
$token = 'your-auth-token';                // 替换为实际的认证令牌

// RRPC参数
$deviceName = 'your-device-name';          // 替换为实际的设备名称
$productKey = 'your-product-key';          // 替换为实际的产品密钥
$messageContent = '{"method":"get","params":{}}'; // 替换为实际的消息内容
$timeout = 5000;                           // 超时时间(毫秒)

echo "===== IoT云平台SDK - RRPC消息发送示例 =====\n\n";

try {
    // 创建客户端和设备管理器
    $client = Client::create($baseUrl, $token);
    $deviceManager = Device::create($client);
    
    echo "正在发送RRPC消息...\n";
    echo "设备名称: {$deviceName}\n";
    echo "产品密钥: {$productKey}\n";
    echo "消息内容: {$messageContent}\n";
    echo "超时时间: {$timeout}毫秒\n\n";
    
    // 执行RRPC消息发送
    $response = $deviceManager->sendRrpcMessage(
        $deviceName,
        $productKey,
        $messageContent,
        $timeout
    );
    
    // 检查响应结果
    if ($client->checkResponse($response)) {
        $resultData = $response['data'] ?? [];
        $messageId = $resultData['messageId'] ?? '未知';
        $deviceResponse = $resultData['response'] ?? null;
        
        echo "RRPC消息发送成功!\n";
        echo "-------------------------------------------------\n";
        echo "消息ID: {$messageId}\n";
        
        if ($deviceResponse !== null) {
            echo "设备响应: {$deviceResponse}\n";
            
            // 尝试解析JSON响应
            $jsonResponse = json_decode($deviceResponse, true);
            if ($jsonResponse !== null && json_last_error() === JSON_ERROR_NONE) {
                echo "\n解析后的设备响应:\n";
                Utils::prettyPrintJson($jsonResponse);
            }
        } else {
            echo "设备未在超时时间内响应\n";
            echo "可能的原因:\n";
            echo "1. 设备当前离线\n";
            echo "2. 超时时间设置过短\n";
            echo "3. 设备未正确处理RRPC请求\n";
        }
        
        echo "-------------------------------------------------\n";
        
        // 输出完整的响应信息（美化格式）
        echo "\n完整的响应信息:\n";
        Utils::prettyPrintJson($resultData);
    } else {
        $errorMsg = $response['errorMessage'] ?? '未知错误';
        echo "RRPC消息发送失败: {$errorMsg}\n";
    }
    
} catch (\Exception $e) {
    echo "发生错误: " . $e->getMessage() . "\n";
}

echo "\n示例运行完成\n"; 