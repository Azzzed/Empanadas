@extends('layouts.app')

@section('title', 'Punto de Venta')
@section('main-class', 'h-[calc(100vh-3.5rem)] overflow-hidden')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="flex h-full gap-0" x-data="posApp()" x-init="init()">

    {{-- ── PANEL IZQUIERDO: Catálogo de productos ─────────────── --}}
    <div class="flex-1 flex flex-col overflow-hidden bg-cream-50">

        {{-- Barra de búsqueda y filtros --}}
        <div class="p-3 bg-white border-b border-cream-200 shadow-sm">
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
                    <input type="text"
                           x-model="busqueda"
                           @input.debounce.300ms="filtrarProductos()"
                           placeholder="Buscar producto, relleno…"
                           class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 bg-cream-50
                                  focus:outline-none focus:ring-2 focus:ring-brand-400 text-sm">
                </div>
                <select x-model="filtroTipo" @change="filtrarProductos()"
                        class="px-3 py-2 rounded-xl border border-gray-200 bg-cream-50 text-sm
                               focus:outline-none focus:ring-2 focus:ring-brand-400 cursor-pointer">
                    <option value="">Todos</option>
                    <option value="empanada">🥟 Empanadas</option>
                    <option value="papa_rellena">🥔 Papas</option>
                </select>
            </div>
        </div>

        {{-- Grid de productos --}}
        <div class="flex-1 overflow-y-auto p-3">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">

                <template x-for="producto in productosFiltrados" :key="producto.id">
                    <button @click="agregarAlCarrito(producto)"
                            class="btn-ripple card-hover bg-white rounded-2xl p-3 text-left
                                   border-2 border-transparent hover:border-brand-300
                                   active:scale-95 transition-all shadow-sm group">
                        <div class="w-full aspect-square rounded-xl bg-cream-100 flex items-center
                                    justify-center mb-2 group-hover:bg-cream-200 transition-colors overflow-hidden">
                            <template x-if="producto.imagen_path">
                                <img :src="'/storage/' + producto.imagen_path"
                                     :alt="producto.nombre"
                                     class="w-full h-full object-cover">
                            </template>
                            <template x-if="!producto.imagen_path">
                                <span class="text-3xl"
                                      x-text="producto.tipo_producto === 'papa_rellena' ? '🥔' : '🥟'"></span>
                            </template>
                        </div>
                        <p class="font-semibold text-sm text-gray-800 leading-tight truncate"
                           x-text="producto.nombre"></p>
                        <p class="text-xs text-gray-500 truncate" x-text="producto.relleno"></p>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-brand-600 font-bold text-sm"
                                  x-text="formatPrecio(producto.precio)"></span>
                            <span class="text-xs text-gray-400 bg-cream-100 px-1.5 py-0.5 rounded-full"
                                  x-text="producto.tamano"></span>
                        </div>
                    </button>
                </template>

                <div x-show="productosFiltrados.length === 0"
                     class="col-span-full flex flex-col items-center justify-center py-16 text-gray-400">
                    <span class="text-5xl mb-3">🔍</span>
                    <p class="text-sm">Sin resultados para "<span x-text="busqueda"></span>"</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── PANEL DERECHO: Carrito ──────────────────────────────── --}}
    <div class="w-80 xl:w-96 flex flex-col bg-white border-l border-cream-200 shadow-xl">

        {{-- Header carrito + selector cliente --}}
        <div class="p-4 border-b border-cream-100">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-gray-800">Orden actual</h2>
                <button @click="limpiarCarrito()"
                        x-show="carrito.length > 0"
                        class="text-xs text-red-400 hover:text-red-600 hover:underline">
                    Limpiar ✕
                </button>
            </div>

            {{-- Selector de cliente --}}
            <div class="relative">
                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-cream-50 border border-cream-200
                            cursor-pointer hover:border-brand-300"
                     @click="mostrarBuscadorCliente = !mostrarBuscadorCliente">
                    <span class="text-lg">👤</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-gray-500">Cliente</p>
                        <p class="text-sm font-medium text-gray-800 truncate" x-text="clienteSeleccionado.nombre"></p>
                    </div>
                    <span class="text-gray-400 text-xs" x-text="mostrarBuscadorCliente ? '▲' : '▼'"></span>
                </div>

                {{-- Dropdown buscador --}}
                <div x-show="mostrarBuscadorCliente"
                     x-cloak
                     class="absolute top-full left-0 right-0 z-20 mt-1 bg-white rounded-2xl shadow-xl
                            border border-cream-200 fade-in overflow-hidden">
                    <div class="p-3 border-b border-cream-100">
                        <input type="text"
                               x-model="busquedaCliente"
                               @input.debounce.300ms="buscarClientes()"
                               placeholder="Nombre, documento o teléfono…"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-brand-400">
                    </div>

                    <div class="max-h-48 overflow-y-auto">
                        <button @click="seleccionarCliente({id:1, nombre:'Cliente de Mostrador'})"
                                class="w-full px-4 py-3 text-left hover:bg-cream-50 border-b border-cream-100">
                            <p class="text-sm font-medium">🏪 Cliente de Mostrador</p>
                            <p class="text-xs text-gray-400">Venta rápida sin datos</p>
                        </button>

                        <template x-for="c in clientesBusqueda" :key="c.id">
                            <button @click="seleccionarCliente(c)"
                                    class="w-full px-4 py-3 text-left hover:bg-cream-50 border-b border-cream-100">
                                <p class="text-sm font-medium" x-text="c.nombre"></p>
                                <p class="text-xs text-gray-400"
                                   x-text="c.tipo_documento + ': ' + c.numero_documento"></p>
                            </button>
                        </template>

                        {{-- Botón crear cliente --}}
                        <button @click="abrirFormCliente()"
        class="w-full px-4 py-3 text-left text-brand-600 hover:bg-brand-50 font-medium text-sm">
    ➕ Crear nuevo cliente
