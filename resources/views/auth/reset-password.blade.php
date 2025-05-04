<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-fulL bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Use esses dados no enpoint:</h1>
            <p>api/reset-password</p>
        </div>

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">TOKEN</h1>
            <p>{{ request()->query('token') }}</p>
        </div>

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">EMAIL</h1>
            <p>{{ request()->query('email') }}</p>
        </div>        
    </div>    
</body>
</html>