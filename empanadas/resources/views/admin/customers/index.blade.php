@extends('layouts.app')
@section('title', 'Clientes')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Clientes</h1>
        <p class="text-sm text-gray-500 mt-0.5">Gestión de clientes con datos de facturación</p>
    </div>
    <a href="{{ route('admin.customers.create') }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-500 hover:bg-brand-600
              text-white font-semibold rounded-xl shadow-md shadow-brand-200 transition-all active:scale-95">
        ➕ Nuevo Cliente
    </a>
</div>

{{-- Filtros --}}
<form method="GET" class="flex flex-wrap gap-3 mb-6">
    <div class="relative flex-1 min-w-48">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔍</span>
        <input type="text" name="buscar" value="{{ request('buscar') }}"
               placeholder="Nombre, documento o teléfono…"
               class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm
                      focus:outline-none focus:ring-2 focus:ring-brand-400">
    </div>
    <select name="ciudad" class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm
                                  focus:outline-none focus:ring-2 focus:ring-brand-400">
        <option value="">Todas las ciudades</option>
        @foreach($ciudades as $ciudad)
        <option value="{{ $ciudad }}" @selected(request('ciudad') === $ciudad)>{{ $ciudad }}</option>
        @endforeach
    </select>
    <select name="estado" class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm
                                  focus:outline-none focus:ring-2 focus:ring-brand-400">
        <option value="">Todos</option>
        <option value="activo" @selected(request('estado') === 'activo')>✅ Activos</option>
        <option value="inactivo" @selected(request('estado') === 'inactivo')>⏸ Inactivos</option>
    </select>
    <button type="submit" class="px-5 py-2.5 bg-gray-800 text-white rounded-xl text-sm font-medium hover:bg-gray-700 transition-colors">
        Filtrar
    </button>
    @if(request()->hasAny(['buscar','ciudad','estado']))
    <a href="{{ route('admin.customers.index') }}"
       class="px-4 py-2.5 border border-gray-200 text-gray-500 rounded-xl text-sm hover:bg-gray-50">✕ Limpiar</a>
    @endif
</form>

{{-- Tabla --}}
<div class="bg-white rounded-2xl border border-cream-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-cream-100">
            <thead class="bg-cream-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Cliente</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden sm:table-cell">Documento</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Ciudad</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Teléfono</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Compras</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Estado</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-cream-100">
                @forelse($clientes as $cliente)
                <tr class="hover:bg-cream-50 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-brand-100 flex items-center justify-center
                                        text-brand-600 font-bold text-sm shrink-0">
                                {{ strtoupper(substr($cliente->nombre, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">{{ $cliente->nombre }}</p>
                                <p class="text-xs text-gray-400 sm:hidden">{{ $cliente->documento_completo }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-sm text-gray-600 hidden sm:table-cell">
                        {{ $cliente->documento_completo }}
                    </td>
                    <td class="px-5 py-4 text-sm text-gray-600 hidden md:table-cell">
                        {{ $cliente->ciudad ?? '—' }}
                    </td>
                    <td class="px-5 py-4 text-sm text-gray-600 hidden md:table-cell">
                        {{ $cliente->telefono ?? '—' }}
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                     bg-brand-100 text-brand-700 text-sm font-bold">
                            {{ $cliente->sales_count }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                     {{ $cliente->activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.customers.show', $cliente) }}"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-brand-500 hover:bg-brand-50 transition-colors"
                               title="Ver detalle">👁️</a>
                            <a href="{{ route('admin.customers.edit', $cliente) }}"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                               title="Editar">✏️</a>
                            <form method="POST" action="{{ route('admin.customers.destroy', $cliente) }}"
                                  onsubmit="return confirm('¿Eliminar a {{ addslashes($cliente->nombre) }}?')">
                                @csrf @method('DELETE')
                                <button class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                                        title="Eliminar">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-16 text-center text-gray-300">
                        <span class="text-5xl block mb-3">👥</span>
                        <p class="text-base font-medium text-gray-400">No hay clientes registrados</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-5">{{ $clientes->links() }}</div>
@endsection