</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lista de ítems del carrito --}}
        <div class="flex-1 overflow-y-auto p-3 space-y-2">
            <template x-for="(item, idx) in carrito" :key="item.id">
                <div class="slide-in flex items-center gap-3 bg-cream-50 rounded-xl p-2.5">
                    <span class="text-xl"
                          x-text="item.tipo_producto === 'papa_rellena' ? '🥔' : '🥟'"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate" x-text="item.nombre"></p>
                        <p class="text-xs text-gray-500" x-text="item.relleno + ' · ' + item.tamano"></p>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button @click="decrementar(idx)"
                                class="w-7 h-7 rounded-full bg-white border border-gray-200 text-gray-600
                                       hover:bg-red-50 hover:border-red-200 hover:text-red-600
                                       flex items-center justify-center font-bold text-sm">−</button>
                        <span class="w-5 text-center text-sm font-bold" x-text="item.cantidad"></span>
                        <button @click="incrementar(idx)"
                                class="w-7 h-7 rounded-full bg-brand-500 text-white
                                       hover:bg-brand-600 flex items-center justify-center font-bold text-sm">+</button>
                    </div>
                    <span class="text-sm font-bold text-brand-600 w-16 text-right shrink-0"
                          x-text="formatPrecio(item.precio * item.cantidad)"></span>
                </div>
            </template>

            <div x-show="carrito.length === 0"
                 class="flex flex-col items-center justify-center h-full py-12 text-gray-300">
                <span class="text-6xl mb-3">🛒</span>
                <p class="text-sm text-center">Selecciona productos<br>del catálogo</p>
            </div>
        </div>

        {{-- Panel de totales y pago --}}
        <div class="p-4 border-t border-cream-200 space-y-3" x-show="carrito.length > 0" x-cloak>

            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 w-24 shrink-0">Descuento %</label>
                <input type="number" x-model="descuentoPct" min="0" max="100" step="1"
                       class="w-20 px-2 py-1.5 border border-gray-200 rounded-lg text-sm text-center
                              focus:outline-none focus:ring-2 focus:ring-brand-400"
                       placeholder="0">
                <span class="text-xs text-gray-400 ml-auto" x-show="descuentoPct > 0">
                    − <span x-text="formatPrecio(descuentoValor)"></span>
                </span>
            </div>

            <div class="bg-cream-50 rounded-xl p-3 space-y-1.5">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Subtotal (<span x-text="totalItems"></span> items)</span>
                    <span x-text="formatPrecio(subtotal)"></span>
                </div>
                <div class="flex justify-between text-sm text-gray-400" x-show="descuentoPct > 0">
                    <span>Descuento</span>
                    <span class="text-red-400">− <span x-text="formatPrecio(descuentoValor)"></span></span>
                </div>
                <div class="flex justify-between text-lg font-bold text-gray-900 border-t border-cream-200 pt-2 mt-1">
                    <span>Total</span>
                    <span class="text-brand-600" x-text="formatPrecio(total)"></span>
                </div>
            </div>

            {{-- Métodos de pago --}}
            <div class="grid grid-cols-3 gap-1.5">
                <template x-for="m in metodosPagoDisponibles" :key="m.id">
                    <button @click="toggleMetodoPago(m.id)"
                            :class="metodosSeleccionados.includes(m.id)
                                ? 'bg-brand-500 text-white border-brand-500'
                                : 'bg-white text-gray-600 border-gray-200 hover:border-brand-300'"
                            class="flex flex-col items-center justify-center p-2 rounded-xl border text-xs
                                   font-medium transition-all">
                        <span class="text-lg mb-0.5" x-text="m.icon"></span>
                        <span x-text="m.label"></span>
                    </button>
                </template>
            </div>

            <button @click="procesarVenta()"
                    :disabled="carrito.length === 0 || metodosSeleccionados.length === 0 || procesando"
                    class="w-full py-3.5 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-2xl
                           shadow-lg shadow-brand-200 active:scale-95 transition-all disabled:opacity-50
                           disabled:cursor-not-allowed btn-ripple text-base">
                <span x-show="!procesando">
                    💰 Cobrar <span x-text="formatPrecio(total)"></span>
                </span>
                <span x-show="procesando" x-cloak class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Procesando…
                </span>
            </button>
        </div>
    </div>
