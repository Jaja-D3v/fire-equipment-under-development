<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");


if (isset($_SESSION['id'])) {

  header("Location: ../dashboard.php");
  exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>IMS Safety Management System</title>

  <link rel="icon" type="image/x-icon" href="../assets/img/IMS.ico">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../css/login.css">

</head>


<body class="light-mode">



  <!-- SUCCESS POPUP -->

  <div class="login-success-overlay" id="loginSuccessOverlay">

    <div class="login-success-popup">
      <div class="success-icon">
        <i class="bi bi-check-lg"></i>
      </div>

      <h4>
        Login Successful
      </h4>

      <p id="successMessage">
        Welcome back!
      </p>

      <div class="success-loader">

        <div
          class="spinner-border spinner-border-sm">
        </div>

        <span>
          Redirecting...
        </span>
      </div>
    </div>
  </div>


  <div class="page-overlay"></div>
  <div class=" container-fluid login-wrapper ">
    <div class=" position-absolute top-0 start-0 p-4 p-md-5 ">
      <div class=" d-flex align-items-center gap-2 ">
        <div class="brand-logo">
          <i class="bi bi-shield-fill-check"> </i>
          IMS
        </div>
        <div class="brand-subtitle">
          Safety Management System
        </div>
      </div>
    </div>

    <button type="button" class="theme-button" id="themeButton" title="Dark mode">
      <i class="bi bi-moon-stars-fill"> </i>
    </button>

    <div class=" row min-vh-100 align-items-center ">
      <div class=" col-lg-7 d-none d-lg-flex ">
        <div class=" left-content d-flex flex-column align-items-start ps-xl-5 ">
          <div class=" safety-label mb-4 ">
            SAFER WORKPLACE
            <br>
            BETTER TOMORROW
          </div>
          <h1 class=" hero-title mb-4 ">
            Safety Today,
            <br>
            <span>
              A Safer
              <br>
              Tomorrow
            </span>
          </h1>


          <p class=" hero-description mb-4 ">

            Integrated systems for a safer,
            more compliant, and more
            productive workplace.

          </p>

          <!-- BENEFITS -->
          <div class="benefit-box p-3">
            <div class=" row g-0">
              <div class=" col-4 px-3 ">
                <div class=" benefit-icon text-info mb-2 ">
                  <i class="bi bi-shield-check">
                  </i>
                </div>
                <div class="benefit-title">

                  Safety
                  <br>
                  Compliance

                </div>
              </div>


              <div class=" col-4 px-3 border-start border-end border-secondary ">
                <div class=" benefit-icon text-success mb-2 ">
                  <i class="bi bi-bar-chart-line">
                  </i>
                </div>

                <div class="benefit-title">
                  Efficient
                  <br>
                  Management
                </div>

              </div>


              <div class=" col-4 px-3 ">
                <div class=" benefit-icon text-warning mb-2 ">
                  <i class="bi bi-people">
                  </i>
                </div>
                <div class="benefit-title">
                  A Safer
                  <br>
                  Team
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class=" col-lg-5 d-flex justify-content-center align-items-center  ">
        <div class="login-card">

          <!-- LOGIN HEADER -->
          <div class="text-center">
            <div class="login-logo">

              <i class="bi bi-shield-fill-check">
              </i>

            </div>

            <div class="login-title">
              IMS
            </div>

            <div class="login-subtitle">
              Safety Management System
            </div>
            <div class="login-line"></div>
            <div class=" small  text-uppercase fw-semibold text-white-50 " style=" letter-spacing:4px; ">
              Login to continue
            </div>
          </div>


          <form action="../backend/controller/AuthController.php" method="POST" id="loginForm" class="mt-4">
            <!-- USERNAME -->

            <div class=" input-group-custom  mb-3  ">
              <i class=" bi bi-person input-icon  "> </i>
              <input type="text" name="username" class=" form-control login-input " placeholder="Username or Employee Code" autocomplete="username" required>
            </div>

            <!-- PASSWORD -->
            <div class=" input-group-custom mb-1 ">
              <i class=" bi bi-lock input-icon "> </i>
              <input type="password" name="password" id="password" class=" form-control login-input " placeholder="Password" autocomplete="current-password" required>
              <button type="button" class="password-toggle" id="passwordToggle" aria-label="Show password">
                <i class=" bi bi-eye " id="passwordIcon"> </i>
              </button>
            </div>

            <!-- ERROR -->
            <div id="loginError" class="login-error">
            </div>

            <!-- BUTTON -->
            <button type="submit" class=" btn btn-signin w-100 " id="loginButton">
              <span id="loginButtonText">
                Sign In
              </span>

              <i class=" bi bi-arrow-right ms-2 " id="loginButtonIcon">
              </i>
            </button>
          </form>

          <!-- FOOTER -->
          <div class=" text-center mt-4  ">
            <div class="login-footer">
              IMS Safety Management System
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- =====================================================
         BOOTSTRAP JS
    ====================================================== -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"> </script>
  <script src="../js/login.js"> </script>
</body>

</html>