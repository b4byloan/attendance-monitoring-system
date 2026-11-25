<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset your password</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background-color:#ffffff;border-radius:12px;padding:32px;">
                    <tr>
                        <td style="padding-bottom:10px;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#047857;">
                            {{ $schoolName }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom:12px;font-size:22px;font-weight:700;color:#064e3b;">
                            Reset your password
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:14px;line-height:1.6;color:#475569;">
                            You are receiving this email because we received a password reset request for your account.
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:24px 0;">
                            <a href="{{ $url }}" style="display:inline-block;background-color:#047857;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 24px;border-radius:8px;">
                                Reset Password
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;line-height:1.6;color:#64748b;">
                            This password reset link will expire in {{ $expiresInMinutes }} minutes.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top:12px;font-size:13px;line-height:1.6;color:#64748b;">
                            If you did not request a password reset, no further action is required.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top:12px;font-size:12px;line-height:1.6;color:#94a3b8;word-break:break-all;">
                            If the button does not work, copy and paste this link into your browser:<br>
                            {{ $url }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top:24px;border-top:1px solid #e2e8f0;font-size:12px;color:#94a3b8;">
                            &copy; {{ date('Y') }} {{ $schoolName }}. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
