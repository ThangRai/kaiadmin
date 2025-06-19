<?php
// lib/column-helper.php

function getColumnSettings($content_type, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM column_settings WHERE content_type = ? LIMIT 1");
    $stmt->execute([$content_type]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        return [
            'id' => null,
            'content_type' => $content_type,
            'items_per_row_tiny' => 2,
            'items_per_row_sm' => 2,
            'items_per_row_md' => 2,
            'items_per_row_lg' => 3,
            'items_per_row_xl' => 6,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }
    return $settings;
}
