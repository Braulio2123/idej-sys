@extends('errors.layout')

@section('code', '500')
@section('title', 'Error interno del sistema')
@section('message', 'Ocurrió un problema interno. Para proteger el sistema, no se muestran detalles técnicos en pantalla.')
@section('hint')
El área de Sistemas debe revisar el registro interno de errores, la bitácora y la acción que generó el problema. No repitas operaciones financieras sin confirmar el estado real del registro.
@endsection
