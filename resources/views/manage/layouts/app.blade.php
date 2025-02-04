<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="../assets/" data-template="vertical-menu-template-free"><head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">

    <title>@yield('title') | {{ __('app.name') }}</title>

    <meta name="description" content="">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}">


    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/fonts/boxicons.css">

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/css/core.css" class="template-customizer-core-css">
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/css/theme-default.css" class="template-customizer-theme-css">
    <link rel="stylesheet" href="{{ asset('') }}assets/css/demo.css">

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css">

    <!-- Page CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">

    <!-- Helpers -->
    <script src="{{ asset('') }}assets/vendor/js/helpers.js"></script><style type="text/css">
.layout-menu-fixed .layout-navbar-full .layout-menu,
.layout-page {
  padding-top: 0px !important;
}
.content-wrapper {
  padding-bottom: 0px !important;
}</style>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('') }}assets/js/config.js"></script>
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->

        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme" data-bg-class="bg-menu-theme">
          <div class="app-brand demo">
            <a href="index.html" class="app-brand-link">
              <span class="app-brand-logo demo">
                <img src="{{ asset('logo.png') }}" alt="Sneat" class="img-fluid w-px-50">
              </span>
              <span class="app-brand-text demo menu-text fw-bolder ms-2">{{ __('app.name') }}</span>
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
              <i class="bx bx-chevron-left bx-sm align-middle"></i>
            </a>
          </div>

          <div class="menu-inner-shadow"></div>

          <ul class="menu-inner py-1 ps ps--active-y">
            <!-- Dashboard -->
            <li class="menu-item {{ request()->routeIs('manage.home') ? 'active' : '' }}">
              <a href="{{ route('manage.home') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">{{ __('manage.home') }}</div>
              </a>
            </li>

            <!-- Splash Screens -->
            <li class="menu-item {{ request()->routeIs('manage.splash-screens.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-layout"></i>
                <div data-i18n="Layouts">{{ __('manage.splash_screens') }}</div>
              </a>

              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('manage.splash-screens.create') ? 'active' : '' }}">
                  <a href="{{ route('manage.splash-screens.create') }}" class="menu-link">
                    <div data-i18n="Without menu">{{ __('manage.add') }}</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('manage.splash-screens.index') ? 'active' : '' }}">
                  <a href="{{ route('manage.splash-screens.index') }}" class="menu-link">
                    <div data-i18n="With menu">{{ __('manage.showAll') }}</div>
                  </a>
              </ul>
            </li>

            <!-- Cities -->
            <li class="menu-item {{ request()->routeIs('manage.cities.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bxs-city"></i>
                <div data-i18n="Layouts">{{ __('manage.cities') }}</div>
              </a>

              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('manage.cities.create') ? 'active' : '' }}">
                  <a href="{{ route('manage.cities.create') }}" class="menu-link">
                    <div data-i18n="Without menu">{{ __('manage.add') }}</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('manage.cities.index') ? 'active' : '' }}">
                  <a href="{{ route('manage.cities.index') }}" class="menu-link">
                    <div data-i18n="With menu">{{ __('manage.showAll') }}</div>
                  </a>
              </ul>
            </li>

            <!-- Districts -->
            <li class="menu-item {{ request()->routeIs('manage.districts.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-building"></i>
                <div data-i18n="Layouts">{{ __('manage.districts') }}</div>
              </a>

              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('manage.districts.create') ? 'active' : '' }}">
                  <a href="{{ route('manage.districts.create') }}" class="menu-link">
                    <div data-i18n="Without menu">{{ __('manage.add') }}</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('manage.districts.index') ? 'active' : '' }}">
                  <a href="{{ route('manage.districts.index') }}" class="menu-link">
                    <div data-i18n="With menu">{{ __('manage.showAll') }}</div>
                  </a>
              </ul>
            </li>



            <li class="menu-header small text-uppercase">
              <span class="menu-header-text">Pages</span>
            </li>
          <div class="ps__rail-x" style="left: 0px; bottom: 0px;"><div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps__rail-y" style="top: 0px; height: 531px; right: 4px;"><div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 252px;"></div></div></ul>
        </aside>
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->

          <nav class="layout-navbar container-fluid navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
              </a>
            </div>

            <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
              <ul class="navbar-nav flex-row align-items-center ms-auto">
                <!-- Place this tag where you want the button to render. -->
                <li class="nav-item dropdown lh-1 me-3">
                    <a class="dropdown-toggle nav-link" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ app()->getLocale() === 'ar' ? asset('assets/img/lang/ar.png') : asset('assets/img/lang/en.png') }}"
                             alt="Current Language" style="width: 20px; height: 20px;" class="me-2" />
                        <span class="align-middle">{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</span>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('changeLocale', ['lang' => app()->getLocale() === 'ar' ? 'en' : 'ar']) }}">
                                <img src="{{ app()->getLocale() === 'ar' ? asset('assets/img/lang/en.png') : asset('assets/img/lang/ar.png') }}"
                                     alt="Change Language" style="width: 20px; height: 20px;" class="me-2" />
                                <span class="align-middle">{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                      <img src="{{ asset('logo.png') }}" alt="" class="w-px-40 h-px-40 rounded-circle">
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="#">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                              <img src="{{ asset('logo.png') }}" alt="" class="w-px-40 h-px-40 rounded-circle">
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <span class="fw-semibold d-block">{{ auth()->user()->name }}</span>
                            <small class="text-muted">{{ auth()->user()->role }}</small>
                          </div>
                        </div>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('changeLocale', ['lang' => app()->getLocale() === 'ar' ? 'en' : 'ar']) }}">
                            <img src="{{ app()->getLocale() === 'ar' ? asset('assets/img/lang/en.png') : asset('assets/img/lang/ar.png') }}"
                                 alt="Change Language" style="width: 20px; height: 20px;" class="me-2" />
                            <span class="align-middle">{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</span>
                        </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="#">
                        <i class="bx bx-cog me-2"></i>
                        <span class="align-middle">Settings</span>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="auth-login-basic.html">
                        <i class="bx bx-power-off me-2"></i>
                        <span class="align-middle">Log Out</span>
                      </a>
                    </li>
                  </ul>
                </li>
                <!--/ User -->
              </ul>
            </div>
          </nav>

          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->

            <div class="container-fluid flex-grow-1 container-p-y">
                @yield('content')
            </div>
            <!-- / Content -->

          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->



    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="{{ asset('') }}assets/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('') }}assets/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="{{ asset('') }}assets/vendor/js/menu.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src="{{ asset('') }}assets/js/main.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>

    <!-- Page JS -->
    @stack('scripts')

    @if (session('success'))
    <script>
        Toastify({
            text: "{{ session('success') }}",
            duration: 3000,
            gravity: "top",
            position: "center",
            style: { background: "linear-gradient(to right, #00b09b, #96c93d)" },
        }).showToast();
    </script>
    @endif



</body></html>
