<?php
/**
 * IoT云平台SDK
 * 提供与IoT云平台交互的简便方法
 */

namespace IoTSdk;

/**
 * SDK版本
 */
const VERSION = '1.0.0';

/**
 * 创建IoT客户端
 * 
 * @param string $baseUrl API基础URL
 * @param string $token 认证令牌
 * @param \Psr\Log\LoggerInterface|null $logger 可选的日志记录器
 * @return Client IoT客户端实例
 */
function createClient(string $baseUrl, string $token, $logger = null)
{
    return Client::create($baseUrl, $token, $logger);
}

/**
 * 创建设备管理器
 * 
 * @param Client $client IoT客户端实例
 * @return Device 设备管理器实例
 */
function createDeviceManager(Client $client)
{
    return Device::create($client);
} 