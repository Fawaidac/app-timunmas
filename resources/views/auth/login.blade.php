<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Timun Mas SFA</title>
    @vite(['resources/css/app.css'])
</head>
<body style="background: linear-gradient(135deg, #c2410c 0%, #ea580c 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;">

<div class="card" style="max-width: 420px; width: 100%; padding: 40px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 70px; height: 70px; background: linear-gradient(135deg, #c2410c, #ea580c); border-radius: 20px; margin-bottom: 20px;">
            <span style="font-size: 36px; font-weight: 900; color: white;">A</span>
        </div>
        <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #1f2937;">ASRI</h1>
        <p style="margin: 5px 0 0; color: #6b7280; font-size: 14px;">Sales Force Automation</p>
    </div>

    @if ($errors->any())
        <div style="padding: 12px 15px; background: #fee2e2; border: 1px solid #fecaca; border-radius: 10px; margin-bottom: 20px;">
            <p style="margin: 0; color: #dc2626; font-size: 13px; font-weight: 600;">{{ $errors->first() }}</p>
        </div>
    @endif

    <form action="{{ route('login.post') }}" method="POST">
        @csrf
        
        <div class="field">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="email@example.com" required autofocus>
        </div>

        <div class="field">
            <label>Password</label>
            <input type="password" name="password" placeholder="Masukkan password" required>
        </div>

        <div style="display: flex; align-items: center; margin-bottom: 25px;">
            <input type="checkbox" name="remember" id="remember" style="width: 18px; height: 18px; cursor: pointer;">
            <label for="remember" style="margin: 0 0 0 8px; font-size: 13px; color: #4b5563; cursor: pointer;">Ingat saya</label>
        </div>

        <button type="submit" class="button button-primary full-width" style="padding: 14px;">
            Masuk
        </button>
    </form>

    <div style="margin-top: 25px; padding-top: 25px; border-top: 1px solid #e5e7eb; text-align: center;">
        <p style="margin: 0; font-size: 12px; color: #9ca3af;">
            © Copyright 2026 ASRI<br>
            <span style="color: #4b5563;">All rights reserved</span><br>
        </p>
    </div>
</div>

</body>
</html>
