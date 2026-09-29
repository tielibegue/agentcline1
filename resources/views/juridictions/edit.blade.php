@extends('layouts.app')

@section('titre', 'Modifier '.$juridiction->libelle)

@section('contenu')
@include('juridictions.form')
@endsection
