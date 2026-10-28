 <head>
    <base href="./">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="CoreUI - Open Source Bootstrap Admin Template">
    <meta name="author" content="Łukasz Holeczek">
    <meta name="keyword" content="Bootstrap,Admin,Template,Open,Source,jQuery,CSS,HTML,RWD,Dashboard">
    <title>IMS | Fire Equipment Management System</title>

    <link rel="icon" type="image/png" sizes="192x192" href="assets/img/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon.ico">
    <link rel="icon" type="image/png" sizes="96x96" href="assets/img/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon.ico">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" rel="stylesheet">
    <!-- <link rel="manifest" href="assets/favicon/manifest.json"> -->
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="assets/">
    <meta name="theme-color" content="#ffffff">
    <!-- Vendors styles-->
    <link rel="stylesheet" href="vendors/simplebar/css/simplebar.css">
    <link rel="stylesheet" href="css/vendors/simplebar.css">
    <!-- Main styles for this application-->
    <link href="css/style.css" rel="stylesheet">
    <!-- <script src="js/color-modes.js"></script> -->
    <!-- We use those styles to show code examples, you should remove them in your application.-->
    <!-- <link href="css/examples.css" rel="stylesheet">
    <script src="js/config.js"></script>
   -->

    <!-- added: Jaja.Dev -->

    <script src="https://unpkg.com/html5-qrcode"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../css/header-nav.css">
    <link href="css/admin-pending-inspections.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 </head>


 <!-- for QR code  -->
 <style>
    /* for  QR generate report */
    .qr-page {
       padding: 0.75rem 0;
    }

    .qr-header {
       margin-bottom: 0.75rem !important;
       border: 1px solid #e9ecef !important;
       box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06) !important;
    }

    .qr-header .card-body {
       padding: 1rem 1.25rem !important;
    }

    .qr-header-icon {
       width: 48px !important;
       height: 48px !important;
    }

    .qr-header h4 {
       font-size: 1.05rem;
    }

    .qr-header p {
       font-size: 0.78rem;
    }

    .qr-main-card {
       border: 1px solid #e9ecef !important;
       box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06) !important;
    }

    .qr-toolbar {
       padding: 0.75rem 1rem !important;
       border-bottom: 1px solid #e9ecef !important;
    }

    .qr-toolbar .form-control,
    .qr-toolbar .form-select,
    .qr-toolbar .input-group-text {
       min-height: 38px;
       border-color: #dee2e6;
    }

    .qr-table-wrapper {
       overflow-x: auto;
    }

    .qr-table {
       min-width: 760px;
       margin-bottom: 0 !important;
       border-collapse: separate;
       border-spacing: 0;
    }

    .qr-table thead th {
       padding: 0.65rem 0.75rem;
       background: #f8f9fa !important;
       border-bottom: 1px solid #dee2e6;
       color: #6c757d !important;
       font-size: 0.75rem;
       font-weight: 600;
       white-space: nowrap;
       vertical-align: middle;
    }

    .qr-table tbody td {
       padding: 0.7rem 0.75rem;
       border-bottom: 1px solid #edf0f2;
       font-size: 0.85rem;
       color: #212529;
       vertical-align: middle;
    }

    .qr-table tbody tr:last-child td {
       border-bottom: 0;
    }

    .qr-table tbody tr:hover {
       background: #f8fafc;
    }

    .qr-table .code-icon {
       width: 32px !important;
       height: 32px !important;
       flex-shrink: 0;
    }

    .qr-table .type-badge {
       font-size: 0.72rem;
       padding: 0.35rem 0.6rem !important;
    }

    .qr-selection-footer {
       padding: 0.7rem 1rem !important;
       border-top: 1px solid #dee2e6 !important;
    }

    .qr-selection-footer .btn {
       font-size: 0.8rem;
       padding: 0.4rem 0.7rem;
    }

    @media (max-width: 767.98px) {
       .qr-page {
          padding: 0.5rem 0;
       }

       .qr-header .card-body {
          padding: 0.85rem 1rem !important;
       }

       .qr-header .badge {
          display: none;
       }

       .qr-toolbar {
          padding: 0.65rem 0.75rem !important;
       }

       .qr-table {
          min-width: 700px;
       }

       .qr-table thead th,
       .qr-table tbody td {
          padding: 0.6rem 0.65rem;
       }
    }

    /* for inspection approve module */

    .inspection-page {
       padding-bottom: 80px;
    }

    .inspection-header {
       border-radius: .75rem;
    }

    .inspection-header-icon {
       width: 64px;
       height: 64px;
       display: flex;
       align-items: center;
       justify-content: center;
       flex-shrink: 0;
    }

    .inspection-filter-card {
       border-radius: .75rem;
    }

    .inspection-filter-card .card-body {
       padding: 1rem;
    }

    .inspection-filter-card .input-group,
    .inspection-filter-card .form-select {
       min-height: 42px;
    }

    .inspection-filter-card .input-group-text {
       min-width: 44px;
       justify-content: center;
    }

    .inspection-table-card {
       border-radius: .75rem;
       overflow: hidden;
    }

    .inspection-table-wrapper {
       width: 100%;
       overflow-x: auto;
       -webkit-overflow-scrolling: touch;
    }

    .inspection-table {
       min-width: 900px;
       margin-bottom: 0 !important;
    }

    .inspection-table th {
       font-size: .82rem;
       font-weight: 600;
       white-space: nowrap;
       vertical-align: middle;
    }

    .inspection-table td {
       font-size: .9rem;
       vertical-align: middle;
    }

    .inspection-table tbody tr {
       height: 64px;
    }

    .inspection-table .status-badge {
       white-space: nowrap;
    }

    .inspection-table .inspection-actions {
       display: flex;
       justify-content: flex-end;
       align-items: center;
       flex-wrap: wrap;
       gap: .35rem;
    }

    .inspection-table .inspection-actions .btn {
       white-space: nowrap;
    }

    .inspection-empty-state {
       min-height: 220px;
       display: flex;
       align-items: center;
       justify-content: center;
    }

    .inspection-pagination {
       z-index: 1020;
    }

    .inspection-pagination-inner {
       width: 100%;
    }

    .inspection-pagination .pagination {
       margin-bottom: 0;
    }

    .inspection-content {
       padding-bottom: 90px;
    }

    @media (max-width:991.98px) {
       .inspection-header .card-body {
          padding: 1.25rem;
       }

       .inspection-header-icon {
          width: 56px;
          height: 56px;
          font-size: 1.6rem !important;
       }

       .inspection-header h2 {
          font-size: 1.4rem;
       }
    }

    @media (max-width:767.98px) {
       .inspection-page {
          padding-left: .5rem;
          padding-right: .5rem;
       }

       .inspection-header .card-body {
          padding: 1rem;
       }

       .inspection-header .row {
          align-items: center;
       }

       .inspection-header-icon {
          width: 52px;
          height: 52px;
          padding: .75rem !important;
          font-size: 1.4rem !important;
       }

       .inspection-header h2 {
          font-size: 1.2rem;
       }

       .inspection-header p {
          font-size: .85rem;
       }

       .inspection-filter-card .card-body {
          padding: .85rem;
       }

       .inspection-table {
          min-width: 900px;
       }

       .inspection-table th,
       .inspection-table td {
          padding-top: .75rem;
          padding-bottom: .75rem;
       }

       .inspection-table .inspection-actions {
          justify-content: flex-end;
       }

       .inspection-table .inspection-actions .btn {
          font-size: .78rem;
          padding: .3rem .55rem;
       }

       .inspection-pagination {
          padding-top: .5rem !important;
          padding-bottom: .5rem !important;
       }

       .inspection-pagination .container-fluid {
          padding-left: .75rem !important;
          padding-right: .75rem !important;
       }
    }

    @media (max-width:575.98px) {
       .inspection-page {
          padding-left: .25rem;
          padding-right: .25rem;
       }

       .inspection-header {
          margin-bottom: .75rem !important;
       }

       .inspection-header .row {
          flex-wrap: nowrap;
       }

       .inspection-header-icon {
          width: 46px;
          height: 46px;
          padding: .6rem !important;
       }

       .inspection-header h2 {
          font-size: 1.05rem;
       }

       .inspection-header p {
          font-size: .78rem;
       }

       .inspection-filter-card {
          margin-bottom: .75rem !important;
       }

       .inspection-filter-card .card-body {
          padding: .75rem;
       }

       .inspection-table-card {
          border-radius: .6rem;
       }

       .inspection-table {
          min-width: 880px;
       }

       .inspection-pagination-inner {
          gap: .5rem !important;
       }

       .inspection-pagination .text-body-secondary {
          font-size: .72rem;
       }

       .inspection-pagination .page-link {
          padding: .3rem .55rem;
       }
    }

    /* Approved Inspection toolbar and table refinements */
    .approved-inspection-page .inspection-approved-toolbar {
       --bs-gutter-y: 1rem;
    }

    .approved-inspection-page .inspection-approved-search,
    .approved-inspection-page .inspection-approved-date-form {
       width: 100%;
       min-width: 0;
       margin: 0;
    }

    .approved-inspection-page .inspection-approved-toolbar .form-label {
       display: block;
       margin-bottom: .35rem !important;
       font-size: .8rem;
       font-weight: 600;
    }

    .approved-inspection-page .inspection-approved-toolbar .form-control,
    .approved-inspection-page .inspection-approved-toolbar .form-select,
    .approved-inspection-page .inspection-approved-toolbar .input-group-text,
    .approved-inspection-page .inspection-approved-toolbar .btn {
       min-height: 42px;
    }

    .approved-inspection-page .inspection-approved-toolbar .form-control,
    .approved-inspection-page .inspection-approved-toolbar .form-select {
       min-width: 0;
    }

    .approved-inspection-page .inspection-approved-date-actions .btn {
       white-space: nowrap;
    }

    .approved-inspection-page .inspection-approved-pdf-button {
       display: inline-flex;
       align-items: center;
       justify-content: center;
       gap: .15rem;
       font-size: .9rem;
    }

    .approved-inspection-page .inspection-table-card {
       min-width: 0;
    }

    .approved-inspection-page .inspection-table-wrapper {
       width: 100%;
       max-width: 100%;
       overflow-x: auto;
       -webkit-overflow-scrolling: touch;
    }

    .approved-inspection-page .inspection-table {
       min-width: 960px;
       margin-bottom: 0 !important;
    }

    .approved-inspection-page .inspection-table th,
    .approved-inspection-page .inspection-table td {
       vertical-align: middle;
    }

    .approved-inspection-page .inspection-location-cell {
       min-width: 12rem;
       max-width: 26rem;
       overflow-wrap: anywhere;
    }

    .approved-inspection-page .inspection-table .status-badge {
       white-space: nowrap;
    }

    .approved-inspection-page .inspection-pagination-inner {
       row-gap: .75rem;
    }

    @media (max-width: 767.98px) {
       .approved-inspection-page .inspection-approved-pdf-button {
          font-size: 1rem;
       }

       .approved-inspection-page .inspection-location-cell {
          min-width: 14rem;
       }
    }
 </style>