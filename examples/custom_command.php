<?php
/**
 * 自定义指令下发示例
 * 
 * 该示例演示如何向设备发送自定义指令
 * 注意：要求设备已订阅了/{productKey}/{deviceName}/user/get主题
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IoTSdk\Client;
use IoTSdk\Device;
use IoTSdk\Utils;

// 配置参数
$baseUrl = 'http://121.40.253.224:10081'; // 替换为实际的API地址
$token = '488820fb-41af-40e5-b2d3-d45a8c576eea'; // 替换为实际的认证令牌

// 设备参数
$deviceName = 'test_device_001'; // 替换为实际的设备名称

// 自定义指令内容（JSON格式）
$messageContent = json_encode([
    'washingMode' => 2,
    'washingTime' => 30
]);

echo "===== IoT云平台SDK - 自定义指令下发示例 =====\n\n";

try {
    // 创建客户端和设备管理器
    $client = Client::create($baseUrl, $token);
    $deviceManager = Device::create($client);
    
    echo "准备发送自定义指令...\n";
    echo "设备名称: {$deviceName}\n";
    echo "消息内容: {$messageContent}\n\n";
    
    // 执行自定义指令发送
    $response = $deviceManager->sendCustomCommand(
        $deviceName,
        $messageContent
    );
    
    // 检查响应结果
    if ($client->checkResponse($response)) {
        echo "自定义指令发送成功!\n";
        echo "-------------------------------------------------\n";
        
        // 如果有响应数据，则输出
        if (isset($response['data'])) {
            echo "响应数据:\n";
            Utils::prettyPrintJson($response['data']);
        } else {
            echo "无响应数据\n";
        }
        
        echo "-------------------------------------------------\n";
        echo "指令已被异步发送到设备，设备将在订阅主题后收到消息。\n";
        echo "提示：消息通过Base64编码发送，设备需要进行解码后处理。\n";
    } else {
        $errorMsg = $response['errorMessage'] ?? '未知错误';
        echo "自定义指令发送失败: {$errorMsg}\n";
        echo "可能的原因:\n";
        echo "1. 设备不存在\n";
        echo "2. API地址或令牌错误\n";
        echo "3. 服务器内部错误\n";
    }
    
} catch (\Exception $e) {
    echo "发生错误: " . $e->getMessage() . "\n";
}

echo "\n示例运行完成\n"; 