<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body { 
            font-family: 'Poppins', Arial, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            margin: 0; 
            padding: 0; 
            background: #f9fafb;
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header { 
            background: linear-gradient(135deg, #10a37f 0%, #1a7f64 100%); 
            color: white; 
            padding: 30px 20px; 
            text-align: center; 
        }
        .logo { 
            font-size: 24px; 
            font-weight: 700; 
            margin-bottom: 10px; 
        }
        .content { 
            padding: 30px; 
            background: white;
        }
        .button { 
            background: linear-gradient(135deg, #10a37f 0%, #1a7f64 100%); 
            color: white; 
            padding: 14px 32px; 
            text-decoration: none; 
            border-radius: 8px; 
            display: inline-block; 
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            border: none;
            cursor: pointer;
        }
        .footer { 
            text-align: center; 
            margin-top: 30px; 
            font-size: 12px; 
            color: #666; 
            padding: 20px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        .info-box {
            background: #f0f9ff;
            border: 1px solid #e0f2fe;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
        }
        .code-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 12px;
            margin: 15px 0;
            font-family: monospace;
            word-break: break-all;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ config('app.name') }}</div>
            <h1>🔐 Password Reset Request</h1>
        </div>
        
        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>
            
            <p>You are receiving this email because we received a password reset request for your account.</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $resetLink }}" class="button" style="color: white;">
                    🔑 Reset My Password
                </a>
            </div>
            
            <div class="info-box">
                <strong>📝 Important Information:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>This password reset link will expire on: <strong>{{ $expiryTime }}</strong></li>
                    <li>If you didn't request this reset, please ignore this email</li>
                    <li>Your password will not change until you create a new one</li>
                </ul>
            </div>
            
            <p style="margin-top: 20px;">
                <strong>Can't click the button?</strong> Copy and paste this link in your browser:
            </p>
            
            <div class="code-box">
                {{ $resetLink }}
            </div>
            
            <p style="margin-top: 25px;">
                Thank you,<br>
                <strong>The {{ config('app.name') }} Team</strong>
            </p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>This is an automated message, please do not reply to this email.</p>
            <p>If you need assistance, please contact our support team.</p>
        </div>
    </div>
</body>
</html>