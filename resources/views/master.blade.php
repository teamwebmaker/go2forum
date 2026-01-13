<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>@yield('title', config('app.name', 'Laravel'))</title>

  <!-- Styles / Scripts -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="min-h-screen bg-slate-50 text-slate-900">
  @include('partials.nav')
  @include('partials.toasts')

  <main class="mx-auto flex w-full max-w-6xl min-h-screen flex-1 px-6 py-10">
    @yield('content')
  </main>

  @include('partials.footer')
</body>

</html>