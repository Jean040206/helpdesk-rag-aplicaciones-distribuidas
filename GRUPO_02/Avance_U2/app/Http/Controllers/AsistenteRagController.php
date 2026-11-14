<?php

namespace App\Http\Controllers;

use App\Http\Requests\RagDecisionRequest;
use App\Models\Auditoria;
use App\Models\EvaluacionRag;
use App\Models\Ticket;
use App\Services\Rag\AsistenteRagService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AsistenteRagController extends Controller
{
    use AuthorizesRequests;

    public function generate(
        Request $request,
        Ticket $ticket,
        AsistenteRagService $asistente
    ): RedirectResponse {
        $this->authorize('generateRag', $ticket);

        $evaluacion = $asistente->generar($ticket, $request->user());

        if ($evaluacion->estado === 'exitosa') {
            Auditoria::registrar(
                'Asistente RAG',
                'GENERAR_RECOMENDACION_RAG',
                'Se generó una recomendación RAG para el ticket '.$ticket->codigo_ticket
                    .' (evaluación #'.$evaluacion->id_evaluacion_rag.').',
                $request->user()->id_usuario
            );

            return redirect()
                ->route('tickets.show', $ticket)
                ->with('status', 'Recomendación RAG generada correctamente.')
                ->withFragment('rag');
        }

        Auditoria::registrar(
            'Asistente RAG',
            'ERROR_RECOMENDACION_RAG',
            'Falló la generación RAG del ticket '.$ticket->codigo_ticket.': '
                .Str::limit((string) $evaluacion->error_mensaje, 180, ''),
            $request->user()->id_usuario
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with(
                'error',
                'No se pudo generar la recomendación: '
                    .Str::limit((string) $evaluacion->error_mensaje, 300, '')
            )
            ->withFragment('rag');
    }

    public function decide(
        RagDecisionRequest $request,
        Ticket $ticket,
        EvaluacionRag $evaluacion
    ): RedirectResponse {
        $this->authorize('decideRag', $ticket);

        abort_unless(
            (int) $evaluacion->id_ticket === (int) $ticket->id_ticket,
            404
        );

        if ($evaluacion->estado !== 'exitosa') {
            return redirect()
                ->route('tickets.show', $ticket)
                ->with('error', 'Solo se pueden decidir recomendaciones generadas correctamente.')
                ->withFragment('rag');
        }

        if ($evaluacion->decision_tecnico !== 'Pendiente') {
            return redirect()
                ->route('tickets.show', $ticket)
                ->with('error', 'Esta recomendación ya tiene una decisión registrada.')
                ->withFragment('rag');
        }

        $datos = $request->validated();
        $decision = (string) $datos['decision_tecnico'];
        $solucion = trim((string) ($datos['solucion_final_tecnico'] ?? ''));

        if ($decision === 'Aceptada' && $solucion === '') {
            $solucion = (string) $evaluacion->solucion_recomendada;
        }

        $comentario = trim((string) ($datos['comentario_tecnico'] ?? ''));

        $evaluacion->update([
            'decision_tecnico' => $decision,
            'solucion_final_tecnico' => $solucion !== '' ? $solucion : null,
            'comentario_tecnico' => $comentario !== '' ? $comentario : null,
            'id_tecnico_evaluador' => $request->user()->id_usuario,
            'fecha_decision' => now(),
        ]);

        Auditoria::registrar(
            'Asistente RAG',
            'DECISION_RAG',
            'Evaluación #'.$evaluacion->id_evaluacion_rag.' del ticket '
                .$ticket->codigo_ticket.': '.$decision.'.',
            $request->user()->id_usuario
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Decisión registrada correctamente.')
            ->withFragment('rag');
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', EvaluacionRag::class);

        $texto = Str::limit(trim((string) $request->input('q')), 100, '');
        $patron = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $texto);
        $estado = (string) $request->input('estado');
        $decision = (string) $request->input('decision');

        $evaluaciones = EvaluacionRag::query()
            ->with(['ticket', 'tecnicoEvaluador'])
            ->withCount('fuentes')
            ->when($texto !== '', function ($query) use ($patron): void {
                $query->where(function ($subquery) use ($patron): void {
                    $subquery
                        ->whereHas('ticket', function ($ticketQuery) use ($patron): void {
                            $ticketQuery
                                ->where('codigo_ticket', 'like', "%{$patron}%")
                                ->orWhere('asunto', 'like', "%{$patron}%");
                        })
                        ->orWhere('modelo_utilizado', 'like', "%{$patron}%");
                });
            })
            ->when(
                in_array($estado, ['exitosa', 'error'], true),
                fn ($query) => $query->where('estado', $estado)
            )
            ->when(
                in_array($decision, ['Pendiente', 'Aceptada', 'Modificada', 'Descartada'], true),
                fn ($query) => $query->where('decision_tecnico', $decision)
            )
            ->orderByDesc('fecha_evento')
            ->orderByDesc('id_evaluacion_rag')
            ->paginate(15)
            ->withQueryString();

        return view('rag.index', [
            'evaluaciones' => $evaluaciones,
            'texto' => $texto,
            'estados' => ['exitosa', 'error'],
            'decisiones' => ['Pendiente', 'Aceptada', 'Modificada', 'Descartada'],
        ]);
    }

    public function show(EvaluacionRag $evaluacion): View
    {
        $this->authorize('view', $evaluacion);

        $evaluacion->load(['ticket', 'tecnicoEvaluador', 'fuentes.articulo']);

        return view('rag.show', ['evaluacion' => $evaluacion]);
    }
}
