<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Email' }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f6f6f6;
            margin: 0;
            padding: 32px 24px;

        }

        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 32px 24px;
        }

        .email-header {
            border-bottom: 1px solid #eaeaea;
            margin-bottom: 24px;
            padding-bottom: 16px;
            text-align: center;
        }

        .email-content {
            color: #333;
            font-size: 16px;
            line-height: 1.6;
        }

        .email-footer {
            margin-top: 32px;
            font-size: 13px;
            color: #888;
            text-align: center;
        }

        /* DaisyUI table styles */
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 16px;
            border-radius: 8px;
            overflow: hidden;
        }

        .table th,
        .table td {
            border: 1px solid #e5e7eb;
            padding: 12px 8px;
            text-align: left;
        }

        .table thead {
            background-color: #f3f4f6;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .table th {
            font-weight: 600;
            color: #374151;
        }

        .table td {
            color: #4b5563;
        }

        /* Angoli arrotondati per la tabella */
        .table th:first-child {
            border-top-left-radius: 8px;
        }

        .table th:last-child {
            border-top-right-radius: 8px;
        }

        .table tr:last-child td:first-child {
            border-bottom-left-radius: 8px;
        }

        .table tr:last-child td:last-child {
            border-bottom-right-radius: 8px;
        }

        /* Dark mode for table */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #1C242A !important;
            }

            .email-container {
                background: #1A1E24 !important;
                color: #EBF8FF !important;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
            }

            .email-header {
                border-bottom: 1px solid #ecf9ff26 !important;
            }

            .email-content {
                color: #EBF8FF !important;
            }

            .email-footer {
                color: #aaa !important;
            }

            .table thead {
                background-color: #23272b !important;
            }

            .table tbody tr:nth-child(even) {
                background-color: #23272b !important;
            }

            .table th {
                color: #EBF8FF !important;
                border: 1px solid #ecf9ff0d !important;
            }

            .table td {
                color: #EBF8FF !important;
                border: 1px solid #ecf9ff0d !important;
            }

        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <h2>{{ $header ?? config('app.name') }}</h2>
        </div>
        <div class="email-content">
            {{ $slot }}
        </div>
        <div class="email-footer">
            © {{ date('Y') }} {{ config('app.name') }}. Tutti i diritti riservati.
        </div>
    </div>
</body>

</html>
