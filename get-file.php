<?php
header('Content-Type: application/json');

$response = ['success' => false, 'files' => []];

try {
    if (!file_exists('files_data.json')) {
        throw new Exception('لا توجد ملفات مرفوعة بعد');
    }

    $filesData = json_decode(file_get_contents('files_data.json'), true);
    
    // ترتيب الملفات من الأحدث إلى الأقدم
    usort($filesData, function($a, $b) {
        return strtotime($b['uploaded_at']) - strtotime($a['uploaded_at']);
    });

    $response['success'] = true;
    $response['files'] = $filesData;

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>
