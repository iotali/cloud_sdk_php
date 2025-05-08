<?php
/**
 * IoTSdk - 设备管理综合示例
 * 本示例展示如何使用应用凭证初始化客户端，并执行多种设备操作
 */

// 包含自动加载文件
require __DIR__ . '/../vendor/autoload.php';

// 使用SDK命名空间
use function IoTSdk\createClientFromCredentials;
use function IoTSdk\createDeviceManager;

// 配置
$baseUrl = 'https://xxx.xxx.com';
$productKey = 'NrateJMx'; // 替换为您的产品密钥

// 应用凭证
$appId = 'app-680***';
$appSecret = '6808aa30614c4*****';

/**
 * 使用应用凭证初始化客户端
 */
function initializeClient($baseUrl, $appId, $appSecret) {
    echo "\n===== 使用应用凭证初始化客户端 =====\n";
    
    try {
        // 使用应用凭证初始化客户端
        $client = createClientFromCredentials($baseUrl, $appId, $appSecret);
        
        echo "客户端初始化成功!\n";
        echo "Base URL: " . $client->getBaseUrl() . "\n";
        echo "Token: " . substr($client->getToken(), 0, 10) . "...\n";
        
        return $client;
    } catch (Exception $e) {
        echo "客户端初始化错误: " . $e->getMessage() . "\n";
        exit(1);
    }
}

/**
 * 注册设备
 */
function registerDevice($client, $productKey, $deviceName = null, $nickName = null) {
    echo "\n===== 设备注册示例 =====\n";
    
    $deviceManager = createDeviceManager($client);
    
    // 准备参数
    $params = ['productKey' => $productKey];
    if ($deviceName) {
        $params['deviceName'] = $deviceName;
    }
    if ($nickName) {
        $params['nickName'] = $nickName;
    }
    
    echo "注册设备中...\n";
    $response = $deviceManager->registerDevice($params['productKey'], $deviceName, $nickName);
    
    if ($client->checkResponse($response)) {
        echo "\n设备注册成功!\n";
        $deviceInfo = $response['data'];
        echo "设备ID: " . $deviceInfo['deviceId'] . "\n";
        echo "设备名称: " . $deviceInfo['deviceName'] . "\n";
        echo "设备密钥: " . $deviceInfo['deviceSecret'] . "\n";
        
        return $deviceInfo['deviceName']; // 返回设备名称，用于后续操作
    } else {
        echo "\n设备注册失败!\n";
        if (isset($response['errorMessage'])) {
            echo "错误: " . $response['errorMessage'] . "\n";
        }
        return null;
    }
}

/**
 * 查询设备详情
 */
function queryDeviceDetail($client, $deviceName) {
    echo "\n===== 设备详情查询示例 =====\n";
    
    $deviceManager = createDeviceManager($client);
    
    echo "查询设备详情中...\n";
    $response = $deviceManager->getDeviceDetail($deviceName);
    
    if ($client->checkResponse($response)) {
        echo "\n设备详情查询成功!\n";
        $deviceInfo = $response['data'];
        echo "设备ID: " . $deviceInfo['deviceId'] . "\n";
        echo "设备名称: " . $deviceInfo['deviceName'] . "\n";
        echo "设备状态: " . $deviceInfo['status'] . "\n";
        
        return $deviceInfo;
    } else {
        echo "\n设备详情查询失败!\n";
        if (isset($response['errorMessage'])) {
            echo "错误: " . $response['errorMessage'] . "\n";
        }
        return null;
    }
}

/**
 * 查询设备状态
 */
function queryDeviceStatus($client, $deviceName) {
    echo "\n===== 设备状态查询示例 =====\n";
    
    $deviceManager = createDeviceManager($client);
    
    echo "查询设备状态中...\n";
    $response = $deviceManager->getDeviceStatus($deviceName);
    
    if ($client->checkResponse($response)) {
        echo "\n设备状态查询成功!\n";
        $statusData = $response['data'];
        echo "设备状态: " . $statusData['status'] . "\n";
        
        if (isset($statusData['timestamp'])) {
            $dateTime = new DateTime();
            $dateTime->setTimestamp($statusData['timestamp'] / 1000); // 毫秒转秒
            echo "状态时间戳: " . $dateTime->format('Y-m-d H:i:s') . "\n";
        }
        
        return $statusData;
    } else {
        echo "\n设备状态查询失败!\n";
        if (isset($response['errorMessage'])) {
            echo "错误: " . $response['errorMessage'] . "\n";
        }
        return null;
    }
}

/**
 * 批量查询设备状态
 */
