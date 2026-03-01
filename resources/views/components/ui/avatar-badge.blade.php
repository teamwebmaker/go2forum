@props([
    'iconName' => 'check-badge',
    'iconClass' => '',
    'iconSizeClass' => 'size-4!',
    'badgeClass' => '',
    'badgeType' => 's',  // o|s|m|c
    'wrapperClass' => '',
])

<span class="{{ $wrapperClass }}">
  <span class="relative flex items-center justify-center rounded-full {{ $badgeClass }}">
    
    {{-- white plug behind the check (circular, not square) --}}
    @if ($iconName === 'check-badge')      
      <span
        class="absolute inset-0 m-auto rounded-full bg-white"
        style="width: 70%; height: 70%;"
        aria-hidden="true"
      ></span>
    @endif

    <x-app-icon
      name="{{ $iconName }}"
      variant="{{ $badgeType }}"
      class="{{ $iconSizeClass }} {{ $iconClass }} relative"
    />
  </span>
</span>
