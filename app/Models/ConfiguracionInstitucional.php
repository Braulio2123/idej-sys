<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ConfiguracionInstitucional extends Model
{
    use HasFactory;

    protected $table = 'configuraciones_institucionales';

    protected $fillable = [
        'nombre_institucion',
        'nombre_corto',
        'razon_social',
        'rfc',
        'lema',
        'logo_path',
        'domicilio',
        'colonia',
        'municipio',
        'estado',
        'codigo_postal',
        'telefono_principal',
        'telefono_secundario',
        'correo_contacto',
        'sitio_web',
        'color_primario',
        'color_secundario',
        'color_acento',
        'recibo_prefijo',
        'recibo_leyenda',
        'recibo_nota_fiscal',
        'recibo_firma_recibio',
        'recibo_firma_conformidad',
        'recibo_mostrar_logo',
        'moneda',
        'zona_horaria',
        'moratorio_porcentaje',
        'moratorio_dias_gracia',
        'recordatorios_pago_activos',
        'actualizado_por_id',
    ];

    protected $casts = [
        'recibo_mostrar_logo' => 'boolean',
        'recordatorios_pago_activos' => 'boolean',
        'moratorio_porcentaje' => 'decimal:2',
        'moratorio_dias_gracia' => 'integer',
    ];

    public static function defaults(): array
    {
        return [
            'nombre_institucion' => 'Instituto de Altos Estudios Jurídicos de Jalisco',
            'nombre_corto' => 'IDEJ',
            'razon_social' => null,
            'rfc' => null,
            'lema' => 'Gestión académica y administrativa institucional',
            'logo_path' => null,
            'domicilio' => null,
            'colonia' => null,
            'municipio' => null,
            'estado' => 'Jalisco',
            'codigo_postal' => null,
            'telefono_principal' => null,
            'telefono_secundario' => null,
            'correo_contacto' => null,
            'sitio_web' => null,
            'color_primario' => '#1E3A8A',
            'color_secundario' => '#0D133A',
            'color_acento' => '#F59E0B',
            'recibo_prefijo' => 'IDEJ',
            'recibo_leyenda' => 'Documento administrativo para control escolar y financiero.',
            'recibo_nota_fiscal' => 'Este recibo es un comprobante interno de control administrativo. No sustituye CFDI ni documento fiscal.',
            'recibo_firma_recibio' => 'Firma de quien recibe',
            'recibo_firma_conformidad' => 'Firma de conformidad',
            'recibo_mostrar_logo' => true,
            'moneda' => 'MXN',
            'zona_horaria' => 'America/Mexico_City',
            'moratorio_porcentaje' => 5.00,
            'moratorio_dias_gracia' => 0,
            'recordatorios_pago_activos' => false,
        ];
    }

    public static function actual(): self
    {
        if (! Schema::hasTable('configuraciones_institucionales')) {
            return new self(self::defaults());
        }

        return Cache::remember('configuracion_institucional_actual', 3600, function () {
            $configuracion = self::query()->first();

            if (! $configuracion) {
                $configuracion = self::create(self::defaults());
            }

            return $configuracion;
        });
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('configuracion_institucional_actual'));
        static::deleted(fn () => Cache::forget('configuracion_institucional_actual'));
    }

    public function actualizadoPor()
    {
        return $this->belongsTo(Usuario::class, 'actualizado_por_id');
    }

    public function direccionCompleta(): string
    {
        return collect([
            $this->domicilio,
            $this->colonia,
            $this->municipio,
            $this->estado,
            $this->codigo_postal ? 'C.P. '.$this->codigo_postal : null,
        ])->filter()->implode(', ');
    }

    public function logoUrl(): string
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            return Storage::url($this->logo_path);
        }

        return asset('images/logo.png');
    }

    public function logoPathPdf(): string
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            return storage_path('app/public/'.$this->logo_path);
        }

        return public_path('images/logo.png');
    }
}
