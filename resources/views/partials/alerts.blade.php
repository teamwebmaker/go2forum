@if(Auth::user() && Auth::user()->shouldVerify() && !Auth::user()->isVerified())
   <x-ui.alert type="warning" slotClasses="gap-1!">
      <span>აპლიკაციის სრულად გამოსაყენებლად აუცილებელია</span>
      <a href="{{ route('profile.verification') }}" class="underline">ვერიფიკაციის გავლა.</a>
   </x-ui.alert>
@endif
@if(Auth::user() && Auth::user()->is_blocked)
   <x-ui.alert type="warning" slotClasses="gap-1!">
      <span>
         თქვენს ანგარიშზე დროებითი შეზღუდვები მოქმედებს აპლიკაციის გამოყენების წესების დარღვევის გამო.
         დამატებითი ინფორმაციისთვის გთხოვთ გაეცნოთ
         <a href="{{ route('page.terms') }}" class="underline font-medium">
            წესებსა და პირობებს
         </a>
         ან
         <a href="{{ config('services.support.gmail_compose_url') }}" target="_blank" rel="noopener noreferrer" class="underline font-medium">
            დაგვიკავშირდით.
         </a>
      </span>
   </x-ui.alert>

@endif
