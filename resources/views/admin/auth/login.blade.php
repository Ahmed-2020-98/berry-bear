<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>تسجيل الدخول — لوحة تحكم بيري بير</title>
    <link rel="icon" href="{{ theme_img('logo.png') }}" type="image/png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Baloo+Bhaijaan+2:wght@700;800&family=Tajawal:wght@400;500;700;800&display=swap">
    @vite(['resources/css/admin.css'])
</head>
<body class="grid min-h-screen place-items-center bg-gradient-to-br from-[#FDE7F0] via-[#F7F5FB] to-[#EEE6F7] p-4">
    <div class="w-full max-w-sm">
        <div class="mb-6 text-center">
            <img src="{{ theme_img('logo.png') }}" alt="Berry Bear" class="mx-auto h-20 w-20 rounded-2xl bg-white object-contain p-2 shadow-sm">
            <h1 class="mt-4 font-display text-2xl font-extrabold text-navy">لوحة تحكم بيري بير</h1>
            <p class="mt-1 text-sm text-slate-500">سجّل الدخول لإدارة محتوى الموقع</p>
        </div>
        <form method="POST" action="{{ route('admin.login.attempt') }}" class="card card-body space-y-4">
            @csrf
            <div>
                <label class="label" for="email">البريد الإلكتروني</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input @error('email') input-error @enderror" dir="ltr">
                @error('email')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="password">كلمة المرور</label>
                <input id="password" name="password" type="password" required autocomplete="current-password" class="input" dir="ltr">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded accent-brand"> تذكّرني
            </label>
            <button class="btn btn-primary w-full py-3">دخول</button>
        </form>
        <p class="mt-6 text-center text-xs text-slate-400"><a href="{{ url('/') }}" class="hover:text-brand">← العودة إلى الموقع</a></p>
    </div>
</body>
</html>
