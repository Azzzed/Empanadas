<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante {{ $sale->numero_factura }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                colors: {
                    brand: { 500: '#f47320', 600: '#e05a10' },
                    cream: { 50: '#fdfaf5', 100: '#f9f1e4' },
                }
            }}
        }
    </script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-card { box-shadow: none !important; border: none !important; }
        }
        body { background: #f9f1e4; font-family: 'Inter', system-ui, sans-serif; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="min-h-screen flex items-start justify-center py-8 px-4">

    {{-- Botones de acción (no imprimen) --}}
    <div class="no-print fixed top-4 right-4 flex gap-2 z-10">
        <button onclick="window.print()"
                class="flex items-center gap-2 px-4 py-2 bg-brand-500 text-white font-semibold
                       rounded-xl shadow-lg hover:bg-brand-600 active:scale-95 transition-all text-sm">
            🖨️ Imprimir
        </button>
        <a href="{{ route('pos.index') }}"
           class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-600
                  font-medium rounded-xl shadow hover:bg-gray-50 transition-colors text-sm">
            ← Nueva venta
        </a>
    </div>

    {{-- Comprobante --}}
    <div class="print-card w-full max-w-sm bg-white rounded-3xl shadow-xl overflow-hidden">

        {{-- Header con logo --}}
        <div class="bg-gradient-to-br from-brand-500 to-brand-600 px-6 py-8 text-white text-center">
            <p class="text-4xl mb-2">🫓</p>
            <h1 class="text-xl font-bold">EmpanadasPOS</h1>
            <p class="text-white/70 text-xs mt-1">Tu sabor de siempre</p>
        </div>

        <div class="px-6 py-5 space-y-4">

            {{-- Info factura --}}
            <div class="text-center border-b border-dashed border-cream-200 pb-4">
                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Comprobante de venta</p>
                <p class="text-2xl font-bold text-brand-600 font-mono">{{ $sale->numero_factura }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $sale->created_at->format('d/m/Y H:i:s') }}</p>
            </div>

            {{-- Cliente --}}
            <div class="text-sm">
                <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Cliente</p>
                @if($sale->es_mostrador)
                <p class="font-medium text-gray-700">🏪 Cliente de Mostrador</p>
                @else
                <p class="font-semibold text-gray-800">{{ $sale->customer->nombre }}</p>
                <p class="text-gray-500 text-xs">{{ $sale->customer->documento_completo }}</p>
                @if($sale->customer->ciudad)
                <p class="text-gray-400 text-xs">📍 {{ $sale->customer->ciudad }}</p>
                @endif
                @endif
            </div>

            {{-- Ítems --}}
            <div class="border-t border-dashed border-cream-200 pt-4">
                <p class="text-xs text-gray-400 uppercase tracking-widest mb-3">Detalle</p>
                <div class="space-y-2.5">
                    @foreach($sale->items as $item)
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 leading-snug">
                                {{ $item->product->tipo_producto === 'papa_rellena' ? '🥔' : '🥟' }}
                                {{ $item->product->nombre }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ $item->cantidad }} × ${{ number_format($item->precio_unitario, 0, ',', '.') }}
                            </p>
                            @if($item->notas_item)
                            <p class="text-xs text-brand-500 italic">💬 {{ $item->notas_item }}</p>
                            @endif
                        </div>
                        <span class="text-sm font-semibold text-gray-800 shrink-0">
                            ${{ number_format($item->subtotal, 0, ',', '.') }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Totales --}}
            <div class="border-t border-dashed border-cream-200 pt-4 space-y-1.5">
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Subtotal</span>
                    <span>${{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($sale->descuento > 0)
                <div class="flex justify-between text-sm text-red-400">
                    <span>Descuento ({{ $sale->descuento_porcentaje }}%)</span>
                    <span>− ${{ number_format($sale->descuento, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between text-lg font-bold text-gray-900 pt-1 border-t border-cream-200 mt-2">
                    <span>TOTAL</span>
                    <span class="text-brand-600">{{ $sale->total_formateado }}</span>
                </div>
            </div>

            {{-- Métodos de pago --}}
            @if($sale->metodos_pago)
            <div class="flex flex-wrap gap-2">
                @foreach($sale->metodos_pago as $mp)
                <span class="text-xs bg-cream-100 text-gray-600 px-3 py-1 rounded-full">
                    {{ match($mp['metodo']) {
                        'efectivo' => '💵 Efectivo',
                        'transferencia' => '📱 Transferencia',
                        'tarjeta' => '💳 Tarjeta',
                        default => $mp['metodo']
                    } }}
                </span>
                @endforeach
            </div>
            @endif

            {{-- Estado --}}
            <div class="text-center pt-2 pb-4">
                <span class="inline-block px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest
                             {{ $sale->estado === 'completada' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-500' }}">
                    {{ $sale->estado === 'completada' ? '✅ Venta completada' : '❌ ' . $sale->estado }}
                </span>
            </div>

            {{-- Footer --}}
            <div class="border-t border-dashed border-cream-200 pt-4 text-center space-y-1">
                <p class="text-xs text-gray-400">¡Gracias por tu compra!</p>
                <p class="text-xs text-gray-300">{{ config('app.name') }} · {{ now()->format('Y') }}</p>
            </div>
        </div>
    </div>

</body>
</html>
