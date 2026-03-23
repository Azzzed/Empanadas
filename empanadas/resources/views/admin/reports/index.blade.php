@extends('layouts.app')
@section('title', 'Informes de Ventas')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- Header + selector de período --}}
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Informes de Ventas</h1>
        <p class="text-sm text-gray-500 mt-0.5">Período: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</p>
    </div>
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="text-xs font-medium text-gray-500 block mb-1">Desde</label>
            <input type="date" name="desde" value="{{ $desde }}"
                   class="px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500 block mb-1">Hasta</label>
            <input type="date" name="hasta" value="{{ $hasta }}"
                   class="px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
        </div>
        <button type="submit"
                class="px-5 py-2 bg-brand-500 text-white text-sm font-semibold rounded-xl
                       hover:bg-brand-600 transition-colors shadow-md shadow-brand-200">
            Consultar
        </button>
    </form>
</div>

{{-- ── KPI Cards ──────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @php
        $kpis = [
            ['label' => 'Total ventas',     'valor' => number_format($totales->total_ventas ?? 0),
             'icon' => '🧾', 'color' => 'from-brand-500 to-brand-700'],
            ['label' => 'Ingresos',         'valor' => '$'.number_format($totales->ingresos_totales ?? 0, 0, ',', '.'),
             'icon' => '💰', 'color' => 'from-emerald-500 to-emerald-700'],
            ['label' => 'Ticket promedio',  'valor' => '$'.number_format($totales->ticket_promedio ?? 0, 0, ',', '.'),
             'icon' => '📊', 'color' => 'from-amber-500 to-orange-600'],
            ['label' => 'Descuentos',       'valor' => '$'.number_format($totales->descuentos_totales ?? 0, 0, ',', '.'),
             'icon' => '🏷️', 'color' => 'from-rose-400 to-rose-600'],
        ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="bg-gradient-to-br {{ $kpi['color'] }} rounded-3xl p-5 text-white shadow-lg">
        <p class="text-3xl mb-1">{{ $kpi['icon'] }}</p>
        <p class="text-2xl font-bold leading-tight">{{ $kpi['valor'] }}</p>
        <p class="text-sm text-white/80 mt-1">{{ $kpi['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- ── Fila: Gráfico de ventas por día + Donut mostrador vs específicos ── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- Gráfico de línea: ventas por día --}}
    <div class="lg:col-span-2 bg-white rounded-3xl border border-cream-200 shadow-sm p-6">
        <h2 class="font-bold text-gray-800 mb-4">Ingresos por día</h2>
        <div class="relative h-56">
            <canvas id="chartDia"></canvas>
        </div>
    </div>

    {{-- Donut: Mostrador vs Específicos --}}
    <div class="bg-white rounded-3xl border border-cream-200 shadow-sm p-6 flex flex-col">
        <h2 class="font-bold text-gray-800 mb-4">Tipo de cliente</h2>
        <div class="relative h-44 flex-1">
            <canvas id="chartClientes"></canvas>
        </div>
        <div class="mt-4 space-y-2">
            <div class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-brand-500 inline-block"></span>
                    <span class="text-gray-600">Mostrador</span>
                </div>
                <span class="font-bold text-gray-800">{{ $pctMostrador }}% <span class="font-normal text-gray-400">({{ $ventasMostrador }})</span></span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span>
                    <span class="text-gray-600">Específicos</span>
                </div>
                <span class="font-bold text-gray-800">{{ $pctEspecificos }}% <span class="font-normal text-gray-400">({{ $ventasEspecificas }})</span></span>
            </div>
        </div>
    </div>
</div>

{{-- ── Fila: Ventas por tipo + Top productos + Ciudades ── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Ventas por tipo de producto --}}
    <div class="bg-white rounded-3xl border border-cream-200 shadow-sm p-6">
        <h2 class="font-bold text-gray-800 mb-4">Por tipo de producto</h2>
        <div class="relative h-48">
            <canvas id="chartTipo"></canvas>
        </div>
        <div class="mt-4 space-y-2">
            @foreach($ventasPorTipo as $tipo)
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">
                    {{ $tipo->tipo_producto === 'papa_rellena' ? '🥔 Papa Rellena' : '🥟 Empanada' }}
                </span>
                <div class="text-right">
                    <span class="font-bold text-gray-800">{{ number_format($tipo->unidades_vendidas) }} uds</span>
                    <span class="text-gray-400 ml-2 text-xs">${{ number_format($tipo->ingresos, 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Top 5 productos --}}
    <div class="bg-white rounded-3xl border border-cream-200 shadow-sm p-6">
        <h2 class="font-bold text-gray-800 mb-4">🏆 Top 5 productos</h2>
        <div class="space-y-3">
            @foreach($topProductos as $idx => $prod)
            <div class="flex items-center gap-3">
                <span class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold shrink-0
                             {{ $idx === 0 ? 'bg-yellow-100 text-yellow-700'
                               : ($idx === 1 ? 'bg-gray-100 text-gray-600'
                               : ($idx === 2 ? 'bg-amber-50 text-amber-700' : 'bg-cream-100 text-gray-500')) }}">
                    {{ $idx + 1 }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $prod->nombre }}</p>
                    <div class="w-full bg-cream-100 rounded-full h-1.5 mt-1">
                        <div class="bg-brand-500 h-1.5 rounded-full transition-all"
                             style="width: {{ $topProductos->first() ? ($prod->unidades / $topProductos->first()->unidades * 100) : 0 }}%">
                        </div>
                    </div>
                </div>
                <span class="text-sm font-bold text-gray-600 shrink-0">{{ number_format($prod->unidades) }}</span>
            </div>
            @endforeach
            @if($topProductos->isEmpty())
            <p class="text-gray-300 text-sm text-center py-6">Sin datos en el período</p>
            @endif
        </div>
    </div>

    {{-- Ventas por ciudad --}}
    <div class="bg-white rounded-3xl border border-cream-200 shadow-sm p-6">
        <h2 class="font-bold text-gray-800 mb-4">📍 Por ciudad</h2>
        <div class="space-y-3">
            @forelse($ventasPorCiudad as $ciudad)
            <div class="flex items-center gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-baseline mb-1">
                        <span class="text-sm font-medium text-gray-700 truncate">{{ $ciudad->ciudad }}</span>
                        <span class="text-xs text-gray-400 shrink-0 ml-2">{{ $ciudad->total_ventas }} ventas</span>
                    </div>
                    <div class="w-full bg-cream-100 rounded-full h-1.5">
                        @php $maxIngreso = $ventasPorCiudad->first()->ingresos ?? 1; @endphp
                        <div class="bg-amber-400 h-1.5 rounded-full"
                             style="width: {{ ($ciudad->ingresos / $maxIngreso * 100) }}%"></div>
                    </div>
                </div>
                <span class="text-xs font-bold text-gray-600 shrink-0 w-20 text-right">
                    ${{ number_format($ciudad->ingresos, 0, ',', '.') }}
                </span>
            </div>
            @empty
            <div class="text-center py-8 text-gray-300">
                <span class="text-4xl block mb-2">📍</span>
                <p class="text-sm">No hay ventas a clientes específicos<br>en este período</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Paleta de colores marca
    const brand  = '#f47320';
    const amber  = '#f59e0b';
    const cream  = '#f9f1e4';
    const gray   = '#e5e7eb';

    Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
    Chart.defaults.color = '#6b7280';

    // ── Gráfico ventas por día ──────────────────────────────────
    const diasData = @json($ventasPorDia);
    new Chart(document.getElementById('chartDia'), {
        type: 'line',
        data: {
            labels: diasData.map(d => {
                const f = new Date(d.fecha + 'T00:00:00');
                return f.toLocaleDateString('es-CO', { day: '2-digit', month: 'short' });
            }),
            datasets: [{
                label: 'Ingresos',
                data: diasData.map(d => d.ingresos),
                borderColor: brand,
                backgroundColor: 'rgba(244,115,32,0.10)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: brand,
                pointRadius: 4,
                pointHoverRadius: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: cream } },
                y: {
                    grid: { color: cream },
                    ticks: { callback: v => '$' + Number(v).toLocaleString('es-CO') },
                },
            },
        },
    });

    // ── Donut Mostrador vs Específicos ─────────────────────────
    new Chart(document.getElementById('chartClientes'), {
        type: 'doughnut',
        data: {
            labels: ['Mostrador', 'Específicos'],
            datasets: [{
                data: [{{ $ventasMostrador }}, {{ $ventasEspecificas }}],
                backgroundColor: [brand, amber],
                borderWidth: 0,
                hoverOffset: 8,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.raw} ventas`
                    }
                },
            },
        },
    });

    // ── Barras tipo producto ────────────────────────────────────
    const tipoData = @json($ventasPorTipo);
    new Chart(document.getElementById('chartTipo'), {
        type: 'bar',
        data: {
            labels: tipoData.map(t => t.tipo_producto === 'papa_rellena' ? '🥔 Papa' : '🥟 Empanada'),
            datasets: [{
                label: 'Unidades',
                data: tipoData.map(t => t.unidades_vendidas),
                backgroundColor: [brand, amber],
                borderRadius: 10,
                borderSkipped: false,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { color: cream }, beginAtZero: true },
            },
        },
    });
</script>
@endpush

@endsection
