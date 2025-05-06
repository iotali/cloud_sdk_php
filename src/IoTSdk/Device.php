<?php

namespace IoTSdk;

use Psr\Log\LoggerInterface;
use DateTime;

/**
 * 设备管理模块，提供设备相关操作
 */
class Device
{
    /**
     * IoT客户端实例
     *
     * @var Client
     */
    private Client $client;

    /**
     * 日志记录器
     *
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * 初始化设备管理模块
     *
     * @param Client $client IoT客户端实例
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->logger = $client->getLogger();
    }

    /**
     * 创建设备管理器的静态方法
     *
     * @param Client $client IoT客户端实例
     * @return Device 设备管理器实例
     */
    public static function create(Client $client): Device
    {
        return new self($client);
    }

    /**
     * 注册设备
     *
     * @param string $productKey 产品唯一标识码
     * @param string|null $deviceName 设备标识码，可选，若未提供则自动生成
     * @param string|null $nickName 设备显示名称，可选
     * @return array 注册结果，包含设备ID和密钥等信息
     */
    public function registerDevice(string $productKey, ?string $deviceName = null, ?string $nickName = null): array
    {
        $endpoint = "/api/v1/quickdevice/register";
        
        // 构建请求体
        $payload = [
            "productKey" => $productKey
        ];
        
        // 添加可选参数
        if ($deviceName !== null) {
            $payload["deviceName"] = $deviceName;
        }
        
        if ($nickName !== null) {
            $payload["nickName"] = $nickName;
        }
        
        // 发送请求
        $response = $this->client->makeRequest($endpoint, $payload);
        
        // 检查结果并格式化输出
        if ($this->client->checkResponse($response)) {
            $deviceInfo = $response["data"];
            $this->logger->info("设备注册成功: {$deviceInfo['deviceName']}");
            
            // 输出详细信息
            $this->logger->info("设备信息摘要:");
            $this->logger->info("产品密钥: {$deviceInfo['productKey']}");
            $this->logger->info("设备名称: {$deviceInfo['deviceName']}");
            $this->logger->info("显示名称: {$deviceInfo['nickName']}");
            $this->logger->info("设备ID: {$deviceInfo['deviceId']}");
            $this->logger->info("设备密钥: {$deviceInfo['deviceSecret']}");
        }
        
        return $response;
    }

    /**
     * 查询设备详情
     *
     * @param string|null $deviceName 设备编码，可选
     * @param string|null $deviceId 设备唯一标识，可选
     * @throws \InvalidArgumentException 参数无效时抛出异常
     * @return array 设备详情信息
     */
    public function getDeviceDetail(?string $deviceName = null, ?string $deviceId = null): array
    {
        // 参数验证
        if ($deviceName === null && $deviceId === null) {
            throw new \InvalidArgumentException("设备编码(deviceName)和设备ID(deviceId)至少需要提供一个");
        }
        
        $endpoint = "/api/v1/quickdevice/detail";
        
        // 构建请求体
        $payload = [];
        if ($deviceName !== null) {
            $payload["deviceName"] = $deviceName;
        }
        if ($deviceId !== null) {
            $payload["deviceId"] = $deviceId;
        }
        
        // 发送请求
        $response = $this->client->makeRequest($endpoint, $payload);
        
        // 检查结果并格式化输出
        if ($this->client->checkResponse($response)) {
            $deviceInfo = $response["data"];
            $deviceStatus = $deviceInfo["status"];
            
            // 格式化设备状态
            $statusMap = [
                "ONLINE" => "在线",
                "OFFLINE" => "离线",
                "UNACTIVE" => "未激活"
            ];
            $statusText = $statusMap[$deviceStatus] ?? $deviceStatus;
            
            // 输出设备基础信息
            $this->logger->info("设备ID: " . ($deviceInfo['deviceId'] ?? '未知'));
            $this->logger->info("设备名称: " . ($deviceInfo['deviceName'] ?? '未知'));
            $this->logger->info("设备状态: {$statusText}");
        }
        
        return $response;
    }

