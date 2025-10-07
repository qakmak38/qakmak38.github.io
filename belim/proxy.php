<?php
// 获取视频ID参数
$videoId = $_GET['id'] ?? '';

// 验证视频ID格式（只允许数字）
if (!preg_match('/^\d+$/', $videoId)) {
    http_response_code(400);
    die('无效的视频ID格式');
}

// API配置 - 使用您提供的API地址
$apiUrl = "https://bilim1.dilkax.com/api/index/get_sepisode_info?user_id=5&id={$videoId}";

// 创建cURL句柄
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; VideoProxy/1.0)',
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => ['Accept: application/json']
]);

// 执行请求
$response = curl_exec($ch);

// 检查cURL错误
if (curl_errno($ch)) {
    $error = curl_error($ch);
    curl_close($ch);
    http_response_code(500);
    die("API请求失败: {$error}");
}

// 获取HTTP状态码
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 检查API响应状态
if ($httpCode !== 200) {
    http_response_code($httpCode);
    die("API返回错误状态: {$httpCode}");
}

// 解析JSON响应
$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    die("API返回无效JSON数据");
}

// 检查play_movie_url字段是否存在
if (!isset($data['result']['play_movie_url'])) {
    http_response_code(404);
    die("API响应中缺少play_movie_url字段");
}

// 获取视频URL
$videoUrl = $data['result']['play_movie_url'];

// 最小化处理：只处理JSON转义字符（\/ -> /）
$videoUrl = str_replace('\/', '/', $videoUrl);

// 最小化处理：只编码空格为%20（避免空格导致重定向失败）
$videoUrl = str_replace(' ', '%20', $videoUrl);

// 直接重定向到视频URL（不进行任何URL验证）
header("Location: {$videoUrl}");
exit;
?>
