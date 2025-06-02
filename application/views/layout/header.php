<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Gantt Chart App</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <style>
        @media (max-width: 768px) {
            .container-fluid {
                padding-left: 15px !important;
                padding-right: 15px !important;
            }
            .table-responsive {
                overflow-x: auto;
            }
        }
        
        /* DataTables custom styling */
        div.dataTables_wrapper div.dataTables_filter {
            text-align: right;
            margin-bottom: 10px;
        }
        
        div.dataTables_wrapper div.dataTables_filter input {
            margin-left: 0.5em;
            display: inline-block;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            background: none !important;
            border: 0 !important;
        }
        div.dataTables_wrapper div.dataTables_length select {
            width: 60px;
        }
    </style>
</head>
<body>
<!--<nav class="navbar navbar-expand-lg navbar-light bg-light">-->
<!--    <a class="navbar-brand" href="<?= site_url('projects'); ?>"></a>-->
<!--</nav>-->
<!-- Authentication Modal -->
<!-- /Authentication Modal -->
<div class="container-fluid mt-4 px-5">

