@props([
    'id' => null,
    'name',
    'type' => 'text',

    'label' => null,
    'required' => false,

    'iconPosition' => 'left', // left | right
    'iconPadding' => '',

    'minlength' => null,
    'maxlength' => null,

    'value' => null,
    'placeholder' => null,

    'accept' => null,
    'isImage' => false,

    'displayError' => true,
    'infoMessage' => null,

    'inputClass' => '',
])

@php
    $id = $id ?? $name;

    // old() wins except for file inputs and passwords
    $currentValue = in_array($type, ['file', 'password'], true) ? null : old($name, $value);

    $hasError = $displayError && $errors->has($name); 

    $autocomplete_name = in_array($type, ['tel', 'email'], true) ? $name : "off";

    
    // detect named slot <x-slot:icon>
    $hasIcon = isset($icon) && $icon instanceof Illuminate\View\ComponentSlot && $icon->isNotEmpty();
    
   $baseInput = 'block w-full rounded-md border bg-white text-black text-sm shadow-sm
    placeholder:text-gray-400
    focus:outline-none focus:ring-2 focus:ring-gray-700/10

    file:mr-4
    file:rounded-md
    file:border-0
    file:bg-gray-100
    file:px-2
    file:py-1
    file:font-medium
    file:text-gray-700
    hover:file:bg-gray-200
    ';

    $inputPaddingClass = $type === 'file'
    ? 'px-1 py-1'
    : 'px-3 py-2';

    $stateInput = $hasError
        ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
        : 'border-gray-300 focus:border-gray-400';

    $iconPadding = $iconPadding ?: ($hasIcon ? ($iconPosition === 'left' ? 'pl-10' : 'pr-10') : '');

    $inputAttributes = $attributes
        ->class([
            $baseInput, 
            $inputPaddingClass,
            $stateInput,
            $iconPadding,
            trim($inputClass)
        ])
        ->merge([
            'type' => $type,
            'id' => $id,
            'name' => $name,
            'placeholder' => $placeholder,
            'minlength' => $minlength,
            'maxlength' => $maxlength,
            'autocomplete' => $autocomplete_name,
        ]);

    if ($type === 'file' && $accept) {
        $inputAttributes = $inputAttributes->merge(['accept' => $accept]);
    }
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
            @if($required)
                <span class="text-red-600 ms-1">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        {{-- LEFT ICON --}}
        @if($hasIcon && $iconPosition === 'left')
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                {{ $icon }}
            </div>
        @endif

        <input
            {{ $inputAttributes }}
            @required($required)
            @if(!is_null($currentValue)) value="{{ $currentValue }}" @endif
        >

        {{-- RIGHT ICON --}}
        @if($hasIcon && $iconPosition === 'right')
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                {{ $icon }}
            </div>
        @endif
    </div>

    {{-- ERROR --}}
    @if ($displayError)
        @error($name)
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    @endif

    {{-- INFO --}}
    @if ($infoMessage)
        <p class="text-sm text-gray-500">{{ $infoMessage }}</p>
    @elseif ($type === 'file' && $isImage)
        <p class="text-sm text-gray-500">
            Supported formats: JPG, PNG, WEBP. Max size: 2MB
        </p>
    @endif
</div>
