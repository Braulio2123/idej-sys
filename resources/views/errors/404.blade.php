@extends('errors.layout')

@section('code', '404')
@section('title', 'Página no encontrada')
@section('message', 'La página que buscas no existe, fue movida o el enlace ya no está disponible.')
@section('hint')
Revisa que el enlace sea correcto. Si venías desde un botón interno del sistema, repórtalo para corregir la ruta o vista correspondiente.
@endsection
