<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>DASHMIN - Login</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="{{ asset('assets/img/favicon.ico') }}" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="{{ asset('assets/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css') }}" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
</head>

<body>
    <div class="container-xxl position-relative bg-white d-flex p-0">
        <!-- Spinner Start -->
        <div id="spinner"
            class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->

        <!-- Sign In Start -->
        <div class="container-fluid">
            <div class="row h-100 align-items-center justify-content-center" style="min-height: 100vh;">
                <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                    <div class="bg-light rounded p-4 p-sm-5 my-4 mx-3">
                        <div class="text-center mb-4">
                            <h3 class="text-muted">Welcome to Configurable Information System</h3>
                            <br>
                            <h4 class="text-primary"><i class="fa me-2"></i>Login</h4>
                        </div>

                        <!-- Error Alert -->
                        <div id="errorAlert" class="alert alert-danger" style="display: none;">
                            <span id="errorMessage"></span>
                        </div>

                        <form id="loginForm" method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="floatingInput" name="email"
                                    placeholder="name@example.com" required>
                                <label for="floatingInput">Email address</label>
                            </div>

                            <div class="form-floating mb-4">
                                <input type="password" class="form-control" id="floatingPassword" name="password"
                                    placeholder="Password" required>
                                <label for="floatingPassword">Password</label>
                            </div>

                            {{-- <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="exampleCheck1" name="remember">
                                    <label class="form-check-label" for="exampleCheck1">Remember me</label>
                                </div>
                                <a href="">Forgot Password</a>
                            </div> --}}

                            <button type="submit" class="btn btn-primary py-3 w-100 mb-4">Sign In</button>

                            {{-- <p class="text-center mb-0">Don't have an Account? <a href="">Sign Up</a></p> --}}
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Sign In End -->
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/lib/chart/chart.min.js') }}"></script>
    <script src="{{ asset('assets/lib/easing/easing.min.js') }}"></script>
    <script src="{{ asset('assets/lib/waypoints/waypoints.min.js') }}"></script>
    <script src="{{ asset('assets/lib/owlcarousel/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('assets/lib/tempusdominus/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/lib/tempusdominus/js/moment-timezone.min.js') }}"></script>
    <script src="{{ asset('assets/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js') }}"></script>

    <!-- Template Javascript -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <script>
        // Hide spinner after page load
        // window.addEventListener('load', function() {
        //     document.getElementById('spinner').classList.remove('show');
        // });

        // // Form submission handler (for demo purposes)
        // document.getElementById('loginForm').addEventListener('submit', function(e) {
        //     // Remove this preventDefault for actual Laravel implementation
        //     e.preventDefault();

        //     // Get form data
        //     const email = document.getElementById('floatingInput').value;
        //     const password = document.getElementById('floatingPassword').value;

        //     // Basic validation
        //     if (!email || !password) {
        //         showError('Please fill in all fields');
        //         return;
        //     }

        //     // Email validation
        //     const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        //     if (!emailPattern.test(email)) {
        //         showError('Please enter a valid email address');
        //         return;
        //     }

        //     // For demo - remove this for actual implementation
        //     showError('Demo mode: Remove preventDefault in script for actual Laravel login');

        //     // In actual Laravel implementation, the form will submit normally
        //     // and Laravel will handle the authentication
        // });

        // function showError(message) {
        //     const errorAlert = document.getElementById('errorAlert');
        //     const errorMessage = document.getElementById('errorMessage');

        //     errorMessage.textContent = message;
        //     errorAlert.style.display = 'block';

        //     // Hide after 5 seconds
        //     setTimeout(() => {
        //         errorAlert.style.display = 'none';
        //     }, 5000);
        // }

        // // Show Laravel errors on page load (if any)
        // // This would be populated by your Laravel controller
        // const laravelErrors = null; // Replace with: @json($errors->first()) in Laravel
        // if (laravelErrors) {
        //     showError(laravelErrors);
        // }
    </script>
</body>

</html>
