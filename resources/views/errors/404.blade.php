@extends('errors.layout')

@section('code', '404')
@section('title', 'Página no encontrada')
@section('message', 'La página que buscas no existe, fue movida o el enlace ya no está disponible.')
@section('hint')
Revisa que el enlace sea correcto. Si llegaste desde un botón del sistema, repórtalo al área de Sistemas para revisarlo.
@endsection
