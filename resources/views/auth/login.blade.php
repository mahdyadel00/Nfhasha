
<!DOCTYPE html>

<!-- =========================================================
* {{ __('app.name') }} - Bootstrap 5 HTML Admin Template - Pro | v1.0.0
==============================================================

* Created by: ThemeSelection
* License: You must have a valid license purchased in order to legally use the theme for your project.

=========================================================
 -->
<!-- beautify ignore:start -->
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="light-style customizer-hide"
    dir="auto"
    data-theme="theme-default"
    data-assets-path="../assets/"
    data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>{{ __('app.name') }}</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}" />
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif !important;
        }
    </style>

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="../assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="../assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="../assets/vendor/css/pages/page-auth.css" />
    <!-- Helpers -->
    <script src="../assets/vendor/js/helpers.js"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="../assets/js/config.js"></script>
  </head>

  <body>
    <!-- Content -->

    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <!-- Register -->
          <div class="card">
            <div class="card-body">
              <!-- Logo -->
              <div class="d-flex justify-content-between align-items-center">
                <!-- Right side: Logo and App Name -->
                <div class="d-flex align-items-center">
                  <a href="#" class="app-brand-link gap-2 d-flex align-items-center">
                    <span class="app-brand-logo demo">
                      <img src="{{ asset('logo.png') }}" alt="Brand Logo" class="img-fluid" style="height: 20px;" />
                    </span>
                    <span class="app-brand-text demo text-body fw-bolder ms-2" style="font-size: 16px;">{{ __('app.name') }}</span>
                  </a>
                </div>

                <!-- Left side: Language Switcher -->
                <div>
                    <a href="{{ route('changeLocale', ['lang' => app()->getLocale() === 'ar' ? 'en' : 'ar']) }}" class="language-switcher">
                      <img
                        src="{{ app()->getLocale() === 'ar' ? asset('assets/img/lang/en.png') : asset('assets/img/lang/ar.png') }}"
                        alt="Change Language"
                        class="img-fluid"
                        style="width: 24px; height: 24px;"
                      />
                    </a>
                </div>
              </div>

              <!-- /Logo -->
              <h4 class="mb-2 mt-4 text-center"> {{ __('messages.welcome_to_login') }} 👋</h4>
              <p class="mb-4 text-center"> {{ __('messages.login_hint') }}</p>

              <form id="formAuthentication" class="mb-3" action="" method="POST">
                @csrf

                <div class="mb-3">
                  <label for="email" class="form-label">{{ __('inputs.email') }}</label>
                  <input
                    type="text"
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="{{ __('inputs.email.placeholder') }}"
                    autofocus
                  />
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-3 form-password-toggle">
                  <div class="d-flex justify-content-between">
                    <label class="form-label" for="password">{{ __('inputs.password') }}</label>
                  </div>
                  <div class="input-group input-group-merge">
                    <input
                      type="password"
                      id="password"
                      class="form-control @error('password') is-invalid @enderror"
                      name="password"
                      placeholder="{{ __('inputs.password.placeholder') }}"
                      aria-describedby="password"
                    />
                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                  </div>
                  @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember-me" name="remember"/>
                    <label class="form-check-label" for="remember-me"> {{ __('inputs.remember_me') }} </label>
                  </div>
                </div>

                <div class="mb-3">
                  <button class="btn btn-primary d-grid w-100" type="submit">{{ __('inputs.login_button') }}</button>
                </div>
              </form>

            </div>
          </div>
          <!-- /Register -->
        </div>
      </div>
    </div>

    <!-- / Content -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="../assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src="../assets/js/main.js"></script>

    <!-- Page JS -->

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
  </body>
</html>
