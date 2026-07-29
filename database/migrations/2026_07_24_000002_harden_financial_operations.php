<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('movimientos_caja') && ! Schema::hasColumn('movimientos_caja', 'operacion_uuid')) {
            Schema::table('movimientos_caja', function (Blueprint $table) {
                $table->uuid('operacion_uuid')->nullable()->unique()->after('id');
            });
        }

        if (Schema::hasTable('cargos') && ! Schema::hasColumn('cargos', 'saldo_favor_aplicado')) {
            Schema::table('cargos', function (Blueprint $table) {
                $table->decimal('saldo_favor_aplicado', 10, 2)->default(0)->after('beca_monto_aplicado');
            });
        }

        if (Schema::hasTable('cargos') && ! Schema::hasColumn('cargos', 'operacion_uuid')) {
            Schema::table('cargos', function (Blueprint $table) {
                $table->uuid('operacion_uuid')->nullable()->unique()->after('id');
            });
        }

        if (Schema::hasTable('cargos_masivos') && ! Schema::hasColumn('cargos_masivos', 'operacion_uuid')) {
            Schema::table('cargos_masivos', function (Blueprint $table) {
                $table->uuid('operacion_uuid')->nullable()->unique()->after('id');
            });
        }

        if (Schema::hasTable('cargos') && ! Schema::hasColumn('cargos', 'cargo_masivo_id')) {
            Schema::table('cargos', function (Blueprint $table) {
                $table->foreignId('cargo_masivo_id')
                    ->nullable()
                    ->after('operacion_uuid')
                    ->constrained('cargos_masivos')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('cortes_caja')) {
            $columnasCierre = [
                'otro_reportado' => 'tarjeta_reportado',
                'diferencia_transferencia' => 'diferencia_efectivo',
                'diferencia_tarjeta' => 'diferencia_transferencia',
                'diferencia_otro' => 'diferencia_tarjeta',
            ];

            foreach ($columnasCierre as $columna => $despuesDe) {
                if (! Schema::hasColumn('cortes_caja', $columna)) {
                    Schema::table('cortes_caja', function (Blueprint $table) use ($columna, $despuesDe) {
                        $table->decimal($columna, 10, 2)->nullable()->after($despuesDe);
                    });
                }
            }
        }

        if (Schema::hasTable('pagos')) {
            $folioDuplicado = DB::table('pagos')
                ->select('folio_recibo', DB::raw('COUNT(*) as total'))
                ->whereNotNull('folio_recibo')
                ->groupBy('folio_recibo')
                ->havingRaw('COUNT(*) > 1')
                ->exists();

            if ($folioDuplicado) {
                throw new \RuntimeException(
                    'No se puede proteger el folio de recibo: existen folios duplicados en pagos. Corrige esos registros antes de continuar.'
                );
            }

            $indiceFolio = collect(Schema::getIndexes('pagos'))
                ->first(fn (array $indice) => ($indice['columns'] ?? []) === ['folio_recibo']);

            if (! (bool) ($indiceFolio['unique'] ?? false)) {
                if (! empty($indiceFolio['name'])) {
                    Schema::table('pagos', function (Blueprint $table) use ($indiceFolio) {
                        $table->dropIndex($indiceFolio['name']);
                    });
                }

                Schema::table('pagos', function (Blueprint $table) {
                    $table->unique('folio_recibo', 'pagos_folio_recibo_unique');
                });
            }
        }

        if (Schema::hasTable('ajustes_caja')) {
            $duplicados = DB::table('ajustes_caja')
                ->select('pago_id', 'tipo', DB::raw('COUNT(*) as total'))
                ->whereNotNull('pago_id')
                ->groupBy('pago_id', 'tipo')
                ->havingRaw('COUNT(*) > 1')
                ->exists();

            if ($duplicados) {
                throw new \RuntimeException(
                    'No se puede crear la protección contra ajustes duplicados: existen pagos con más de un ajuste del mismo tipo. Revisa ajustes_caja antes de continuar.'
                );
            }

            $indiceAjuste = collect(Schema::getIndexes('ajustes_caja'))
                ->first(fn (array $indice) => ($indice['columns'] ?? []) === ['pago_id', 'tipo'] && (bool) ($indice['unique'] ?? false));

            if (! $indiceAjuste) {
                Schema::table('ajustes_caja', function (Blueprint $table) {
                    $table->unique(['pago_id', 'tipo'], 'ajustes_caja_pago_tipo_unique');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cortes_caja')) {
            foreach (['diferencia_otro', 'diferencia_tarjeta', 'diferencia_transferencia', 'otro_reportado'] as $columna) {
                if (Schema::hasColumn('cortes_caja', $columna)) {
                    Schema::table('cortes_caja', function (Blueprint $table) use ($columna) {
                        $table->dropColumn($columna);
                    });
                }
            }
        }

        if (Schema::hasTable('pagos')) {
            $indiceFolio = collect(Schema::getIndexes('pagos'))
                ->first(fn (array $indice) => ($indice['name'] ?? null) === 'pagos_folio_recibo_unique');

            if ($indiceFolio) {
                Schema::table('pagos', function (Blueprint $table) {
                    $table->dropUnique('pagos_folio_recibo_unique');
                    $table->index('folio_recibo', 'pagos_folio_recibo_index');
                });
            }
        }

        if (Schema::hasTable('ajustes_caja')) {
            $indiceAjuste = collect(Schema::getIndexes('ajustes_caja'))
                ->first(fn (array $indice) => ($indice['name'] ?? null) === 'ajustes_caja_pago_tipo_unique');

            if ($indiceAjuste) {
                Schema::table('ajustes_caja', function (Blueprint $table) {
                    $table->dropUnique('ajustes_caja_pago_tipo_unique');
                });
            }
        }

        if (Schema::hasTable('cargos') && Schema::hasColumn('cargos', 'cargo_masivo_id')) {
            Schema::table('cargos', function (Blueprint $table) {
                $table->dropConstrainedForeignId('cargo_masivo_id');
            });
        }

        if (Schema::hasTable('cargos_masivos') && Schema::hasColumn('cargos_masivos', 'operacion_uuid')) {
            Schema::table('cargos_masivos', function (Blueprint $table) {
                $table->dropColumn('operacion_uuid');
            });
        }

        if (Schema::hasTable('cargos') && Schema::hasColumn('cargos', 'saldo_favor_aplicado')) {
            Schema::table('cargos', function (Blueprint $table) {
                $table->dropColumn('saldo_favor_aplicado');
            });
        }

        if (Schema::hasTable('cargos') && Schema::hasColumn('cargos', 'operacion_uuid')) {
            Schema::table('cargos', function (Blueprint $table) {
                $table->dropColumn('operacion_uuid');
            });
        }

        if (Schema::hasTable('movimientos_caja') && Schema::hasColumn('movimientos_caja', 'operacion_uuid')) {
            Schema::table('movimientos_caja', function (Blueprint $table) {
                $table->dropColumn('operacion_uuid');
            });
        }
    }
};
