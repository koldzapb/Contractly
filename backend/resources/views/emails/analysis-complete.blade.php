<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract Analysis Complete</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 32px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 32px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
        }
        .success-box {
            background-color: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 16px;
            margin-bottom: 24px;
            border-radius: 0 8px 8px 0;
        }
        .success-box h2 {
            margin: 0 0 8px 0;
            font-size: 18px;
            color: #065f46;
        }
        .risk-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 14px;
            font-weight: 600;
            text-transform: capitalize;
        }
        .risk-low {
            background-color: #d1fae5;
            color: #065f46;
        }
        .risk-medium {
            background-color: #fef3c7;
            color: #92400e;
        }
        .risk-high, .risk-critical {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .stats {
            display: table;
            width: 100%;
            margin-bottom: 24px;
        }
        .stat-item {
            display: table-cell;
            text-align: center;
            padding: 16px;
            border-right: 1px solid #e5e7eb;
        }
        .stat-item:last-child {
            border-right: none;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
        }
        .stat-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
        }
        .findings {
            margin-bottom: 24px;
        }
        .findings h3 {
            font-size: 16px;
            margin-bottom: 12px;
            color: #374151;
        }
        .findings ul {
            margin: 0;
            padding-left: 20px;
        }
        .findings li {
            margin-bottom: 8px;
            color: #4b5563;
        }
        .button {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
        }
        .button:hover {
            background-color: #4338ca;
        }
        .footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .unsubscribe {
            margin-top: 16px;
        }
        .unsubscribe a {
            color: #6b7280;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Contractly</div>
        </div>

        <div class="success-box">
            <h2>Analysis Complete!</h2>
            <p style="margin: 0;">Your contract "<strong>{{ $contract->title }}</strong>" has been analyzed.</p>
        </div>

        <div style="text-align: center; margin-bottom: 24px;">
            <span class="risk-badge risk-{{ $riskLevel }}">
                {{ ucfirst($riskLevel) }} Risk
            </span>
        </div>

        <div class="stats">
            <div class="stat-item">
                <div class="stat-value">{{ $clauseCount }}</div>
                <div class="stat-label">Clauses Found</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $deadlineCount }}</div>
                <div class="stat-label">Deadlines</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ count($keyFindings) }}</div>
                <div class="stat-label">Key Findings</div>
            </div>
        </div>

        @if(count($keyFindings) > 0)
            <div class="findings">
                <h3>Key Findings</h3>
                <ul>
                    @foreach(array_slice($keyFindings, 0, 5) as $finding)
                        <li>{{ $finding }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div style="text-align: center;">
            <a href="{{ $appUrl }}/contracts/{{ $contract->id }}" class="button">
                View Full Analysis
            </a>
        </div>

        <div class="footer">
            <p>
                Hi {{ $user->name }}, your contract analysis is ready for review.
            </p>
            <p>
                &copy; {{ date('Y') }} Contractly. All rights reserved.
            </p>
            <div class="unsubscribe">
                <a href="{{ $appUrl }}/settings/notifications">Manage notification preferences</a>
            </div>
        </div>
    </div>
</body>
</html>
