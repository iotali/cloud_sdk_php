<?php
/**
 * 批量设备状态查询示例
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IoTSdk\Client;
use IoTSdk\Device;
use IoTSdk\Utils;

// 配置参数
$baseUrl = 'http://your-iot-platform-url'; // 替换为实际的API地址
$token = 'your-auth-token';                // 替换为实际的认证令牌

// 设备参数 - 根据设备名称查询的示例
$deviceNames = [
    'device1',
    'device2',
    'device3'
    // 最多可添加100个设备名称
];

// 或者使用设备ID列表查询
// $deviceIds = [
//     'id1',
//     'id2',
//     'id3'
//     // 最多可添加100个设备ID
// ];

echo "===== IoT云平台SDK - 批量设备状态查询示例 =====\n\n";

try {
    // 创建客户端和设备管理器
    $client = Client::create($baseUrl, $token);
    $deviceManager = Device::create($client);
    
    echo "正在批量查询 " . count($deviceNames) . " 个设备的状态...\n\n";
    
    // 执行批量设备状态查询
    // 通过设备名称列表查询
    $response = $deviceManager->batchGetDeviceStatus($deviceNames);
    
    // 或者通过设备ID列表查询
    // $response = $deviceManager->batchGetDeviceStatus(null, $deviceIds);
    
    // 检查响应结果
    if ($client->checkResponse($response)) {
        $devicesData = $response['data'] ?? [];
        $totalDevices = count($devicesData);
        
        echo "批量设备状态查询成功，获取到 {$totalDevices} 个设备的状态\n";
        echo "-------------------------------------------------\n";
        
        // 统计各状态设备数量
        $statusCounts = ["ONLINE" => 0, "OFFLINE" => 0, "UNACTIVE" => 0];
        
        // 逐个处理设备状态
        foreach ($devicesData as $index => $deviceInfo) {
            $deviceName = $deviceInfo['deviceName'] ?? '未知';
            $deviceId = $deviceInfo['deviceId'] ?? '未知';
            
            $deviceStatus = $deviceInfo['deviceStatus'] ?? [];
            $status = $deviceStatus['status'] ?? '未知';
            $timestampMs = $deviceStatus['timestamp'] ?? null;
            
            // 更新状态计数
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }
            
            // 获取状态文本描述
            $statusText = Utils::getStatusText($status);
            
            // 获取格式化的时间
            $timeStr = Utils::formatTimestamp($timestampMs);
            
            // 输出设备信息
            echo "\n设备 " . ($index + 1) . ":\n";
            echo "  设备名称: {$deviceName}\n";
            echo "  设备ID: {$deviceId}\n";
            echo "  设备状态: {$statusText}\n";
            echo "  状态更新时间: {$timeStr}\n";
            
            // 如果设备离线，显示离线时长
            if ($status === 'OFFLINE' && $timestampMs !== null) {
                $offlineText = Utils::formatOfflineDuration($timestampMs);
                echo "  离线时长: {$offlineText}\n";
            }
        }
        
        // 输出统计结果
        echo "\n设备状态统计:\n";
        echo "  在线: {$statusCounts['ONLINE']}\n";
        echo "  离线: {$statusCounts['OFFLINE']}\n";
        echo "  未激活: {$statusCounts['UNACTIVE']}\n";
        echo "-------------------------------------------------\n";
        
        // 输出完整的状态信息（美化格式）
        echo "\n是否需要查看完整的状态信息？(y/n): ";
        $input = trim(fgets(STDIN));
        if (strtolower($input) === 'y') {
            echo "\n完整的状态信息:\n";
            Utils::prettyPrintJson($devicesData);
        }
    } else {
        $errorMsg = $response['errorMessage'] ?? '未知错误';
        echo "批量设备状态查询失败: {$errorMsg}\n";
    }
    
} catch (\Exception $e) {
    echo "发生错误: " . $e->getMessage() . "\n";
}

echo "\n示例运行完成\n"; 