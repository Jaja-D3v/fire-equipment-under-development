<?php

include_once './backend/controller/FireExtinguisherController.php';

$expiringCount = getAllExpiringCount();

// Show only once per login session
if (
    !isset($_SESSION['expiration_notice_shown']) &&
    $expiringCount > 0
):

    $_SESSION['expiration_notice_shown'] = true;

?>

<div
    id="expirationNotice"
    class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3"
    role="alert"
    style="
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translate(-50%, -120%);
        opacity: 0;
        z-index: 9999;
        width: min(500px, calc(100% - 30px));
        border-radius: 12px;
    "
>
    <div
        class="d-flex align-items-center justify-content-center
               bg-warning text-dark rounded-circle flex-shrink-0"
        style="width: 42px; height: 42px;"
    >
        <i class="bi bi-exclamation-triangle-fill"></i>
    </div>

    <div class="flex-grow-1">
        <div class="fw-semibold">
            Expiration Notice
        </div>

        <div class="small">
            <?= $expiringCount ?>
            fire extinguisher(s) will expire within the next 2 months.
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const notice = document.getElementById('expirationNotice');

    if (!notice) return;

    setTimeout(function () {

        notice.style.transition =
            'transform 0.5s ease, opacity 0.5s ease';

        notice.style.transform =
            'translate(-50%, 0)';

        notice.style.opacity = '1';

    }, 100);

    setTimeout(function () {

        notice.style.transform =
            'translate(-50%, -120%)';

        notice.style.opacity = '0';

    }, 3000);

});
</script>

<?php endif; ?>