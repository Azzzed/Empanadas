<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    // ---------------------------------------------------------------
    // Vista principal del POS
    // ---------------------------------------------------------------

    public function index(): View
    {
        $productos = Product::activos()
            ->orderBy('tipo_producto')
            ->orderBy('nombre')
            ->get()
            ->groupBy('tipo_producto');

        $clienteMostrador = Customer::find(Customer::MOSTRADOR_ID);

        return view('pos.index', compact('productos', 'clienteMostrador'));
    }

    // ---------------------------------------------------------------
    // Búsqueda rápida de productos (AJAX)
    // ---------------------------------------------------------------

    public function buscarProductos(Request $request): JsonResponse
    {
        $termino = $request->get('q', '');

        $productos = Product::activos()
            ->buscar($termino)
            ->select('id', 'nombre', 'precio', 'relleno', 'tamano', 'tipo_producto', 'stock')
            ->limit(10)
            ->get();

        return response()->json($productos);
    }

    // ---------------------------------------------------------------
    // Búsqueda rápida de clientes (AJAX)
    // ---------------------------------------------------------------

    public function buscarClientes(Request $request): JsonResponse
    {
        $termino = $request->get('q', '');

        $clientes = Customer::activos()
            ->reales()
            ->buscar($termino)
            ->select('id', 'nombre', 'numero_documento', 'tipo_documento', 'telefono', 'ciudad')
            ->limit(8)
            ->get();

        return response()->json($clientes);
    }

    // ---------------------------------------------------------------
    // Procesar la venta
    // ---------------------------------------------------------------

    public function procesarVenta(Request $request): JsonResponse
{
    $request->validate([
        'customer_id'             => 'nullable|exists:customers,id',
        'items'                   => 'required|array|min:1',
        'items.*.product_id'      => 'required|exists:products,id',
        'items.*.cantidad'        => 'required|integer|min:1',
        'items.*.precio_unitario' => 'required|numeric|min:0',
        'descuento_porcentaje'    => 'nullable|numeric|min:0|max:100',
        'metodos_pago'            => 'required|array|min:1',
        'metodos_pago.*.metodo'   => 'required|string',
        'metodos_pago.*.monto'    => 'required|numeric|min:0',
        'notas'                   => 'nullable|string|max:500',
    ]);

    $customerId    = $request->customer_id ?? Customer::MOSTRADOR_ID;
    $descuentoPct  = $request->descuento_porcentaje ?? 0;

    // 1. Crear la venta
    $sale = Sale::create([
        'customer_id'          => $customerId,
        'descuento_porcentaje' => $descuentoPct,
        'metodos_pago'         => $request->metodos_pago,
        'estado'               => 'completada',
        'notas'                => $request->notas,
        'cajero_id'            => auth()->id(),
    ]);

    // 2. Crear los ítems
    foreach ($request->items as $itemData) {
        $sale->items()->create([
            'product_id'      => $itemData['product_id'],
            'cantidad'        => $itemData['cantidad'],
            'precio_unitario' => $itemData['precio_unitario'],
            'descuento_item'  => 0,
            'notas_item'      => $itemData['notas_item'] ?? null,
        ]);
    }

    // 3. Recalcular totales
    $sale->load('items');
    $sale->recalcularTotales();

    return response()->json([
        'success'          => true,
        'sale_id'          => $sale->id,
        'numero_factura'   => $sale->numero_factura,
        'total'            => $sale->total,
        'total_formateado' => $sale->total_formateado,
    ]);
}

    // ---------------------------------------------------------------
    // Ver detalle / comprobante de una venta (para imprimir)
    // ---------------------------------------------------------------

    public function comprobante(Sale $sale): View
    {
        $sale->load(['customer', 'items.product']);

        return view('pos.comprobante', compact('sale'));
    }

    // ---------------------------------------------------------------
    // Crear cliente rápido desde el POS (AJAX)
    // ---------------------------------------------------------------

    public function crearClienteRapido(Request $request): JsonResponse
    {
        $request->validate([
            'tipo_documento'   => 'required|string|in:CC,NIT,CE,PASAPORTE',
            'numero_documento' => 'required|string|max:20|unique:customers,numero_documento',
            'nombre'           => 'required|string|max:150',
            'telefono'         => 'nullable|string|max:20',
            'ciudad'           => 'nullable|string|max:100',
        ]);

        $cliente = Customer::create($request->only([
            'tipo_documento', 'numero_documento', 'nombre', 'telefono', 'ciudad',
        ]));

        return response()->json([
            'success'  => true,
            'customer' => $cliente->only('id', 'nombre', 'numero_documento', 'tipo_documento', 'ciudad'),
        ]);
    }
}
