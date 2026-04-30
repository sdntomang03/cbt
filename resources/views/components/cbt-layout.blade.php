<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Judul Tab Browser -->
    <title>{{ config('app.name', 'Aplikasi CBT') }} - Ujian</title>

    <!-- Google Fonts: Nunito -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Compile Tailwind CSS dan Alpine JS bawaan Laravel -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Tempat untuk menyisipkan CSS/Style tambahan dari halaman ujian -->
    @stack('styles')
</head>

<body class="font-sans antialiased text-slate-800 bg-slate-50 overflow-hidden">

    <!--
        Variabel $slot di bawah ini adalah tempat di mana seluruh isi
        dari <x-cbt-layout> ... </x-cbt-layout> pada file run.blade.php
        Bapak akan dirender secara otomatis oleh Laravel.
    -->
    {{ $slot }}

    <!-- Tempat untuk menyisipkan Script tambahan dari halaman ujian -->
    @stack('scripts')
</body>

</html>