<?php

namespace Database\Seeders;

use App\Models\AjusteCaja;
use App\Models\Alumno;
use App\Models\Beca;
use App\Models\Bitacora;
use App\Models\CalendarioAcademico;
use App\Models\CalendarioMateria;
use App\Models\CalendarioSesion;
use App\Models\Cargo;
use App\Models\CicloEscolar;
use App\Models\ConceptoPago;
use App\Models\Convenio;
use App\Models\CorteCaja;
use App\Models\CursoAsistencia;
use App\Models\CursoEducacionContinua;
use App\Models\CursoInscrito;
use App\Models\CursoSesion;
use App\Models\Docente;
use App\Models\DocumentoAlumno;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\ParcialidadConvenio;
use App\Models\Pago;
use App\Models\Programa;
use App\Models\Prospecto;
use App\Models\RequisitoDocumental;
use App\Models\Rol;
use App\Models\Seguimiento;
use App\Models\SolicitudPagoDocente;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatosDemoIntegralSeeder extends Seeder
{
    private ?Usuario $admin = null;
    private ?Usuario $academica = null;
    private ?Usuario $cadmin = null;
    private ?Usuario $recepcion = null;
    private ?Usuario $finanzas = null;
    private ?Usuario $sistemas = null;
    private ?Usuario $rrpp = null;

    public function run(): void
    {
        $this->usuariosBase();
        $this->reforzarCatalogosBase();
        $this->sembrarProspectosYSeguimientos();
        $this->sembrarExpedientesDocumentales();
        $this->sembrarFinanzasCajaPagosConvenios();
        $this->sembrarCalendariosAcademicos();
        $this->sembrarEducacionContinua();
        $this->sembrarSolicitudesPagoDocenteDemo();
        $this->sembrarBitacoraDemo();

        $this->command?->info('✅ Datos demo integrales de IDEJ-SYS cargados correctamente.');
    }

    private function usuariosBase(): void
    {
        $this->admin = Usuario::whereHas('rol', fn ($q) => $q->where('clave', Rol::ADMIN))->first() ?? Usuario::first();
        $this->academica = Usuario::whereHas('rol', fn ($q) => $q->where('clave', Rol::ACADEMICA))->first() ?? $this->admin;
        $this->cadmin = Usuario::whereHas('rol', fn ($q) => $q->where('clave', Rol::CADMIN))->first() ?? $this->admin;
        $this->recepcion = Usuario::whereHas('rol', fn ($q) => $q->where('clave', Rol::RECEPCION))->first() ?? $this->admin;
        $this->finanzas = Usuario::whereHas('rol', fn ($q) => $q->where('clave', Rol::FINANZAS))->first() ?? $this->admin;
        $this->sistemas = Usuario::whereHas('rol', fn ($q) => $q->where('clave', Rol::SISTEMAS))->first() ?? $this->admin;
        $this->rrpp = Usuario::whereHas('rol', fn ($q) => $q->where('clave', Rol::RRPP))->first() ?? $this->admin;
    }

    private function reforzarCatalogosBase(): void
    {
        $programas = [
            ['nombre' => 'Posdoctorado en Derecho', 'nivel' => 'Posdoctorado'],
            ['nombre' => 'Educación Continua IDEJ', 'nivel' => 'Educación continua'],
        ];

        foreach ($programas as $programa) {
            Programa::firstOrCreate(['nombre' => $programa['nombre']], $programa);
        }

        $ciclo = CicloEscolar::firstOrCreate(
            ['nombre' => '2026-A'],
            [
                'tipo_periodo' => 'Semestral',
                'fecha_inicio' => '2026-01-15',
                'fecha_fin' => '2026-08-31',
                'fecha_inicio_inscripcion' => '2026-01-01',
                'fecha_fin_inscripcion' => '2026-03-15',
                'fecha_inicio_clases' => '2026-03-01',
                'fecha_fin_clases' => '2026-08-31',
                'activo' => true,
            ]
        );

        $lic = Programa::where('nombre', 'Licenciatura en Derecho')->first();
        $maestria = Programa::where('nombre', 'Maestría en Derecho Constitucional')->first();
        $doctorado = Programa::where('nombre', 'Doctorado en Derecho Penal')->first();

        $grupos = [
            ['nombre' => 'Licenciatura Sabatino 7mo', 'programa_id' => $lic?->id, 'turno' => 'Sabatino', 'aula' => 'Aula 3', 'semestre_o_cuatrimestre' => 7],
            ['nombre' => 'Licenciatura Vespertino 3ro', 'programa_id' => $lic?->id, 'turno' => 'Vespertino', 'aula' => 'Aula 2', 'semestre_o_cuatrimestre' => 3],
            ['nombre' => 'Maestría 4 - Grupo 2-A', 'programa_id' => $maestria?->id, 'turno' => 'Mixto', 'aula' => 'Aula Virtual / Aula 1', 'semestre_o_cuatrimestre' => 4],
            ['nombre' => 'Doctorado 5', 'programa_id' => $doctorado?->id, 'turno' => 'Mixto', 'aula' => 'Aula 4', 'semestre_o_cuatrimestre' => 5],
        ];

        foreach ($grupos as $grupo) {
            if (! $grupo['programa_id']) {
                continue;
            }

            Grupo::firstOrCreate(
                ['nombre' => $grupo['nombre'], 'programa_id' => $grupo['programa_id']],
                [
                    'ciclo_escolar_id' => $ciclo->id,
                    'docente_id' => Docente::first()?->id,
                    'semestre_o_cuatrimestre' => $grupo['semestre_o_cuatrimestre'],
                    'turno' => $grupo['turno'],
                    'aula' => $grupo['aula'],
                    'cupo_maximo' => 35,
                ]
            );
        }

        $materias = [
            ['clave' => 'IDEJ-MED-401', 'nombre' => 'Contrato Médico y Responsabilidad Civil Profesional', 'nivel' => 'Maestría', 'programa_id' => $maestria?->id, 'semestre_o_cuatrimestre' => 4],
            ['clave' => 'IDEJ-MED-402', 'nombre' => 'El Expediente Clínico', 'nivel' => 'Maestría', 'programa_id' => $maestria?->id, 'semestre_o_cuatrimestre' => 4],
            ['clave' => 'IDEJ-DOC-501', 'nombre' => 'Jurisprudencia Teorética', 'nivel' => 'Doctorado', 'programa_id' => $doctorado?->id, 'semestre_o_cuatrimestre' => 5],
            ['clave' => 'IDEJ-DOC-502', 'nombre' => 'Taller de Tesis II', 'nivel' => 'Doctorado', 'programa_id' => $doctorado?->id, 'semestre_o_cuatrimestre' => 5],
            ['clave' => 'IDEJ-LIC-701', 'nombre' => 'Derecho Comparado', 'nivel' => 'Licenciatura', 'programa_id' => $lic?->id, 'semestre_o_cuatrimestre' => 7],
            ['clave' => 'IDEJ-LIC-302', 'nombre' => 'Inglés Jurídico II', 'nivel' => 'Licenciatura', 'programa_id' => $lic?->id, 'semestre_o_cuatrimestre' => 3],
        ];

        foreach ($materias as $materia) {
            if (! $materia['programa_id']) {
                continue;
            }

            Materia::updateOrCreate(
                ['clave' => $materia['clave']],
                [
                    ...$materia,
                    'creditos' => 8,
                    'horas_teoricas' => 4,
                    'horas_practicas' => 1,
                    'estatus' => Materia::ESTATUS_ACTIVA,
                    'descripcion' => 'Materia demo para pruebas del planeador académico IDEJ.',
                ]
            );
        }
    }

    private function sembrarProspectosYSeguimientos(): void
    {
        $programas = Programa::pluck('id', 'nombre');
        $prospectos = [
            ['nombre_completo' => 'Alejandra Ríos Contreras', 'correo' => 'alejandra.rios.demo@example.com', 'telefono' => '3310000001', 'whatsapp' => '3310000001', 'programa_id' => $programas['Maestría en Derecho Constitucional'] ?? null, 'nivel_interes' => 'Maestría', 'medio_contacto' => 'Facebook', 'origen' => 'Campaña Enero 2026', 'estatus' => Prospecto::ESTATUS_EN_SEGUIMIENTO, 'prioridad' => Prospecto::PRIORIDAD_ALTA, 'fecha_proximo_contacto' => now()->addDay()],
            ['nombre_completo' => 'Héctor Campos Duarte', 'correo' => 'hector.campos.demo@example.com', 'telefono' => '3310000002', 'whatsapp' => '3310000002', 'programa_id' => $programas['Licenciatura en Derecho'] ?? null, 'nivel_interes' => 'Licenciatura', 'medio_contacto' => 'WhatsApp', 'origen' => 'Referido', 'estatus' => Prospecto::ESTATUS_INTERESADO, 'prioridad' => Prospecto::PRIORIDAD_NORMAL, 'fecha_proximo_contacto' => now()->addDays(3)],
            ['nombre_completo' => 'Paola Hernández Mata', 'correo' => 'paola.hernandez.demo@example.com', 'telefono' => '3310000003', 'whatsapp' => '3310000003', 'programa_id' => $programas['Doctorado en Derecho Penal'] ?? null, 'nivel_interes' => 'Doctorado', 'medio_contacto' => 'Instagram', 'origen' => 'Historia promocional', 'estatus' => Prospecto::ESTATUS_CONTACTADO, 'prioridad' => Prospecto::PRIORIDAD_URGENTE, 'fecha_proximo_contacto' => now()->subDay()],
            ['nombre_completo' => 'Martín Zavala Robles', 'correo' => 'martin.zavala.demo@example.com', 'telefono' => '3310000004', 'whatsapp' => '3310000004', 'programa_id' => $programas['Educación Continua IDEJ'] ?? null, 'nivel_interes' => 'Educación continua', 'medio_contacto' => 'Página web', 'origen' => 'Formulario web', 'estatus' => Prospecto::ESTATUS_NUEVO, 'prioridad' => Prospecto::PRIORIDAD_NORMAL, 'fecha_proximo_contacto' => now()->addDays(5)],
        ];

        foreach ($prospectos as $data) {
            $prospecto = Prospecto::updateOrCreate(
                ['correo' => $data['correo']],
                [
                    ...$data,
                    'asesor_id' => $this->rrpp?->id,
                    'fecha_contacto' => now()->subDays(2),
                    'observaciones' => 'Prospecto demo para probar seguimiento, filtros y conversión.',
                ]
            );

            Seguimiento::updateOrCreate(
                ['prospecto_id' => $prospecto->id, 'asunto' => 'Primer contacto de seguimiento'],
                [
                    'usuario_id' => $this->rrpp?->id,
                    'area' => 'Relaciones Públicas',
                    'tipo' => Seguimiento::TIPO_WHATSAPP,
                    'prioridad' => $prospecto->prioridad,
                    'estatus' => Seguimiento::ESTATUS_ABIERTO,
                    'descripcion' => 'Se envió información del programa y costos. Pendiente confirmar documentación.',
                    'fecha_contacto' => now()->subDay(),
                    'fecha_proximo_contacto' => $prospecto->fecha_proximo_contacto,
                ]
            );
        }

        Alumno::orderBy('id')->take(6)->get()->each(function (Alumno $alumno, int $i) {
            Seguimiento::updateOrCreate(
                ['alumno_id' => $alumno->id, 'asunto' => 'Seguimiento administrativo demo '.$alumno->matricula],
                [
                    'usuario_id' => $this->recepcion?->id,
                    'area' => $i % 2 === 0 ? 'Recepción' : 'Coordinación Administrativa',
                    'tipo' => $i % 2 === 0 ? Seguimiento::TIPO_LLAMADA : Seguimiento::TIPO_ACUERDO_PAGO,
                    'prioridad' => $i % 3 === 0 ? Seguimiento::PRIORIDAD_ALTA : Seguimiento::PRIORIDAD_NORMAL,
                    'estatus' => $i % 4 === 0 ? Seguimiento::ESTATUS_EN_PROCESO : Seguimiento::ESTATUS_ABIERTO,
                    'descripcion' => 'Registro demo para probar historial del expediente del alumno.',
                    'resultado' => $i % 2 === 0 ? 'Alumno solicita información de adeudo.' : null,
                    'fecha_contacto' => now()->subDays($i + 1),
                    'fecha_proximo_contacto' => now()->addDays($i + 1),
                ]
            );
        });
    }

    private function sembrarExpedientesDocumentales(): void
    {
        $requisitos = RequisitoDocumental::orderBy('orden')->take(6)->get();

        if ($requisitos->isEmpty()) {
            return;
        }

        Alumno::orderBy('id')->take(8)->get()->each(function (Alumno $alumno, int $i) use ($requisitos) {
            foreach ($requisitos->take(4) as $idx => $requisito) {
                $estatus = match (($i + $idx) % 4) {
                    0 => DocumentoAlumno::ESTATUS_ACEPTADO,
                    1 => DocumentoAlumno::ESTATUS_ENTREGADO,
                    2 => DocumentoAlumno::ESTATUS_EN_REVISION,
                    default => DocumentoAlumno::ESTATUS_PENDIENTE,
                };

                DocumentoAlumno::updateOrCreate(
                    ['alumno_id' => $alumno->id, 'requisito_documental_id' => $requisito->id],
                    [
                        'usuario_subio_id' => in_array($estatus, [DocumentoAlumno::ESTATUS_ENTREGADO, DocumentoAlumno::ESTATUS_EN_REVISION, DocumentoAlumno::ESTATUS_ACEPTADO], true) ? $this->recepcion?->id : null,
                        'usuario_reviso_id' => $estatus === DocumentoAlumno::ESTATUS_ACEPTADO ? $this->academica?->id : null,
                        'tipo_documento' => $requisito->tipo_documento,
                        'nombre_original' => $estatus === DocumentoAlumno::ESTATUS_PENDIENTE ? null : Str::slug($requisito->tipo_documento).'_'.$alumno->matricula.'.pdf',
                        'archivo_path' => $estatus === DocumentoAlumno::ESTATUS_PENDIENTE ? null : 'demo/documentos/'.$alumno->matricula.'/'.Str::slug($requisito->tipo_documento).'.pdf',
                        'mime_type' => $estatus === DocumentoAlumno::ESTATUS_PENDIENTE ? null : 'application/pdf',
                        'extension' => $estatus === DocumentoAlumno::ESTATUS_PENDIENTE ? null : 'pdf',
                        'tamano_bytes' => $estatus === DocumentoAlumno::ESTATUS_PENDIENTE ? null : 245760,
                        'estatus' => $estatus,
                        'fecha_documento' => now()->subMonths(2)->toDateString(),
                        'fecha_entrega' => $estatus === DocumentoAlumno::ESTATUS_PENDIENTE ? null : now()->subDays(10 - $idx),
                        'fecha_revision' => $estatus === DocumentoAlumno::ESTATUS_ACEPTADO ? now()->subDays(5) : null,
                        'observaciones' => 'Documento demo generado para pruebas de expediente documental.',
                    ]
                );
            }
        });
    }

    private function sembrarFinanzasCajaPagosConvenios(): void
    {
        $conceptoColegiatura = ConceptoPago::where('nombre', 'Colegiatura Mensual')->first() ?? ConceptoPago::first();
        $conceptoInscripcion = ConceptoPago::where('nombre', 'Inscripción')->first() ?? $conceptoColegiatura;
        $alumnos = Alumno::orderBy('id')->take(10)->get();

        if (! $conceptoColegiatura || $alumnos->isEmpty()) {
            return;
        }

        $corteAbierto = CorteCaja::firstOrCreate(
            ['usuario_id' => $this->recepcion?->id, 'estatus' => CorteCaja::ESTATUS_ABIERTA],
            [
                'fecha_apertura' => now()->startOfDay()->addHours(8),
                'saldo_inicial' => 500,
                'observaciones_apertura' => 'Caja demo abierta para pruebas de pagos.',
            ]
        );

        $corteCerrado = CorteCaja::firstOrCreate(
            ['usuario_id' => $this->finanzas?->id, 'fecha_apertura' => now()->subDay()->startOfDay()->addHours(8)],
            [
                'fecha_cierre' => now()->subDay()->endOfDay()->subHours(2),
                'saldo_inicial' => 0,
                'estatus' => CorteCaja::ESTATUS_CERRADA,
                'observaciones_apertura' => 'Corte demo del día anterior.',
                'observaciones_cierre' => 'Corte cerrado sin diferencia relevante.',
            ]
        );

        foreach ($alumnos as $index => $alumno) {
            $beca = $alumno->becas()->where('estatus', Beca::ESTATUS_ACTIVA)->first();
            $montoOriginal = $index % 2 === 0 ? (float) $conceptoColegiatura->monto_base : (float) $conceptoInscripcion->monto_base;
            $becaPorcentaje = $conceptoColegiatura->es_becable && $beca ? (int) $beca->porcentaje : 0;
            $descuento = round($montoOriginal * ($becaPorcentaje / 100), 2);
            $adeudo = round($montoOriginal - $descuento, 2);

            Cargo::updateOrCreate(
                ['alumno_id' => $alumno->id, 'descripcion_cargo' => 'Colegiatura demo julio 2026'],
                [
                    'concepto_id' => $conceptoColegiatura->id,
                    'beca_id' => $beca?->id,
                    'monto_original' => $montoOriginal,
                    'beca_porcentaje_aplicado' => $becaPorcentaje,
                    'beca_monto_aplicado' => $descuento,
                    'monto_adeudo' => $index < 3 ? 0 : $adeudo,
                    'fecha_vencimiento' => '2026-07-15',
                    'estatus' => $index < 3 ? 'Pagado' : 'Pendiente',
                    'moratorio_aplicado' => false,
                ]
            );
        }

        $cargoPagado = Cargo::where('descripcion_cargo', 'Colegiatura demo julio 2026')->where('estatus', 'Pagado')->first();

        if ($cargoPagado) {
            $pago = Pago::firstOrCreate(
                ['folio_recibo' => 'IDEJ-DEMO-202605-000001'],
                [
                    'alumno_id' => $cargoPagado->alumno_id,
                    'usuario_id' => $this->recepcion?->id,
                    'corte_caja_id' => $corteAbierto->id,
                    'metodo_pago' => 'Efectivo',
                    'monto_total_pagado' => $cargoPagado->monto_original - $cargoPagado->beca_monto_aplicado,
                    'saldo_a_favor_generado' => 0,
                    'estatus' => 'Activo',
                    'fecha_pago' => now()->toDateString(),
                    'recibo_uuid' => (string) Str::uuid(),
                    'recibo_emitido_at' => now(),
                    'recibo_version' => 1,
                    'observaciones' => 'Pago demo activo dentro de caja abierta.',
                ]
            );

            DB::table('cargo_pago')->updateOrInsert(
                ['cargo_id' => $cargoPagado->id, 'pago_id' => $pago->id],
                ['monto_aplicado' => $pago->monto_total_pagado, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $cargoConvenio = Cargo::where('descripcion_cargo', 'Colegiatura demo julio 2026')->where('estatus', 'Pendiente')->first();

        if ($cargoConvenio) {
            $convenio = Convenio::firstOrCreate(
                ['alumno_id' => $cargoConvenio->alumno_id, 'descripcion' => 'Convenio demo de regularización 2026'],
                [
                    'cargo_original_id' => $cargoConvenio->id,
                    'total_reestructurado' => $cargoConvenio->monto_adeudo,
                    'numero_parcialidades' => 3,
                    'estatus' => 'Activo',
                ]
            );

            DB::table('cargo_convenio')->updateOrInsert(
                ['cargo_id' => $cargoConvenio->id],
                [
                    'convenio_id' => $convenio->id,
                    'monto_original' => $cargoConvenio->monto_original,
                    'monto_adeudo_original' => $cargoConvenio->monto_adeudo,
                    'estatus_original' => 'Pendiente',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $cargoConvenio->update(['estatus' => 'En Convenio']);
            $montoParcialidad = round(((float) $convenio->total_reestructurado) / 3, 2);

            for ($i = 1; $i <= 3; $i++) {
                ParcialidadConvenio::updateOrCreate(
                    ['convenio_id' => $convenio->id, 'fecha_vencimiento' => Carbon::parse('2026-07-30')->addMonths($i - 1)->toDateString()],
                    [
                        'monto_parcialidad' => $i === 3 ? round((float) $convenio->total_reestructurado - ($montoParcialidad * 2), 2) : $montoParcialidad,
                        'monto_adeudo' => $i === 1 ? 0 : ($i === 3 ? round((float) $convenio->total_reestructurado - ($montoParcialidad * 2), 2) : $montoParcialidad),
                        'estatus' => $i === 1 ? 'Pagado' : 'Pendiente',
                    ]
                );
            }
        }

        $cargoCancelado = Cargo::where('descripcion_cargo', 'Colegiatura demo julio 2026')->where('estatus', 'Pagado')->skip(1)->first();

        if ($cargoCancelado) {
            $pagoCancelado = Pago::firstOrCreate(
                ['folio_recibo' => 'IDEJ-DEMO-202605-000002'],
                [
                    'alumno_id' => $cargoCancelado->alumno_id,
                    'usuario_id' => $this->finanzas?->id,
                    'cancelado_por_id' => $this->cadmin?->id,
                    'corte_caja_id' => $corteCerrado->id,
                    'metodo_pago' => 'Transferencia',
                    'monto_total_pagado' => $cargoCancelado->monto_original - $cargoCancelado->beca_monto_aplicado,
                    'saldo_a_favor_generado' => 0,
                    'estatus' => 'Cancelado',
                    'fecha_pago' => now()->subDay()->toDateString(),
                    'fecha_cancelacion' => now(),
                    'folio_recibo' => 'IDEJ-DEMO-202605-000002',
                    'recibo_uuid' => (string) Str::uuid(),
                    'recibo_emitido_at' => now()->subDay(),
                    'recibo_version' => 1,
                    'referencia_bancaria' => 'TR-DEMO-0002',
                    'motivo_cancelacion' => 'Cancelación demo para probar ajuste administrativo en caja cerrada.',
                    'observaciones' => 'Pago cancelado demo.',
                ]
            );

            AjusteCaja::firstOrCreate(
                ['pago_id' => $pagoCancelado->id, 'tipo' => AjusteCaja::TIPO_CANCELACION_PAGO_CERRADO],
                [
                    'corte_caja_id' => $corteCerrado->id,
                    'alumno_id' => $cargoCancelado->alumno_id,
                    'usuario_id' => $this->cadmin?->id,
                    'metodo_pago' => 'Transferencia',
                    'monto_ajuste' => -1 * (float) $pagoCancelado->monto_total_pagado,
                    'estatus' => AjusteCaja::ESTATUS_APLICADO,
                    'motivo' => 'Ajuste demo por cancelación posterior al cierre de caja.',
                    'fecha_aplicacion' => now(),
                ]
            );
        }

        foreach ([$corteAbierto, $corteCerrado] as $corte) {
            $totales = $corte->calcularTotalesSistema();
            $corte->update([
                ...$totales,
                'efectivo_reportado' => $corte->estatus === CorteCaja::ESTATUS_CERRADA ? $totales['efectivo_sistema'] : null,
                'transferencia_reportado' => $corte->estatus === CorteCaja::ESTATUS_CERRADA ? $totales['transferencia_sistema'] : null,
                'tarjeta_reportado' => $corte->estatus === CorteCaja::ESTATUS_CERRADA ? $totales['tarjeta_sistema'] : null,
                'total_reportado' => $corte->estatus === CorteCaja::ESTATUS_CERRADA ? $totales['total_sistema'] : null,
                'diferencia_total' => $corte->estatus === CorteCaja::ESTATUS_CERRADA ? 0 : null,
            ]);
        }

        Alumno::whereHas('cargos', fn ($q) => $q->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado', 'En Convenio']))
            ->update(['estatus_financiero' => 'Con Adeudo']);
    }

    private function sembrarCalendariosAcademicos(): void
    {
        $ciclo = CicloEscolar::where('nombre', '2026-A')->first() ?? CicloEscolar::first();
        $grupoMaestria = Grupo::where('nombre', 'Maestría 4 - Grupo 2-A')->first() ?? Grupo::where('nombre', 'Grupo 2-A')->first();
        $grupoDoctorado = Grupo::where('nombre', 'Doctorado 5')->first() ?? Grupo::where('programa_id', Programa::where('nivel', 'Doctorado')->value('id'))->first();
        $grupoSabatino = Grupo::where('nombre', 'Licenciatura Sabatino 7mo')->first();
        $grupoVespertino = Grupo::where('nombre', 'Licenciatura Vespertino 3ro')->first();

        $this->crearCalendarioConMaterias(
            $grupoMaestria,
            $ciclo,
            'MAESTRÍA 4 - Grupo 2-A / 2026 A',
            CalendarioAcademico::TIPO_POSGRADO_VIERNES_SABADO,
            CalendarioAcademico::MODALIDAD_MIXTA,
            '2026-07-01',
            '2026-12-31',
            [
                ['clave' => 'IDEJ-MED-401', 'docente' => 1, 'fechas' => [['2026-07-10', '17:00', '21:00'], ['2026-07-11', '08:00', '13:00'], ['2026-08-14', '17:00', '21:00'], ['2026-08-15', '08:00', '13:00']]],
                ['clave' => 'IDEJ-MED-402', 'docente' => 2, 'fechas' => [['2026-09-11', '17:00', '21:00'], ['2026-09-12', '08:00', '13:00'], ['2026-10-09', '17:00', '21:00'], ['2026-10-10', '08:00', '13:00']]],
            ]
        );

        $this->crearCalendarioConMaterias(
            $grupoDoctorado,
            $ciclo,
            'DOCTORADO 5 / 2026 A',
            CalendarioAcademico::TIPO_POSGRADO_VIERNES_SABADO,
            CalendarioAcademico::MODALIDAD_MIXTA,
            '2026-05-01',
            '2026-09-30',
            [
                ['clave' => 'IDEJ-DOC-501', 'docente' => 3, 'fechas' => [['2026-05-15', '17:00', '21:00'], ['2026-05-16', '08:00', '13:00'], ['2026-06-19', '17:00', '21:00'], ['2026-06-20', '08:00', '13:00']]],
                ['clave' => 'IDEJ-DOC-502', 'docente' => 4, 'fechas' => [['2026-07-24', '17:00', '21:00'], ['2026-07-25', '08:00', '13:00'], ['2026-08-14', '17:00', '21:00'], ['2026-08-15', '08:00', '13:00']]],
            ]
        );

        $this->crearCalendarioConMaterias(
            $grupoSabatino,
            $ciclo,
            'LICENCIATURA SABATINO 7MO / 2026 A',
            CalendarioAcademico::TIPO_LICENCIATURA_SABATINA,
            CalendarioAcademico::MODALIDAD_PRESENCIAL,
            '2026-05-01',
            '2026-09-30',
            [
                ['clave' => 'IDEJ-LIC-701', 'docente' => 5, 'fechas' => [['2026-05-09', '08:00', '13:00'], ['2026-05-16', '08:00', '13:00'], ['2026-05-23', '08:00', '13:00'], ['2026-05-30', '08:00', '13:00']]],
            ]
        );

        $this->crearCalendarioConMaterias(
            $grupoVespertino,
            $ciclo,
            'LICENCIATURA VESPERTINA 3RO / 2026 A',
            CalendarioAcademico::TIPO_LICENCIATURA_VESPERTINA,
            CalendarioAcademico::MODALIDAD_PRESENCIAL,
            '2026-05-01',
            '2026-09-30',
            [
                ['clave' => 'IDEJ-LIC-302', 'docente' => 6, 'fechas' => [['2026-05-11', '17:00', '20:00'], ['2026-05-13', '17:00', '20:00'], ['2026-05-18', '17:00', '20:00'], ['2026-05-20', '17:00', '20:00']]],
            ]
        );

        $sesion = CalendarioSesion::where('fecha', '2026-06-19')->first();
        if ($sesion && ! CalendarioSesion::where('sesion_origen_id', $sesion->id)->exists()) {
            $sesion->update([
                'estatus' => CalendarioSesion::ESTATUS_CANCELADA,
                'cancelada_por_id' => $this->academica?->id,
                'motivo_cancelacion' => 'Cancelación demo por ajuste de agenda académica.',
            ]);

            CalendarioSesion::create([
                'calendario_materia_id' => $sesion->calendario_materia_id,
                'sesion_origen_id' => $sesion->id,
                'fecha' => '2026-06-26',
                'hora_inicio' => '17:00',
                'hora_fin' => '21:00',
                'aula' => $sesion->aula,
                'modalidad' => $sesion->modalidad,
                'tipo_sesion' => CalendarioSesion::TIPO_CLASE,
                'estatus' => CalendarioSesion::ESTATUS_PROGRAMADA,
                'reprogramada_por_id' => $this->academica?->id,
                'fecha_reprogramacion' => now(),
                'motivo_reprogramacion' => 'Reposición demo de clase cancelada.',
                'observaciones' => 'Sesión de reposición generada por seeder.',
            ]);
        }
    }

    private function crearCalendarioConMaterias(?Grupo $grupo, ?CicloEscolar $ciclo, string $nombre, string $tipo, string $modalidad, string $inicio, string $fin, array $materias): void
    {
        if (! $grupo) {
            return;
        }

        $calendario = CalendarioAcademico::updateOrCreate(
            ['nombre' => $nombre, 'grupo_id' => $grupo->id],
            [
                'ciclo_escolar_id' => $ciclo?->id,
                'periodo' => '2026 A',
                'modalidad' => $modalidad,
                'tipo_calendario' => $tipo,
                'estatus' => CalendarioAcademico::ESTATUS_PLANEADO,
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'observaciones' => 'Calendario demo generado para probar bloqueos, conteos y reprogramaciones.',
                'creado_por_id' => $this->academica?->id,
            ]
        );

        foreach ($materias as $index => $item) {
            $materia = Materia::where('clave', $item['clave'])->first() ?? Materia::first();
            $docente = Docente::find($item['docente']) ?? Docente::skip($index)->first() ?? Docente::first();

            if (! $materia) {
                continue;
            }

            $calMateria = CalendarioMateria::updateOrCreate(
                ['calendario_academico_id' => $calendario->id, 'materia_id' => $materia->id],
                [
                    'docente_id' => $docente?->id,
                    'orden' => $index + 1,
                    'nombre_materia_snapshot' => $materia->nombre,
                    'docente_snapshot' => $docente?->nombre_completo,
                    'estatus' => CalendarioMateria::ESTATUS_CONFIRMADA,
                    'observaciones' => 'Materia demo del calendario académico.',
                ]
            );

            foreach ($item['fechas'] as [$fecha, $horaInicio, $horaFin]) {
                CalendarioSesion::updateOrCreate(
                    ['calendario_materia_id' => $calMateria->id, 'fecha' => $fecha],
                    [
                        'hora_inicio' => $horaInicio,
                        'hora_fin' => $horaFin,
                        'aula' => $grupo->aula ?: 'Aula por confirmar',
                        'modalidad' => $modalidad,
                        'tipo_sesion' => CalendarioSesion::TIPO_CLASE,
                        'estatus' => CalendarioSesion::ESTATUS_PROGRAMADA,
                        'observaciones' => 'Sesión demo para agenda operativa.',
                    ]
                );
            }
        }
    }

    private function sembrarEducacionContinua(): void
    {
        $cursoMasc = CursoEducacionContinua::updateOrCreate(
            ['nombre' => 'MASC 2026 - Generación Demo'],
            [
                'tipo' => CursoEducacionContinua::TIPO_MASC,
                'modalidad' => CursoEducacionContinua::MODALIDAD_MIXTA,
                'horas_totales' => 130,
                'fecha_inicio' => '2026-05-15',
                'fecha_fin' => '2026-09-26',
                'estatus' => CursoEducacionContinua::ESTATUS_EN_CURSO,
                'responsable_id' => $this->academica?->id,
                'creado_por_id' => $this->academica?->id,
                'cupo_maximo' => 35,
                'costo' => 8500,
                'requiere_equipo' => true,
                'equipo_requerido' => ['Cámara', 'Micrófono', 'Zoom', 'Grabación'],
                'observaciones' => 'Curso demo con viernes 17:00-21:00 y sábado 09:00-13:00.',
            ]
        );

        $this->crearSesionesCurso($cursoMasc, '2026-05-15', 12, [
            5 => ['17:00', '21:00'],
            6 => ['09:00', '13:00'],
        ], ['Cámara', 'Micrófono', 'Zoom', 'Grabación']);

        $masterClass = CursoEducacionContinua::updateOrCreate(
            ['nombre' => 'MasterClass Argumentación Jurídica Demo'],
            [
                'tipo' => CursoEducacionContinua::TIPO_MASTERCLASS,
                'modalidad' => CursoEducacionContinua::MODALIDAD_VIRTUAL,
                'horas_totales' => 16,
                'fecha_inicio' => '2026-05-12',
                'fecha_fin' => '2026-05-28',
                'estatus' => CursoEducacionContinua::ESTATUS_ABIERTO,
                'responsable_id' => $this->academica?->id,
                'creado_por_id' => $this->academica?->id,
                'cupo_maximo' => 80,
                'costo' => 1200,
                'requiere_equipo' => true,
                'equipo_requerido' => ['Zoom', 'Grabación', 'Streaming'],
                'observaciones' => 'MasterClass demo martes y jueves 17:00-21:00.',
            ]
        );

        $this->crearSesionesCurso($masterClass, '2026-05-12', 4, [
            2 => ['17:00', '21:00'],
            4 => ['17:00', '21:00'],
        ], ['Zoom', 'Grabación', 'Streaming']);

        $oratoria = CursoEducacionContinua::updateOrCreate(
            ['nombre' => 'Taller de Oratoria Jurídica Demo'],
            [
                'tipo' => CursoEducacionContinua::TIPO_ORATORIA,
                'modalidad' => CursoEducacionContinua::MODALIDAD_PRESENCIAL,
                'horas_totales' => 20,
                'fecha_inicio' => '2026-06-03',
                'fecha_fin' => '2026-07-01',
                'estatus' => CursoEducacionContinua::ESTATUS_PLANEADO,
                'responsable_id' => $this->academica?->id,
                'creado_por_id' => $this->academica?->id,
                'cupo_maximo' => 25,
                'costo' => 1800,
                'requiere_equipo' => true,
                'equipo_requerido' => ['Micrófono', 'Bocina'],
                'observaciones' => 'Taller presencial demo para practicar agenda operativa.',
            ]
        );

        $this->crearSesionesCurso($oratoria, '2026-06-03', 5, [
            3 => ['17:00', '21:00'],
        ], ['Micrófono', 'Bocina']);

        $alumnos = Alumno::orderBy('id')->take(5)->get();
        $prospectos = Prospecto::orderBy('id')->take(2)->get();

        foreach ([$cursoMasc, $masterClass, $oratoria] as $curso) {
            foreach ($alumnos as $alumno) {
                CursoInscrito::updateOrCreate(
                    ['curso_id' => $curso->id, 'alumno_id' => $alumno->id],
                    [
                        'tipo_participante' => CursoInscrito::TIPO_ALUMNO,
                        'estatus' => CursoInscrito::ESTATUS_INSCRITO,
                        'fecha_inscripcion' => now()->subDays(15)->toDateString(),
                        'observaciones' => 'Inscripción demo desde alumno existente.',
                    ]
                );
            }

            foreach ($prospectos as $prospecto) {
                CursoInscrito::updateOrCreate(
                    ['curso_id' => $curso->id, 'prospecto_id' => $prospecto->id],
                    [
                        'tipo_participante' => CursoInscrito::TIPO_PROSPECTO,
                        'estatus' => CursoInscrito::ESTATUS_INSCRITO,
                        'fecha_inscripcion' => now()->subDays(10)->toDateString(),
                        'observaciones' => 'Inscripción demo desde prospecto.',
                    ]
                );
            }

            CursoInscrito::updateOrCreate(
                ['curso_id' => $curso->id, 'correo_externo' => 'externo.'.Str::slug($curso->tipo).'@example.com'],
                [
                    'tipo_participante' => CursoInscrito::TIPO_EXTERNO,
                    'nombre_externo' => 'Participante Externo '.$curso->tipo,
                    'telefono_externo' => '3312340000',
                    'estatus' => CursoInscrito::ESTATUS_INSCRITO,
                    'fecha_inscripcion' => now()->subDays(8)->toDateString(),
                    'observaciones' => 'Participante externo demo.',
                ]
            );

            $primeraSesion = $curso->sesiones()->orderBy('fecha')->first();
            if ($primeraSesion) {
                $curso->inscritos()->take(4)->get()->each(function (CursoInscrito $inscrito, int $i) use ($primeraSesion) {
                    CursoAsistencia::updateOrCreate(
                        ['curso_sesion_id' => $primeraSesion->id, 'curso_inscrito_id' => $inscrito->id],
                        [
                            'estatus' => $i === 3 ? CursoAsistencia::ESTATUS_RETARDO : CursoAsistencia::ESTATUS_ASISTIO,
                            'horas_acreditadas' => $i === 3 ? max(0, (float) $primeraSesion->duracion_horas - 0.5) : $primeraSesion->duracion_horas,
                            'registrado_por_id' => $this->academica?->id,
                            'observaciones' => 'Asistencia demo para control de horas.',
                        ]
                    );
                });
            }
        }
    }

    private function crearSesionesCurso(CursoEducacionContinua $curso, string $fechaInicio, int $numeroSesiones, array $diasHorarios, array $equipo): void
    {
        $fecha = Carbon::parse($fechaInicio);
        $creadas = 0;
        $docente = Docente::first();

        while ($creadas < $numeroSesiones) {
            $diaIso = $fecha->dayOfWeekIso;

            if (isset($diasHorarios[$diaIso])) {
                [$horaInicio, $horaFin] = $diasHorarios[$diaIso];
                $duracion = round(Carbon::parse($horaInicio)->diffInMinutes(Carbon::parse($horaFin)) / 60, 2);

                CursoSesion::updateOrCreate(
                    ['curso_id' => $curso->id, 'fecha' => $fecha->toDateString(), 'hora_inicio' => $horaInicio],
                    [
                        'docente_id' => $docente?->id,
                        'expositor_nombre' => $docente?->nombre_completo ?? 'Expositor demo IDEJ',
                        'hora_fin' => $horaFin,
                        'duracion_horas' => $duracion,
                        'aula_liga' => $curso->modalidad === CursoEducacionContinua::MODALIDAD_VIRTUAL ? 'Liga Zoom por confirmar' : 'Aula 1',
                        'modalidad' => $curso->modalidad,
                        'estatus' => $creadas === 0 ? CursoSesion::ESTATUS_IMPARTIDA : CursoSesion::ESTATUS_PROGRAMADA,
                        'requiere_equipo' => true,
                        'equipo_requerido' => $equipo,
                        'observaciones' => 'Sesión demo generada desde patrón semanal.',
                    ]
                );

                $creadas++;
            }

            $fecha->addDay();
        }
    }

    private function sembrarSolicitudesPagoDocenteDemo(): void
    {
        $docente = Docente::first();
        $calMateria = CalendarioMateria::with(['calendario.grupo.programa', 'materia'])->first();
        $curso = CursoEducacionContinua::where('nombre', 'like', 'MASC%')->first();
        $cursoSesion = $curso?->sesiones()->first();

        if (! $docente) {
            return;
        }

        $solicitudes = [
            [
                'folio' => 'SPD-DEMO-000001',
                'docente_id' => $calMateria?->docente_id ?? $docente->id,
                'creado_por_id' => $this->academica?->id,
                'calendario_materia_id' => $calMateria?->id,
                'origen' => SolicitudPagoDocente::ORIGEN_CALENDARIO,
                'concepto_pago' => SolicitudPagoDocente::CONCEPTO_HONORARIOS,
                'nivel' => $calMateria?->calendario?->grupo?->programa?->nivel ?? 'Maestría',
                'programa_grupo' => $calMateria?->calendario?->nombre ?? 'Calendario académico demo',
                'materia_actividad' => $calMateria?->nombre_materia ?? 'Materia demo',
                'periodo' => '2026 A',
                'modalidad' => 'Mixta',
                'numero_sesiones' => 4,
                'horas_totales' => 18,
                'tarifa_hora' => 500,
                'monto' => 9000,
                'fecha_solicitud' => now()->subDays(3)->toDateString(),
                'fecha_limite_pago' => now()->addDays(7)->toDateString(),
                'prioridad' => 'Alta',
                'observaciones_academica' => 'Solicitud demo pendiente por honorarios de materia.',
                'estatus' => SolicitudPagoDocente::ESTATUS_PENDIENTE,
            ],
            [
                'folio' => 'SPD-DEMO-000002',
                'docente_id' => $cursoSesion?->docente_id ?? $docente->id,
                'creado_por_id' => $this->academica?->id,
                'autorizado_por_id' => $this->cadmin?->id,
                'procesado_por_id' => $this->finanzas?->id,
                'curso_id' => $curso?->id,
                'curso_sesion_id' => $cursoSesion?->id,
                'origen' => SolicitudPagoDocente::ORIGEN_EDUCACION_CONTINUA,
                'concepto_pago' => SolicitudPagoDocente::CONCEPTO_CONFERENCIA,
                'nivel' => 'Educación continua',
                'programa_grupo' => $curso?->nombre ?? 'Curso demo',
                'materia_actividad' => 'Sesión MASC demo',
                'periodo' => '2026 A',
                'modalidad' => $curso?->modalidad ?? 'Mixta',
                'numero_sesiones' => 1,
                'horas_totales' => 4,
                'tarifa_hora' => 600,
                'monto' => 2400,
                'fecha_solicitud' => now()->subDays(10)->toDateString(),
                'fecha_limite_pago' => now()->subDays(2)->toDateString(),
                'fecha_autorizacion' => now()->subDays(8),
                'fecha_pago' => now()->subDays(1)->toDateString(),
                'prioridad' => 'Normal',
                'metodo_pago' => 'Transferencia',
                'referencia_pago' => 'PAGO-DEMO-MASC-001',
                'banco_pago' => 'BBVA',
                'observaciones_academica' => 'Pago demo de educación continua.',
                'observaciones_administracion' => 'Autorizada y pagada para pruebas.',
                'estatus' => SolicitudPagoDocente::ESTATUS_PAGADA,
            ],
            [
                'folio' => 'SPD-DEMO-000003',
                'docente_id' => $docente->id,
                'creado_por_id' => $this->academica?->id,
                'origen' => SolicitudPagoDocente::ORIGEN_MANUAL,
                'concepto_pago' => SolicitudPagoDocente::CONCEPTO_ASESORIA,
                'nivel' => 'Doctorado',
                'programa_grupo' => 'Doctorado 5',
                'materia_actividad' => 'Asesoría extraordinaria de tesis',
                'periodo' => '2026 A',
                'modalidad' => 'Virtual',
                'numero_sesiones' => 2,
                'horas_totales' => 3,
                'tarifa_hora' => 450,
                'monto' => 1350,
                'fecha_solicitud' => now()->subDays(5)->toDateString(),
                'fecha_limite_pago' => now()->addDays(4)->toDateString(),
                'prioridad' => 'Urgente',
                'motivo_observacion' => 'Falta confirmar evidencia de sesiones impartidas.',
                'observaciones_academica' => 'Solicitud demo observada.',
                'observaciones_administracion' => 'Devolver a Académica para adjuntar soporte.',
                'estatus' => SolicitudPagoDocente::ESTATUS_OBSERVADA,
            ],
        ];

        foreach ($solicitudes as $solicitud) {
            SolicitudPagoDocente::updateOrCreate(['folio' => $solicitud['folio']], $solicitud);
        }
    }

    private function sembrarBitacoraDemo(): void
    {
        $eventos = [
            ['usuario_id' => $this->admin?->id, 'accion' => 'Inicio de datos demo', 'modulo' => 'Sistema', 'descripcion' => 'Carga integral de seeders para pruebas locales.'],
            ['usuario_id' => $this->academica?->id, 'accion' => 'Planeación académica demo', 'modulo' => 'Calendarios', 'descripcion' => 'Se generaron calendarios académicos con fechas exactas.'],
            ['usuario_id' => $this->finanzas?->id, 'accion' => 'Corte de caja demo', 'modulo' => 'Caja', 'descripcion' => 'Se generaron pagos, cortes y ajustes demo.'],
            ['usuario_id' => $this->rrpp?->id, 'accion' => 'Seguimiento prospecto demo', 'modulo' => 'Prospectos', 'descripcion' => 'Se cargaron prospectos y seguimientos de prueba.'],
        ];

        foreach ($eventos as $evento) {
            Bitacora::updateOrCreate(
                ['accion' => $evento['accion'], 'modulo' => $evento['modulo']],
                [
                    ...$evento,
                    'tipo' => 'Visita',
                    'fecha_evento' => now(),
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Seeder IDEJ-SYS',
                    'url' => '/seeders/demo',
                    'metodo_http' => 'SEED',
                ]
            );
        }
    }
}