    /**
     * 查询设备在线状态
     *
     * @param string|null $deviceName 设备编码，可选
     * @param string|null $deviceId 设备唯一标识，可选
     * @throws \InvalidArgumentException 参数无效时抛出异常
     * @return array 设备状态信息
     */
    public function getDeviceStatus(?string $deviceName = null, ?string $deviceId = null): array
    {
        // 参数验证
        if ($deviceName === null && $deviceId === null) {
            throw new \InvalidArgumentException("设备编码(deviceName)和设备ID(deviceId)至少需要提供一个");
        }
        
        $endpoint = "/api/v1/quickdevice/status";
        
        // 构建请求体
        $payload = [];
        if ($deviceName !== null) {
            $payload["deviceName"] = $deviceName;
        }
        if ($deviceId !== null) {
            $payload["deviceId"] = $deviceId;
        }
        
        // 发送请求
        $response = $this->client->makeRequest($endpoint, $payload);
        
        // 检查结果并格式化输出
        if ($this->client->checkResponse($response)) {
            $statusData = $response["data"];
            $deviceStatus = $statusData["status"] ?? null;
            $timestampMs = $statusData["timestamp"] ?? null;
            
            // 状态映射
            $statusMap = [
                "ONLINE" => "在线",
                "OFFLINE" => "离线",
                "UNACTIVE" => "未激活"
            ];
            $statusText = $statusMap[$deviceStatus] ?? $deviceStatus;
            
            // 时间戳格式化
            $timeStr = "未知";
            if ($timestampMs !== null) {
                $dt = new DateTime();
                $dt->setTimestamp((int)($timestampMs / 1000));  // 毫秒转秒
                $timeStr = $dt->format("Y-m-d H:i:s");
            }
            
            // 显示状态信息
            $this->logger->info("设备状态: {$statusText}");
            $this->logger->info("状态更新时间: {$timeStr}");
            
            // 如果设备离线，计算离线时长
            if ($deviceStatus === "OFFLINE" && $timestampMs !== null) {
                $nowMs = (int)(microtime(true) * 1000);
                $offlineDurationMs = $nowMs - $timestampMs;
                $offlineMinutes = $offlineDurationMs / (1000 * 60);
                
                $offlineText = "";
                if ($offlineMinutes < 60) {
                    $offlineText = "约 " . (int)$offlineMinutes . " 分钟";
                } else {
                    $offlineHours = $offlineMinutes / 60;
                    if ($offlineHours < 24) {
                        $offlineText = "约 " . (int)$offlineHours . " 小时 " . (int)($offlineMinutes % 60) . " 分钟";
                    } else {
                        $offlineDays = (int)($offlineHours / 24);
                        $remainingHours = (int)($offlineHours % 24);
                        $offlineText = "约 {$offlineDays} 天 {$remainingHours} 小时";
                    }
                }
                
                $this->logger->info("离线时长: {$offlineText}");
            }
        }
        
        return $response;
    }

    /**
     * 批量查询设备运行状态
     *
     * @param array|null $deviceNameList 设备编码列表，可选
     * @param array|null $deviceIdList 设备唯一标识列表，可选
     * @throws \InvalidArgumentException 参数无效时抛出异常
     * @return array 批量设备状态信息
     */
    public function batchGetDeviceStatus(?array $deviceNameList = null, ?array $deviceIdList = null): array
    {
        // 参数验证
        if (($deviceNameList === null || empty($deviceNameList)) && 
            ($deviceIdList === null || empty($deviceIdList))) {
            throw new \InvalidArgumentException("设备编码列表(deviceName)和设备ID列表(deviceId)至少需要提供一个");
        }
        
        // 检查设备数量限制
        $deviceCount = count($deviceNameList ?? []) + count($deviceIdList ?? []);
        if ($deviceCount > 100) {
            throw new \InvalidArgumentException("单次请求最多支持查询100个设备，当前请求包含{$deviceCount}个设备");
        }
        
        $endpoint = "/api/v1/quickdevice/batchGetDeviceState";
        
        // 构建请求体
        $payload = [];
        if ($deviceNameList !== null && !empty($deviceNameList)) {
            $payload["deviceName"] = $deviceNameList;
        }
        if ($deviceIdList !== null && !empty($deviceIdList)) {
            $payload["deviceId"] = $deviceIdList;
        }
        
        // 发送请求
        $response = $this->client->makeRequest($endpoint, $payload);
        
        // 检查结果并统计输出
        if ($this->client->checkResponse($response)) {
            // 统计各状态设备数量
            $statusCounts = ["ONLINE" => 0, "OFFLINE" => 0, "UNACTIVE" => 0];
            
            // 获取设备状态列表
            $devicesData = $response["data"] ?? [];
            $this->logger->info("共返回 " . count($devicesData) . " 个设备信息");
            
            // 遍历统计
            foreach ($devicesData as $deviceInfo) {
                // 直接从设备信息中获取状态，而不是通过deviceStatus字段
                $status = $deviceInfo["status"] ?? "未知";
                
                // 更新状态计数
                if (isset($statusCounts[$status])) {
                    $statusCounts[$status]++;
                }
                
                // 输出设备详细信息
                $deviceName = $deviceInfo["deviceName"] ?? "未知";
                $deviceId = $deviceInfo["deviceId"] ?? "未知";
                $this->logger->debug("设备: {$deviceName} (ID: {$deviceId}) - 状态: " . Utils::getStatusText($status));
                
                // 显示额外信息（如果存在）
                if (isset($deviceInfo["lastOnlineTime"])) {
                    $lastOnlineTime = (int)($deviceInfo["lastOnlineTime"] / 1000);
                    $dt = new DateTime();
                    $dt->setTimestamp($lastOnlineTime);
                    $this->logger->debug("  最后在线时间: " . $dt->format("Y-m-d H:i:s"));
                }
                
                if (isset($deviceInfo["asAddress"])) {
                    $this->logger->debug("  接入IP: " . $deviceInfo["asAddress"]);
                }
            }
            
            // 输出统计结果
            $this->logger->info("设备状态统计:");
            $this->logger->info("在线: {$statusCounts['ONLINE']}");
            $this->logger->info("离线: {$statusCounts['OFFLINE']}");
            $this->logger->info("未激活: {$statusCounts['UNACTIVE']}");
        }
        
        return $response;
    }

