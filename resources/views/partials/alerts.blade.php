@php
   $authUser = Auth::user();
   $siteAlerts = collect();

   if (\Illuminate\Support\Facades\Schema::hasTable('site_alerts')) {
      $siteAlerts = \App\Models\SiteAlert::query()
         ->visibleFor($authUser)
         ->get();
   }
@endphp

<div class="space-y-1 sm:space-y-1.5">
   @if($authUser && $authUser->shouldVerify() && !$authUser->isVerified())
      <x-ui.alert type="warning" slotClasses="gap-1!">
         <span>აპლიკაციის სრულად გამოსაყენებლად აუცილებელია</span>
         <a href="{{ route('profile.verification') }}" class="underline">ვერიფიკაციის გავლა.</a>
      </x-ui.alert>
   @endif

   @if($authUser && $authUser->is_blocked)
      <x-ui.alert type="warning" slotClasses="gap-1!">
         <span>
            თქვენს ანგარიშზე დროებითი შეზღუდვები მოქმედებს აპლიკაციის გამოყენების წესების დარღვევის გამო.
            დამატებითი ინფორმაციისთვის გთხოვთ გაეცნოთ
            <a href="{{ route('page.terms') }}" class="underline font-medium">
               წესებსა და პირობებს
            </a>
            ან
            <a href="{{ config('services.support.gmail_compose_url') }}" target="_blank" rel="noopener noreferrer"
               class="underline font-medium">
               დაგვიკავშირდით.
            </a>
         </span>
      </x-ui.alert>
   @endif

   @foreach($siteAlerts as $siteAlert)
      <x-ui.alert :type="$siteAlert->type" :closable="$siteAlert->is_closable" :alertKey="$siteAlert->dismiss_storage_key"
         slotClasses="items-start gap-1!">
         @if(filled($siteAlert->title))
            <span class="font-semibold">{{ $siteAlert->title }}</span>
         @endif
         <span class="whitespace-pre-line">{{ $siteAlert->content }}</span>
      </x-ui.alert>
   @endforeach
</div>
