<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('auth.email_activation_confirmation_subject') }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #1a1a1a; font-family: Arial, sans-serif;">
    <!-- Main Container -->
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #1a1a1a; padding: 20px;">
        <tr>
            <td align="center">
                <!-- Email Container -->
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #2d2d2d; border: 3px solid #8b4513; border-radius: 15px; box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="padding: 30px; text-align: center; background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); border-radius: 12px 12px 0 0;">
                            <div style="color: #ffd700; font-size: 28px; font-weight: bold; margin-bottom: 10px; text-shadow: 0 0 10px rgba(255, 215, 0, 0.5);">
                                {{ $siteName }}
                            </div>
                            <h1 style="color: #d4af37; font-size: 22px; margin: 0; font-weight: bold;">
                                {{ __('auth.email_activation_confirmation_subject') }}
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px; background-color: #2d2d2d;">
                            
                            <!-- Greeting -->
                            <p style="color: #d4af37; font-size: 18px; font-weight: bold; margin: 0 0 20px 0;">
                                {{ __('auth.email_confirmation_greeting', ['username' => $username]) }}
                            </p>
                            
                            <!-- Success Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 25px 0;">
                                <tr>
                                    <td style="background: linear-gradient(135deg, rgba(25, 135, 84, 0.2) 0%, rgba(25, 135, 84, 0.1) 100%); border: 2px solid #198754; border-radius: 12px; padding: 20px;">
                                        <strong style="color: #d1e7dd; font-size: 16px;">
                                            {{ __('auth.email_activation_success') }}
                                        </strong>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Divider -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 25px 0;">
                                <tr>
                                    <td style="height: 2px; background: linear-gradient(90deg, transparent, #8b4513, #d4af37, #8b4513, transparent);"></td>
                                </tr>
                            </table>
                            
                            <!-- Main Message -->
                            <p style="color: #e2d3b7; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                {{ __('auth.email_activation_confirmation_body') }}
                            </p>
                            
                            <!-- Login Instructions -->
                            <p style="color: #a0a0a0; font-size: 14px; margin: 20px 0 0 0;">
                                {{ __('auth.email_login_instructions') }}
                            </p>
                            
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 25px 30px; text-align: center; background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); border-radius: 0 0 12px 12px; border-top: 2px solid #8b4513;">
                            <p style="color: #666; font-size: 12px; margin: 0;">
                                &copy; {{ date('Y') }} {{ $siteName }}. {{ __('auth.email_rights') }}
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
