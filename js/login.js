
    /* =====================================================
           PASSWORD SHOW / HIDE
        ====================================================== */

    const password =
      document.getElementById("password");

    const passwordToggle =
      document.getElementById("passwordToggle");

    const passwordIcon =
      document.getElementById("passwordIcon");


    passwordToggle.addEventListener(
      "click",
      function() {

        if (
          password.type === "password"
        ) {

          password.type = "text";

          passwordIcon.classList.remove(
            "bi-eye"
          );

          passwordIcon.classList.add(
            "bi-eye-slash"
          );

          passwordToggle.setAttribute(
            "aria-label",
            "Hide password"
          );

        } else {

          password.type = "password";

          passwordIcon.classList.remove(
            "bi-eye-slash"
          );

          passwordIcon.classList.add(
            "bi-eye"
          );

          passwordToggle.setAttribute(
            "aria-label",
            "Show password"
          );
        }

      }
    );


    /* =====================================================
       DARK / LIGHT MODE
    ====================================================== */

    const themeButton =
      document.getElementById(
        "themeButton"
      );

    const themeIcon =
      themeButton.querySelector("i");


    themeButton.addEventListener(
      "click",
      function() {

        document.body.classList.toggle(
          "light-mode"
        );


        const isLight =
          document.body.classList.contains(
            "light-mode"
          );


        if (isLight) {

          themeIcon.classList.remove(
            "bi-sun-fill"
          );

          themeIcon.classList.add(
            "bi-moon-stars-fill"
          );

          themeButton.title =
            "Dark mode";

        } else {

          themeIcon.classList.remove(
            "bi-moon-stars-fill"
          );

          themeIcon.classList.add(
            "bi-sun-fill"
          );

          themeButton.title =
            "Light mode";
        }

      }
    );


    /* =====================================================
       LOGIN
    ====================================================== */

    const loginForm =
      document.getElementById(
        "loginForm"
      );

    const loginButton =
      document.getElementById(
        "loginButton"
      );

    const loginButtonText =
      document.getElementById(
        "loginButtonText"
      );

    const loginButtonIcon =
      document.getElementById(
        "loginButtonIcon"
      );

    const loginError =
      document.getElementById(
        "loginError"
      );

    const successOverlay =
      document.getElementById(
        "loginSuccessOverlay"
      );


    loginForm.addEventListener(
      "submit",
      async function(event) {

        event.preventDefault();


        /* CLEAR ERROR */

        loginError.textContent = "";

        loginError.classList.remove(
          "show"
        );


        /* LOADING */

        loginButton.disabled = true;

        loginButtonText.textContent =
          "Logging in...";

        loginButtonIcon.className =
          "spinner-border spinner-border-sm ms-2";


        try {

          const formData =
            new FormData(
              loginForm
            );


          /* =========================================
             SEND REQUEST
          ========================================== */

          const response =
            await fetch(
              loginForm.action, {
                method: "POST",

                body: formData,

                credentials: "same-origin",

                headers: {
                  "X-Requested-With": "XMLHttpRequest",

                  "Accept": "application/json"
                }
              }
            );


          /* =========================================
             READ RESPONSE AS TEXT FIRST
          ========================================== */

          const responseText =
            await response.text();


          console.log(
            "AuthController response:",
            responseText
          );


          /* =========================================
             CHECK HTTP STATUS
          ========================================== */

          if (!response.ok) {

            throw new Error(
              "Server error (" +
              response.status +
              ")"
            );
          }


          /* =========================================
             PARSE JSON
          ========================================== */

          let result;

          try {

            result =
              JSON.parse(
                responseText
              );

          } catch (jsonError) {

            console.error(
              "Invalid JSON from server:",
              responseText
            );


            throw new Error(
              "The server returned an invalid response."
            );
          }


          /* =========================================
             SUCCESS
          ========================================== */

          if (
            result.success === true
          ) {

            successOverlay.classList.add(
              "show"
            );


            document.body.style.overflow =
              "hidden";


            setTimeout(
              function() {

                /*
                 * replace() instead of href
                 * prevents returning to login
                 * through browser history.
                 */

                window.location.replace(
                  result.redirect ||
                  "../dashboard.php"
                );

              },
              1500
            );


            return;
          }


          /* =========================================
             LOGIN FAILED
          ========================================== */

          loginButton.disabled =
            false;

          loginButtonText.textContent =
            "Sign In";

          loginButtonIcon.className =
            "bi bi-arrow-right ms-2";


          loginError.textContent =
            result.message ||
            "Invalid username or password.";


          loginError.classList.add(
            "show"
          );


          password.focus();

        }


        /* =========================================
           ERROR
        ========================================== */
        catch (error) {

          console.error(
            "LOGIN ERROR:",
            error
          );


          loginButton.disabled =
            false;

          loginButtonText.textContent =
            "Sign In";

          loginButtonIcon.className =
            "bi bi-arrow-right ms-2";


          /*
           * Show the actual error
           * instead of falsely saying
           * server is disconnected.
           */

          if (
            error.message.includes(
              "invalid response"
            )
          ) {

            loginError.textContent =
              "Server error. Check AuthController.php.";

          } else {

            loginError.textContent =
              error.message ||
              "Unable to connect to the server. Please try again.";
          }


          loginError.classList.add(
            "show"
          );

        }

      }
    );


    /* =====================================================
       BACK BUTTON / BFCACHE PROTECTION
    ====================================================== */

    window.addEventListener(
      "pageshow",
      function(event) {

        if (
          event.persisted
        ) {

          window.location.reload();

        }

      }
    );

