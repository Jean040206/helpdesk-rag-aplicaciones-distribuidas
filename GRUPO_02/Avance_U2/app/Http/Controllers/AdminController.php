<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $roles = Rol::query()
            ->withCount('usuarios')
            ->orderBy('id_rol')
            ->get();

        $usuarios = Usuario::query()
            ->with('rol')
            ->orderBy('id_rol')
            ->orderBy('nombre_completo')
            ->get();

        return view('admin.index', compact('roles', 'usuarios'));
    }
}