    /**
     * 发送RRPC消息
     *
     * @param string $deviceName 设备编码
     * @param string $productKey 产品唯一标识
     * @param string $messageContent 消息内容
     * @param int $timeout 超时时间(毫秒)，默认5000
     * @return array 发送结果
     */
    public function sendRrpcMessage(string $deviceName, string $productKey, string $messageContent, int $timeout = 5000): array
    {
        $endpoint = "/api/v1/quickdevice/rrpc";
        
        // 构建请求体
        $payload = [
            "deviceName" => $deviceName,
            "productKey" => $productKey,
            "messageContent" => $messageContent,
            "timeout" => $timeout
        ];
        
        // 发送请求
        $response = $this->client->makeRequest($endpoint, $payload);
        
        // 检查结果并格式化输出
        if ($this->client->checkResponse($response)) {
            $resultData = $response["data"] ?? [];
            $messageId = $resultData["messageId"] ?? "未知";
            $deviceResponse = $resultData["response"] ?? null;
            
            $this->logger->info("RRPC消息已发送，消息ID: {$messageId}");
            
            if ($deviceResponse !== null) {
                $this->logger->info("设备响应: {$deviceResponse}");
            } else {
                $this->logger->info("设备未在超时时间内响应");
            }
        }
        
        return $response;
    }
    
    /**
     * 发送自定义指令（异步）
     * 
     * 注意：此方法要求设备已订阅了/{productKey}/{deviceName}/user/get主题
     *
     * @param string $deviceName 设备编码
     * @param string $messageContent 消息内容，会被自动Base64编码
     * @return array 发送结果
     */
    public function sendCustomCommand(string $deviceName, string $messageContent): array
    {
        $endpoint = "/api/v1/device/down/record/add/custom";
        
        // 将消息内容转换为Base64编码
        $base64Message = base64_encode($messageContent);
        
        // 构建请求体
        $payload = [
            "deviceName" => $deviceName,
            "messageContent" => $base64Message
        ];
        
        // 发送请求
        $response = $this->client->makeRequest($endpoint, $payload);
        
        // 检查结果并格式化输出
        if ($this->client->checkResponse($response)) {
            $this->logger->info("自定义指令已发送到设备: {$deviceName}");
            $this->logger->debug("原始消息内容: {$messageContent}");
            
            if (isset($response["data"])) {
                $this->logger->debug("响应数据: " . json_encode($response["data"], JSON_UNESCAPED_UNICODE));
            }
        } else {
            $errorMsg = $response["errorMessage"] ?? "未知错误";
            $this->logger->error("自定义指令发送失败: {$errorMsg}");
        }
        
        return $response;
    }
} 