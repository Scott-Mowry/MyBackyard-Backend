@extends('Admin.layouts.login')
@section('title','My Backyard | Login')

@section('content')
<section class="h-100 gradient-form">
  <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-xl-12">
        <div class="card rounded-3 text-black">
          <div class="row g-0">
            <div class="col-lg-12">
              <div class="card-body p-md-5 mx-md-4">

                <div class="text-center">
                  <img src="{{asset('admin/assets/images/logo.png')}}"
                    style="width: 185px;" alt="logo">
					<h4 class="mt-3 mb-3 pb-1">Admin</h4>
                </div>
				
				<form  method="post" action="{{route('admin.login.submit')}}">
                    @csrf
                  <p>Please login to your account</p>
                  
				  <label class="form-label" for="form2Example22">Username</label>
				  <div class="wrap-input100 validate-input" data-validate = "Valid email is required: ex@abc.xyz" style="height:50px;margin-top:5px;">
						<input class="input100" type="email" name="email" placeholder="Enter Username" value="{{ old('email') }}" required>
						<span class="focus-input100"></span>
						
					</div>
				  @error('email')
                    <p class="text-danger">You have enter invalid email address.</p>
                    @enderror
					<label class="form-label" for="form2Example22">Password</label>
					<div class="wrap-input100 validate-input" data-validate="Password is required" style="height:50px;margin-top:5px;">
					
					<input required class="input100" placeholder="Enter Password" type="password" name="password" id="password">
						<span class="focus-input100"></span>
						<span class="eye-icon" onclick="togglePasswordVisibility()">
							<i class="fas fa-eye-slash" style="color:black" id="eye"></i>
						</span>
						
					</div>
                 
				

				  @if(Session::has('error_msg'))
                    <p class="text-danger">{{Session::get('error_msg')}}</p>
                    @endif
                  <div class="text-center pt-1 mb-5 pb-1">
                    <button class="btn btn-block fa-lg mb-3 login100-form-btn"  type="submit">LOGIN</button>
        
                  </div>

                 

                </form>

              </div>
            </div>
          
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
	
			
		
@endsection
@push('body-scripts')
<script>



function togglePasswordVisibility() {
    var passwordInput = document.getElementById("password");
    var eyeIcon = document.getElementById("eye");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeIcon.classList.remove("fa-eye-slash");
        eyeIcon.classList.add("fa-eye");
    } else {
        passwordInput.type = "password";
        eyeIcon.classList.remove("fa-eye");
        eyeIcon.classList.add("fa-eye-slash");
    }
}
</script>
@endpush