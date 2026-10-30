<?php
require_once __DIR__ . '/../backend/authentication/only-admin.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS | Safety Management System</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/IMS.ico">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/system-selector.css">
</head>


<body>
    <div class="page-container">
        <header class=" topbar d-flex justify-content-between align-items-center ">
            <!-- BRAND -->
            <a href="#" class="brand">

                <div class="brand-icon">
                    <i class="bi bi-shield-fill-check"></i>
                </div>

                <div>
                    <div class="brand-name">
                        IMS
                    </div>
                    <div class="brand-subtitle">
                        Safety Management System
                    </div>
                </div>
            </a>

            <!-- ACTIONS -->
            <div class="top-actions">

                <!-- THEME -->
                <button
                    type="button"
                    class="theme-toggle"
                    id="themeToggle"
                    title="Toggle theme">
                    <i class="bi bi-sun-fill" id="themeIcon"></i>
                </button>

                <!-- PROFILE -->
                <div class="dropdown">

                    <button class=" profile-btn dropdown-toggle " type="button" data-bs-toggle="dropdown" aria-expanded="false">

                        <div class="profile-avatar">
                            <?php
                            $employeeName = trim($_SESSION['EmployeeName'] ?? '');
                            $nameParts = preg_split('/\s+/', $employeeName);

                            if (count($nameParts) >= 2) {
                                $initials = strtoupper(
                                    substr($nameParts[0], 0, 1) .
                                        substr($nameParts[count($nameParts) - 1], 0, 1)
                                );
                            } elseif (count($nameParts) === 1 && $nameParts[0] !== '') {
                                $initials = strtoupper(substr($nameParts[0], 0, 1));
                            } else {
                                $initials = 'U';
                            }

                            echo htmlspecialchars($initials);
                            ?>
                        </div>

                        <span class="profile-name">
                            <?php echo htmlspecialchars($_SESSION['EmployeeName'] ?? 'User'); ?>
                        </span>

                    </button>


                    <ul class=" dropdown-menu dropdown-menu-end shadow ">
                        <li>
                            <a class=" dropdown-item logout-item " href="../backend/authentication/Logout.php" onclick="try { localStorage.removeItem('ems_last_activity'); } catch (storageError) {}"> <i class=" bi bi-box-arrow-right me-2 ">
                                </i> Logout </a>
                        </li>

                    </ul>

                </div>

            </div>

        </header>

        <section class="welcome-section">

            <div class="greeting" id="greeting">
                Good morning,
            </div>

            <h1 class="welcome-title">
                Welcome back,
                <span><?php echo $_SESSION['EmployeeName']; ?>!</span>
            </h1>

            <div class="subtitle">
                Select a system to continue
            </div>

        </section>

        <!-- SYSTEMS -->
        <div class="row g-4 pb-5">

            <!-- FIRE EQUIPMENT -->
            <div class="col-lg-4 col-md-6">
                <a href="../dashboard.php" class="text-decoration-none">
                    <div class="system-card fire-card">

                        <div class="kpfems-logo-container">
                            <img
                                src="../assets/img/KPFEMS.png"
                                alt="KPFEMS Logo"
                                class="kpfems-logo">
                        </div>

                        <h2 class="system-title">
                           Fire Equipment<br>
                            Management System
                        </h2>

                        <p class="system-description">
                            Fire equipment, inspections,
                            QR code management, and approvals
                        </p>

                        <div class="system-arrow">
                            <i class="bi bi-arrow-right"></i>
                        </div>

                    </div>
                </a>
            </div>


            <!-- ACCIDENT MANAGEMENT -->
            <div class="col-lg-4 col-md-6">
                <a href="accident/" class="text-decoration-none">
                    <div class="system-card accident-card ">


                        <div class=" system-icon accident-icon ">
                            <i class=" bi bi-exclamation-triangle-fill "></i>
                        </div>


                        <h2 class="system-title">
                            Incident 
                            <br>
                            Management System
                        </h2>


                        <p class="system-description">
                            Incident reports,
                            investigation,
                            incident records
                        </p>

                        <div class="system-arrow">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>
                </a>
            </div>


            <!-- SAFETY MANAGEMENT -->

            <div class="col-lg-4 col-md-6">
                <a href="safety/" class="text-decoration-none">
                    <div class="  system-card safety-card ">
                        <div class="system-icon safety-icon ">
                            <i class=" bi bi-shield-check "></i>
                        </div>


                        <h2 class="system-title">
                            Safety
                            <br>
                            Management
                        </h2>

                        <p class="system-description">

                            Safety inspections,
                            compliance,
                            reports

                        </p>

                        <div class="system-arrow">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js "></script>

    <script>
        function updateGreeting() {

            const hour = new Date().getHours();
            let greeting =
                "Good evening,";
            if (hour >= 5 && hour < 12) {
                greeting = "Good morning,";

            } else if (hour >= 12 && hour < 18) {
                greeting = "Good afternoon,";
            }

            document.getElementById("greeting").textContent = greeting;
        }
        updateGreeting();



        /*  DARK / LIGHT MODE  DEFAULT = DARK  */
        const body = document.body;
        const themeToggle = document.getElementById("themeToggle");
        const themeIcon = document.getElementById("themeIcon");
        themeToggle.addEventListener("click", function() {

            body.classList.toggle("dark-mode");
            const isDarkMode = body.classList.contains("dark-mode");

            if (isDarkMode) {

                /* DARK MODE */
                themeIcon.classList.remove("bi-moon-fill");
                themeIcon.classList.add("bi-sun-fill");

            } else {
                /* LIGHT MODE */
                themeIcon.classList.remove("bi-sun-fill");
                themeIcon.classList.add("bi-moon-fill");
            }

        });
    </script>

    <style>
        .kpfems-logo-container {
            display: flex;
            justify-content: flex-end;
            width: 100%;
            margin-bottom: 1rem;
            overflow: visible;
        }

        .kpfems-logo {
            height: 80px;
            width: auto;
            max-width: 100%;
            object-fit: contain;
            transform: translateX(-130px);
        }

        /* Tablet */
        @media (max-width: 991.98px) {
            .kpfems-logo {
                height: 70px;
                transform: translateX(-60px);
            }
        }

        /* Mobile */
        @media (max-width: 575.98px) {
            .kpfems-logo {
                height: 60px;
                transform: translateX(0);
            }
        }
    </style>
</body>

</html>