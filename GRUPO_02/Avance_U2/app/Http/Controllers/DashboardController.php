<?php

namespace App\Http\Controllers;

use App\Models\BaseConocimiento;
use App\Models\EvaluacionRag;
use App\Models\Ticket;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $consulta = Ticket::query()->visiblesPara($usuario);

        $resumen = [
            'total' => (clone $consulta)->count(),
            'abiertos' => (clone $consulta)->where('id_estado', Ticket::ESTADO_ABIERTO)->count(),
            'en_atencion' => (clone $consulta)->whereIn('id_estado', [
                Ticket::ESTADO_ASIGNADO,
                Ticket::ESTADO_EN_PROCESO,
            ])->count(),
            'resueltos' => (clone $consulta)->whereIn('id_estado', [
                Ticket::ESTADO_RESUELTO,
                Ticket::ESTADO_CERRADO,
            ])->count(),
        ];

        $ultimosTickets = (clone $consulta)
            ->with(['solicitante', 'tecnicoAsignado', 'servicio', 'categoria', 'prioridad', 'estado'])
            ->orderByDesc('fecha_registro')
            ->limit(6)
            ->get();

        $puedeVerMetricasKb = $usuario->can('viewAny', BaseConocimiento::class);
        $puedeVerMetricasRag = $usuario->can('viewAny', EvaluacionRag::class);

        $totalArticulos = $puedeVerMetricasKb
            ? BaseConocimiento::query()->activos()->count()
            : null;

        $ragEvaluadas = $puedeVerMetricasRag
            ? EvaluacionRag::query()->count()
            : null;

        $ragAceptadas = $puedeVerMetricasRag
            ? EvaluacionRag::query()->where('decision_tecnico', 'Aceptada')->count()
            : null;

        $totalUsuarios = $usuario->hasRole('Administrador')
            ? Usuario::query()->count()
            : null;

        $mostrarPanelLateral = $puedeVerMetricasKb
            || $puedeVerMetricasRag
            || $usuario->hasRole('Administrador');

        return view('dashboard', compact(
            'usuario',
            'resumen',
            'ultimosTickets',
            'totalArticulos',
            'ragEvaluadas',
            'ragAceptadas',
            'totalUsuarios',
            'mostrarPanelLateral'
        ));
    }
}
