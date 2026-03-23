{{-- resources/views/admin/products/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Nuevo Producto')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.products.index') }}"
           class="p-2 rounded-xl hover:bg-cream-100 text-gray-500 transition-colors">←</a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Nuevo Producto</h1>
            <p class="text-sm text-gray-400">Agrega una empanada o papa rellena al catálogo</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-cream-200 p-6">
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.products._form')
        </form>
    </div>
</div>
@endsection
