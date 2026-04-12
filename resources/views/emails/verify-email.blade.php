<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifica tu correo electrónico - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2d3748;
        }
        .message {
            color: #4a5568;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            padding: 16px 40px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .expiry {
            background-color: #fffaf0;
            border-left: 4px solid #ed8936;
            padding: 15px 20px;
            margin: 30px 0;
            border-radius: 0 4px 4px 0;
            color: #744210;
            font-size: 14px;
        }
        .footer {
            background-color: #f7fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 0 0 10px 0;
            color: #718096;
            font-size: 14px;
        }
        .url-fallback {
            background-color: #edf2f7;
            padding: 15px;
            border-radius: 4px;
            word-break: break-all;
            font-size: 13px;
            color: #4a5568;
            margin-top: 20px;
        }
        .url-fallback a {
            color: #667eea;
            text-decoration: none;
        }
        @media only screen and (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
            .content {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>
        
        <div class="content">
            <p class="greeting">¡Hola{{ $notifiable->name ? ' ' . $notifiable->name : '' }}!</p>
            
            <p class="message">
                Gracias por registrarte en {{ config('app.name') }}. Para completar tu registro y activar tu cuenta, 
                por favor verifica tu dirección de correo electrónico haciendo clic en el botón de abajo.
            </p>
            
            <div class="button-container">
                <a href="{{ $url }}" class="button">Verificar correo electrónico</a>
            </div>
            
            <div class="expiry">
                <strong>Nota:</strong> Este enlace de verificación expirará en {{ $expire }} minutos por seguridad. 
                Si no verificas tu correo dentro de este tiempo, deberás solicitar un nuevo enlace.
            </div>
            
            <p class="message" style="margin-top: 30px;">
                Si no creaste esta cuenta, puedes ignorar este mensaje de forma segura. 
                No se realizará ninguna acción en tu nombre.
            </p>
            
            <div class="url-fallback">
                <p style="margin: 0 0 10px 0;"><strong>Si tienes problemas con el botón:</strong></p>
                <a href="{{ $url }}">{{ $url }}</a>
            </div>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
            <p style="font-size: 12px; color: #a0aec0;">
                Este es un correo automático, por favor no respondas a este mensaje.
            </p>
        </div>
    </div>
</body>
</html>