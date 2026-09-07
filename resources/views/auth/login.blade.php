<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - QR Attendance System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-sm">
        <h1 class="text-xl font-semibold mb-6 text-center">Sign In</h1>

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-600">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required class="w-full border rounded px-3 py-2">
            </div>
            <label class="flex items-center mb-4 text-sm">
                <input type="checkbox" name="remember" class="mr-2"> Remember me
            </label>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                Log in
            </button>
        </form>
    </div>
</body>
</html>