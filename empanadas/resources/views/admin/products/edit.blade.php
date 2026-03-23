{{-- resources/views/admin/products/edit.blade.php --}}
@extends('layouts.app')
@section('title', 'Editar Producto')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.products.index') }}"
           class="p-2 rounded-xl hover:bg-cream-100 text-gray-500 transition-colors">←</a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Editar: {{ $product->nombre }}</h1>
            <p class="text-sm text-gray-400">Modifica los datos del producto</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-cream-200 p-6">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('admin.products._form')
        </form>
    </div>
</div>
@endsection
