<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::reales();

        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }

        if ($request->filled('ciudad')) {
            $query->porCiudad($request->ciudad);
        }

        if ($request->filled('estado')) {
            $request->estado === 'activo'
                ? $query->where('activo', true)
                : $query->where('activo', false);
        }

        $clientes = $query->withCount('sales')
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        $ciudades = Customer::reales()->select('ciudad')
            ->whereNotNull('ciudad')
            ->distinct()
            ->orderBy('ciudad')
            ->pluck('ciudad');

        return view('admin.customers.index', compact('clientes', 'ciudades'));
    }

    public function create(): View
    {
        return view('admin.customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'tipo_documento'   => 'required|in:CC,NIT,CE,PASAPORTE',
            'numero_documento' => 'required|string|max:20|unique:customers,numero_documento',
            'nombre'           => 'required|string|max:150',
            'direccion'        => 'nullable|string|max:255',
            'ciudad'           => 'nullable|string|max:100',
            'telefono'         => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:150',
        ]);

        Customer::create($datos);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Cliente registrado exitosamente.');
    }

    public function show(Customer $customer): View
    {
        $ventas = $customer->sales()
            ->with('items.product')
            ->latest()
            ->paginate(10);

        $totalCompras = $customer->sales()->completadas()->sum('total');

        return view('admin.customers.show', compact('customer', 'ventas', 'totalCompras'));
    }

    public function edit(Customer $customer): View
    {
        if ($customer->es_mostrador) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'El cliente de mostrador no se puede editar.');
        }

        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        if ($customer->es_mostrador) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'El cliente de mostrador no se puede modificar.');
        }

        $datos = $request->validate([
            'tipo_documento'   => 'required|in:CC,NIT,CE,PASAPORTE',
            'numero_documento' => "required|string|max:20|unique:customers,numero_documento,{$customer->id}",
            'nombre'           => 'required|string|max:150',
            'direccion'        => 'nullable|string|max:255',
            'ciudad'           => 'nullable|string|max:100',
            'telefono'         => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:150',
        ]);

        $customer->update($datos);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->es_mostrador) {
            return back()->with('error', 'El cliente de mostrador no puede eliminarse.');
        }

        if ($customer->tieneSalesAsociadas()) {
            return back()->with('error',
                "No se puede eliminar a '{$customer->nombre}' porque tiene compras registradas. Puedes desactivarlo."
            );
        }

        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Cliente eliminado.');
    }
}