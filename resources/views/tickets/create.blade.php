@extends('layouts.app')

@section('titre', 'Nouvelle demande')

@section('contenu')
<div class="alert alert--info">
    Décrivez la plainte ou le bug constaté : le support Agent-Justice prendra en charge votre demande
    et vous tiendra informé de sa résolution.
</div>

@include('tickets._form')
@endsection
