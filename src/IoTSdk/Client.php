<?php

namespace IoTSdk;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * IoT云平台SDK客户端类
 * 提供与IoT云平台交互的基础功能
 */
class Client
{
    /**
     * API基础URL
     *
     * @var string
     */
    private string $baseUrl;

    /**
     * 认证令牌
     *
     * @var string
     */
    private string $token;

    /**
     * 日志记录器
     *
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * HTTP客户端
     *
     * @var HttpClient
     */
    private HttpClient $httpClient;

    /**
     * 初始化IoT客户端
     *
     * @param string $baseUrl API基础URL
     * @param string $token 认证令牌
     * @param LoggerInterface|null $logger 可选的日志记录器
     * @throws \InvalidArgumentException 参数无效时抛出异常
     */
    public function __construct(string $baseUrl, string $token, ?LoggerInterface $logger = null)
    {
        // 检查参数有效性
        if (empty($baseUrl)) {
            throw new \InvalidArgumentException("无效的base_url");
        }
        if (empty($token)) {
            throw new \InvalidArgumentException("无效的token");
        }

        $this->baseUrl = rtrim($baseUrl, '/');
        $this->token = $token;
        
        // 设置日志记录器
        if ($logger === null) {
            $this->logger = new Logger('iotsdk');
            $this->logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
        } else {
            $this->logger = $logger;
        }
        
        // 初始化HTTP客户端
        $this->httpClient = new HttpClient([
            'timeout' => 10.0,
            'connect_timeout' => 3.0
        ]);
        
        $this->logger->info("IoT客户端已初始化: {$this->baseUrl}");
    }

    /**
     * 创建IoT客户端的静态方法
     *
     * @param string $baseUrl API基础URL
     * @param string $token 认证令牌
     * @param LoggerInterface|null $logger 可选的日志记录器
     * @return Client IoT客户端实例
     */
    public static function create(string $baseUrl, string $token, ?LoggerInterface $logger = null): Client
    {
        return new self($baseUrl, $token, $logger);
    }

    /**
     * 发送API请求的通用方法
     *
     * @param string $endpoint API端点路径
     * @param array|null $payload 请求体数据
     * @param string $method HTTP方法(默认POST)
     * @param array|null $additionalHeaders 附加的请求头
     * @return array API响应结果
     * @throws \Exception 请求出错时抛出异常
     */
    public function makeRequest(string $endpoint, ?array $payload = null, string $method = 'POST', ?array $additionalHeaders = null): array
    {
        // 构建完整URL
        $url = "{$this->baseUrl}{$endpoint}";
        
        // 设置请求头
        $headers = [
            "Content-Type" => "application/json",
            "token" => $this->token
        ];
        
        // 添加附加的请求头
        if ($additionalHeaders) {
            $headers = array_merge($headers, $additionalHeaders);
        }
        
        // 准备请求数据
        $options = [
            'headers' => $headers
        ];
        
        if ($payload !== null) {
            if (strtoupper($method) === 'GET') {
                $options['query'] = $payload;
            } else {
                $options['json'] = $payload;
            }
        }
        
        $this->logger->debug("发送请求: {$method} {$url}", [
            'headers' => $headers,
            'payload' => $payload
        ]);
        
        try {
            // 发送请求
            $response = $this->httpClient->request($method, $url, $options);
            
            // 解析响应
            $responseBody = $response->getBody()->getContents();
            $result = json_decode($responseBody, true);
            
            if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("无法解析响应为JSON: " . json_last_error_msg());
            }
            
            $this->logger->debug("收到响应", ['response' => $result]);
            
            return $result;
            
        } catch (RequestException $e) {
            $this->logger->error("请求错误: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 检查API响应是否成功
     *
     * @param array|null $response API响应
     * @return bool 是否成功
     */
    public function checkResponse(?array $response): bool
    {
        if ($response === null) {
            return false;
        }
        
        $success = $response['success'] ?? false;
        
        if (!$success) {
            $errorMsg = $response['errorMessage'] ?? '未知错误';
            $this->logger->warning("API调用失败: {$errorMsg}");
        }
        
        return $success;
    }

    /**
     * 获取日志记录器
     *
     * @return LoggerInterface 日志记录器
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
} 