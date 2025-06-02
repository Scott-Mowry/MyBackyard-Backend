<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{asset('admin/assets/images/logo.ico')}}">
    <title>@yield('title','Itz Yourz-Login')</title>
<!--===============================================================================================-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{asset('admin/assets/vendor/bootstrap/css/bootstrap.min.css')}}">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{asset('admin/assets/fonts/font-awesome-4.7.0/css/font-awesome.min.css')}}">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{asset('admin/assets/fonts/Linearicons-Free-v1.0.0/icon-font.min.css')}}">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{asset('admin/assets/vendor/animate/animate.css')}}">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{asset('admin/assets/vendor/css-hamburgers/hamburgers.min.css')}}">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{asset('admin/assets/vendor/animsition/css/animsition.min.css')}}">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{asset('admin/assets/vendor/select2/select2.min.css')}}">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{asset('admin/assets/vendor/daterangepicker/daterangepicker.css')}}">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{asset('admin/assets/css/util.css')}}">
	<link rel="stylesheet" type="text/css" href="{{asset('admin/assets/css/main.css')}}">
<!--===============================================================================================-->


   <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
<style>
	.body{
		background-color:#000;
		/* background-image: url('{{ asset("admin/assets/images/bg_image.jpg") }}'); */
	}
	.input100 {
    border: 1px solid #e6e6e6;
    border-radius: 10px;
	}
	.btn-itz{
  background-color: #6A0DAD;
  color:#FFFFFF;
}
	.gradient-custom-2 {
/* fallback for old browsers */
background: #fccb90;

/* Chrome 10-25, Safari 5.1-6 */
background: -webkit-linear-gradient(to right, #6A0DAD, #FFFFFF);

/* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
background: linear-gradient(to right,#6A0DAD, #FFFFFF);
}

@media (min-width: 768px) {
.gradient-form {
height: 100vh !important;
}
}
@media (min-width: 769px) {
.gradient-custom-2 {
border-top-right-radius: .3rem;
border-bottom-right-radius: .3rem;
}
}
	/* You might need to adjust the positioning based on your layout */
	.eye-icon {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    cursor: pointer;
    z-index: 1; /* Ensure the eye icon appears above the input */
}

/* Styling for the eye icon */
.eye-icon i {
    font-size: 16px;
    color: #aaa; /* Default color */
}

.eye-icon.active i {
    color: #333; /* Dark color when active */
}

.btn-itz:hover {
  background-color:#CCCCCC;
  border-color:purple;
}
	</style>



</head>
<body >

<div class="limiter" >
<div class="container-login100" style="background-color:rgb(40, 40, 40);">
<!--style="background-image: url('{{ asset('admin/assets/images/bg_image.jpg') }}');background-size: cover;background-repeat: no-repeat;"-->
            @yield('content')
        </div>
</div>

 <!--===============================================================================================-->
 <script src="{{asset('admin/assets/vendor/jquery/jquery-3.2.1.min.js')}}"></script>
<!--===============================================================================================-->
	<script src="{{asset('admin/assets/vendor/animsition/js/animsition.min.js')}}"></script>
<!--===============================================================================================-->
	<script src="{{asset('admin/assets/vendor/bootstrap/js/popper.js')}}"></script>
	<script src="{{asset('admin/assets/vendor/bootstrap/js/bootstrap.min.js')}}"></script>
<!--===============================================================================================-->
	<script src="{{asset('admin/assets/vendor/select2/select2.min.js')}}"></script>
<!--===============================================================================================-->
	<script src="{{asset('admin/assets/vendor/daterangepicker/moment.min.js')}}"></script>
	<script src="{{asset('admin/assets/vendor/daterangepicker/daterangepicker.js')}}"></script>
<!--===============================================================================================-->
	<script src="{{asset('admin/assets/vendor/countdowntime/countdowntime.js')}}"></script>
<!--===============================================================================================-->
	<script src="{{asset('admin/assets/js/main.js')}}"></script>

    @stack('body-scripts')
</body>
</html>
