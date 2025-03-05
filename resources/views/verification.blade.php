<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Verificación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 500px;
            margin: 40px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .code {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            letter-spacing: 2px;
        }
        .footer {
            font-size: 12px;
            color: #6c757d;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <div class="container text-center">
        <h3 class="mb-3">🔐 Verificación de Cuenta</h3>
        <p>Hola,</p>
        <p>Tu código de verificación es:</p>
        <p class="code">{{ $codigo }}</p>
        <p>Este código expira en <strong>10 minutos</strong>.</p>
        <hr>
        <p class="footer">Si no solicitaste este código, puedes ignorar este mensaje.</p>
    </div>

</body>
</html>
