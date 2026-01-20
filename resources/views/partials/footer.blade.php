<footer class="border-t border-slate-200/70 bg-white">
  <div class="mx-auto w-full max-w-6xl px-6 py-6 text-sm text-slate-600">
    <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
      <span class="text-center leading-relaxed sm:text-left">
        &copy; {{ date('Y') }} {{ config('app.name', 'go2forum') }}.
        <span class="opacity-80">ყველა უფლება დაცულია.</span>
      </span>

      <nav class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2">
        <a href="/privacy" class="transition-colors hover:text-slate-900 hover:underline">
          უსაფრთხოება
        </a>

        <a href="/terms" class="transition-colors hover:text-slate-900 hover:underline">
          წესები & პირობები
        </a>

        <a href="mailto:info@go2forum" target="_blank" rel="noopener noreferrer"
          class="transition-colors hover:text-slate-900 hover:underline">
          კონტაქტი
        </a>
      </nav>
    </div>
  </div>
</footer>