<?php

namespace App\Services;

use App\Models\SolicitudPagoDocente;
use InvalidArgumentException;

class TeacherPaymentCalculator
{
    private const MIN_AMOUNT_CENTS = 100;
    private const MAX_AMOUNT_CENTS = 999999999;

    /**
     * Calcula el monto administrativo definitivo sin confiar en el total enviado por el navegador.
     */
    public function calculate(
        string $scheme,
        mixed $unitRate,
        int $sessions,
        mixed $hours,
        mixed $fixedAmount = null,
    ): string {
        return match ($scheme) {
            SolicitudPagoDocente::ESQUEMA_SESION => $this->calculateBySessions($unitRate, $sessions),
            SolicitudPagoDocente::ESQUEMA_HORA => $this->calculateByHours($unitRate, $hours),
            SolicitudPagoDocente::ESQUEMA_FIJO => $this->normalizeMoney($fixedAmount, 'Indica el monto fijo aprobado.'),
            default => throw new InvalidArgumentException('El esquema de pago no es válido.'),
        };
    }

    private function calculateBySessions(mixed $unitRate, int $sessions): string
    {
        if ($sessions < 1) {
            throw new InvalidArgumentException('La solicitud no tiene sesiones impartidas para calcular el pago.');
        }

        $rateInCents = $this->moneyToCents($unitRate, 'Indica una tarifa válida por sesión.');

        return $this->validatedAmount($rateInCents * $sessions);
    }

    private function calculateByHours(mixed $unitRate, mixed $hours): string
    {
        $rateInCents = $this->moneyToCents($unitRate, 'Indica una tarifa válida por hora.');
        $hoursInHundredths = $this->decimalToScaledInteger(
            $hours,
            2,
            'La solicitud debe tener horas académicas mayores a cero para calcular un pago por hora.'
        );

        if ($hoursInHundredths < 1) {
            throw new InvalidArgumentException('La solicitud debe tener horas académicas mayores a cero para calcular un pago por hora.');
        }

        // centavos × centésimas de hora / 100, con redondeo comercial al centavo.
        $amountInCents = intdiv(($rateInCents * $hoursInHundredths) + 50, 100);

        return $this->validatedAmount($amountInCents);
    }

    private function normalizeMoney(mixed $value, string $message): string
    {
        return $this->validatedAmount($this->moneyToCents($value, $message));
    }

    private function moneyToCents(mixed $value, string $message): int
    {
        $cents = $this->decimalToScaledInteger($value, 2, $message);

        if ($cents < 1) {
            throw new InvalidArgumentException($message);
        }

        return $cents;
    }

    private function validatedAmount(int $cents): string
    {
        if ($cents < self::MIN_AMOUNT_CENTS) {
            throw new InvalidArgumentException('El monto calculado debe ser de al menos $1.00.');
        }

        if ($cents > self::MAX_AMOUNT_CENTS) {
            throw new InvalidArgumentException('El monto calculado excede el máximo permitido. Revisa la tarifa y la cantidad reportada.');
        }

        return $this->centsToMoney($cents);
    }

    private function decimalToScaledInteger(mixed $value, int $scale, string $message): int
    {
        if ($value === null || $value === '') {
            throw new InvalidArgumentException($message);
        }

        $normalized = trim(str_replace(',', '.', (string) $value));

        if (! preg_match('/^\d+(?:\.\d+)?$/', $normalized)) {
            throw new InvalidArgumentException($message);
        }

        [$whole, $fraction] = array_pad(explode('.', $normalized, 2), 2, '');
        $factor = 10 ** $scale;
        $fraction = preg_replace('/\D/', '', $fraction) ?? '';
        $mainFraction = str_pad(substr($fraction, 0, $scale), $scale, '0');
        $scaled = ((int) $whole * $factor) + (int) $mainFraction;

        $roundingDigit = (int) ($fraction[$scale] ?? 0);
        if ($roundingDigit >= 5) {
            $scaled++;
        }

        return $scaled;
    }

    private function centsToMoney(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
