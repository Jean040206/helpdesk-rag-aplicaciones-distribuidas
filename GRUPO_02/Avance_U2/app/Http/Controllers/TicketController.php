<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignTicketRequest;
use App\Http\Requests\TicketCommentRequest;
use App\Http\Requests\TicketRequest;
use App\Http\Requests\UpdateTicketStatusRequest;
use App\Models\Auditoria;
use App\Models\Categoria;
use App\Models\Estado;
use App\Models\Prioridad;
use App\Models\Servicio;
use App\Models\Ticket;
use App\Models\TicketEvidencia;
use App\Models\TicketHistorial;
use App\Models\TicketSeguimiento;
use App\Models\Usuario;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketController extends Controller
{
    use AuthorizesRequests;

    private const EXTENSIONES_EVIDENCIA = ['jpg', 'jpeg', 'png', 'pdf', 'docx'];

    public function index(Request $request): View
    {
        $usuario = $request->user();

        $texto = trim((string) $request->input('q'));
        if (mb_strlen($texto) > 100) {
            $texto = mb_substr($texto, 0, 100);
        }

        $patron = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $texto);

        $consulta = Ticket::query()
            ->visiblesPara($usuario)
            ->with(['solicitante', 'tecnicoAsignado', 'servicio', 'categoria', 'prioridad', 'estado'])
            ->when($texto !== '', function ($query) use ($patron): void {
                $query->where(function ($subquery) use ($patron): void {
                    $subquery->where('codigo_ticket', 'like', "%{$patron}%")
                        ->orWhere('asunto', 'like', "%{$patron}%");
                });
            })
            ->when($request->filled('id_estado'), fn ($query) => $query->where('id_estado', $request->integer('id_estado')))
            ->when($request->filled('id_prioridad'), fn ($query) => $query->where('id_prioridad', $request->integer('id_prioridad')))
            ->when($request->filled('id_servicio'), fn ($query) => $query->where('id_servicio', $request->integer('id_servicio')));

        $tickets = $consulta
            ->orderByDesc('fecha_registro')
            ->paginate(10)
            ->withQueryString();

        $alcance = match (true) {
            $usuario->hasRole('Solicitante') => 'Tickets que has registrado en la plataforma.',
            $usuario->hasRole('Tecnico') => 'Tickets asignados a tu usuario como técnico de soporte.',
            $usuario->hasRole('Supervisor') => 'Bandeja general de incidencias para revisión y asignación.',
            default => 'Bandeja general de tickets de la plataforma.',
        };

        return view('tickets.index', [
            'tickets' => $tickets,
            'estados' => Estado::orderBy('id_estado')->get(),
            'prioridades' => Prioridad::orderBy('nivel_orden')->get(),
            'servicios' => Servicio::orderBy('nombre')->get(),
            'alcance' => $alcance,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Ticket::class);

        return view('tickets.create', $this->catalogos());
    }

    public function store(TicketRequest $request): RedirectResponse
    {
        $this->authorize('create', Ticket::class);

        $datos = $request->validated();
        $usuario = $request->user();

        $ticket = DB::transaction(function () use ($datos, $request, $usuario): Ticket {
            $ticket = Ticket::create([
                'codigo_ticket' => $this->generarCodigo(),
                'id_solicitante' => $usuario->id_usuario,
                'id_servicio' => $datos['id_servicio'],
                'id_categoria' => $datos['id_categoria'],
                'id_prioridad' => $datos['id_prioridad'],
                'id_estado' => Ticket::ESTADO_ABIERTO,
                'asunto' => $datos['asunto'],
                'descripcion' => $datos['descripcion'],
            ]);

            TicketHistorial::create([
                'id_ticket' => $ticket->id_ticket,
                'id_estado_anterior' => null,
                'id_estado_nuevo' => Ticket::ESTADO_ABIERTO,
                'id_usuario_responsable' => $usuario->id_usuario,
                'observacion' => 'Creación inicial del ticket por el solicitante.',
            ]);

            $this->guardarEvidencias($ticket, $request->file('evidencias') ?? []);

            Auditoria::registrar(
                'Tickets',
                'REGISTRO_TICKET',
                'Se registró el ticket '.$ticket->codigo_ticket.' — '.$ticket->asunto,
                $usuario->id_usuario
            );

            return $ticket;
        });

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Ticket registrado correctamente.');
    }

    public function show(Request $request, Ticket $ticket): View
    {
        $this->authorize('view', $ticket);

        $usuario = $request->user();

        $ticket->load([
            'solicitante',
            'tecnicoAsignado',
            'supervisorAsignador',
            'servicio',
            'categoria',
            'prioridad',
            'estado',
            'evidencias',
            'seguimientos.usuario',
            'historial.estadoAnterior',
            'historial.estadoNuevo',
            'historial.usuarioResponsable',
        ]);

        $estadosDisponibles = Estado::orderBy('id_estado')
            ->get()
            ->filter(fn (Estado $estado) => $usuario->can('updateStatus', [$ticket, $estado]))
            ->values();

        $tecnicos = $usuario->can('assign', $ticket)
            ? Usuario::query()
                ->with('rol')
                ->where('activo', true)
                ->whereHas('rol', fn ($query) => $query->where('nombre', 'Tecnico'))
                ->orderBy('nombre_completo')
                ->get()
            : collect();

        $puedeVerRag = $usuario->can('viewRag', $ticket);
        $ultimaEvaluacionRag = null;
        $ragEvaluacionesTotal = 0;

        if ($puedeVerRag) {
            $ultimaEvaluacionRag = $ticket->evaluacionesRag()
                ->with(['fuentes.articulo', 'tecnicoEvaluador'])
                ->first();

            $ragEvaluacionesTotal = $ticket->evaluacionesRag()->count();
        }

        return view('tickets.show', [
            'ticket' => $ticket,
            'usuario' => $usuario,
            'estadosDisponibles' => $estadosDisponibles,
            'tecnicos' => $tecnicos,
            'prioridades' => Prioridad::orderBy('nivel_orden')->get(),
            'puedeVerRag' => $puedeVerRag,
            'puedeGenerarRag' => $usuario->can('generateRag', $ticket),
            'puedeDecidirRag' => $usuario->can('decideRag', $ticket),
            'ultimaEvaluacionRag' => $ultimaEvaluacionRag,
            'ragEvaluacionesTotal' => $ragEvaluacionesTotal,
            'ragDisponible' => filled(config('services.gemini.key')),
        ]);
    }

    public function edit(Ticket $ticket): View
    {
        $this->authorize('update', $ticket);

        return view('tickets.edit', array_merge(
            ['ticket' => $ticket],
            $this->catalogos()
        ));
    }

    public function update(TicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $datos = $request->validated();

        DB::transaction(function () use ($ticket, $datos, $request): void {
            $ticket->update([
                'id_servicio' => $datos['id_servicio'],
                'id_categoria' => $datos['id_categoria'],
                'id_prioridad' => $datos['id_prioridad'],
                'asunto' => $datos['asunto'],
                'descripcion' => $datos['descripcion'],
            ]);

            $this->guardarEvidencias($ticket, $request->file('evidencias') ?? []);
        });

        Auditoria::registrar(
            'Tickets',
            'ACTUALIZAR_TICKET',
            'Se actualizó el ticket '.$ticket->codigo_ticket.' — '.$ticket->asunto,
            $request->user()->id_usuario
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Ticket actualizado correctamente.');
    }

    public function destroy(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('delete', $ticket);

        $codigo = $ticket->codigo_ticket;
        $ticket->load('evidencias');

        DB::transaction(function () use ($ticket): void {
            foreach ($ticket->evidencias as $evidencia) {
                $ruta = $this->resolverRutaEvidencia($evidencia, $ticket);

                if ($ruta !== null) {
                    Storage::disk('local')->delete($ruta);
                }
            }

            $ticket->delete();
        });

        Auditoria::registrar(
            'Tickets',
            'ELIMINAR_TICKET',
            'Se eliminó el ticket '.$codigo,
            $request->user()->id_usuario
        );

        return redirect()
            ->route('tickets.index')
            ->with('status', 'Ticket eliminado correctamente.');
    }

    public function assign(AssignTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('assign', $ticket);

        $datos = $request->validated();
        $usuario = $request->user();

        $tecnico = Usuario::with('rol')->find($datos['id_tecnico_asignado']);

        if (! $tecnico || ! $tecnico->activo || ! $tecnico->hasRole('Tecnico')) {
            throw ValidationException::withMessages([
                'id_tecnico_asignado' => 'Seleccione un técnico activo con rol Técnico.',
            ]);
        }

        $prioridad = Prioridad::findOrFail($datos['id_prioridad']);
        $estadoAnterior = (int) $ticket->id_estado;
        $estadoNuevo = $estadoAnterior === Ticket::ESTADO_ABIERTO
            ? Ticket::ESTADO_ASIGNADO
            : $estadoAnterior;

        $observacion = trim($datos['observacion'] ?? '');
        $observacion = $observacion !== ''
            ? $observacion
            : 'Asignación de técnico responsable.';

        DB::transaction(function () use (
            $ticket,
            $tecnico,
            $prioridad,
            $usuario,
            $estadoAnterior,
            $estadoNuevo,
            $observacion
        ): void {
            $ticket->update([
                'id_tecnico_asignado' => $tecnico->id_usuario,
                'id_supervisor_asignador' => $usuario->id_usuario,
                'id_prioridad' => $prioridad->id_prioridad,
                'id_estado' => $estadoNuevo,
                'fecha_asignacion' => now(),
            ]);

            if ($estadoAnterior !== $estadoNuevo) {
                TicketHistorial::create([
                    'id_ticket' => $ticket->id_ticket,
                    'id_estado_anterior' => $estadoAnterior,
                    'id_estado_nuevo' => $estadoNuevo,
                    'id_usuario_responsable' => $usuario->id_usuario,
                    'observacion' => $observacion,
                ]);
            } else {
                TicketSeguimiento::create([
                    'id_ticket' => $ticket->id_ticket,
                    'id_usuario' => $usuario->id_usuario,
                    'comentario' => 'Reasignación de técnico: '.$tecnico->nombre_completo
                        .' (prioridad '.$prioridad->nombre.').',
                    'tipo_registro' => 'Comentario',
                ]);
            }
        });

        Auditoria::registrar(
            'Tickets',
            'ASIGNAR_TECNICO',
            'Ticket '.$ticket->codigo_ticket.' asignado a '.$tecnico->nombre_completo
                .' con prioridad '.$prioridad->nombre.'.',
            $usuario->id_usuario
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Ticket asignado correctamente.');
    }

    public function updateStatus(UpdateTicketStatusRequest $request, Ticket $ticket): RedirectResponse
    {
        $datos = $request->validated();
        $nuevoEstado = Estado::findOrFail($datos['id_estado']);

        $this->authorize('updateStatus', [$ticket, $nuevoEstado]);

        $usuario = $request->user();
        $ticket->loadMissing('estado');

        $estadoAnterior = (int) $ticket->id_estado;
        $nombreEstadoAnterior = $ticket->estado->nombre;
        $diagnosticoAnterior = $ticket->diagnostico_tecnico;
        $solucionAnterior = $ticket->solucion_aplicada;

        $diagnostico = trim($datos['diagnostico_tecnico'] ?? '');
        $diagnostico = $diagnostico !== '' ? $diagnostico : $diagnosticoAnterior;

        $solucion = trim($datos['solucion_aplicada'] ?? '');
        $solucion = $solucion !== '' ? $solucion : $solucionAnterior;

        if ((int) $nuevoEstado->id_estado === Ticket::ESTADO_RESUELTO && blank($solucion)) {
            throw ValidationException::withMessages([
                'solucion_aplicada' => 'Registre la solución aplicada antes de marcar el ticket como Resuelto.',
            ]);
        }

        $observacion = trim($datos['observacion'] ?? '');
        $observacion = $observacion !== ''
            ? $observacion
            : "Cambio de estado: {$nombreEstadoAnterior} → {$nuevoEstado->nombre}.";

        DB::transaction(function () use (
            $ticket,
            $nuevoEstado,
            $usuario,
            $estadoAnterior,
            $diagnostico,
            $diagnosticoAnterior,
            $solucion,
            $solucionAnterior,
            $observacion
        ): void {
            $ticket->update([
                'id_estado' => $nuevoEstado->id_estado,
                'diagnostico_tecnico' => $diagnostico,
                'solucion_aplicada' => $solucion,
                'fecha_resolucion' => in_array((int) $nuevoEstado->id_estado, [
                    Ticket::ESTADO_RESUELTO,
                    Ticket::ESTADO_CERRADO,
                ], true)
                    ? ($ticket->fecha_resolucion ?? now())
                    : null,
            ]);

            TicketHistorial::create([
                'id_ticket' => $ticket->id_ticket,
                'id_estado_anterior' => $estadoAnterior,
                'id_estado_nuevo' => $nuevoEstado->id_estado,
                'id_usuario_responsable' => $usuario->id_usuario,
                'observacion' => $observacion,
            ]);

            if ($diagnostico && $diagnostico !== $diagnosticoAnterior) {
                TicketSeguimiento::create([
                    'id_ticket' => $ticket->id_ticket,
                    'id_usuario' => $usuario->id_usuario,
                    'comentario' => $diagnostico,
                    'tipo_registro' => 'Diagnostico',
                ]);
            }

            if ($solucion && $solucion !== $solucionAnterior) {
                TicketSeguimiento::create([
                    'id_ticket' => $ticket->id_ticket,
                    'id_usuario' => $usuario->id_usuario,
                    'comentario' => $solucion,
                    'tipo_registro' => 'Solucion',
                ]);
            }
        });

        Auditoria::registrar(
            'Tickets',
            'CAMBIO_ESTADO',
            'Ticket '.$ticket->codigo_ticket.': '.$nombreEstadoAnterior.' → '.$nuevoEstado->nombre.'.',
            $usuario->id_usuario
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Estado actualizado correctamente.');
    }

    public function comment(TicketCommentRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('comment', $ticket);

        $usuario = $request->user();
        $datos = $request->validated();

        TicketSeguimiento::create([
            'id_ticket' => $ticket->id_ticket,
            'id_usuario' => $usuario->id_usuario,
            'comentario' => $datos['comentario'],
            'tipo_registro' => 'Comentario',
        ]);

        Auditoria::registrar(
            'Tickets',
            'AGREGAR_SEGUIMIENTO',
            'Se agregó un seguimiento al ticket '.$ticket->codigo_ticket.'.',
            $usuario->id_usuario
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Comentario registrado correctamente.');
    }

    public function downloadEvidence(Ticket $ticket, TicketEvidencia $evidencia): StreamedResponse
    {
        $this->authorize('view', $ticket);

        abort_unless(
            (int) $evidencia->id_ticket === (int) $ticket->id_ticket,
            404
        );

        $ruta = $this->resolverRutaEvidencia($evidencia, $ticket);

        abort_if(
            $ruta === null,
            404,
            'La evidencia solicitada no está disponible.'
        );

        $nombreDescarga = $this->nombreArchivoSeguro(
            $evidencia->nombre_original,
            'evidencia-'.$evidencia->id_evidencia.'.'.($evidencia->extension ?: 'bin')
        );

        return Storage::disk('local')->download($ruta, $nombreDescarga);
    }

    private function catalogos(): array
    {
        return [
            'servicios' => Servicio::query()
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(),
            'categorias' => Categoria::query()
                ->orderBy('nombre')
                ->get(),
            'prioridades' => Prioridad::query()
                ->orderBy('nivel_orden')
                ->get(),
            'prioridadDefault' => Prioridad::query()
                ->where('nombre', 'Media')
                ->value('id_prioridad'),
        ];
    }

    private function generarCodigo(): string
    {
        $prefijo = 'TKT-'.now()->year.'-';

        $ultimoCodigo = Ticket::query()
            ->where('codigo_ticket', 'like', $prefijo.'%')
            ->orderByDesc('id_ticket')
            ->value('codigo_ticket');

        $secuencia = $ultimoCodigo
            ? ((int) Str::afterLast($ultimoCodigo, '-')) + 1
            : 1;

        return sprintf('%s%04d', $prefijo, $secuencia);
    }

    private function guardarEvidencias(Ticket $ticket, array $archivos): void
    {
        foreach ($archivos as $archivo) {
            if (! $archivo) {
                continue;
            }

            $ruta = $archivo->store('ticket_evidencias/'.$ticket->id_ticket, 'local');

            if (! $ruta) {
                throw ValidationException::withMessages([
                    'evidencias' => 'No se pudo guardar una de las evidencias adjuntas.',
                ]);
            }

            $extension = strtolower((string) $archivo->getClientOriginalExtension());

            if (! in_array($extension, self::EXTENSIONES_EVIDENCIA, true)) {
                $extension = strtolower((string) $archivo->extension());
            }

            if (! in_array($extension, self::EXTENSIONES_EVIDENCIA, true)) {
                Storage::disk('local')->delete($ruta);

                throw ValidationException::withMessages([
                    'evidencias' => 'Una de las evidencias tiene un formato no permitido.',
                ]);
            }

            $ticket->evidencias()->create([
                'nombre_original' => $this->nombreArchivoSeguro(
                    $archivo->getClientOriginalName(),
                    'evidencia-'.Str::random(12).'.'.$extension
                ),
                'ruta_archivo' => $ruta,
                'extension' => $extension,
                'tamano_kb' => (int) ceil($archivo->getSize() / 1024),
            ]);
        }
    }

    private function nombreArchivoSeguro(?string $nombre, string $fallback): string
    {
        $nombre = str_replace('\\', '/', (string) $nombre);
        $nombre = basename($nombre);
        $nombre = preg_replace('/[\x00-\x1F\x7F]/u', '', $nombre) ?? '';
        $nombre = ltrim(trim($nombre), '.');

        if ($nombre === '') {
            $nombre = $fallback;
        }

        return Str::limit($nombre, 180, '');
    }

    private function resolverRutaEvidencia(TicketEvidencia $evidencia, Ticket $ticket): ?string
    {
        $ruta = str_replace('\\', '/', trim((string) $evidencia->ruta_archivo));
        $prefijo = 'ticket_evidencias/'.$ticket->id_ticket.'/';

        if ($ruta === '' || str_contains($ruta, "\0") || str_starts_with($ruta, '/')) {
            return null;
        }

        if (preg_match('#(^|/)\.\.?(/|$)#', $ruta) === 1) {
            return null;
        }

        if (! str_starts_with($ruta, $prefijo)) {
            return null;
        }

        $disco = Storage::disk('local');

        if (! $disco->exists($ruta)) {
            return null;
        }

        $raiz = realpath($disco->path(''));
        $rutaReal = realpath($disco->path($ruta));

        if ($raiz === false || $rutaReal === false) {
            return null;
        }

        $raiz = rtrim($raiz, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        if (! str_starts_with($rutaReal, $raiz)) {
            return null;
        }

        return $ruta;
    }
}
