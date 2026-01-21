<!DOCTYPE html>
<html lang="es" class="h-100">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sistema Financiero INTESA</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('dimages/LogoIntesa.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            height: 100vh;
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(24, 140, 82, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(24, 140, 82, 0.06) 0%, transparent 50%);
            z-index: 0;
        }
        
        body::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: linear-gradient(135deg, rgba(24, 140, 82, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 0;
        }
        
        .welcome-container {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.12),
                0 8px 24px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(24, 140, 82, 0.1);
            padding: 80px 60px;
            text-align: center;
            max-width: 560px;
            width: 90%;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.8s ease-out;
            overflow: hidden;
        }
        
        .welcome-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #188c52 0%, #156f42 100%);
            z-index: 2;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo-container {
            margin-bottom: 48px;
            padding-bottom: 40px;
            border-bottom: 2px solid #f3f4f6;
            position: relative;
        }
        
        .logo-container::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, #188c52 0%, #156f42 100%);
        }
        
        .logo-container img {
            max-width: 200px;
            height: auto;
            filter: brightness(1);
            transition: transform 0.3s ease;
        }
        
        .logo-container:hover img {
            transform: scale(1.05);
        }
        
        .welcome-title {
            font-size: 36px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
            line-height: 1.2;
            background: linear-gradient(135deg, #111827 0%, #374151 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .welcome-subtitle {
            font-size: 18px;
            color: #188c52;
            margin-bottom: 24px;
            font-weight: 600;
            line-height: 1.4;
            letter-spacing: 0.2px;
        }
        
        .welcome-description {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 48px;
            line-height: 1.7;
            font-weight: 400;
            max-width: 420px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .login-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 16px 48px;
            font-size: 16px;
            font-weight: 600;
            color: #ffffff;
            background: linear-gradient(135deg, #188c52 0%, #156f42 100%);
            border: none;
            border-radius: 12px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.3px;
            box-shadow: 
                0 4px 12px rgba(24, 140, 82, 0.3),
                0 2px 4px rgba(24, 140, 82, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .login-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .login-button:hover::before {
            left: 100%;
        }
        
        .login-button:hover {
            background: linear-gradient(135deg, #156f42 0%, #125a35 100%);
            color: #ffffff;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 
                0 8px 20px rgba(24, 140, 82, 0.4),
                0 4px 8px rgba(24, 140, 82, 0.3);
        }
        
        .login-button:active {
            transform: translateY(0);
            box-shadow: 
                0 2px 8px rgba(24, 140, 82, 0.3),
                0 1px 4px rgba(24, 140, 82, 0.2);
        }
        
        .login-button i {
            margin-right: 12px;
            font-size: 16px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .welcome-container {
                padding: 60px 40px;
                border-radius: 20px;
            }
            
            .logo-container {
                margin-bottom: 40px;
                padding-bottom: 32px;
            }
            
            .logo-container img {
                max-width: 160px;
            }
            
            .welcome-title {
                font-size: 28px;
            }
            
            .welcome-subtitle {
                font-size: 16px;
                margin-bottom: 20px;
            }
            
            .welcome-description {
                font-size: 15px;
                margin-bottom: 40px;
            }
            
            .login-button {
                padding: 14px 40px;
                font-size: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .welcome-container {
                padding: 50px 30px;
                width: 95%;
                border-radius: 16px;
            }
            
            .welcome-title {
                font-size: 24px;
            }
            
            .welcome-subtitle {
                font-size: 15px;
            }
            
            .logo-container {
                margin-bottom: 32px;
                padding-bottom: 28px;
            }
            
            .logo-container img {
                max-width: 140px;
            }
            
            .login-button {
                padding: 12px 36px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body class="h-100">
    <div class="welcome-container">
        <div class="logo-container">
            <img src="{{ asset('dimages/LogoIntesa.png') }}" alt="INTESA Logo">
        </div>
        
        <h1 class="welcome-title">Sistema Financiero</h1>
        
        <p class="welcome-subtitle">Instituto Técnico Del Saber</p>
        
        <p class="welcome-description">
            Gestiona y administra las operaciones financieras de manera eficiente y segura.
        </p>
        
        <a href="{{ route('login') }}" class="login-button">
            <i class="fas fa-sign-in-alt"></i>
            Iniciar Sesión
        </a>
    </div>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="{{ asset('dvendor/fontawesome/css/all.css') }}">
</body>
</html>
