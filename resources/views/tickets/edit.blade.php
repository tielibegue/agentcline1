@extends('layouts.app')

@section('titre', 'Modifier '.$ticket->reference)

@section('contenu')
@include('tickets._form')
@endsection
