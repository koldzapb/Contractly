<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract Deadline Reminder</title>
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
        .alert-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 16px;
            margin-bottom: 24px;
            border-radius: 0 8px 8px 0;
        }
        .alert-box.urgent {
            background-color: #fee2e2;
            border-left-color: #ef4444;
        }
        .alert-box.today {
            background-color: #fef3c7;
            border-left-color: #f59e0b;
        }
        .alert-box h2 {
            margin: 0 0 8px 0;
            font-size: 18px;
            color: #92400e;
        }
        .alert-box.urgent h2 {
            color: #991b1b;
        }
        .details {
            margin-bottom: 24px;
        }
        .details-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .details-label {
            font-weight: 600;
            color: #6b7280;
            width: 140px;
            flex-shrink: 0;
        }
        .details-value {
            color: #111827;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Contractly</div>
        </div>

        <div class="alert-box {{ $daysUntil !== null && $daysUntil <= 1 ? ($daysUntil < 0 ? 'urgent' : 'today') : '' }}">
            <h2>
                @if($daysUntil === null)
                    Deadline Reminder
                @elseif($daysUntil < 0)
                    Deadline Passed!
                @elseif($daysUntil === 0)
                    Deadline is Today!
                @elseif($daysUntil === 1)
                    Deadline is Tomorrow!
                @else
                    Upcoming Deadline in {{ $daysUntil }} days
                @endif
            </h2>
            <p style="margin: 0;">{{ $reminder->title }}</p>
        </div>

        <div class="details">
            <div class="details-row">
                <span class="details-label">Contract</span>
                <span class="details-value">{{ $contract->title }}</span>
            </div>

            @if($deadline)
                <div class="details-row">
                    <span class="details-label">Deadline Type</span>
                    <span class="details-value">{{ $deadline->deadline_type->label() }}</span>
                </div>

                @if($deadline->deadline_date)
                    <div class="details-row">
                        <span class="details-label">Due Date</span>
                        <span class="details-value">{{ $deadline->deadline_date->format('l, F j, Y') }}</span>
                    </div>
                @endif

                @if($deadline->description)
                    <div class="details-row">
                        <span class="details-label">Description</span>
                        <span class="details-value">{{ $deadline->description }}</span>
                    </div>
                @endif
            @endif
        </div>

        <div style="text-align: center;">
            <a href="{{ $appUrl }}/contracts/{{ $contract->id }}" class="button">
                View Contract
            </a>
        </div>

        <div class="footer">
            <p>
                You received this email because you set a reminder for this contract deadline.
            </p>
            <p>
                &copy; {{ date('Y') }} Contractly. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
