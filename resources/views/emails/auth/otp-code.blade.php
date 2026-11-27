<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your verification code</title>
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
                            Verify your email address
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:14px;line-height:1.6;color:#475569;">
                            @if ($recipientName)
                                Hi {{ $recipientName }},
                            @else
                                Hello,
                            @endif
                            use the one-time password below to finish setting up your school portal account.
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:24px 0;">
                            <div style="display:inline-block;background-color:#ecfdf5;border:1px solid #a7f3d0;border-radius:12px;padding:16px 28px;font-size:32px;letter-spacing:10px;font-weight:700;color:#065f46;">
                                {{ $code }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;line-height:1.6;color:#64748b;">
                            This code expires in {{ $expiresInMinutes }} minutes and can only be used once.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top:12px;font-size:13px;line-height:1.6;color:#64748b;">
                            If you did not create this account, you can safely ignore this email.
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