function batchQueryDeviceStatus($client, array $deviceNames) {
    echo "\n===== 批量设备状态查询示例 =====\n";
    
    $deviceManager = createDeviceManager($client);
    
    echo "批量查询设备状态中...\n";
    $response = $deviceManager->batchGetDeviceStatus($deviceNames);
    
    if ($client->checkResponse($response)) {
        echo "\n批量查询设备状态成功!\n";
        $devicesData = $response['data'];
        echo "查询到 " . count($devicesData) . " 个设备\n\n";
        
        // 统计各状态设备数量
        $statusCounts = ['ONLINE' => 0, 'OFFLINE' => 0, 'UNACTIVE' => 0];
        
        foreach ($devicesData as $device) {
            // 显示设备信息
            echo "设备名称: " . $device['deviceName'] . ", 状态: " . $device['status'] . "\n";
            echo "设备ID: " . $device['deviceId'] . "\n";
            echo "最后在线时间: " . ($device['lastOnlineTime'] ?? 'N/A') . "\n";
            echo "时间戳: " . ($device['timestamp'] ?? 'N/A') . "\n";
            echo "接入IP: " . ($device['asAddress'] ?? 'N/A') . "\n";
            echo "----------------------------------------\n";
            
            // 更新状态计数
            $status = $device['status'];
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }
        }
        
        // 显示统计结果
        echo "\n设备状态统计:\n";
        echo "在线设备: " . $statusCounts['ONLINE'] . " 台\n";
        echo "离线设备: " . $statusCounts['OFFLINE'] . " 台\n";
        echo "未激活设备: " . $statusCounts['UNACTIVE'] . " 台\n";
        
        return $devicesData;
    } else {
        echo "\n批量查询设备状态失败!\n";
        if (isset($response['errorMessage'])) {
            echo "错误: " . $response['errorMessage'] . "\n";
        }
        return null;
    }
}

/**
 * 发送RRPC消息
 */
function sendRrpcMessage($client, $deviceName, $productKey, $messageContent) {
    echo "\n===== 发送RRPC消息示例 =====\n";
    
    $deviceManager = createDeviceManager($client);
    
    echo "发送RRPC消息中...\n";
    echo "消息内容: " . $messageContent . "\n";
    
    $response = $deviceManager->sendRrpcMessage($deviceName, $productKey, $messageContent);
    
    if ($client->checkResponse($response)) {
        echo "\nRRPC消息发送成功!\n";
        if (isset($response['payloadBase64Byte'])) {
            $base64Content = $response['payloadBase64Byte'];
            $decodedContent = base64_decode($base64Content);
            echo "收到响应: " . $decodedContent . "\n";
        }
        return true;
    } else {
        echo "\nRRPC消息发送失败!\n";
        if (isset($response['errorMessage'])) {
            echo "错误: " . $response['errorMessage'] . "\n";
        }
        return false;
    }
}

/**
 * 发送自定义指令
 */
function sendCustomCommand($client, $deviceName, $messageContent) {
    echo "\n===== 自定义指令下发示例 =====\n";
    
    $deviceManager = createDeviceManager($client);
    
    echo "原始消息内容: " . $messageContent . "\n";
    
    // 将消息内容转换为Base64编码
    $base64Message = base64_encode($messageContent);
    
    // 构建请求体
    $payload = [
        "deviceName" => $deviceName,
        "messageContent" => $base64Message
    ];
    
    echo "发送自定义指令中...\n";
    $response = $client->makeRequest("/api/v1/device/down/record/add/custom", $payload);
    
    if ($client->checkResponse($response)) {
        echo "\n自定义指令下发成功!\n";
        echo "响应数据: " . json_encode($response['data'] ?? []) . "\n";
        return true;
    } else {
        echo "\n自定义指令下发失败: " . ($response['errorMessage'] ?? '未知错误') . "\n";
        return false;
    }
}

/**
 * 主程序
 */
function main() {
    global $baseUrl, $appId, $appSecret, $productKey;
    
    echo "======================================================\n";
    echo "           IoT SDK 设备管理综合示例程序             \n";
    echo "======================================================\n";
    
    // 初始化客户端 - 使用应用凭证获取token
    $client = initializeClient($baseUrl, $appId, $appSecret);
    
    // 注册一个新设备
    $deviceName = registerDevice($client, $productKey, "hello333", "SDK测试设备333");
    
    if ($deviceName) {
        // 如果注册成功，查询该设备的详情
        $deviceInfo = queryDeviceDetail($client, $deviceName);
        
        // 查询该设备的状态
        $deviceStatus = queryDeviceStatus($client, $deviceName);
        
        // 批量查询多个设备的状态
        $deviceList = [$deviceName, "hello1111", "hello222"]; // 添加其他已知设备
        $batchStatus = batchQueryDeviceStatus($client, $deviceList);
        
        // 发送RRPC消息
        $messageResult = sendRrpcMessage($client, $deviceName, $productKey, "Hello from PHP SDK");
        
        // 发送自定义指令
        $commandJson = '{"washingMode": 2, "washingTime": 30}';
        $commandResult = sendCustomCommand($client, $deviceName, $commandJson);
    }
    
    echo "\n======================================================\n";
    echo "             示例程序执行完成                        \n";
    echo "======================================================\n";
}

// 执行主程序
main(); 