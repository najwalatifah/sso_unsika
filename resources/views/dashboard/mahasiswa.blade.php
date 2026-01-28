@extends('layouts.app')

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-3">Dashboard Mahasiswa</h1>
            <p class="mb-1">Nama: {{ auth()->user()->name }}</p>
            <p class="mb-0">Role: {{ auth()->user()->role }}</p>
        </div>
    </div>
@endsection
