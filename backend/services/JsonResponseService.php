<?php

class JsonResponse
{
    public static function jsonSuccess(string $msg, mixed $data = ''): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $msg, 'data' => $data]);
    }

    public static function jsonError(string $msg): void
    {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $msg]);
    }
}
