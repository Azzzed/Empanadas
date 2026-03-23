@extends('layouts.app')
@section('title', $customer->nombre)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-2">
        <a href="{{ route('admin.customers.index') }}"
           class="p-2 rounded-xl hover:bg-cream-100 text-gray-500 transition-colors">←</a>
        <h1 class="text-2xl font-bold text-gray-800">Perfil del cliente</h1>
    </div>

    {{-- Card principal --}}
    <div class="bg-white rounded-3xl border border-cream-200 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-start gap-5">

            {{-- Avatar --}}
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-brand-400 to-brand-600
                        flex items-center justify-center text-white font-bold text-3xl shrink-0 shadow-md">
                {{ strtoupper(substr($customer->nombre, 0, 1)) }}
            </div>

            {{-- Info --}}
            <div class="flex-1 grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div class="col-span-2 sm:col-span-3">
                    <p class="text-xl font-bold text-gray-800">{{ $customer->nombre }}</p>
                    <p class="text-sm text-gray-400">{{ $customer->documento_completo }}</p>
                </div>
                @if($customer->email)
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Email</p>
                    <p class="text-sm font-medium text-gray-700">{{ $customer->email }}</p>
                </div>
                @endif
                @if($customer->telefono)
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Teléfono</p>
                    <p class="text-sm font-medium text-gray-700">{{ $customer->telefono }}</p>
                </div>
                @endif
                @if($customer->ciudad)
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Ciudad</p>
                    <p class="text-sm font-medium text-gray-700">{{ $customer->ciudad }}</p>
                </div>
                @endif
                @if($customer->direccion)
                <div class="col-span-2">
                    <p class="text-xs text-gray-400 mb-0.5">Dirección</p>
                    <p class="text-sm font-medium text-gray-700">{{ $customer->direccion }}</p>
                </div>
                @endif
            </div>

            {{-- Acciones --}}
            <a href="{{ route('admin.customers.edit', $customer) }}"
               class="shrink-0 px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-sm
                      hover:bg-cream-50 font-medium transition-colors">
                ✏️ Editar
            </a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-6 pt-6 border-t border-cream-100">
            <div class="bg-cream-50 rounded-2xl p-4 text-center">
                <p class="text-2xl font-bold text-brand-600">{{ $ventas->total() }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Compras totales</p>
            </div>
            <div class="bg-cream-50 rounded-2xl p-4 text-center">
                <p class="text-2xl font-bold text-brand-600">
                    ${{ number_format($totalCompras, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5">Total gastado</p>
            </div>
            <div class="bg-cream-50 rounded-2xl p-4 text-center col-span-2 sm:col-span-1">
                <p class="text-2xl font-bold {{ $customer->activo ? 'text-green-600' : 'text-gray-400' }}">
                    {{ $customer->activo ? 'Activo' : 'Inactivo' }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5">Estado</p>
            </div>
        </div>
    </div>

    {{-- Historial de ventas --}}
    <div>
        <h2 class="text-lg font-bold text-gray-800 mb-4">Historial de compras</h2>
        <div class="space-y-3">
            @forelse($ventas as $venta)
            <div class="bg-white rounded-2xl border border-cream-200 shadow-sm p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-sm font-bold text-brand-600">{{ $venta->numero_factura }}</span>
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium
                                     {{ $venta->estado === 'completada' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ ucfirst($venta->estado) }}
                        </span>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-gray-800">{{ $venta->total_formateado }}</p>
                        <p class="text-xs text-gray-400">{{ $venta->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                {{-- Ítems de la venta --}}
                <div class="flex flex-wrap gap-2">
                    @foreach($venta->items as $item)
                    <span class="inline-flex items-center gap-1.5 bg-cream-50 text-gray-600 text-xs
                                 px-3 py-1.5 rounded-full border border-cream-200">
                        <span>{{ $item->product->tipo_producto === 'papa_rellena' ? '🥔' : '🥟' }}</span>
                        <span>{{ $item->cantidad }}× {{ $item->product->nombre }}</span>
                    </span>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-cream-200 p-12 text-center text-gray-300">
                <span class="text-5xl block mb-3">🛒</span>
                <p class="text-gray-400">Este cliente aún no tiene compras registradas</p>
            </div>
            @endforelse
        </div>

        <div class="mt-4">{{ $ventas->links() }}</div>
    </div>
</div>
@endsection
