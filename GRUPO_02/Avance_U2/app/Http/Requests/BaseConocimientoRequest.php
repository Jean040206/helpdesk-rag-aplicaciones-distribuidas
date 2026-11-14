<?php

namespace App\Http\Requests;

use App\Models\BaseConocimiento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BaseConocimientoRequest extends FormRequest
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
            'tipo_documento' => [
                'required',
                Rule::in(array_keys(BaseConocimiento::TIPOS_DOCUMENTO)),
            ],
            'titulo' => ['required', 'string', 'min:5', 'max:180'],
            'sintomas_problema' => ['required', 'string', 'min:20', 'max:5000'],
            'pasos_solucion' => ['required', 'string', 'min:20', 'max:10000'],
            'palabras_clave' => ['required', 'string', 'min:3', 'max:200'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_servicio.required' => 'Seleccione el servicio al que pertenece el artículo.',
            'id_servicio.exists' => 'El servicio seleccionado no es válido.',
            'id_categoria.required' => 'Seleccione la categoría del artículo.',
            'id_categoria.exists' => 'La categoría seleccionada no pertenece al servicio indicado.',
            'tipo_documento.required' => 'Seleccione el tipo de documento.',
            'tipo_documento.in' => 'El tipo de documento seleccionado no es válido.',
            'titulo.required' => 'Ingrese el título del artículo.',
            'titulo.min' => 'El título debe tener al menos 5 caracteres.',
            'titulo.max' => 'El título no puede superar los 180 caracteres.',
            'sintomas_problema.required' => 'Describa los síntomas o el problema que resuelve el artículo.',
            'sintomas_problema.min' => 'Los síntomas deben tener al menos 20 caracteres.',
            'sintomas_problema.max' => 'Los síntomas no pueden superar los 5000 caracteres.',
            'pasos_solucion.required' => 'Registre los pasos de solución.',
            'pasos_solucion.min' => 'Los pasos de solución deben tener al menos 20 caracteres.',
            'pasos_solucion.max' => 'Los pasos de solución no pueden superar los 10000 caracteres.',
            'palabras_clave.required' => 'Ingrese palabras clave para la búsqueda.',
            'palabras_clave.min' => 'Las palabras clave deben tener al menos 3 caracteres.',
            'palabras_clave.max' => 'Las palabras clave no pueden superar los 200 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'id_servicio' => 'servicio',
            'id_categoria' => 'categoría',
            'tipo_documento' => 'tipo de documento',
            'titulo' => 'título',
            'sintomas_problema' => 'síntomas o problema',
            'pasos_solucion' => 'pasos de solución',
            'palabras_clave' => 'palabras clave',
            'activo' => 'estado',
        ];
    }
}
