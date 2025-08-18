<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Followup Sales</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .content {
            padding: 30px 20px;
        }
        .followup-info {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
            min-width: 140px;
        }
        .info-value {
            color: #212529;
            text-align: right;
            flex: 1;
        }
        .priority-high {
            background-color: #fff3cd;
            border-left-color: #ffc107;
        }
        .priority-urgent {
            background-color: #f8d7da;
            border-left-color: #dc3545;
        }
        .note-section {
            background-color: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .note-title {
            font-weight: bold;
            color: #0056b3;
            margin-bottom: 10px;
        }
        .note-content {
            color: #495057;
            white-space: pre-line;
        }
        .cta-section {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #e9ecef;
        }
        .date-highlight {
            color: #dc3545;
            font-weight: bold;
        }
        .id-badge {
            background-color: #e9ecef;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            color: #495057;
        }

        @media (max-width: 600px) {
            .info-row {
                flex-direction: column;
                align-items: flex-start;
            }
            .info-value {
                text-align: left;
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>🔔 Notifikasi Followup Sales</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Reminder untuk tindak lanjut prospek</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p>Halo Sales Team,</p>
            <p>Anda memiliki followup yang perlu dilakukan. Berikut adalah detail informasinya:</p>

            <!-- Followup Info -->
            <div class="followup-info {{ $data['followup_date'] <= now() ? 'priority-urgent' : ($data['followup_date'] <= now()->addDay() ? 'priority-high' : '') }}">
                <div class="info-row">
                    <span class="info-label">📅 Tanggal Followup:</span>
                    <span class="info-value date-highlight">
                        {{ \Carbon\Carbon::parse($data['followup_date'])->format('d F Y, H:i') }} WIB
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">⏰ Hari Terakhir:</span>
                    <span class="info-value">
                        {{ \Carbon\Carbon::parse($data['followup_last_day'])->format('d F Y') }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">👤 Konsumen:</span>
                    <span class="info-value">
                        <span class="id-badge">{{ $data['konsumen']['name'] }}</span>
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">🎯 Prospek:</span>
                    <span class="info-value">
                        <span class="id-badge">{{ $data['prospek']['name'] }}</span>
                    </span>
                </div>
            </div>

            <!-- Followup Note -->
            @if(!empty($data['followup_note']))
            <div class="note-section">
                <div class="note-title">📝 Catatan Followup:</div>
                <div class="note-content">{{ $data['followup_note'] }}</div>
            </div>
            @endif

            <p>Pastikan untuk melakukan followup tepat waktu agar tidak kehilangan kesempatan dengan prospek ini.</p>

            <p>Terima kasih,<br>
            <strong>Tim Sales Management</strong></p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Email ini dikirim secara otomatis dari sistem CRM.<br>
            Jika ada pertanyaan, silakan hubungi tim support.</p>
            <p><small>© {{ date('Y') }} {{ config('app.name', 'Your Company') }}. All rights reserved.</small></p>
        </div>
    </div>
</body>
</html>
