<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Agent Dashboard')</title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="{{ asset('backend/img/logo.png') }}">
  
  <!-- Font Awesome -->
  <link href="{{ asset('backend/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,400,600,700,900" rel="stylesheet">

  <!-- Admin Theme CSS -->
  <link href="{{ asset('backend/css/sb-admin-2.min.css') }}" rel="stylesheet">

  <!-- Agent Dashboard CSS riêng -->
  <link href="{{ asset('backend/css/agent-dashboard.css') }}" rel="stylesheet">
  
  <link href="{{asset('backend/vendor/fontawesome-free/css/all.min.css')}}" rel="stylesheet" type="text/css">

  @stack('styles')

</head>
