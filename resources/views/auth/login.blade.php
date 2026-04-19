<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Login') }} - OFPPT - Emploi du Temps</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        
        body {
            height: 100vh;
            overflow: hidden;
            background: #0f172a;
        }
        
        .split-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: 100vh;
        }
        
        /* ===== LEFT PANEL ===== */
        .left-panel {
            background: radial-gradient(circle at top left, #1e88e5 0%, #0f172a 55%, #020617 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 60px 40px;
        }
        
        .left-panel::before {
            content: '';
            position: absolute;
            width: 700px;
            height: 700px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.22) 0%, transparent 70%);
            border-radius: 50%;
            top: -350px;
            left: -350px;
            animation: pulse 8s ease-in-out infinite;
        }
        
        .left-panel::after {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.2) 0%, transparent 70%);
            border-radius: 50%;
            bottom: -300px;
            right: -300px;
            animation: pulse 10s ease-in-out infinite reverse;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.08); opacity: 0.85; }
        }
        
        /* ===== LOGO ===== */
        .logo-container {
            position: absolute;
            top: 34px;
            left: 36px;
            z-index: 100;
        }
        
        .logo-box {
            width: 80px;
            height: 80px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: linear-gradient(135deg, var(--blue-light), var(--blue-primary));
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            box-shadow: 0 6px 16px rgba(56, 189, 248, 0.45);
        }

        .logo-box:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 12px 30px rgba(56, 189, 248, 0.65);
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: radial-gradient(circle at 30% 20%, #38bdf8 0%, #1e88e5 40%, #0f172a 100%);
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 20px;
            box-shadow: 0 6px 16px rgba(56, 189, 248, 0.6);
        }
        
        .logo-text {
            font-size: 20px;
            font-weight: 800;
            color: #e5e7eb;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        
        /* ===== WELCOME TEXT ===== */
        .welcome-title {
            color: white;
            text-align: center;
            margin-bottom: 46px;
            position: relative;
            z-index: 10;
        }
        
        .welcome-title h1 {
            font-size: 40px;
            font-weight: 800;
            margin-bottom: 14px;
            text-shadow: 0 10px 35px rgba(15, 23, 42, 0.9);
            letter-spacing: -0.04em;
        }
        
        .welcome-title p {
            font-size: 16px;
            opacity: 0.96;
            line-height: 1.7;
            font-weight: 400;
            color: #e5e7eb;
        }
        
        /* ===== ILLUSTRATION ===== */
        .illustration-container {
            position: relative;
            z-index: 10;
            animation: float 4s ease-in-out infinite;
            max-width: 520px;
            filter: drop-shadow(0 24px 60px rgba(15, 23, 42, 0.9));
            border-radius: 32px;
            overflow: hidden;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-16px); }
        }
        
        .illustration-container img {
            max-width: 100%;
            height: auto;
            display: block;
            mix-blend-mode: multiply;
            filter: brightness(1.3) saturate(1.2);
        }
        
        /* ===== RIGHT PANEL ===== */
        .right-panel {
            background: radial-gradient(circle at top right, #e5f2ff 0%, #f9fafb 40%, #eef2ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            position: relative;
        }
        
        /* ===== SE CONNECTER BUTTON ===== */
        .se-connecter-btn {
            position: absolute;
            top: 34px;
            right: 40px;
            background: white;
            color: #1e88e5;
            padding: 11px 26px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 13px;
            border: 1px solid rgba(148, 163, 184, 0.6);
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(148, 163, 184, 0.5);
            z-index: 100;
        }
        
        .se-connecter-btn:hover {
            background: #1e88e5;
            color: white;
            border-color: transparent;
            transform: translateY(-1px);
            box-shadow: 0 14px 32px rgba(37, 99, 235, 0.4);
        }
        
        /* ===== LOGIN BOX ===== */
        .login-box {
            background: white;
            padding: 52px 46px;
            border-radius: 22px;
            box-shadow: 0 26px 80px rgba(15, 23, 42, 0.18);
            width: 100%;
            max-width: 440px;
            border: 1px solid rgba(209, 213, 219, 0.7);
        }
        
        .connexion-header {
            margin-bottom: 32px;
        }
        
        .connexion-header h2 {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
            letter-spacing: -0.04em;
        }
        
        .connexion-header p {
            color: #6b7280;
            font-size: 14px;
            font-weight: 400;
        }
        
        /* ===== FORM ===== */
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            color: #374151;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .form-group label .required {
            color: #ef4444;
            margin-left: 2px;
        }
        
        .form-input {
            width: 100%;
            padding: 13px 16px;
            border: 1.5px solid #e5e7eb;
            border-radius: 11px;
            font-size: 14px;
            transition: all 0.2s ease;
            background: #f9fafb;
            color: #111827;
        }
        
        .form-input:hover {
            border-color: #d1d5db;
            background: white;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #1e88e5;
            background: white;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25);
        }
        
        .form-input::placeholder {
            color: #9ca3af;
        }
        
        /* ===== SUBMIT BUTTON ===== */
        .btn-connexion {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
            color: white;
            border: none;
            border-radius: 999px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            margin-top: 10px;
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.5);
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        
        .btn-connexion:hover {
            background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
            transform: translateY(-1px);
            box-shadow: 0 16px 40px rgba(30, 136, 229, 0.6);
        }
        
        .btn-connexion:active {
            transform: translateY(0);
            box-shadow: 0 6px 16px rgba(30, 136, 229, 0.45);
        }
        
        /* ===== ALERTS ===== */
        .error-alert {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .success-alert {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
            color: #065f46;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 968px) {
            .split-container {
                grid-template-columns: 1fr;
            }
            
            .left-panel {
                display: none;
            }
            
            .right-panel {
                background: radial-gradient(circle at top, #1e88e5 0%, #0f172a 70%);
            }
            
            .login-box {
                box-shadow: 0 20px 60px rgba(15, 23, 42, 0.65);
            }
            
            .se-connecter-btn {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .login-box {
                padding: 40px 28px;
            }
            
            .connexion-header h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    
    <div class="split-container">
        
        <!-- Left Panel - Illustration -->
        <div class="left-panel">
            
            <!-- Logo -->
            <div class="logo-container">
                <div class="logo-box">
                    <img
                        src="{{ asset('images/ofppt_login.png') }}"
                        alt="OFPPT Logo"
                        style="
                            width:64px;
                            height:64px;
                            border-radius:50%;
                            display:block;
                            object-fit:cover;
                            background:#ffffff;
                        "
                    >
                </div>
            </div>
            
            <!-- Welcome Text -->
            <div class="welcome-title">
                <h1>{{ __('Welcome to OFPPT') }}</h1>
                <p>{!! nl2br(e(__('Access your account to view timetables.'))) !!}</p>
            </div>
            
            <!-- Illustration image -->
            <div class="illustration-container">
                <img src="{{ asset('images/image_login.png') }}" alt="Illustration emplois du temps" />
            </div>
            
        </div>
        
        <!-- Right Panel - Login Form -->
        <div class="right-panel">
            
            <!-- Se connecter button (top right) -->
            <button class="se-connecter-btn" type="button">{{ __('Log in') }}</button>
            
            <!-- Login Box -->
            <div class="login-box">
                
                <div class="connexion-header">
                    <h2>{{ __('Login') }}</h2>
                    <p>{{ __('Access your account to view timetables.') }}</p>
                </div>
                
                <!-- Session Status -->
                @if (session('status'))
                    <div class="success-alert">
                        {{ session('status') }}
                    </div>
                @endif
                
                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="error-alert">
                        <strong>{{ __('Error! Please check your credentials.') }}</strong>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <!-- Email Input -->
                    <div class="form-group">
                        <label for="email">{{ __('Username') }}<span class="required">*</span></label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email"
                            class="form-input"
                            placeholder="{{ __('Username') }}"
                            value="{{ old('email') }}"
                            required 
                            autofocus 
                            autocomplete="username"
                        />
                    </div>
                    
                    <!-- Password Input -->
                    <div class="form-group">
                        <label for="password">{{ __('Password') }}<span class="required">*</span></label>
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            class="form-input"
                            placeholder="{{ __('Password') }}"
                            required 
                            autocomplete="current-password"
                        />
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn-connexion">
                        {{ __('Log in') }}
                    </button>
                    
                </form>
                
            </div>
            
        </div>
        
    </div>
    