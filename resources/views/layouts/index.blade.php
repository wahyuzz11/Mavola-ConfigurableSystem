<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Mavola</title>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Libraries Stylesheet -->
    <link href="{{ asset('assets/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css') }}" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    @yield('link')
</head>

<body>
    <div class="container-xxl position-relative bg-white d-flex p-0">
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-light navbar-light">
                <a href="index.html" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary">Mavola</h3>
                </a>

                
                <div class="navbar-nav w-100">
                    <a href="{{ route('home') }}" class="nav-item nav-link active"><i
                            class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i
                                class="fas fa-layer-group me-2"></i>Inventory</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="{{ route('products.index') }}" class="dropdown-item">Product List</a>
                            <a href="{{ route('products.category') }}" class="dropdown-item">Product Category</a>
                            <a href="{{ route('products.create') }}" class="dropdown-item">Add new products</a>
                        </div>
                    </div>

                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i
                                class="fas fa-layer-group me-2"></i>Purchase</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="{{ route('purchases.index') }}" class="dropdown-item">Purchase Reports</a>
                            <a href="{{ route('purchases.create') }}" class="dropdown-item">Add New Purchase</a>
                            <a href="{{ route('purchases.suppliers') }}" class="dropdown-item">Suppliers</a>
                            @if ($showDebtHistory ?? false)
                                <a href="{{ route('debts.index') }}" class="dropdown-item">Debt History</a>
                            @endif
                        </div>
                    </div>

                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i
                                class="fas fa-layer-group me-2"></i>Sale</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="{{ route('sales.index') }}" class="dropdown-item">Sale Reports</a>
                            <a href="{{ route('sales.create') }}" class="dropdown-item">Add New Sale</a>
                            <a href="{{ route('sales.customers') }}" class="dropdown-item">Customers</a>
                        </div>
                    </div>

                    @if (auth()->user()->employee->position == 'owner')
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i
                                    class="fas fa-layer-group me-2"></i>Settings</a>
                            <div class="dropdown-menu bg-transparent border-0">
                                <a href="{{ route('settings.inventory') }}" class="dropdown-item">Inventory
                                    Configuration</a>
                                <a href="{{ route('settings.purchase') }}" class="dropdown-item">Purchase
                                    Configuration</a>
                                <a href="{{ route('settings.sale') }}" class="dropdown-item">Sale Configuration</a>
                            </div>
                        </div>
                    @endif



                </div>
            </nav>
        </div>
        <!-- Sidebar End -->


        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
                <a href="{{ route('home') }}" class="navbar-brand d-flex d-lg-none me-4">
                    <h2 class="text-primary mb-0"><i class="fa fa-hashtag"></i></h2>
                </a>
                <a href="#" class="sidebar-toggler flex-shrink-0">

                    <i class="fa fa-bars"></i>
                </a>
                {{-- <form class="d-none d-md-flex ms-4">
                    <input class="form-control border-0" type="search" placeholder="Search" />
                </form> --}}
                <div class="navbar-nav align-items-center ms-auto">


                    <div class="nav-item dropdown">
                        {{-- <img class="rounded-circle me-lg-2" src="img/user.jpg" alt=""
                        style="width: 40px; height: 40px;"> --}}
                        <span class="d-none d-lg-inline-flex">
                            @auth
                            Welcome, {{ auth()->user()->employee->name }}
                            
                            @endauth
                            
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                        <a href="#" class="dropdown-item" onclick="return confirmLogout()">Log Out</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                        style="display: none;">
                        @csrf
                    </form>
                    
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Navbar End -->
    
    <!-- Sale & Revenue Start -->
    
    <!-- Sale & Revenue End -->
    <div class="container-fluid pt-4 px-4">
        @yield('content')
    </div>
    
    <!-- Sales Chart Start -->
    
    <!-- Footer End -->
</div>
<!-- Content End -->


<!-- Back to Top -->
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
</div>



<!-- JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/lib/chart/chart.min.js') }}"></script>
    <script src="{{ asset('assets/lib/easing/easing.min.js') }}"></script>
    <script src="{{ asset('assets/lib/waypoints/waypoints.min.js') }}"></script>
    <script src="{{ asset('assets/lib/owlcarousel/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('assets/lib/tempusdominus/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/lib/tempusdominus/js/moment-timezone.min.js') }}"></script>
    <script src="{{ asset('assets/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out of the system!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, logout!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit the hidden logout form
                    document.getElementById('logout-form').submit();
                }
            });
            return false; // Prevent default anchor behavior
        }
    </script>
    @yield('javascript')

    <!-- Template Javascript -->

</body>

</html>
