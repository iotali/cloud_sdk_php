<?php
/**
 * 设备详情查询示例
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IoTSdk\Client;
use IoTSdk\Device;
use IoTSdk\Utils;

// 配置参数
$baseUrl = 'http://your-iot-platform-url'; // 替换为实际的API地址
$token = 'your-auth-token';                // 替换为实际的认证令牌

// 设备参数
$deviceName = 'your-device-name';          // 替换为实际的设备名称
// 或者使用设备ID查询
// $deviceId = 'your-device-id';           // 替换为实际的设备ID

echo "===== IoT云平台SDK - 设备详情查询示例 =====\n\n";

try {
    // 创建客户端和设备管理器
    $client = Client::create($baseUrl, $token);
    $deviceManager = Device::create($client);
    
    echo "正在查询设备详情...\n";
    echo "设备名称: {$deviceName}\n\n";
    
    // 执行设备详情查询
    // 通过设备名称查询
    $response = $deviceManager->getDeviceDetail($deviceName);
    
    // 或者通过设备ID查询
    // $response = $deviceManager->getDeviceDetail(null, $deviceId);
    
    // 检查响应结果
    if ($client->checkResponse($response)) {
        $deviceInfo = $response['data'];
        
        // 获取设备状态的文字描述
        $statusText = Utils::getStatusText($deviceInfo['status']);
        
        echo "设备详情查询成功!\n";
        echo "-------------------------------------------------\n";
        echo "设备ID: " . ($deviceInfo['deviceId'] ?? '未知') . "\n";
        echo "设备名称: " . ($deviceInfo['deviceName'] ?? '未知') . "\n";
        echo "显示名称: " . ($deviceInfo['nickName'] ?? '未知') . "\n";
        echo "设备状态: {$statusText}\n";
        echo "产品名称: " . ($deviceInfo['productName'] ?? '未知') . "\n";
        echo "产品密钥: " . ($deviceInfo['productKey'] ?? '未知') . "\n";
        
        // 如果有激活时间信息
        if (isset($deviceInfo['activeTime'])) {
            $activeTime = Utils::formatIsoTime($deviceInfo['activeTime']);
            echo "激活时间: {$activeTime}\n";
        }
        
        // 如果有上线时间信息
        if (isset($deviceInfo['onlineTime'])) {
            $onlineTime = Utils::formatIsoTime($deviceInfo['onlineTime']);
            echo "最近上线时间: {$onlineTime}\n";
        }
        
        echo "-------------------------------------------------\n";
        
        // 输出完整的设备信息（美化格式）
        echo "\n完整的设备信息:\n";
        Utils::prettyPrintJson($deviceInfo);
    } else {
        $errorMsg = $response['errorMessage'] ?? '未知错误';
        echo "设备详情查询失败: {$errorMsg}\n";
    }
    
} catch (\Exception $e) {
    echo "发生错误: " . $e->getMessage() . "\n";
}

echo "\n示例运行完成\n"; 