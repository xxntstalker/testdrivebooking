<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiLoggerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('X-Testing') === 'true') {
            return $next($request);
        }
        // Запоминаем время начала запроса
        $startTime = microtime(true);

        // Генерируем уникальный ID запроса
        $requestId = $request->header('X-Request-ID') ?? uniqid('req_', true);

        // Логируем входящий запрос
        $requestLog = [
            'type' => 'request',
            'request_id' => $requestId,
            'timestamp' => now()->toIso8601String(),
            'method' => $request->method(),
            'uri' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $this->filterSensitiveHeaders($request->headers->all()),
            'body' => $this->filterSensitiveData($request->all()),
        ];

        Log::channel('api')->info('API Request', $requestLog);

        // Получаем ответ
        $response = $next($request);

        // Вычисляем время выполнения
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        // Логируем исходящий ответ
        $responseLog = [
            'type' => 'response',
            'request_id' => $requestId,
            'timestamp' => now()->toIso8601String(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'body' => $this->getResponseContent($response),
        ];

        Log::channel('api')->info('API Response', $responseLog);

        // Добавляем X-Request-ID в ответ для трассировки
        if ($response instanceof JsonResponse) {
            $response->header('X-Request-ID', $requestId);
        }

        return $response;
    }

    /**
     * Фильтруем чувствительные заголовки
     */
    private function filterSensitiveHeaders(array $headers): array
    {
        $sensitive = ['authorization', 'cookie', 'set-cookie'];
        foreach ($sensitive as $key) {
            if (isset($headers[$key])) {
                $headers[$key] = '***REDACTED***';
            }
        }
        return $headers;
    }

    /**
     * Фильтруем чувствительные данные в теле запроса
     */
    private function filterSensitiveData(array $data): array
    {
        $sensitive = ['password', 'token', 'secret', 'api_key', 'credit_card'];
        foreach ($sensitive as $key) {
            if (isset($data[$key])) {
                $data[$key] = '***REDACTED***';
            }
        }
        return $data;
    }

    /**
     * Получаем содержимое ответа
     */
    private function getResponseContent(Response $response): mixed
    {
        $content = $response->getContent();

        // Пытаемся декодировать JSON
        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return substr($content, 0, 1000); // Обрезаем если не JSON
    }
}
