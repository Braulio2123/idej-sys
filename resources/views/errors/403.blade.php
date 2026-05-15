@extends('errors.layout')

@section('code', '403')
@section('title', 'Acceso no autorizado')
@section('message', 'Tu usuario no tiene permisos para entrar a este módulo o ejecutar esta acción dentro de IDEJ-SYS.')
@section('hint')
Si consideras que deberías tener acceso, solicita revisión de tu rol al área de Sistemas o Administración. No intentes acceder manualmente por URL.
@endsection
