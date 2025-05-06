<?php
/**
 * 批量查询设备状态示例
 * 
 * 该示例演示如何批量查询多个设备的状态信息
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IoTSdk\Client;
use IoTSdk\Device;
use IoTSdk\Utils;

// 配置参数
$baseUrl = 'http://121.40.253.224:10081'; // 替换为实际的API地址
$token = '488820fb-41af-40e5-b2d3-d45a8c576eea'; // 替换为实际的认证令牌

// 要查询的设备名称列表
$deviceNameList = [
    'test_device_001',
    'hbqPvaMbBQ'
];

echo "===== IoT云平台SDK - 批量查询设备状态示例 =====\n\n";

try {
    // 创建客户端和设备管理器
    $client = Client::create($baseUrl, $token);
    $deviceManager = Device::create($client);
    
    echo "准备批量查询设备状态...\n";
    echo "设备列表: " . implode(", ", $deviceNameList) . "\n\n";
    
    // 执行批量查询
    $response = $deviceManager->batchGetDeviceStatus(
        $deviceNameList
    );
    
    // 检查响应结果
    if ($client->checkResponse($response)) {
        $devicesData = $response['data'] ?? [];
        $deviceCount = count($devicesData);
        
        echo "批量查询成功! 共 {$deviceCount} 个设备\n";
        echo "-------------------------------------------------\n";
        
        // 手动解析并展示每个设备的详细信息
        foreach ($devicesData as $index => $deviceInfo) {
            $deviceName = $deviceInfo['deviceName'] ?? '未知';
            $deviceId = $deviceInfo['deviceId'] ?? '未知';
            $status = $deviceInfo['status'] ?? '未知';
            $statusText = Utils::getStatusText($status);
            
            echo ($index + 1) . ". 设备: {$deviceName} (ID: {$deviceId})\n";
            echo "   状态: {$statusText}\n";
            
            // 显示时间戳信息
            if (isset($deviceInfo['timestamp'])) {
                $timestamp = (int)($deviceInfo['timestamp'] / 1000); // 毫秒转秒
                $dateTime = date('Y-m-d H:i:s', $timestamp);
                echo "   状态时间: {$dateTime}\n";
            }
            
            // 显示最后在线时间
            if (isset($deviceInfo['lastOnlineTime'])) {
                $lastOnlineTime = (int)($deviceInfo['lastOnlineTime'] / 1000); // 毫秒转秒
                $lastOnlineDate = date('Y-m-d H:i:s', $lastOnlineTime);
                echo "   最后在线: {$lastOnlineDate}\n";
            }
            
            // 显示IP地址
            if (isset($deviceInfo['asAddress'])) {
                echo "   接入IP: {$deviceInfo['asAddress']}\n";
            }
            
            echo "-------------------------------------------------\n";
        }
        
        // 显示在线状态统计
        $onlineCount = 0;
        $offlineCount = 0;
        $unactiveCount = 0;
        
        foreach ($devicesData as $deviceInfo) {
            $status = $deviceInfo['status'] ?? null;
            if ($status === 'ONLINE') $onlineCount++;
            elseif ($status === 'OFFLINE') $offlineCount++;
            elseif ($status === 'UNACTIVE') $unactiveCount++;
        }
        
        echo "\n设备状态统计:\n";
        echo "在线设备: {$onlineCount}\n";
        echo "离线设备: {$offlineCount}\n";
        echo "未激活设备: {$unactiveCount}\n";
        
    } else {
        $errorMsg = $response['errorMessage'] ?? '未知错误';
        echo "批量查询设备状态失败: {$errorMsg}\n";
        echo "可能的原因:\n";
        echo "1. 设备名称列表中包含不存在的设备\n";
        echo "2. API地址或令牌错误\n";
        echo "3. 服务器内部错误\n";
    }
    
} catch (\Exception $e) {
    echo "发生错误: " . $e->getMessage() . "\n";
}

echo "\n示例运行完成\n"; 