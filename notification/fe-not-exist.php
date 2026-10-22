 <!-- notif if success edit FE -->

    <?php if (isset($_GET['fe-not-exist'])): ?>

      <div
        id="successAlert"
        class="alert alert-danger border-0 shadow-lg position-fixed start-50"
        role="alert"
        style="
            top: 25px;
            z-index: 9999;
            width: min(420px, calc(100% - 30px));
            border-radius: 14px;
            overflow: hidden;

            /* Initial state - nasa taas */
            opacity: 0;
            transform: translate(-50%, -80px);
        ">

        <div class="d-flex align-items-center gap-3 p-2">

          <!-- Success Icon -->
          <div
            class="d-flex align-items-center justify-content-center bg-danger text-white rounded-circle flex-shrink-0"
            style="width: 42px; height: 42px;">
            <i class="bi bi-x-lg fs-5"></i>
          </div>

          <!-- Message -->
          <div class="flex-grow-1">

            <div class="fw-bold">
              QR Code Validation
            </div>

            <div class="small text-body-secondary">
              Invalid QR code. Fire extinguisher not found.
            </div>

          </div>

        </div>

        <!-- Progress -->
        <div
          style="
                height: 4px;
                background: rgba(135, 25, 25, 0.15);
            ">
          <div
            id="successProgress"
            style="
                    height: 100%;
                    width: 100%;
                    background: #871919;
                "></div>
        </div>

      </div>

      <style>
        #successAlert {
          transition:
            opacity 0.45s ease,
            transform 0.45s cubic-bezier(0.22, 1, 0.36, 1);
        }

        #successProgress {
          transition: width 0.05s linear;
        }
      </style>


      <script>
        const successAlert =
          document.getElementById('successAlert');

        const successProgress =
          document.getElementById('successProgress');


        /*
        ========================================
        POP UP FROM TOP
        ========================================
        */

        setTimeout(function() {

          successAlert.style.opacity = '1';

          successAlert.style.transform =
            'translate(-50%, 0)';

        }, 50);


        /*
        ========================================
        3 SECOND TIMER
        ========================================
        */

        let width = 100;

        const timer = setInterval(function() {

          width -= 100 / 60;

          successProgress.style.width =
            width + '%';


          /*
          ====================================
          CLOSE
          ====================================
          */

          if (width <= 0) {

            clearInterval(timer);

            // Smooth slide up + fade out
            successAlert.style.opacity = '0';

            successAlert.style.transform =
              'translate(-50%, -60px)';

            setTimeout(function() {

              successAlert.remove();

              // Remove ?success=1
              window.history.replaceState({},
                document.title,
                window.location.pathname
              );

            }, 450);

          }

        }, 50);
      </script>

    <?php endif; ?>

    <!-- end notif -->