<?php

namespace IoTSdk;

use DateTime;

/**
 * 工具函数集，提供格式化、数据处理等辅助功能
 */
class Utils
{
    /**
     * 将毫秒时间戳格式化为可读字符串
     *
     * @param int|null $timestampMs 毫秒时间戳
     * @return string 格式化后的时间字符串
     */
    public static function formatTimestamp(?int $timestampMs): string
    {
        if ($timestampMs === null) {
            return "未知";
        }
        
        try {
            $dt = new DateTime();
            $dt->setTimestamp((int)($timestampMs / 1000));  // 毫秒转秒
            return $dt->format("Y-m-d H:i:s");
        } catch (\Exception $e) {
            return (string)$timestampMs;
        }
    }
    
    /**
     * 将ISO格式时间字符串转换为可读字符串
     *
     * @param string|null $timeStr ISO格式时间字符串
     * @return string 格式化后的时间字符串
     */
    public static function formatIsoTime(?string $timeStr): string
    {
        if ($timeStr === null || $timeStr === '') {
            return "未知";
        }
        
        try {
            $timeStr = str_replace('Z', '+00:00', $timeStr);
            $dt = new DateTime($timeStr);
            return $dt->format("Y-m-d H:i:s");
        } catch (\Exception $e) {
            return $timeStr;
        }
    }
    
    /**
     * 计算并格式化离线时长
     *
     * @param int $timestampMs 离线时的毫秒时间戳
     * @return string 格式化的离线时长
     */
    public static function formatOfflineDuration(int $timestampMs): string
    {
        $nowMs = (int)(microtime(true) * 1000);
        $offlineDurationMs = $nowMs - $timestampMs;
        $offlineMinutes = $offlineDurationMs / (1000 * 60);
        
        if ($offlineMinutes < 60) {
            return "约 " . (int)$offlineMinutes . " 分钟";
        }
        
        $offlineHours = $offlineMinutes / 60;
        if ($offlineHours < 24) {
            return "约 " . (int)$offlineHours . " 小时 " . (int)($offlineMinutes % 60) . " 分钟";
        }
        
        $offlineDays = (int)($offlineHours / 24);
        $remainingHours = (int)($offlineHours % 24);
        return "约 {$offlineDays} 天 {$remainingHours} 小时";
    }
    
    /**
     * 美化打印JSON数据
     *
     * @param array $data 要打印的JSON数据
     * @return void
     */
    public static function prettyPrintJson(array $data): void
    {
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
    
    /**
     * 获取设备状态的中文描述
     *
     * @param string $status 设备状态码
     * @return string 状态的中文描述
     */
    public static function getStatusText(string $status): string
    {
        $statusMap = [
            "ONLINE" => "在线",
            "OFFLINE" => "离线",
            "UNACTIVE" => "未激活"
        ];
        
        return $statusMap[$status] ?? $status;
    }
} 