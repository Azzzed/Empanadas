@extends('layouts.app')
@section('title', 'Productos')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Productos</h1>
        <p class="text-sm text-gray-500 mt-0.5">Gestión del catálogo de empanadas y papas rellenas</p>
    </div>
    <a href="{{ route('admin.products.create') }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-500 hover:bg-brand-600
              text-white font-semibold rounded-xl shadow-md shadow-brand-200 transition-all active:scale-95">
        ➕ Nuevo Producto
    </a>
</div>

{{-- Filtros --}}
<form method="GET" class="flex flex-wrap gap-3 mb-6">
    <div class="relative flex-1 min-w-48">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔍</span>
        <input type="text" name="buscar" value="{{ request('buscar') }}"
               placeholder="Buscar por nombre o relleno…"
               class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm
                      focus:outline-none focus:ring-2 focus:ring-brand-400">
    </div>
    <select name="tipo"
            class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm
                   focus:outline-none focus:ring-2 focus:ring-brand-400">
        <option value="">Todos los tipos</option>
        <option value="empanada" @selected(request('tipo') === 'empanada')>🥟 Empanadas</option>
        <option value="papa_rellena" @selected(request('tipo') === 'papa_rellena')>🥔 Papas Rellenas</option>
    </select>
    <select name="relleno"
            class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm
                   focus:outline-none focus:ring-2 focus:ring-brand-400">
        <option value="">Todos los rellenos</option>
        @foreach($rellenos as $r)
            <option @selected(request('relleno') === $r)>{{ $r }}</option>
        @endforeach
    </select>
    <button type="submit"
            class="px-5 py-2.5 bg-gray-800 text-white rounded-xl text-sm font-medium hover:bg-gray-700 transition-colors">
        Filtrar
    </button>
    @if(request()->hasAny(['buscar','tipo','relleno']))
    <a href="{{ route('admin.products.index') }}"
       class="px-4 py-2.5 border border-gray-200 text-gray-500 rounded-xl text-sm hover:bg-gray-50 transition-colors">
        ✕ Limpiar
    </a>
    @endif
</form>

{{-- Grid de cards --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
    @forelse($productos as $producto)
    <div class="card-hover bg-white rounded-2xl overflow-hidden border border-cream-200 shadow-sm
                @if(!$producto->activo) opacity-60 @endif">

        {{-- Imagen / placeholder --}}
        <div class="aspect-square bg-cream-100 relative overflow-hidden">
            @if($producto->imagen_path)
                <img src="{{ Storage::url($producto->imagen_path) }}" alt="{{ $producto->nombre }}"
                     class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center">
                    <span class="text-5xl opacity-40">
                        {{ $producto->tipo_producto === 'papa_rellena' ? '🥔' : '🥟' }}
                    </span>
                </div>
                <div class="absolute inset-0 flex items-end p-2 opacity-0 hover:opacity-100 transition-opacity
                            bg-gradient-to-t from-black/30">
                    <span class="text-white text-xs bg-black/40 px-2 py-0.5 rounded-full">Sin imagen</span>
                </div>
            @endif

            {{-- Badge tipo --}}
            <div class="absolute top-2 left-2">
                <span class="text-xs px-2 py-0.5 rounded-full font-medium
                             {{ $producto->tipo_producto === 'papa_rellena'
                                ? 'bg-amber-100 text-amber-700'
                                : 'bg-orange-100 text-orange-700' }}">
                    {{ $producto->tipo_label }}
                </span>
            </div>

            {{-- Badge activo/inactivo --}}
            <div class="absolute top-2 right-2">
                @if(!$producto->activo)
                <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-600 font-medium">Inactivo</span>
                @endif
            </div>
        </div>

        <div class="p-3">
            <p class="font-bold text-gray-800 truncate" title="{{ $producto->nombre }}">{{ $producto->nombre }}</p>
            <p class="text-xs text-gray-400 truncate mb-2">{{ $producto->relleno }} · {{ $producto->tamano }}</p>

            <div class="flex items-center justify-between mb-3">
                <span class="text-brand-600 font-bold text-base">{{ $producto->precio_formateado }}</span>
                <span class="text-xs text-gray-400">Stock: {{ $producto->stock }}</span>
            </div>

            {{-- Acciones --}}
            <div class="flex gap-1.5">
                <a href="{{ route('admin.products.edit', $producto) }}"
                   class="flex-1 text-center py-1.5 text-xs font-medium rounded-lg bg-cream-100
                          hover:bg-cream-200 text-gray-700 transition-colors">
                    ✏️ Editar
                </a>

                {{-- Toggle activo --}}
                <form method="POST" action="{{ route('admin.products.toggle', $producto) }}">
                    @csrf @method('PATCH')
                    <button class="py-1.5 px-2 text-xs rounded-lg transition-colors
                                   {{ $producto->activo
                                      ? 'bg-green-50 text-green-600 hover:bg-green-100'
                                      : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                        {{ $producto->activo ? '✅' : '⏸️' }}
                    </button>
                </form>

                {{-- Eliminar --}}
                <form method="POST" action="{{ route('admin.products.destroy', $producto) }}"
                      onsubmit="return confirm('¿Eliminar «{{ addslashes($producto->nombre) }}»?')">
                    @csrf @method('DELETE')
                    <button class="py-1.5 px-2 text-xs rounded-lg bg-red-50 text-red-500
                                   hover:bg-red-100 transition-colors">
                        🗑️
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full flex flex-col items-center py-20 text-gray-300">
        <span class="text-6xl mb-4">📦</span>
        <p class="text-base font-medium text-gray-400">No hay productos registrados</p>
        <a href="{{ route('admin.products.create') }}" class="mt-4 text-brand-500 hover:underline text-sm">
            Crear el primer producto →
        </a>
    </div>
    @endforelse
</div>

{{-- Paginación --}}
<div class="mt-6">{{ $productos->links() }}</div>
@endsection
