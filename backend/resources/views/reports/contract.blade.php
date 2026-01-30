<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Contract Analysis Report - {{ $contract->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #1f2937;
        }

        .page-break {
            page-break-after: always;
        }

        /* Header */
        .header {
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header-title {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }

        .header-subtitle {
            font-size: 12px;
            color: #6b7280;
        }

        /* Contract Info Box */
        .info-box {
            background-color: #f3f4f6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }

        .info-box table {
            width: 100%;
        }

        .info-box td {
            padding: 5px 10px;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            color: #4b5563;
            width: 150px;
        }

        .info-value {
            color: #1f2937;
        }

        /* Risk Badge */
        .risk-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }

        .risk-high {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .risk-medium {
            background-color: #fef3c7;
            color: #d97706;
        }

        .risk-low {
            background-color: #d1fae5;
            color: #059669;
        }

        .risk-none {
            background-color: #f3f4f6;
            color: #6b7280;
        }

        /* Sections */
        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        /* Summary Box */
        .summary-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin-bottom: 20px;
        }

        .summary-text {
            font-size: 12px;
            line-height: 1.6;
        }

        /* Key Findings */
        .findings-list {
            list-style: none;
            padding: 0;
        }

        .findings-list li {
            padding: 8px 0;
            padding-left: 20px;
            position: relative;
            border-bottom: 1px solid #f3f4f6;
        }

        .findings-list li:before {
            content: "•";
            color: #3b82f6;
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        .findings-list li:last-child {
            border-bottom: none;
        }

        /* Statistics */
        .stats-grid {
            width: 100%;
            margin-bottom: 20px;
        }

        .stats-grid td {
            width: 25%;
            text-align: center;
            padding: 15px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
        }

        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #1e40af;
        }

        .stat-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
        }

        /* Clauses Table */
        .clauses-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .clauses-table th {
            background-color: #1e40af;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }

        .clauses-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
            font-size: 10px;
        }

        .clauses-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .clause-type {
            font-weight: bold;
            white-space: nowrap;
        }

        .clause-text {
            max-width: 300px;
            word-wrap: break-word;
        }

        /* Deadlines Table */
        .deadlines-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .deadlines-table th {
            background-color: #1e40af;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }

        .deadlines-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
            font-size: 10px;
        }

        .deadlines-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .deadline-urgent {
            color: #dc2626;
            font-weight: bold;
        }

        .deadline-upcoming {
            color: #d97706;
        }

        .deadline-past {
            color: #9ca3af;
            text-decoration: line-through;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 15px 40px;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
        }

        .footer-left {
            float: left;
        }

        .footer-right {
            float: right;
        }

        /* Disclaimer */
        .disclaimer {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 4px;
            padding: 12px;
            margin-top: 30px;
            font-size: 10px;
        }

        .disclaimer-title {
            font-weight: bold;
            color: #d97706;
            margin-bottom: 5px;
        }

        /* Warning boxes */
        .warning-box {
            background-color: #fee2e2;
            border-left: 4px solid #dc2626;
            padding: 10px 15px;
            margin-bottom: 10px;
        }

        .no-data {
            text-align: center;
            padding: 30px;
            color: #9ca3af;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-title">Contract Analysis Report</div>
        <div class="header-subtitle">Generated by Contractly on {{ $generatedAt->format('F j, Y \a\t g:i A') }}</div>
    </div>

    <!-- Contract Information -->
    <div class="info-box">
        <table>
            <tr>
                <td class="info-label">Contract Title:</td>
                <td class="info-value">{{ $contract->title }}</td>
                <td class="info-label">Overall Risk:</td>
                <td class="info-value">
                    @if($contract->overall_risk_level)
                        <span class="risk-badge risk-{{ $contract->overall_risk_level->value }}">
                            {{ $contract->overall_risk_level->label() }}
                        </span>
                    @else
                        <span class="risk-badge risk-none">Not Assessed</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="info-label">Original File:</td>
                <td class="info-value">{{ $contract->original_filename }}</td>
                <td class="info-label">File Type:</td>
                <td class="info-value">{{ $contract->file_type->label() }}</td>
            </tr>
            <tr>
                <td class="info-label">Upload Date:</td>
                <td class="info-value">{{ $contract->created_at->format('F j, Y') }}</td>
                <td class="info-label">Analysis Date:</td>
                <td class="info-value">{{ $contract->analyzed_at?->format('F j, Y') ?? 'Not analyzed' }}</td>
            </tr>
        </table>
    </div>

    <!-- Statistics -->
    <table class="stats-grid">
        <tr>
            <td>
                <div class="stat-number">{{ $totalClauses }}</div>
                <div class="stat-label">Total Clauses</div>
            </td>
            <td>
                <div class="stat-number" style="color: #dc2626;">{{ $highRiskCount }}</div>
                <div class="stat-label">High Risk</div>
            </td>
            <td>
                <div class="stat-number" style="color: #d97706;">{{ $mediumRiskCount }}</div>
                <div class="stat-label">Medium Risk</div>
            </td>
            <td>
                <div class="stat-number">{{ $totalDeadlines }}</div>
                <div class="stat-label">Deadlines</div>
            </td>
        </tr>
    </table>

    @if($analysis)
        <!-- Executive Summary -->
        <div class="section">
            <div class="section-title">Executive Summary</div>
            <div class="summary-box">
                <div class="summary-text">{{ $analysis->summary }}</div>
            </div>
        </div>

        <!-- Key Findings -->
        @if(count($analysis->key_findings) > 0)
            <div class="section">
                <div class="section-title">Key Findings</div>
                <ul class="findings-list">
                    @foreach($analysis->key_findings as $finding)
                        <li>{{ $finding }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- High Risk Clauses Alert -->
        @if(count($clausesByRisk['high']) > 0)
            <div class="warning-box">
                <strong>Attention Required:</strong> This contract contains {{ count($clausesByRisk['high']) }} high-risk clause(s) that may require legal review.
            </div>
        @endif

        <!-- Page Break before Clauses -->
        <div class="page-break"></div>

        <!-- Extracted Clauses -->
        <div class="section">
            <div class="section-title">Extracted Clauses ({{ $totalClauses }})</div>

            @if($totalClauses > 0)
                <table class="clauses-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Type</th>
                            <th style="width: 10%;">Risk</th>
                            <th style="width: 35%;">Original Text</th>
                            <th style="width: 35%;">Plain Explanation</th>
                            <th style="width: 5%;">Page</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(['high', 'medium', 'low', 'none'] as $riskLevel)
                            @foreach($clausesByRisk[$riskLevel] as $clause)
                                <tr>
                                    <td class="clause-type">{{ $clause->clause_type->label() }}</td>
                                    <td>
                                        <span class="risk-badge risk-{{ $clause->risk_level->value }}">
                                            {{ $clause->risk_level->label() }}
                                        </span>
                                    </td>
                                    <td class="clause-text">{{ Str::limit($clause->original_text, 200) }}</td>
                                    <td class="clause-text">{{ Str::limit($clause->plain_explanation, 200) }}</td>
                                    <td>{{ $clause->page_number ?? '-' }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">No clauses were extracted from this contract.</div>
            @endif
        </div>

        <!-- Page Break before Deadlines -->
        @if($totalDeadlines > 0)
            <div class="page-break"></div>
        @endif

        <!-- Important Deadlines -->
        <div class="section">
            <div class="section-title">Important Deadlines ({{ $totalDeadlines }})</div>

            @if($totalDeadlines > 0)
                @if(count($upcomingDeadlines) > 0)
                    <h4 style="font-size: 12px; margin-bottom: 10px; color: #1e40af;">Upcoming Deadlines</h4>
                    <table class="deadlines-table">
                        <thead>
                            <tr>
                                <th style="width: 15%;">Type</th>
                                <th style="width: 25%;">Title</th>
                                <th style="width: 35%;">Description</th>
                                <th style="width: 15%;">Date</th>
                                <th style="width: 10%;">Days Until</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingDeadlines as $deadline)
                                @php
                                    $urgencyClass = match(true) {
                                        $deadline->days_until !== null && $deadline->days_until <= 7 => 'deadline-urgent',
                                        $deadline->days_until !== null && $deadline->days_until <= 30 => 'deadline-upcoming',
                                        default => '',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $deadline->deadline_type->label() }}</td>
                                    <td class="{{ $urgencyClass }}">{{ $deadline->title }}</td>
                                    <td>{{ Str::limit($deadline->description, 150) ?? '-' }}</td>
                                    <td class="{{ $urgencyClass }}">{{ $deadline->deadline_date?->format('M j, Y') ?? 'Not specified' }}</td>
                                    <td class="{{ $urgencyClass }}">
                                        @if($deadline->days_until !== null)
                                            {{ $deadline->days_until }} day(s)
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                @if(count($pastDeadlines) > 0)
                    <h4 style="font-size: 12px; margin: 20px 0 10px 0; color: #6b7280;">Past Deadlines</h4>
                    <table class="deadlines-table">
                        <thead>
                            <tr>
                                <th style="width: 15%;">Type</th>
                                <th style="width: 25%;">Title</th>
                                <th style="width: 35%;">Description</th>
                                <th style="width: 15%;">Date</th>
                                <th style="width: 10%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pastDeadlines as $deadline)
                                <tr>
                                    <td class="deadline-past">{{ $deadline->deadline_type->label() }}</td>
                                    <td class="deadline-past">{{ $deadline->title }}</td>
                                    <td class="deadline-past">{{ Str::limit($deadline->description, 150) ?? '-' }}</td>
                                    <td class="deadline-past">{{ $deadline->deadline_date?->format('M j, Y') ?? 'Not specified' }}</td>
                                    <td class="deadline-past">Past Due</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @else
                <div class="no-data">No deadlines were extracted from this contract.</div>
            @endif
        </div>
    @else
        <div class="no-data" style="margin-top: 50px;">
            This contract has not been analyzed yet. Please run the analysis first.
        </div>
    @endif

    <!-- Disclaimer -->
    <div class="disclaimer">
        <div class="disclaimer-title">Disclaimer</div>
        This report is generated by AI-powered analysis and is intended for informational purposes only.
        It does not constitute legal advice. For legal decisions regarding this contract, please consult
        with a qualified attorney. Contractly and its AI systems are not liable for any decisions made
        based on this analysis.
    </div>

    <!-- Footer -->
    <div class="footer">
        <span class="footer-left">Contractly - AI Contract Analysis</span>
        <span class="footer-right">Generated: {{ $generatedAt->format('Y-m-d H:i:s') }} UTC</span>
    </div>
</body>
</html>
