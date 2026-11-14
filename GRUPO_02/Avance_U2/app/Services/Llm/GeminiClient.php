<?php

namespace App\Services\Llm;

use App\Contracts\LlmClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GeminiClient implements LlmClient
{
    public function generate(string $prompt, array $options = []): LlmResponse
    {
        $clave = trim((string) config('services.gemini.key'));

        if ($clave === '') {
            throw new LlmException('No se configuró la API key de Gemini (GEMINI_API_KEY).');
        }

        $modelo = trim((string) config('services.gemini.model', 'gemini-3.8-flash'));
        $baseUrl = rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $timeout = max(5, (int) config('services.gemini.timeout', 45));

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => array_filter([
                'temperature' => $options['temperature'] ?? config('services.gemini.temperature'),
                'maxOutputTokens' => $options['max_output_tokens'] ?? config('services.gemini.max_output_tokens'),
                'responseMimeType' => ($options['response_json'] ?? true) ? 'application/json' : null,
            ], fn (mixed $valor): bool => $valor !== null),
        ];

        $instruccionesSistema = trim((string) ($options['system'] ?? ''));

        if ($instruccionesSistema !== '') {
            $payload['systemInstruction'] = [
                'parts' => [
                    ['text' => $instruccionesSistema],
                ],
            ];
        }

        try {
            $respuesta = Http::withHeaders([
                'x-goog-api-key' => $clave,
                'Accept' => 'application/json',
            ])
                ->connectTimeout(10)
                ->timeout($timeout)
                ->post("{$baseUrl}/models/{$modelo}:generateContent", $payload);
        } catch (ConnectionException $exception) {
            throw new LlmException(
                'No se pudo conectar con Gemini: '.$this->ocultarClave($exception->getMessage(), $clave),
                0,
                $exception
            );
        }

        if ($respuesta->failed()) {
            $detalle = (string) (
                data_get($respuesta->json(), 'error.message')
                ?: Str::limit($respuesta->body(), 500, '')
            );

            throw new LlmException(
                "Gemini respondió HTTP {$respuesta->status()}: ".$this->ocultarClave($detalle, $clave)
            );
        }

        $datos = $respuesta->json();
        $contenido = data_get($datos, 'candidates.0.content.parts.0.text');

        if (! is_string($contenido) || trim($contenido) === '') {
            $bloqueo = data_get($datos, 'promptFeedback.blockReason');

            throw new LlmException(
                $bloqueo
                    ? "La respuesta fue bloqueada por Gemini ({$bloqueo})."
                    : 'Gemini no devolvió contenido utilizable.'
            );
        }

        $tokensEntrada = data_get($datos, 'usageMetadata.promptTokenCount');
        $tokensSalida = data_get($datos, 'usageMetadata.candidatesTokenCount');
        $modeloReportado = data_get($datos, 'modelVersion');

        return new LlmResponse(
            content: trim($contenido),
            model: is_string($modeloReportado) && $modeloReportado !== '' ? $modeloReportado : $modelo,
            promptTokens: is_numeric($tokensEntrada) ? (int) $tokensEntrada : null,
            completionTokens: is_numeric($tokensSalida) ? (int) $tokensSalida : null,
            raw: is_array($datos) ? $datos : [],
        );
    }

    private function ocultarClave(string $mensaje, string $clave): string
    {
        return $clave !== '' ? str_replace($clave, '[API_KEY_OCULTA]', $mensaje) : $mensaje;
    }
}
