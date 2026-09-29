@extends('layouts.app')

@section('titre', 'Modifier '.$utilisateur->email)

@section('contenu')
@include('utilisateurs.form')
@endsection
