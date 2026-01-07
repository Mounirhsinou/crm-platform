<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $pageTitle ?? 'Secure Portal'; ?> -
        <?php echo Branding::getCompanyName(); ?>
    </title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo APP_URL; ?>/assets/img/favicon.png">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
            color: #333;
        }

        .public-container {
            max-width: 900px;
            margin: 40px auto;
        }

        .checkout-container {
            max-width: 520px;
            margin: 40px auto;
        }

        .public-card {
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border-radius: 16px;
            background: #fff;
            overflow: hidden;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }

        .form-label {
            color: #495057;
            margin-bottom: 0.4rem;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background-color: #fff;
            }

            .public-container {
                margin: 0;
                max-width: 100%;
            }

            .public-card {
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <div class="container public-container">
        <div class="public-card">