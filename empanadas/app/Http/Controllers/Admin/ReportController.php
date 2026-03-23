<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        // Período por defecto: mes actual
        $desde = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', now()->endOfMonth()->toDateString());

        // ── 1. Totales del período ───────────────────────────────────
        $totales = Sale::completadas()
            ->porPeriodo($desde . ' 00:00:00', $hasta . ' 23:59:59')
            ->selectRaw('
                COUNT(*)             AS total_ventas,
                SUM(total)           AS ingresos_totales,
                SUM(descuento)       AS descuentos_totales,
                AVG(total)           AS ticket_promedio
            ')
            ->first();

        // ── 2. Ventas por tipo de producto ───────────────────────────
        $ventasPorTipo = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.estado', 'completada')
            ->whereBetween('sales.created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->select(
                'products.tipo_producto',
                DB::raw('SUM(sale_items.cantidad) AS unidades_vendidas'),
                DB::raw('SUM(sale_items.subtotal) AS ingresos')
            )
            ->groupBy('products.tipo_producto')
            ->get();

        // ── 3. Top 5 productos más vendidos ─────────────────────────
        $topProductos = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.estado', 'completada')
            ->whereBetween('sales.created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->select(
                'products.nombre',
                'products.tipo_producto',
                DB::raw('SUM(sale_items.cantidad) AS unidades'),
                DB::raw('SUM(sale_items.subtotal) AS ingresos')
            )
            ->groupBy('products.id', 'products.nombre', 'products.tipo_producto')
            ->orderByDesc('unidades')
            ->limit(5)
            ->get();

        // ── 4. % Mostrador vs. Clientes específicos ──────────────────
        $totalVentas = Sale::completadas()
            ->porPeriodo($desde . ' 00:00:00', $hasta . ' 23:59:59')
            ->count();

        $ventasMostrador = Sale::completadas()
            ->deCLienetesMostrador()
            ->porPeriodo($desde . ' 00:00:00', $hasta . ' 23:59:59')
            ->count();

        $ventasEspecificas = $totalVentas - $ventasMostrador;
        $pctMostrador      = $totalVentas > 0 ? round($ventasMostrador / $totalVentas * 100, 1) : 0;
        $pctEspecificos    = 100 - $pctMostrador;

        // ── 5. Ventas por ciudad ─────────────────────────────────────
        $ventasPorCiudad = Sale::join('customers', 'sales.customer_id', '=', 'customers.id')
            ->where('sales.estado', 'completada')
            ->whereNotNull('customers.ciudad')
            ->where('customers.id', '!=', Customer::MOSTRADOR_ID)
            ->whereBetween('sales.created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->select(
                'customers.ciudad',
                DB::raw('COUNT(sales.id) AS total_ventas'),
                DB::raw('SUM(sales.total) AS ingresos')
            )
            ->groupBy('customers.ciudad')
            ->orderByDesc('ingresos')
            ->limit(10)
            ->get();

        // ── 6. Ventas por día (para gráfico de líneas) ───────────────
        $ventasPorDia = Sale::completadas()
            ->porPeriodo($desde . ' 00:00:00', $hasta . ' 23:59:59')
            ->select(
                DB::raw("DATE(created_at) AS fecha"),
                DB::raw('COUNT(*) AS ventas'),
                DB::raw('SUM(total) AS ingresos')
            )
            ->groupByRaw('DATE(created_at)')
            ->orderBy('fecha')
            ->get();

        return view('admin.reports.index', compact(
            'desde', 'hasta', 'totales',
            'ventasPorTipo', 'topProductos',
            'ventasMostrador', 'ventasEspecificas', 'pctMostrador', 'pctEspecificos',
            'ventasPorCiudad', 'ventasPorDia', 'totalVentas'
        ));
    }
}
