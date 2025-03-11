<?php

// Đọc nội dung từ routes.json
$routesJson = file_get_contents('routes.json');
$routes = json_decode($routesJson, true);

// Kiểm tra nếu JSON bị lỗi hoặc trống
if (json_last_error() !== JSON_ERROR_NONE) {
    die("❌ Lỗi JSON: " . json_last_error_msg() . "\n");
}

if (!is_array($routes)) {
    die("❌ Lỗi: JSON không phải là một mảng!\n");
}

// Tạo cấu trúc Postman Collection
$postmanCollection = [
    "info" => [
        "_postman_id" => uniqid(),
        "name" => "Laravel API Collection",
        "schema" => "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    ],
    "item" => []
];

// Chuyển đổi các route thành request của Postman
foreach ($routes as $route) {
    if (!isset($route['method'], $route['uri'])) {
        continue; // Bỏ qua nếu không có method hoặc uri
    }

    // Chuyển `method` thành mảng (nếu có nhiều phương thức)
    $methods = explode('|', $route['method']);

    foreach ($methods as $method) {
        $method = strtoupper(trim($method)); // Chuyển về chữ in hoa

        // Chuyển đổi tham số `{param}` thành `:param` (format của Postman)
        $uri = preg_replace('/\{(.+?)\}/', ':$1', $route['uri']);

        // Tạo request cho Postman
        $postmanCollection['item'][] = [
            "name" => $uri,
            "request" => [
                "method" => $method,
                "header" => [],
                "url" => [
                    "raw" => "{{base_url}}/" . ltrim($uri, "/"),
                    "host" => ["{{base_url}}"],
                    "path" => explode("/", $uri)
                ]
            ]
        ];
    }
}

// Lưu vào file JSON để import vào Postman
file_put_contents('postman_collection.json', json_encode($postmanCollection, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "✅ Postman Collection đã được tạo thành công: postman_collection.json\n";
