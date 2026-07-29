<?php

namespace App\Mail;

use App\Models\Alumno;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class RecordatorioPago extends Mailable
{
    use Queueable, SerializesModels;

    public Alumno $alumno;
    public Collection $cargos;
    public float $totalAdeudo;

    public function __construct(Alumno $alumno, ?Collection $cargos = null)
    {
        $this->alumno = $alumno;
        $this->cargos = $cargos ?? $alumno->cargos;
        $this->totalAdeudo = (float) $this->cargos->sum('monto_adeudo');
    }

    public function build(): self
    {
        return $this->subject('Recordatorio de pago - IDEJ')
            ->view('emails.recordatorio-pago')
            ->with([
                'alumno' => $this->alumno,
                'cargos' => $this->cargos,
                'totalAdeudo' => $this->totalAdeudo,
                'primerVencimiento' => $this->cargos->sortBy('fecha_vencimiento')->first()?->fecha_vencimiento,
            ]);
    }
}