</div>

{{-- ── Modal: Crear cliente rápido ─────────────────────────────── --}}
<div x-show="mostrarFormCliente"
     x-cloak
     x-transition
     @click.self="mostrarFormCliente = false"
     class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl p-6 fade-in">
        <h3 class="font-bold text-xl mb-4 text-gray-800">Nuevo cliente</h3>
        <form @submit.prevent="crearClienteRapido()">
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="text-xs font-medium text-gray-500">Tipo doc.</label>
                    <select x-model="nuevoCliente.tipo_documento"
                            class="w-full mt-1 px-3 py-2 rounded-xl border border-gray-200 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-brand-400">
                        <option>CC</option>
                        <option>NIT</option>
                        <option>CE</option>
                        <option>PASAPORTE</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Número documento</label>
                    <input type="text" x-model="nuevoCliente.numero_documento" required
                           class="w-full mt-1 px-3 py-2 rounded-xl border border-gray-200 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
                <div class="col-span-2">
                    <label class="text-xs font-medium text-gray-500">Nombre completo</label>
                    <input type="text" x-model="nuevoCliente.nombre" required
                           class="w-full mt-1 px-3 py-2 rounded-xl border border-gray-200 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Teléfono</label>
                    <input type="text" x-model="nuevoCliente.telefono"
                           class="w-full mt-1 px-3 py-2 rounded-xl border border-gray-200 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Ciudad</label>
                    <input type="text" x-model="nuevoCliente.ciudad"
                           class="w-full mt-1 px-3 py-2 rounded-xl border border-gray-200 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
            </div>

            {{-- Mensaje de error --}}
            <p x-show="errorCliente" x-cloak x-text="errorCliente"
               class="text-xs text-red-500 mb-3 bg-red-50 p-2 rounded-lg"></p>

            <div class="flex gap-3">
                <button type="button" @click="mostrarFormCliente = false"
                        class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600
                               hover:bg-gray-50 font-medium text-sm">
                    Cancelar
                </button>
                <button type="submit"
                        class="flex-1 py-2.5 rounded-xl bg-brand-500 text-white font-bold
                               hover:bg-brand-600 transition-colors text-sm">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
{{-- posApp primero, Alpine después --}}
<script>
function posApp() {
    return {
        todosLosProductos: @json($productos->flatten()->values()),
        productosFiltrados: [],
        busqueda: '',
        filtroTipo: '',
        carrito: [],
        clienteSeleccionado: { id: 1, nombre: 'Cliente de Mostrador' },
        mostrarBuscadorCliente: false,
        busquedaCliente: '',
        clientesBusqueda: [],
        mostrarFormCliente: false,
        errorCliente: '',
        nuevoCliente: { tipo_documento: 'CC', numero_documento: '', nombre: '', telefono: '', ciudad: '' },
        descuentoPct: 0,
        metodosSeleccionados: [],
        procesando: false,
        metodosPagoDisponibles: [
            { id: 'efectivo',      label: 'Efectivo',     icon: '💵' },
            { id: 'transferencia', label: 'Transferencia', icon: '📱' },
            { id: 'tarjeta',       label: 'Tarjeta',       icon: '💳' },
        ],
        init() { this.productosFiltrados = this.todosLosProductos; },
        filtrarProductos() {
            const t = this.busqueda.toLowerCase();
            this.productosFiltrados = this.todosLosProductos.filter(p => {
                const matchTipo = !this.filtroTipo || p.tipo_producto === this.filtroTipo;
                const matchBusq = !t || p.nombre.toLowerCase().includes(t) || p.relleno.toLowerCase().includes(t);
                return matchTipo && matchBusq;
            });
        },
        agregarAlCarrito(producto) {
            const idx = this.carrito.findIndex(i => i.id === producto.id);
            idx >= 0 ? this.carrito[idx].cantidad++ : this.carrito.push({ ...producto, cantidad: 1 });
        },
        incrementar(idx) { this.carrito[idx].cantidad++; },
        decrementar(idx) { this.carrito[idx].cantidad > 1 ? this.carrito[idx].cantidad-- : this.carrito.splice(idx, 1); },
        limpiarCarrito() { this.carrito = []; this.descuentoPct = 0; this.metodosSeleccionados = []; },
        get totalItems() { return this.carrito.reduce((s, i) => s + i.cantidad, 0); },
        get subtotal()   { return this.carrito.reduce((s, i) => s + i.precio * i.cantidad, 0); },
        get descuentoValor() { return Math.round(this.subtotal * (this.descuentoPct / 100)); },
        get total()      { return this.subtotal - this.descuentoValor; },
        formatPrecio(v)  { return '$' + Math.round(v).toLocaleString('es-CO'); },
        toggleMetodoPago(id) {
            const idx = this.metodosSeleccionados.indexOf(id);
            idx >= 0 ? this.metodosSeleccionados.splice(idx, 1) : this.metodosSeleccionados.push(id);
        },
        async buscarClientes() {
            if (!this.busquedaCliente) { this.clientesBusqueda = []; return; }
            const res = await fetch(`/pos/buscar-clientes?q=${encodeURIComponent(this.busquedaCliente)}`);
            this.clientesBusqueda = await res.json();
        },
        seleccionarCliente(c) {
            this.clienteSeleccionado = c;
            this.mostrarBuscadorCliente = false;
            this.busquedaCliente = '';
            this.clientesBusqueda = [];
        },
        abrirFormCliente() {
            this.mostrarBuscadorCliente = false;
            this.mostrarFormCliente = true;
        },
        async crearClienteRapido() {
            this.errorCliente = '';
            try {
                const res = await fetch('/pos/cliente-rapido', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify(this.nuevoCliente),
                });
                const data = await res.json();
                if (data.success) {
                    this.seleccionarCliente(data.customer);
                    this.mostrarFormCliente = false;
                    this.nuevoCliente = { tipo_documento: 'CC', numero_documento: '', nombre: '', telefono: '', ciudad: '' };
                } else {
                    this.errorCliente = data.message || 'Error al guardar.';
                }
            } catch (e) { this.errorCliente = 'Error de conexión.'; }
        },
        async procesarVenta() {
            if (this.procesando) return;
            this.procesando = true;
            try {
                const res = await fetch('/pos/venta', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({
                        customer_id: this.clienteSeleccionado.id,
                        items: this.carrito.map(i => ({ product_id: i.id, cantidad: i.cantidad, precio_unitario: i.precio })),
                        descuento_porcentaje: this.descuentoPct,
                        metodos_pago: this.metodosSeleccionados.map(m => ({ metodo: m, monto: this.total })),
                    }),
                });
                const data = await res.json();
                if (data.success) {
                    window.open(`/pos/comprobante/${data.sale_id}`, '_blank');
                    this.limpiarCarrito();
                    this.clienteSeleccionado = { id: 1, nombre: 'Cliente de Mostrador' };
                } else { alert('Error: ' + data.message); }
            } catch (e) { alert('Error de conexión.'); }
            finally { this.procesando = false; }
        },
    };
}
</script>
{{-- Alpine DESPUÉS de posApp --}}
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush


