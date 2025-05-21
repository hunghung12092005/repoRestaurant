<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @extends('layouts.app')
        @vite(['resources/js/app.js', 'resources/css/app.css'])
        @section('title', 'welcome')
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    </head>
    <body class="font-sans antialiased dark:bg-black dark:text-white/50">
        @if (session('error'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @section('content')
            <div id="app"></div>
        @endsection
    </body>
    {{-- <script>
        window.userSession = {
            name: "{{ session('name') ?? '' }}",
            id: "{{ session('id') ?? '' }}",
            role: "{{ session('role') ?? '' }}",
            admin: "{{ session('admin') ?? '' }}"
        };
    </script> --}}
</html>
