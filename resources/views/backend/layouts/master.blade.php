<!DOCTYPE html>
<html lang="en">
@include('backend.layouts.head') <!-- Load head chuẩn -->

<body id="page-top">
    <div id="wrapper">
        @include('backend.layouts.sidebar') <!-- Sidebar -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                @include('backend.layouts.header') <!-- Header -->
                @yield('main-content') <!-- Nội dung chính -->
            </div>
            @include('backend.layouts.footer') <!-- Footer -->
        </div>
    </div>
</body>
</html>
