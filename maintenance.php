<?php
require_once 'backend/authentication/only-admin.php';
require_once __DIR__ . '/backend/config/db.php';
require_once __DIR__ . '/backend/controller/MaintenanceController.php';


if (isset($_POST['action'])) {

    switch ($_POST['action']) {

        case 'add':

            addController();

            break;

        case 'update':

            updateController();

            break;

        case 'delete':

            deleteController();

            break;
    }
}

$data = indexController();
$branchData = $data['branch'];
$placement = $data['placement'];
$typeData = $data['type'];
$fireClassData = $data['fire_class'];
$capacityData = $data['capacity'];
$locationData = $data['location'];
?>

<!DOCTYPE html>

<!-- CoreUI - Free Bootstrap Admin Template -->

<html lang="en">

<?php include 'partials/header.php'; ?>

<body>

    <?php include 'partials/side-nav.php'; ?>

    <div class="wrapper d-flex flex-column min-vh-100">

        <?php include 'partials/header-nav.php'; ?>

        <div class="container-fluid py-0">
            <style>
                .maintenance-page {
                    padding: 14px 12px 24px;
                    background: #f4f7fb;
                    min-height: calc(100vh - 70px)
                }

                .maintenance-hero,
                .maintenance-tabs,
                .maintenance-panel {
                    background: #fff;
                    border: 1px solid #e5ebf3;
                    border-radius: 14px;
                    box-shadow: 0 4px 18px rgba(24, 50, 85, .045)
                }

                .maintenance-hero {
                    padding: 18px 22px;
                    margin-bottom: 16px
                }

                .maintenance-hero-icon,
                .maintenance-category-icon {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #1677ff;
                    background: #eaf2ff;
                    border-radius: 12px;
                    width: 52px;
                    height: 52px;
                    flex: 0 0 52px
                }

                .maintenance-hero h2 {
                    font-size: 1.55rem;
                    color: #17233c
                }

                .maintenance-hero p,
                .maintenance-subtitle {
                    color: #71809a
                }

                .maintenance-tabs {
                    display: flex;
                    align-items: stretch;
                    gap: 0;
                    padding: 0 10px;
                    margin-bottom: 16px;
                    overflow-x: auto
                }

                .maintenance-tab {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 9px;
                    flex: 1 0 auto;
                    padding: 15px 18px;
                    color: #64748b;
                    font-weight: 600;
                    text-decoration: none;
                    border: 0;
                    border-bottom: 3px solid transparent;
                    background: #fff;
                    white-space: nowrap
                }

                .maintenance-tab:hover {
                    color: #1677ff;
                    background: #f8fbff
                }

                .maintenance-tab.active {
                    color: #1677ff;
                    border-bottom-color: #1677ff
                }

                .maintenance-tab i {
                    font-size: 1.2rem
                }

                .maintenance-category-panel button[data-bs-target="#addModal"] {
                    display: none !important
                }

                .maintenance-panel {
                    padding: 20px
                }

                .maintenance-category-panel>.card {
                    border: 1px solid #e5ebf3 !important;
                    border-radius: 14px !important;
                    box-shadow: 0 4px 18px rgba(24, 50, 85, .045) !important;
                    margin-top: 0 !important;
                    height: auto !important
                }

                .maintenance-category-panel>.card>.card-body {
                    padding: 20px
                }

                .maintenance-panel-heading {
                    display: flex;
                    align-items: center;
                    gap: 13px;
                    margin-bottom: 18px
                }

                .maintenance-category-panel .table thead th {
                    background: #f3f6fa;
                    color: #61718c;
                    font-size: .72rem;
                    text-transform: uppercase;
                    letter-spacing: .035em;
                    font-weight: 700;
                    border-bottom: 1px solid #e4eaf2;
                    padding: 12px 14px
                }

                .maintenance-category-panel .table tbody td {
                    padding: 11px 14px;
                    border-color: #e9eef5;
                    color: #24334d
                }

                .maintenance-category-panel .table-responsive {
                    height: auto !important;
                    max-height: 360px
                }

                .maintenance-category-panel .btn.btn-sm {
                    min-width: 32px;
                    min-height: 32px;
                    border-radius: 6px
                }

                .maintenance-category-panel .pagination {
                    margin-top: 16px !important
                }

                @media(max-width:767.98px) {
                    .maintenance-category-panel>.card>.card-body {
                        padding: 14px 10px
                    }

                    .maintenance-page {
                        padding: 10px 8px 20px
                    }

                    .maintenance-hero {
                        padding: 14px
                    }

                    .maintenance-hero h2 {
                        font-size: 1.25rem
                    }

                    .maintenance-hero .btn {
                        padding: .55rem .7rem;
                        white-space: nowrap
                    }

                    .maintenance-tabs {
                        justify-content: flex-start;
                        padding: 0 6px
                    }

                    .maintenance-tab {
                        padding: 13px 12px
                    }

                    .maintenance-panel {
                        padding: 14px 10px
                    }
                }

                .wrapper {
                    min-height: 100vh;
                }

                .wrapper>.container-fluid {
                    flex: 1 0 auto;
                }
            </style>
            <div class="maintenance-page">
                <div class="maintenance-hero">
                    <div class="d-flex align-items-center gap-3">
                        <div class="maintenance-hero-icon"><i class="bi bi-tools fs-3"></i></div>
                        <div class="flex-grow-1">
                            <h2 class="fw-bold mb-1">Maintenance</h2>
                            <p class="mb-0">Manage system dropdown options and reference data.</p>
                        </div>
                        <button type="button" class="btn btn-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#addModal" onclick="setCategoryFromActiveTab()"><i class="bi bi-plus-lg me-1"></i> Add New</button>
                    </div>
                </div>
                <div class="maintenance-tabs" role="tablist" aria-label="Maintenance categories">
                    <button type="button" class="maintenance-tab" data-category-tab="branch" onclick="showMaintenanceCategory('branch')"><i class="bi bi-buildings"></i> Branch</button>
                    <button type="button" class="maintenance-tab" data-category-tab="placement" onclick="showMaintenanceCategory('placement')"><i class="bi bi-geo-alt"></i> Placement</button>
                    <button type="button" class="maintenance-tab" data-category-tab="type" onclick="showMaintenanceCategory('type')"><i class="bi bi-fire"></i> Type</button>
                    <button type="button" class="maintenance-tab" data-category-tab="fire_class" onclick="showMaintenanceCategory('fire_class')"><i class="bi bi-file-earmark-text"></i> Fire Class</button>
                    <button type="button" class="maintenance-tab" data-category-tab="capacity" onclick="showMaintenanceCategory('capacity')"><i class="bi bi-box-seam"></i> Capacity</button>
                </div>
                <div class="container-fluid px-0" id="maintenance-options">

                    <div class="row g-4">

                        <!-- BRANCH -->

                        <div class="col-12 maintenance-category-panel" id="maintenance-category-branch" data-category-panel="branch">

                            <div class="card h-100 border border-primary-subtle shadow rounded-3 mt-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded p-2">
                                                <i class="bi bi-buildings fs-4"></i>
                                            </div>
                                            <div>
                                                <h5 class="fw-bold mb-0">
                                                    Branch
                                                </h5>
                                                <small class="text-muted">
                                                    Manage branch options.
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- SEARCH -->
                                    <form method="GET" class="mb-3">

                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="bi bi-search"></i>
                                            </span>
                                            <input type="text"
                                                name="branch_search"
                                                value="<?= htmlspecialchars($branchData['search']) ?>"
                                                class="form-control"
                                                placeholder="Search branch...">
                                            <button class="btn btn-primary">
                                                Search
                                            </button>
                                        </div>
                                    </form>

                                    <!-- TABLE -->
                                    <div class="table-responsive" style="height: 270px;">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Branch Name</th>
                                                    <th>Status</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php if (empty($branchData['records'])): ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-4">
                                                            No branch found.
                                                        </td>
                                                    </tr>

                                                <?php else: ?>

                                                    <?php foreach ($branchData['records'] as $index => $row): ?>

                                                        <tr>

                                                            <td>
                                                                <?= (($branchData['page'] - 1) * 5) + $index + 1 ?>
                                                            </td>

                                                            <td>
                                                                <?= htmlspecialchars($row['value']) ?>
                                                            </td>

                                                            <td>
                                                                <span class="badge text-bg-success">
                                                                    Active
                                                                </span>
                                                            </td>

                                                            <td class="text-end">
                                                                <button type="button"
                                                                    class="btn btn-primary btn-sm"
                                                                    onclick="editItem(
                                                                    <?= $row['id'] ?>, 
                                                                    '<?= htmlspecialchars($row['value'], ENT_QUOTES) ?>' )">

                                                                    <i class="bi bi-pencil"></i>

                                                                </button>

                                                                <button type="button" class="btn btn-danger btn-sm"
                                                                    onclick="deleteItem(
                                                                    <?= $row['id'] ?>,
                                                                    '<?= htmlspecialchars($row['value'], ENT_QUOTES) ?>' )">

                                                                    <i class="bi bi-trash"></i>

                                                                </button>

                                                            </td>

                                                        </tr>

                                                    <?php endforeach; ?>

                                                <?php endif; ?>

                                            </tbody>

                                        </table>

                                    </div>

                                    <!-- PAGINATION -->

                                    <?php if ($branchData['total_pages'] > 1): ?>

                                        <nav class="mt-3">

                                            <ul class="pagination pagination-sm justify-content-center mb-0">

                                                <?php for ($i = 1; $i <= $branchData['total_pages']; $i++): ?>

                                                    <?php
                                                    $params = $_GET;
                                                    $params['branch_page'] = $i;
                                                    $params['branch_search'] = $branchData['search'];
                                                    $url = $_SERVER['PHP_SELF'] . '?' . http_build_query($params);
                                                    ?>

                                                    <li class="page-item <?= $i == $branchData['page'] ? 'active' : '' ?>">
                                                        <a class="page-link px-2 py-1" href="<?= htmlspecialchars($url) ?>#maintenance-options">
                                                            <?= $i ?>
                                                        </a>
                                                    </li>

                                                <?php endfor; ?>
                                            </ul>
                                        </nav>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- PLACEMENT -->
                        <div class="col-12 maintenance-category-panel" id="maintenance-category-placement" data-category-panel="placement">
                            <div class="card h-100 border border-primary-subtle shadow rounded-3 mt-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded p-2">
                                                <i class="bi bi-wrench-adjustable fs-4"></i>
                                            </div>

                                            <div>
                                                <h5 class="fw-bold mb-0">
                                                    Placement
                                                </h5>
                                                <small class="text-muted">
                                                    Manage placement options.
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <form method="GET" class="mb-3">

                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="bi bi-search"></i>
                                            </span>

                                            <input type="text" name="placement_search" value="<?= htmlspecialchars($placement['search']) ?>" class="form-control" placeholder="Search placement...">

                                            <button class="btn btn-primary">
                                                Search
                                            </button>
                                        </div>

                                    </form>

                                    <div class="table-responsive" style="height: 270px;">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">

                                                <tr>
                                                    <th>#</th>
                                                    <th>Placement</th>
                                                    <th>Status</th>
                                                    <th class="text-end">Actions</th>

                                                </tr>

                                            </thead>

                                            <tbody>
                                                <?php if (empty($placement['records'])): ?>

                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-4">
                                                            No Placement found.
                                                        </td>
                                                    </tr>

                                                <?php else: ?>
                                                    <?php foreach ($placement['records'] as $index => $row): ?>
                                                        <tr>
                                                            <td>
                                                                <?= (($placement['page'] - 1) * 5) + $index + 1 ?>
                                                            </td>

                                                            <td>
                                                                <?= htmlspecialchars($row['value']) ?>
                                                            </td>

                                                            <td>
                                                                <span class="badge text-bg-success">
                                                                    Active
                                                                </span>
                                                            </td>

                                                            <td class="text-end">

                                                                <button type="button" class="btn btn-primary btn-sm"
                                                                    onclick="editItem(
                                                                    <?= $row['id'] ?>,
                                                                    '<?= htmlspecialchars($row['value'], ENT_QUOTES) ?>' )">
                                                                    <i class="bi bi-pencil"></i>
                                                                </button>

                                                                <button type="button" class="btn btn-danger btn-sm"
                                                                    onclick="deleteItem(
                                                                    <?= $row['id'] ?>,
                                                                    '<?= htmlspecialchars($row['value'], ENT_QUOTES) ?>' )">

                                                                    <i class="bi bi-trash"></i>
                                                                </button>

                                                            </td>
                                                        </tr>

                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <?php if ($placement['total_pages'] > 1): ?>

                                        <nav class="mt-3">
                                            <ul class="pagination pagination-sm justify-content-center mb-0">

                                                <?php for ($i = 1; $i <= $placement['total_pages']; $i++): ?>

                                                    <li class="page-item  <?= $i == $placement['page'] ? 'active' : '' ?>">
                                                        <a class="page-link px-2 py-1" href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?placement_page=<?= $i ?>&placement_search=<?= urlencode($placement['search']) ?>">
                                                            <?= $i ?>
                                                        </a>
                                                    </li>

                                                <?php endfor; ?>
                                            </ul>
                                        </nav>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- TYPE -->
                        <div class="col-12 maintenance-category-panel" id="maintenance-category-type" data-category-panel="type">
                            <div class="card h-100 border border-primary-subtle shadow rounded-3 mt-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded p-2">
                                                <i class="bi bi-fire fs-4"></i>
                                            </div>

                                            <div>
                                                <h5 class="fw-bold mb-0">
                                                    Type
                                                </h5>
                                                <small class="text-muted">
                                                    Manage extinguisher types.
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <form method="GET" class="mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="bi bi-search"></i>
                                            </span>
                                            <input type="text" name="type_search" value="<?= htmlspecialchars($typeData['search']) ?>" class="form-control" placeholder="Search type...">
                                            <button class="btn btn-primary">
                                                Search
                                            </button>
                                        </div>
                                    </form>

                                    <div class="table-responsive" style="height: 270px;">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Type</th>
                                                    <th>Status</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php if (empty($typeData['records'])): ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-4">
                                                            No type found.
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($typeData['records'] as $index => $row): ?>
                                                        <tr>
                                                            <td>
                                                                <?= (($typeData['page'] - 1) * 5) + $index + 1 ?>
                                                            </td>
                                                            <td>
                                                                <?= htmlspecialchars($row['value']) ?>
                                                            </td>
                                                            <td>
                                                                <span class="badge text-bg-success">
                                                                    Active
                                                                </span>
                                                            </td>

                                                            <td class="text-end">

                                                                <button type="button"
                                                                    class="btn btn-primary btn-sm"
                                                                    onclick="editItem(
                                                                    <?= $row['id'] ?>,
                                                                    '<?= htmlspecialchars($row['value'], ENT_QUOTES) ?>' )">
                                                                    <i class="bi bi-pencil"></i>
                                                                </button>

                                                                <button type="button" class="btn btn-danger btn-sm"
                                                                    onclick="deleteItem( <?= $row['id'] ?>, '<?= htmlspecialchars($row['value'], ENT_QUOTES) ?>')">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <?php if ($typeData['total_pages'] > 1): ?>
                                        <nav class="mt-3">
                                            <ul class="pagination pagination-sm justify-content-center mb-0">

                                                <?php for ($i = 1; $i <= $typeData['total_pages']; $i++): ?>
                                                    <li class="page-item  <?= $i == $typeData['page'] ? 'active' : '' ?>">
                                                        <a class="page-link px-2 py-1" href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?type_page=<?= $i ?>&type_search=<?= urlencode($typeData['search']) ?>">
                                                            <?= $i ?>
                                                        </a>
                                                    </li>
                                                <?php endfor; ?>
                                            </ul>
                                        </nav>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- FIRE CLASS -->
                        <div class="col-12 maintenance-category-panel" id="maintenance-category-fire_class" data-category-panel="fire_class">
                            <div class="card h-100 border border-primary-subtle shadow rounded-3 mt-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded p-2">
                                                <i class="bi bi-fire fs-4"></i>
                                            </div>

                                            <div>
                                                <h5 class="fw-bold mb-0">
                                                    Fire Class
                                                </h5>
                                                <small class="text-muted">
                                                    Manage fire class options.
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <form method="GET" class="mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="bi bi-search"></i>
                                            </span>

                                            <input type="text" name="fire_class_search" value="<?= htmlspecialchars($fireClassData['search']) ?>" class="form-control" placeholder="Search fire class...">

                                            <button class="btn btn-primary">
                                                Search
                                            </button>
                                        </div>
                                    </form>

                                    <div class="table-responsive" style="height: 270px;">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Fire Class</th>
                                                    <th>Status</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($fireClassData['records'])): ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-4">
                                                            No fire class found.
                                                        </td>
                                                    </tr>

                                                <?php else: ?>
                                                    <?php foreach ($fireClassData['records'] as $index => $row): ?>
                                                        <tr>
                                                            <td>
                                                                <?= (($fireClassData['page'] - 1) * 5) + $index + 1 ?>
                                                            </td>
                                                            <td>
                                                                <?= htmlspecialchars($row['value']) ?>
                                                            </td>
                                                            <td>
                                                                <span class="badge text-bg-success">
                                                                    Active
                                                                </span>
                                                            </td>

                                                            <td class="text-end">
                                                                <button type="button" class="btn btn-primary btn-sm" onclick="editItem( <?= $row['id'] ?>, '<?= htmlspecialchars($row['value'], ENT_QUOTES) ?>' )">
                                                                    <i class="bi bi-pencil"></i>
                                                                </button>

                                                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteItem( <?= $row['id'] ?>, '<?= htmlspecialchars($row['value'], ENT_QUOTES) ?>' )">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <?php if ($fireClassData['total_pages'] > 1): ?>
                                        <nav class="mt-3">
                                            <ul class="pagination pagination-sm justify-content-center mb-0">

                                                <?php for ($i = 1; $i <= $fireClassData['total_pages']; $i++): ?>

                                                    <li class="page-item <?= $i == $fireClassData['page'] ? 'active' : '' ?>">
                                                        <a class="page-link px-2 py-1" href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?fire_class_page=<?= $i ?>&fire_class_search=<?= urlencode($fireClassData['search']) ?>">
                                                            <?= $i ?>
                                                        </a>
                                                    </li>
                                                <?php endfor; ?>
                                            </ul>
                                        </nav>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- CAPACITY -->
                        <div class="col-12 maintenance-category-panel" id="maintenance-category-capacity" data-category-panel="capacity">
                            <div class="card h-100 border border-primary-subtle shadow rounded-3 mt-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded p-2">
                                                <i class="bi bi-box-seam fs-4"></i>
                                            </div>
                                            <div>
                                                <h5 class="fw-bold mb-0">
                                                    Capacity
                                                </h5>
                                                <small class="text-muted">
                                                    Manage capacity options.
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <form method="GET" class="mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="bi bi-search"></i>
                                            </span>

                                            <input type="text" name="capacity_search" value="<?= htmlspecialchars($capacityData['search']) ?>" class="form-control" placeholder="Search capacity...">
                                            <button class="btn btn-primary">
                                                Search
                                            </button>
                                        </div>

                                    </form>
                                    <div class="table-responsive" style="height: 270px;">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Capacity</th>
                                                    <th>Status</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($capacityData['records'])): ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-4">
                                                            No capacity found.
                                                        </td>
                                                    </tr>

                                                <?php else: ?>
                                                    <?php foreach ($capacityData['records'] as $index => $row): ?>
                                                        <tr>
                                                            <td>
                                                                <?= (($capacityData['page'] - 1) * 5) + $index + 1 ?>
                                                            </td>
                                                            <td>
                                                                <?= htmlspecialchars($row['value']) ?>
                                                            </td>
                                                            <td>
                                                                <span class="badge text-bg-success">
                                                                    Active
                                                                </span>
                                                            </td>
                                                            <td class="text-end">
                                                                <button type="button" class="btn btn-primary btn-sm" onclick="editItem( <?= $row['id'] ?>, '<?= htmlspecialchars($row['value'], ENT_QUOTES) ?>' )">
                                                                    <i class="bi bi-pencil"></i>
                                                                </button>

                                                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteItem( <?= $row['id'] ?>, '<?= htmlspecialchars($row['value'], ENT_QUOTES) ?>' )">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>

                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <?php if ($capacityData['total_pages'] > 1): ?>

                                        <nav class="mt-3">
                                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                                <?php for ($i = 1; $i <= $capacityData['total_pages']; $i++): ?>
                                                    <li class="page-item
                                                    <?= $i == $capacityData['page'] ? 'active' : '' ?>">
                                                        <a class="page-link px-2 py-1" href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?capacity_page=<?= $i ?>&capacity_search=<?= urlencode($capacityData['search']) ?>">
                                                            <?= $i ?>
                                                        </a>
                                                    </li>
                                                <?php endfor; ?>
                                            </ul>
                                        </nav>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Add Dropdown Option
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">
                                Category
                            </label>
                            <select name="category" id="addCategory" class="form-select" required>

                                <option value="">
                                    Select category
                                </option>

                                <option value="branch">
                                    Branch
                                </option>

                                <option value="placement">
                                    Placement
                                </option>

                                <option value="type">
                                    Type
                                </option>

                                <option value="fire_class">
                                    Fire Class
                                </option>

                                <option value="capacity">
                                    Capacity
                                </option>

                                <option value="location">
                                    Location / Area
                                </option>
                            </select>

                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Value
                            </label>
                            <input type="text" name="value" class="form-control" placeholder="Enter value" required>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i>
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            Edit Dropdown Option
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                        </button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label class="form-label">
                                Value
                            </label>
                            <input type="text" name="value" id="editValue" class="form-control" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i>
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- delete modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Delete
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                        </button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteId">
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle"></i>
                            Are you sure you want to delete
                            <strong id="deleteValue"></strong>?
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i>
                            Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let activeMaintenanceCategory = 'branch';

        function showMaintenanceCategory(category) {
            activeMaintenanceCategory = category;
            document.querySelectorAll('[data-category-panel]').forEach(panel => panel.classList.toggle('d-none', panel.dataset.categoryPanel !== category));
            document.querySelectorAll('[data-category-tab]').forEach(tab => {
                const active = tab.dataset.categoryTab === category;
                tab.classList.toggle('active', active);
                tab.setAttribute('aria-selected', active ? 'true' : 'false');
            });
        }

        function setCategoryFromActiveTab() {
            document.getElementById('addCategory').value = activeMaintenanceCategory;
        }

        function setCategory(category) {
            document.getElementById('addCategory').value = category;
        }
        const requestedMaintenanceCategory = <?php
                                                $requestedCategory = 'branch';
                                                foreach (['branch', 'placement', 'type', 'fire_class', 'capacity'] as $categoryKey) {
                                                    if (isset($_GET[$categoryKey . '_search']) || isset($_GET[$categoryKey . '_page'])) $requestedCategory = $categoryKey;
                                                }
                                                echo json_encode($requestedCategory);
                                                ?>;
        showMaintenanceCategory(requestedMaintenanceCategory);

        function editItem(id, value) {
            document.getElementById('editId').value = id;
            document.getElementById('editValue').value = value;

            const modal = new bootstrap.Modal(
                document.getElementById('editModal')
            );

            modal.show();
        }

        function deleteItem(id, value) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteValue').textContent = value;

            const modal = new bootstrap.Modal(
                document.getElementById('deleteModal')
            );

            modal.show();
        }
    </script>

    <?php include 'partials/footer.php'; ?>

    </div>

    <!-- CoreUI and necessary plugins-->

    <script src="vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>

    <script src="vendors/simplebar/js/simplebar.min.js"></script>

    <script>
        const header = document.querySelector("header.header");

        document.addEventListener("scroll", () => {
            if (header) {
                header.classList.toggle("shadow-sm", document.documentElement.scrollTop > 0);
            }
        });
    </script>

    <?php include_once 'notification/session_timeout.php'; ?>

</body>

</html>