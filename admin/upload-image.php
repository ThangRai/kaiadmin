<?php
header('Content-Type: application/json');

$targetDir = __DIR__ . '/../../uploads/'; // thư mục gốc /uploads
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

if (isset($_FILES['upload'])) {
    $file = $_FILES['upload'];
    $fileName = 'img_' . uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $filePath = $targetDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        echo json_encode([
            'uploaded' => 1,
            'fileName' => $fileName,
            'url' => '/uploads/' . $fileName
        ]);
    } else {
        echo json_encode([
            'uploaded' => 0,
            'error' => ['message' => 'Upload failed.']
        ]);
    }
} else {
    echo json_encode([
        'uploaded' => 0,
        'error' => ['message' => 'No file received.']
    ]);
}
?>
