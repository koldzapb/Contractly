<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Contractly Weekly Summary</title>
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
        .period {
            color: #6b7280;
            font-size: 14px;
            margin-top: 8px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 24px;
        }
        .stats {
            display: table;
            width: 100%;
            margin-bottom: 32px;
            background-color: #f9fafb;
            border-radius: 8px;
        }
        .stat-item {
            display: table-cell;
            text-align: center;
            padding: 20px 16px;
            border-right: 1px solid #e5e7eb;
        }
        .stat-item:last-child {
            border-right: none;
        }
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #4f46e5;
        }
        .stat-value.warning {
            color: #f59e0b;
        }
        .stat-value.danger {
            color: #ef4444;
        }
        .stat-label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .section {
            margin-bottom: 32px;
        }
        .section h3 {
            font-size: 16px;
            color: #374151;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }
        .item-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .item-list li {
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .item-list li:last-child {
            border-bottom: none;
        }
        .item-title {
            font-weight: 600;
            color: #111827;
        }
        .item-meta {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }
        .risk-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
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
        .empty-state {
            text-align: center;
            padding: 24px;
            color: #6b7280;
            font-style: italic;
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
            <div class="period">
                Weekly Summary: {{ $periodStart->format('M j') }} - {{ $periodEnd->format('M j, Y') }}
            </div>
        </div>

        <p class="greeting">Hi {{ $user->name }},</p>
        <p>Here's what happened with your contracts this week:</p>

        <div class="stats">
            <div class="stat-item">
                <div class="stat-value">{{ $contractsAnalyzed }}</div>
                <div class="stat-label">Contracts Analyzed</div>
            </div>
            <div class="stat-item">
                <div class="stat-value {{ $highRiskCount > 0 ? 'danger' : '' }}">{{ $highRiskCount }}</div>
                <div class="stat-label">High-Risk Items</div>
            </div>
            <div class="stat-item">
                <div class="stat-value {{ $upcomingDeadlines > 0 ? 'warning' : '' }}">{{ $upcomingDeadlines }}</div>
                <div class="stat-label">Upcoming Deadlines</div>
            </div>
        </div>

        @if($recentContracts->isNotEmpty())
            <div class="section">
                <h3>Recently Analyzed Contracts</h3>
                <ul class="item-list">
                    @foreach($recentContracts as $contract)
                        <li>
                            <div class="item-title">{{ $contract->title }}</div>
                            <div class="item-meta">
                                Analyzed {{ $contract->updated_at->diffForHumans() }}
                                @if($contract->analysis?->overall_risk_level)
                                    <span class="risk-badge risk-{{ $contract->analysis->overall_risk_level->value }}">
                                        {{ $contract->analysis->overall_risk_level->label() }}
                                    </span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($upcomingReminders->isNotEmpty())
            <div class="section">
                <h3>Upcoming Deadlines This Week</h3>
                <ul class="item-list">
                    @foreach($upcomingReminders as $reminder)
                        <li>
                            <div class="item-title">{{ $reminder->title }}</div>
                            <div class="item-meta">
                                {{ $reminder->remind_at->format('l, M j') }}
                                &bull;
                                {{ $reminder->contract->title }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($recentContracts->isEmpty() && $upcomingReminders->isEmpty())
            <div class="empty-state">
                <p>No activity this week. Upload a contract to get started!</p>
            </div>
        @endif

        <div style="text-align: center; margin-top: 24px;">
            <a href="{{ $appUrl }}/dashboard" class="button">
                View Dashboard
            </a>
        </div>

        <div class="footer">
            <p>
                Stay on top of your contracts with Contractly.
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
