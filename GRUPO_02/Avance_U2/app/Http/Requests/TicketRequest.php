<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_servicio' => ['required', 'integer', 'exists:servicios,id_servicio'],
            'id_categoria' => [
                'required',
                'integer',
                Rule::exists('categorias', 'id_categoria')
                    ->where(fn ($query) => $query->where('id_servicio_default', $this->integer('id_servicio'))),
            ],
            'id_prioridad' => ['required', 'integer', 'exists:prioridades,id_prioridad'],
            'asunto' => ['required', 'string', 'min:5', 'max:180'],
            'descripcion' => ['required', 'string', 'min:20', 'max:5000'],
            'evidencias' => ['nullable', 'array', 'max:5'],
            'evidencias.*' => ['file', 'mimes:jpg,jpeg,png,pdf,docx', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_servicio.required' => 'Seleccione el servicio afectado.',
            'id_servicio.exists' => 'El servicio seleccionado no es válido.',
            'id_categoria.required' => 'Seleccione la categoría de la incidencia.',
            'id_categoria.exists' => 'La categoría seleccionada no pertenece al servicio indicado.',
            'id_prioridad.required' => 'Seleccione la prioridad.',
            'id_prioridad.exists' => 'La prioridad seleccionada no es válida.',
            'asunto.required' => 'Ingrese un asunto para el ticket.',
            'asunto.min' => 'El asunto debe tener al menos 5 caracteres.',
            'asunto.max' => 'El asunto no puede superar los 180 caracteres.',
            'descripcion.required' => 'Describa la incidencia.',
            'descripcion.min' => 'La descripción debe tener al menos 20 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 5000 caracteres.',
            'evidencias.max' => 'Puede adjuntar como máximo 5 archivos.',
            'evidencias.*.file' => 'Cada evidencia debe ser un archivo válido.',
            'evidencias.*.mimes' => 'Las evidencias deben ser JPG, PNG, PDF o DOCX.',
            'evidencias.*.max' => 'Cada evidencia no puede superar los 2 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'id_servicio' => 'servicio',
            'id_categoria' => 'categoría',
            'id_prioridad' => 'prioridad',
            'asunto' => 'asunto',
            'descripcion' => 'descripción',
            'evidencias' => 'evidencias',
        ];
    }
}
