<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_tecnico_asignado' => ['required', 'integer', 'exists:usuarios,id_usuario'],
            'id_prioridad' => ['required', 'integer', 'exists:prioridades,id_prioridad'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_tecnico_asignado.required' => 'Seleccione el técnico responsable.',
            'id_tecnico_asignado.exists' => 'El técnico seleccionado no es válido.',
            'id_prioridad.required' => 'Seleccione la prioridad del ticket.',
            'id_prioridad.exists' => 'La prioridad seleccionada no es válida.',
            'observacion.max' => 'La observación no puede superar los 255 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'id_tecnico_asignado' => 'técnico',
            'id_prioridad' => 'prioridad',
            'observacion' => 'observación',
        ];
    }
}
