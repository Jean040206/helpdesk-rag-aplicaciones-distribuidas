<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RagDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decision_tecnico' => [
                'required',
                Rule::in(['Aceptada', 'Modificada', 'Descartada']),
            ],
            'solucion_final_tecnico' => ['nullable', 'string', 'max:10000'],
            'comentario_tecnico' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $decision = (string) $this->input('decision_tecnico');

            if ($decision === 'Modificada' && blank($this->input('solucion_final_tecnico'))) {
                $validator->errors()->add(
                    'solucion_final_tecnico',
                    'Registre la solución final modificada antes de guardar la decisión.'
                );
            }

            if ($decision === 'Descartada' && blank($this->input('comentario_tecnico'))) {
                $validator->errors()->add(
                    'comentario_tecnico',
                    'Indique el motivo por el que se descarta la recomendación.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'decision_tecnico.required' => 'Seleccione una decisión para la recomendación.',
            'decision_tecnico.in' => 'La decisión seleccionada no es válida.',
            'solucion_final_tecnico.max' => 'La solución final no puede superar los 10000 caracteres.',
            'comentario_tecnico.max' => 'El comentario no puede superar los 2000 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'decision_tecnico' => 'decisión',
            'solucion_final_tecnico' => 'solución final',
            'comentario_tecnico' => 'comentario',
        ];
    }
}
