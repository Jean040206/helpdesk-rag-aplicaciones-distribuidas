<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_estado' => ['required', 'integer', 'exists:estados,id_estado'],
            'diagnostico_tecnico' => ['nullable', 'string', 'max:5000'],
            'solucion_aplicada' => ['nullable', 'string', 'max:5000'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_estado.required' => 'Seleccione el nuevo estado del ticket.',
            'id_estado.exists' => 'El estado seleccionado no es válido.',
            'diagnostico_tecnico.max' => 'El diagnóstico no puede superar los 5000 caracteres.',
            'solucion_aplicada.max' => 'La solución no puede superar los 5000 caracteres.',
            'observacion.max' => 'La observación no puede superar los 255 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'id_estado' => 'estado',
            'diagnostico_tecnico' => 'diagnóstico técnico',
            'solucion_aplicada' => 'solución aplicada',
            'observacion' => 'observación',
        ];
    }
}
