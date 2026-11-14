<?php

namespace App\Contracts;

use App\Services\Llm\LlmResponse;

interface LlmClient
{
    /**
     * Genera una respuesta de texto a partir de un prompt.
     *
     * Opciones soportadas:
     * - system: instrucciones de sistema separadas del contenido no confiable.
     * - temperature: creatividad del modelo.
     * - max_output_tokens: límite de tokens de salida.
     * - response_json: solicita una respuesta JSON al proveedor.
     *
     * @param  array{system?: string, temperature?: float, max_output_tokens?: int, response_json?: bool}  $options
     */
    public function generate(string $prompt, array $options = []): LlmResponse;
}
