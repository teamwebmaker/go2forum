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
         <a href="" class="underline font-medium">
            წესებსა და პირობებს
         </a>
         ან
         <a href="" class="underline font-medium">
            დაგვიკავშირდით
         </a>.
      </span>
   </x-ui.alert>

@endif