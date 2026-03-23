<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query();

        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo_producto', $request->tipo);
        }

        if ($request->filled('relleno')) {
            $query->porRelleno($request->relleno);
        }

        $productos = $query->withTrashed()
            ->orderBy('tipo_producto')
            ->orderBy('nombre')
            ->paginate(16)
            ->withQueryString();

        $rellenos = Product::select('relleno')->distinct()->pluck('relleno');

        return view('admin.products.index', compact('productos', 'rellenos'));
    }

    public function create(): View
    {
        return view('admin.products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre'        => 'required|string|max:150',
            'descripcion'   => 'nullable|string',
            'precio'        => 'required|numeric|min:0',
            'relleno'       => 'required|string|max:100',
            'tamano'        => 'required|string|max:50',
            'tipo_producto' => 'required|in:empanada,papa_rellena',
            'activo'        => 'boolean',
            'stock'         => 'integer|min:0',
            'imagen'        => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('imagen')) {
            $datos['imagen_path'] = $request->file('imagen')->store('productos', 'public');
        }

        Product::create($datos);

        return redirect()->route('admin.products.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    public function show(Product $product): View
    {
        $product->load('saleItems.sale');

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $datos = $request->validate([
            'nombre'        => 'required|string|max:150',
            'descripcion'   => 'nullable|string',
            'precio'        => 'required|numeric|min:0',
            'relleno'       => 'required|string|max:100',
            'tamano'        => 'required|string|max:50',
            'tipo_producto' => 'required|in:empanada,papa_rellena',
            'activo'        => 'boolean',
            'stock'         => 'integer|min:0',
            'imagen'        => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($product->imagen_path) {
                Storage::disk('public')->delete($product->imagen_path);
            }
            $datos['imagen_path'] = $request->file('imagen')->store('productos', 'public');
        }

        $product->update($datos);

        return redirect()->route('admin.products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Restricción de negocio: no borrar si tiene ventas
        if ($product->tieneVentasAsociadas()) {
            return back()->with('error',
                "No se puede eliminar '{$product->nombre}' porque tiene ventas asociadas. Puedes desactivarlo en su lugar."
            );
        }

        $product->delete(); // SoftDelete

        return redirect()->route('admin.products.index')
            ->with('success', 'Producto eliminado.');
    }

    /** Activar/desactivar producto rápidamente (toggle) */
    public function toggleActivo(Product $product): RedirectResponse
    {
        $product->update(['activo' => ! $product->activo]);

        $estado = $product->activo ? 'activado' : 'desactivado';

        return back()->with('success', "Producto {$estado}.");
    }
}
