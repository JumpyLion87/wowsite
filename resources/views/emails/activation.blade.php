<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('auth.email_activation_subject') }}</title>
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
                                {{ __('auth.email_activation_subject') }}
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px; background-color: #2d2d2d;">
                            
                            <!-- Greeting -->
                            <p style="color: #d4af37; font-size: 18px; font-weight: bold; margin: 0 0 20px 0;">
                                {{ __('auth.email_greeting', ['username' => $username]) }}
                            </p>
                            
                            <!-- Main Message -->
                            <p style="color: #e2d3b7; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                {{ __('auth.email_activation_body') }}
                            </p>
                            
                            <!-- Divider -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 25px 0;">
                                <tr>
                                    <td style="height: 2px; background: linear-gradient(90deg, transparent, #8b4513, #d4af37, #8b4513, transparent);"></td>
                                </tr>
                            </table>
                            
                            <!-- Activation Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 25px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $activationUrl }}" style="display: inline-block; background: linear-gradient(135deg, #d4af37 0%, #ffd700 50%, #d4af37 100%); color: #1a1a1a; padding: 16px 32px; text-decoration: none; border-radius: 12px; font-weight: bold; font-size: 18px; border: 2px solid #8b4513; box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);">
                                            {{ __('auth.email_activate_button') }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Warning Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 25px 0;">
                                <tr>
                                    <td style="background: linear-gradient(135deg, rgba(255, 215, 0, 0.15) 0%, rgba(255, 215, 0, 0.05) 100%); border: 2px solid #ffd700; border-radius: 12px; padding: 20px;">
                                        <strong style="color: #ffd700; font-size: 16px;">
                                            {{ __('auth.email_activation_warning') }}
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
                            
                            <!-- Alternative Link -->
                            <p style="color: #a0a0a0; font-size: 14px; margin: 0 0 15px 0;">
                                {{ __('auth.email_activation_alternative') }}
                            </p>
                            
                            <!-- URL Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 15px 0;">
                                <tr>
                                    <td style="background: linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 100%); border: 1px solid #8b4513; border-radius: 8px; padding: 15px;">
                                        <div style="color: #d4af37; font-family: 'Courier New', monospace; font-size: 14px; word-break: break-all;">
                                            {{ $activationUrl }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 25px 30px; text-align: center; background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); border-radius: 0 0 12px 12px; border-top: 2px solid #8b4513;">
                            <p style="color: #a0a0a0; font-size: 14px; margin: 0 0 10px 0;">
                                {{ __('auth.email_ignore') }}
                            </p>
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
