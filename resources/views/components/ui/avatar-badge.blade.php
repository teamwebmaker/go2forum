@props([
    'iconClass' => '',
    'iconSizeClass' => 'size-4!',
    'badgeClass' => '',
    'badgeType' => 's',  // o|s|m|c
    'wrapperClass' => '',
])

<span class="{{ $wrapperClass }}">
  <span class="relative flex items-center justify-center rounded-full {{ $badgeClass }}">
    
    {{-- white plug behind the check (circular, not square) --}}
    <span
      class="absolute inset-0 m-auto rounded-full bg-white"
      style="width: 70%; height: 70%;"
      aria-hidden="true"
    ></span>

    <x-app-icon
      name="check-badge"
      variant="{{ $badgeType }}"
      class="{{ $iconSizeClass }} {{ $iconClass }} relative"
    />
  </span>
</span>
