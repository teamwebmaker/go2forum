@if(Auth::user() && Auth::user()->shouldVerify() && !Auth::user()->isVerified())
   <x-ui.alert type="warning" slotClasses="gap-1!">
      <span>აპლიკაციის სრულად გამოსაყენებლად აუცილებელია</span>
      <a href="{{ route('profile.verification') }}" class="underline">ვერიფიკაციის გავლა.</a>
   </x-ui.alert>
@endif