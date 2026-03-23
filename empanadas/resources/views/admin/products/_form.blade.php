{{-- Partial: admin/products/_form.blade.php --}}
{{-- Usar en create y edit. La variable $producto es opcional (edit). --}}

@php $editando = isset($product); @endphp

{{-- Errores globales --}}
@if($errors->any())
<div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl p-4">
    <p class="font-semibold text-sm mb-1">Por favor corrige los siguientes errores:</p>
    <ul class="text-sm space-y-0.5 list-disc pl-4">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="space-y-5">

    {{-- Tipo de producto --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de producto <span class="text-red-400">*</span></label>
        <div class="grid grid-cols-2 gap-3">
            @foreach(['empanada' => ['icon' => '🥟', 'label' => 'Empanada'],
                      'papa_rellena' => ['icon' => '🥔', 'label' => 'Papa Rellena']] as $val => $info)
            <label class="cursor-pointer">
                <input type="radio" name="tipo_producto" value="{{ $val }}"
                       class="sr-only peer"
                       {{ old('tipo_producto', $editando ? $product->tipo_producto : 'empanada') === $val ? 'checked' : '' }}>
                <div class="flex items-center gap-3 p-4 rounded-2xl border-2 border-gray-200
                            peer-checked:border-brand-500 peer-checked:bg-brand-50 hover:border-brand-300 transition-all">
                    <span class="text-2xl">{{ $info['icon'] }}</span>
                    <span class="font-semibold text-gray-700">{{ $info['label'] }}</span>
                </div>
            </label>
            @endforeach
        </div>
    </div>

    {{-- Nombre --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
            Nombre del producto <span class="text-red-400">*</span>
        </label>
        <input type="text" name="nombre" value="{{ old('nombre', $editando ? $product->nombre : '') }}"
               placeholder="Ej: Empanada de Pipián"
               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none
                      focus:ring-2 focus:ring-brand-400 @error('nombre') border-red-400 @enderror">
        @error('nombre') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- Relleno y Tamaño --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                Relleno <span class="text-red-400">*</span>
            </label>
            <input type="text" name="relleno" value="{{ old('relleno', $editando ? $product->relleno : '') }}"
                   placeholder="Ej: Carne, Pollo, Queso"
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none
                          focus:ring-2 focus:ring-brand-400 @error('relleno') border-red-400 @enderror">
            @error('relleno') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                Tamaño <span class="text-red-400">*</span>
            </label>
            <select name="tamano"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none
                           focus:ring-2 focus:ring-brand-400 @error('tamano') border-red-400 @enderror">
                @foreach(['Personal', 'Mediana', 'Grande', 'Familiar', 'Miniatura'] as $t)
                <option value="{{ $t }}" {{ old('tamano', $editando ? $product->tamano : '') === $t ? 'selected' : '' }}>
                    {{ $t }}
                </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Precio y Stock --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                Precio <span class="text-red-400">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">$</span>
                <input type="number" name="precio" min="0" step="100"
                       value="{{ old('precio', $editando ? $product->precio : '') }}"
                       placeholder="5000"
                       class="w-full pl-8 pr-4 py-3 rounded-xl border border-gray-200 focus:outline-none
                              focus:ring-2 focus:ring-brand-400 @error('precio') border-red-400 @enderror">
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Stock del día</label>
            <input type="number" name="stock" min="0"
                   value="{{ old('stock', $editando ? $product->stock : 0) }}"
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none
                          focus:ring-2 focus:ring-brand-400">
        </div>
    </div>

    {{-- Descripción --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Descripción</label>
        <textarea name="descripcion" rows="3"
                  placeholder="Describe ingredientes, origen o características especiales…"
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none
                         focus:ring-2 focus:ring-brand-400 resize-none">{{ old('descripcion', $editando ? $product->descripcion : '') }}</textarea>
    </div>

    {{-- Imagen (placeholder para escalar) --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
            Imagen de referencia
            <span class="text-xs font-normal text-gray-400">(JPG, PNG o WebP, máx 2 MB)</span>
        </label>
        <div class="flex items-center gap-4">
            @if($editando && $product->imagen_path)
            <img src="{{ Storage::url($product->imagen_path) }}" alt="Imagen actual"
                 class="w-20 h-20 rounded-xl object-cover border border-cream-200">
            @else
            <div class="w-20 h-20 rounded-xl bg-cream-100 flex items-center justify-center text-gray-300 text-3xl border-2 border-dashed border-cream-200">
                📷
            </div>
            @endif
            <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp"
                   class="block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl
                          file:border-0 file:text-sm file:font-medium file:bg-cream-100 file:text-brand-700
                          hover:file:bg-cream-200 cursor-pointer">
        </div>
    </div>

    {{-- Activo --}}
    <div class="flex items-center gap-3 p-4 bg-cream-50 rounded-xl">
        <input type="hidden" name="activo" value="0">
        <input type="checkbox" id="activo" name="activo" value="1"
               {{ old('activo', $editando ? $product->activo : true) ? 'checked' : '' }}
               class="w-5 h-5 rounded accent-brand-500 cursor-pointer">
        <label for="activo" class="text-sm font-medium text-gray-700 cursor-pointer">
            Producto activo (visible en el POS)
        </label>
    </div>

    {{-- Botones --}}
    <div class="flex gap-3 pt-2">
        <a href="{{ route('admin.products.index') }}"
           class="flex-1 text-center py-3 rounded-xl border border-gray-200 text-gray-600
                  hover:bg-gray-50 font-medium transition-colors">
            Cancelar
        </a>
        <button type="submit"
                class="flex-1 py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl
                       shadow-md shadow-brand-200 transition-all active:scale-95">
            {{ $editando ? '💾 Guardar cambios' : '✅ Crear producto' }}
        </button>
    </div>
</div>
