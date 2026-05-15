@extends('errors.layout')

@section('code', '419')
@section('title', 'Sesión expirada')
@section('message', 'Tu sesión expiró por seguridad o el formulario estuvo abierto demasiado tiempo. Vuelve a iniciar sesión o recarga el formulario antes de enviar información crítica.')
@section('hint')
En pagos, caja, becas o solicitudes de pago docente, verifica que la operación se haya guardado antes de repetirla.
@endsection
