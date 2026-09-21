@props(['name', 'label', 'type' => 'text', 'required' => false, 'maxlength' => null, 'max' => null, 'placeholder' => '', 'options' => [], 'wide' => false, 'autocomplete' => 'off', 'id' => null])
@php
    $id = $id ?? 'reporte-' . $name;
    $valor = is_scalar(old($name)) ? old($name) : '';
@endphp
<div class="report-field {{ $wide ? 'report-field--wide' : '' }}">
    <label for="{{ $id }}">{{ $label }} @if ($required)<span aria-hidden="true">*</span>@else<span class="report-optional">Opcional</span>@endif</label>
    @if ($options)
        <select id="{{ $id }}" name="{{ $name }}" class="input" @required($required)
            @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $id }}-error" data-server-error @endif>
            <option value="">Selecciona una opción</option>
            @if ($valor !== '' && !array_key_exists($valor, $options))<option value="{{ $valor }}" selected>{{ $valor }}</option>@endif
            @foreach ($options as $value => $text)<option value="{{ $value }}" @selected($valor === $value)>{{ $text }}</option>@endforeach
        </select>
    @elseif ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" class="input" rows="3" placeholder="{{ $placeholder }}" @required($required)
            @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $id }}-error" data-server-error @endif>{{ $valor }}</textarea>
    @else
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" class="input" value="{{ $valor }}"
            placeholder="{{ $placeholder }}" autocomplete="{{ $autocomplete }}" @required($required)
            @if ($maxlength) maxlength="{{ $maxlength }}" @endif @if ($max) max="{{ $max }}" @endif
            @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $id }}-error" data-server-error @endif>
    @endif
    @error($name)<p class="report-field-error" id="{{ $id }}-error">{{ $message }}</p>@enderror
</div>
