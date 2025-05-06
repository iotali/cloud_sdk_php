<?php
/**
 * 设备状态查询示例
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

echo "===== IoT云平台SDK - 设备状态查询示例 =====\n\n";

try {
    // 创建客户端和设备管理器
    $client = Client::create($baseUrl, $token);
    $deviceManager = Device::create($client);
    
    echo "正在查询设备状态...\n";
    echo "设备名称: {$deviceName}\n\n";
    
    // 执行设备状态查询
    // 通过设备名称查询
    $response = $deviceManager->getDeviceStatus($deviceName);
    
    // 或者通过设备ID查询
    // $response = $deviceManager->getDeviceStatus(null, $deviceId);
    
    // 检查响应结果
    if ($client->checkResponse($response)) {
        $statusData = $response['data'];
        
        // 获取设备状态并格式化
        $status = $statusData['status'] ?? '未知';
        $statusText = Utils::getStatusText($status);
        
        // 处理时间戳
        $timestampMs = $statusData['timestamp'] ?? null;
        $timeStr = Utils::formatTimestamp($timestampMs);
        
        echo "设备状态查询成功!\n";
        echo "-------------------------------------------------\n";
        echo "设备状态: {$statusText}\n";
        echo "状态更新时间: {$timeStr}\n";
        
        // 如果设备离线，计算离线时长
        if ($status === 'OFFLINE' && $timestampMs !== null) {
            $offlineText = Utils::formatOfflineDuration($timestampMs);
            echo "离线时长: {$offlineText}\n";
        }
        
        echo "-------------------------------------------------\n";
        
        // 输出完整的状态信息（美化格式）
        echo "\n完整的状态信息:\n";
        Utils::prettyPrintJson($statusData);
    } else {
        $errorMsg = $response['errorMessage'] ?? '未知错误';
        echo "设备状态查询失败: {$errorMsg}\n";
    }
    
} catch (\Exception $e) {
    echo "发生错误: " . $e->getMessage() . "\n";
}

echo "\n示例运行完成\n"; 