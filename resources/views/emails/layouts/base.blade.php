<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <style>
        :root {
            --tito-primary: #166534;
            --tito-primary-dark: #14532d;
            --tito-primary-light: #dcfce7;
            --tito-foreground: #0f172a;
            --tito-muted: #64748b;
            --tito-muted-light: #94a3b8;
            --tito-border: #e2e8f0;
            --tito-background: #ffffff;
            --tito-surface: #f8fafc;
        }

        body {
            background-color: var(--tito-background);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 0;
            width: 100% !important;
            -webkit-font-smoothing: antialiased;
        }

        table {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: var(--tito-surface);
            padding: 48px 0;
        }

        .content {
            max-width: 600px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .header {
            padding-bottom: 40px;
        }

        .logo {
            width: 140px;
            height: auto;
            margin-bottom: 8px;
        }

        .date {
            color: var(--tito-muted-light);
            font-size: 14px;
            font-weight: 500;
        }

        .body-content {
            color: var(--tito-muted);
            font-size: 16px;
            line-height: 1.6;
        }

        .preview-text {
            display: none;
        }

        .title {
            color: var(--tito-foreground);
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 24px 0;
        }

        .greeting {
            margin-bottom: 4px;
            color: var(--tito-foreground);
            font-weight: 500;
        }

        .text {
            margin-bottom: 24px;
        }

        .button-wrapper {
            padding: 24px 0 32px 0;
        }

        .button {
            background-color: var(--tito-primary);
            border-radius: 8px;
            color: #ffffff !important;
            display: inline-block;
            font-size: 15px;
            font-weight: 600;
            line-height: 1;
            padding: 16px 32px;
            text-decoration: none;
            width: auto;
            text-align: center;
            transition: background-color 0.2s ease;
        }

        .button:hover {
            background-color: var(--tito-primary-dark);
        }

        .expiry-text {
            font-size: 14px;
            color: var(--tito-muted-light);
            margin-bottom: 24px;
        }

        .fallback-text {
            font-size: 14px;
            color: var(--tito-muted-light);
            margin-bottom: 48px;
        }

        .fallback-link {
            color: var(--tito-primary);
            text-decoration: none;
            word-break: break-all;
            display: block;
        }

        .fallback-link:hover {
            color: var(--tito-primary-dark);
            text-decoration: underline;
        }

        .signature {
            margin-bottom: 40px;
            color: var(--tito-muted);
        }

        .divider {
            border-top: 1px solid var(--tito-border);
            margin-bottom: 24px;
        }

        .footer {
            color: var(--tito-muted-light);
            font-size: 13px;
            line-height: 1.5;
        }

        /* Status badges */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-success {
            background-color: var(--tito-primary-light);
            color: var(--tito-primary-dark);
        }

        .badge-error {
            background-color: #fee2e2;
            color: #dc2626;
        }

        /* Data tables */
        .data-table {
            width: 100%;
            border: 1px solid var(--tito-border);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 24px;
            background-color: var(--tito-background);
        }

        .data-table td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--tito-border);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table td:first-child {
            color: var(--tito-muted);
        }

        .data-table td:last-child {
            text-align: right;
            color: var(--tito-foreground);
            font-weight: 500;
        }

        @media screen and (max-width: 600px) {
            .button {
                display: block;
                width: 100%;
                box-sizing: border-box;
            }

            .wrapper {
                padding: 24px 0;
            }

            .content {
                padding: 0 16px;
            }

            .title {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="content">

            <!-- Preview Text -->
            <div style="display:none;overflow:hidden;line-height:1px;opacity:0;max-height:0;max-width:0"
                data-skip-in-text="true">
                @yield('preview')
                <div
                    style="display:none !important; visibility:hidden; opacity:0; color:transparent; height:0; width:0; max-height:0; max-width:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px;">
                    &#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;
                    &#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;
                    &#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;
                    &#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;
                    &#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;
                </div>
            </div>

            <!-- Header -->
            <div class="header">
                <img src="{{ $message->embed(public_path('media/logo.png')) }}" alt="Tito" class="logo">
                <div class="date">{{ now()->format('jS F Y') }}</div>
            </div>

            <!-- Body -->
            <div class="body-content">
                <h1 class="title">@yield('title')</h1>

                @yield('content')

                @hasSection('action')
                <div class="button-wrapper">
                    @yield('action')
                </div>
                @endif

                @hasSection('expiry')
                <p class="expiry-text">@yield('expiry')</p>
                @endif

                @hasSection('fallback')
                <p class="fallback-text">@yield('fallback')</p>
                @endif

                <div class="signature">
                    @yield('signature')
                </div>

                <div class="divider"></div>

                <div class="footer">
                    @yield('footer')
                </div>
            </div>
        </div>
    </div>
</body>

</html>
