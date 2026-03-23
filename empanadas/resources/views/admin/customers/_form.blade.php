{{-- Partial: admin/customers/_form.blade.php --}}
@php $editando = isset($customer); @endphp

@if($errors->any())
<div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl p-4">
    <p class="font-semibold text-sm mb-1">Por favor corrige los siguientes errores:</p>
    <ul class="text-sm space-y-0.5 list-disc pl-4">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<div class="space-y-5">

    {{-- Tipo + Número documento --}}
    <div class="grid grid-cols-5 gap-3">
        <div class="col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                Tipo <span class="text-red-400">*</span>
            </label>
            <select name="tipo_documento"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none
                           focus:ring-2 focus:ring-brand-400 @error('tipo_documento') border-red-400 @enderror">
                @foreach(['CC' => 'Cédula (CC)', 'NIT' => 'NIT', 'CE' => 'Cédula Extranjera', 'PASAPORTE' => 'Pasaporte'] as $val => $label)
                <option value="{{ $val }}"
                    {{ old('tipo_documento', $editando ? $customer->tipo_documento : 'CC') === $val ? 'selected' : '' }}>
                    {{ $label }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-span-3">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                Número de documento <span class="text-red-400">*</span>
            </label>
            <input type="text" name="numero_documento"
                   value="{{ old('numero_documento', $editando ? $customer->numero_documento : '') }}"
                   placeholder="Ej: 1023456789"
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none
                          focus:ring-2 focus:ring-brand-400 @error('numero_documento') border-red-400 @enderror">
            @error('numero_documento')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Nombre completo --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
            Nombre completo <span class="text-red-400">*</span>
        </label>
        <input type="text" name="nombre"
               value="{{ old('nombre', $editando ? $customer->nombre : '') }}"
               placeholder="Ej: María Fernanda Torres"
               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none
                      focus:ring-2 focus:ring-brand-400 @error('nombre') border-red-400 @enderror">
        @error('nombre')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    {{-- Dirección --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Dirección</label>
        <input type="text" name="direccion"
               value="{{ old('direccion', $editando ? $customer->direccion : '') }}"
               placeholder="Ej: Cra 15 #45-20, Apt 301"
               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none
                      focus:ring-2 focus:ring-brand-400">
    </div>

    {{-- Ciudad y Teléfono --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Ciudad</label>
            <input type="text" name="ciudad"
                   value="{{ old('ciudad', $editando ? $customer->ciudad : '') }}"
                   placeholder="Ej: Bogotá"
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none
                          focus:ring-2 focus:ring-brand-400">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Teléfono</label>
            <input type="text" name="telefono"
                   value="{{ old('telefono', $editando ? $customer->telefono : '') }}"
                   placeholder="Ej: 3001234567"
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none
                          focus:ring-2 focus:ring-brand-400">
        </div>
    </div>

    {{-- Email --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Correo electrónico</label>
        <input type="email" name="email"
               value="{{ old('email', $editando ? $customer->email : '') }}"
               placeholder="ejemplo@correo.com"
               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none
                      focus:ring-2 focus:ring-brand-400 @error('email') border-red-400 @enderror">
        @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    {{-- Activo --}}
    <div class="flex items-center gap-3 p-4 bg-cream-50 rounded-xl">
        <input type="hidden" name="activo" value="0">
        <input type="checkbox" id="activo" name="activo" value="1"
               {{ old('activo', $editando ? $customer->activo : true) ? 'checked' : '' }}
               class="w-5 h-5 rounded accent-brand-500 cursor-pointer">
        <label for="activo" class="text-sm font-medium text-gray-700 cursor-pointer">
            Cliente activo
        </label>
    </div>

    {{-- Botones --}}
    <div class="flex gap-3 pt-2">
        <a href="{{ route('admin.customers.index') }}"
           class="flex-1 text-center py-3 rounded-xl border border-gray-200 text-gray-600
                  hover:bg-gray-50 font-medium transition-colors">
            Cancelar
        </a>
        <button type="submit"
                class="flex-1 py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl
                       shadow-md shadow-brand-200 transition-all active:scale-95">
            {{ $editando ? '💾 Guardar cambios' : '✅ Registrar cliente' }}
        </button>
    </div>
</div>
