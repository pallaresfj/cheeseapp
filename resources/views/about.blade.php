<!DOCTYPE html>
<html class="no-js" lang="">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Base - Multipurpose Bootstrap 5 Template</title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicon.png') }}"/>
    <!-- Place favicon.ico in the root directory -->

    <!-- ========================= CSS here ========================= -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-5.0.0-alpha-2.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/LineIcons.2.0.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/animate.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/lindy-uikit.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/base-style.css') }}"/>
  </head>
  <body>
    <!--[if lte IE 9]>
      <p class="browserupgrade">
        You are using an <strong>outdated</strong> browser. Please
        <a href="https://browsehappy.com/">upgrade your browser</a> to improve
        your experience and security.
      </p>
    <![endif]-->

    <!-- ========================= preloader start ========================= -->
    <div class="preloader">
      <div class="loader">
        <div class="spinner">
          <div class="spinner-container">
            <div class="spinner-rotator">
              <div class="spinner-left">
                <div class="spinner-circle"></div>
              </div>
              <div class="spinner-right">
                <div class="spinner-circle"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- ========================= preloader end ========================= -->

    <!-- ========================= hero-section-wrapper-1 start ========================= -->
    <section id="home" class="hero-section-wrapper hero-section-wrapper-1">

      <!-- ========================= header-4 start ========================= -->
      <header class="header header-4">
        <div class="navbar-area">
          <div class="container">
            <div class="row align-items-center">
              <div class="col-lg-12">
                <nav class="navbar navbar-expand-lg">
                  <a class="navbar-brand" href="/">
                    <img src="{{ asset('assets/img/logo/logo.svg') }}" alt="Logo" />
                  </a>
                  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent4" aria-controls="navbarSupportedContent4" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="toggler-icon"></span>
                    <span class="toggler-icon"></span>
                    <span class="toggler-icon"></span>
                  </button>

                  <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent4">
                    <ul id="nav4" class="navbar-nav ml-auto">
                      <li class="nav-item">
                        <a class="page-scroll" href="/">Inicio</a>
                      </li>
                      <li class="nav-item">
                        <a class="page-scroll active" href="/about">Nosotros</a>
                      </li>
                      <li class="nav-item">
                        <a class="page-scroll" href="/contact">Contacto</a>
                      </li>
                    </ul>
                    
                  </div>
                  <div class="header-search">
                    <a href="#0"> <i class="lni lni-search-alt"></i> </a>
                    <form action="#">
                      <input type="text" placeholder="Buscar...">
                    </form>
                  </div>
                  <!-- navbar collapse -->
                </nav>
                <!-- navbar -->
              </div>
            </div>
            <!-- row -->
          </div>
          <!-- container -->
        </div>
        <!-- navbar area -->
      </header>
      <!-- ========================= header-4 end ========================= -->

      <!-- ========================= banner start ========================= -->
      <div class="banner-section">
        <div class="banner-area img-bg">
          <div class="container">
            <div class="row">
              <div class="col-lg-6">
                <div class="banner-content">
                  <h3 class="mb-20">Nosotros</h3>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a href="/">Inicio</a></li>
                      <li class="breadcrumb-item active" aria-current="page">Nosotros</li>
                    </ol>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- ========================= banner end ========================= -->
    </section>
    <!-- ========================= hero-section-wrapper-1 end ========================= -->

            <div class="col-md-12 text-center">
            <div class="section-title text-center pt-60 mb-60">
              <h3 class="mb-15">Prueba CheeseApp</h3>
              <p>Solicita una prueba de CheeseApp por 30 días y pon a prueba todas sus funcionalidades.</p><br/>
              <a href="https://rebrand.ly/base-gg" rel="nofollow" target="_blank" class="button radius-30">Probar ahora</a>
            </div>
          </div>
    <!-- ========================= feature style-3 start ========================= -->
    <section id="features" class="feature-section feature-style-3">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-xxl-5 col-xl-5 col-lg-7 col-md-8">
            <div class="section-title text-center mb-60">
              <h3 class="mb-15">Funciones de CheeseApp</h3>
              <p>Deja de perder tiempo y dinero utilizando planillas de papel o planillas de Excel que no obtienen resultados. ¡Satisfacción garantizada!</p>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-4 col-md-6">
            <div class="single-feature">
              <div class="icon">
                <i class="lni lni-vector"></i>
              </div>
              <div class="content">
                <h5>Graphics Design</h5>
                <p>Short description for the ones who look for something new.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="single-feature">
              <div class="icon">
                <i class="lni lni-pallet"></i>
              </div>
              <div class="content">
                <h5>Print Design</h5>
                <p>Short description for the ones who look for something new.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="single-feature">
              <div class="icon">
                <i class="lni lni-stats-up"></i>
              </div>
              <div class="content">
                <h5>Business Analysis</h5>
                <p>Short description for the ones who look for something new.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="single-feature">
              <div class="icon">
                <i class="lni lni-code-alt"></i>
              </div>
              <div class="content">
                <h5>Web Development</h5>
                <p>Short description for the ones who look for something new.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="single-feature">
              <div class="icon">
                <i class="lni lni-lock"></i>
              </div>
              <div class="content">
                <h5>Best Security</h5>
                <p>Short description for the ones who look for something new.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="single-feature">
              <div class="icon">
                <i class="lni lni-code"></i>
              </div>
              <div class="content">
                <h5>Web Design</h5>
                <p>Short description for the ones who look for something new.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
		<!-- ========================= feature style-3 end ========================= -->

    <!-- ========================= about style-5 start ========================= -->
    <section id="about" class="about-section about-style-5" style="background-image: url('assets/img/about/about-5/about-img.jpg')">
      <div class="container">
        <div class="row">
          <div class="col-xl-6 col-lg-8 col-md-10">
            <div class="about-content-wrapper">
              <div class="section-title mb-30">
                <h3 class="mb-25">The future of designing starts here</h3>
                <p>Stop wasting time and money designing and managing a website that doesn’t get results. Happiness guaranteed, Stop wasting time and money designing and managing a website that doesn’t get results. Happiness guaranteed,</p>
              </div>
              <a href="#0" class="button button-lg radius-10">Saber Más</a>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- ========================= about style-5 end ========================= -->

    <!-- ========================= clients-logo start ========================= -->
    <section class="clients-logo-section pt-100 pb-100">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="section-title text-center mb-60">
              <h3>Tecnologías utilizadas</h3>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <div class="client-logo wow fadeInUp" data-wow-delay=".2s">
              <img src="assets/img/clients/brands.svg" alt="" class="w-100">
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- ========================= clients-logo end ========================= -->

    <!-- ========================= footer style-4 footer-dark start ========================= -->
    <footer class="footer footer-style-4 footer-dark">
      <div class="container">
        <div class="widget-wrapper">
          <div class="row">
            <div class="col-xl-3 col-lg-4 col-md-6">
              <div class="footer-widget">
                <div class="logo">
                  <a href="#0"> <img src="{{ asset('assets/img/logo/logo-dark.svg') }}" alt=""> </a>
                </div>
                <p class="desc">Síguenos en nuestras redes sociales.</p>
                <ul class="socials">
                  <li> <a href="#0"> <i class="lni lni-facebook-filled"></i> </a> </li>
                  <li> <a href="#0"> <i class="lni lni-twitter-filled"></i> </a> </li>
                  <li> <a href="#0"> <i class="lni lni-instagram-filled"></i> </a> </li>
                  <li> <a href="#0"> <i class="lni lni-linkedin-original"></i> </a> </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="copyright-wrapper">
          <p>Desarrollado por <a href="https://asyservicios.com" rel="nofollow" target="_blank">AS&Servicios.com</a></p>        </div>
      </div>
    </footer>
    <!-- ========================= footer style-4 footer-dark end ========================= -->

    <!-- ========================= scroll-top start ========================= -->
    <a href="#" class="scroll-top"> <i class="lni lni-chevron-up"></i> </a>
    <!-- ========================= scroll-top end ========================= -->
		

    <!-- ========================= JS here ========================= -->
    <script src="assets/js/bootstrap.5.0.0.alpha-2-min.js"></script>
    <script src="assets/js/count-up.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/main.js"></script>


    </script>
  </body>
</html>
