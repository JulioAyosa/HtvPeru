<?php
// 500.php — Página de Error Interno del Servidor
// FASE 4: Página limpia para producción. Nunca muestra detalles técnicos al usuario.
http_response_code(500);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error del Servidor - HTVPERU</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 2rem;
        }
        .error-card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 24px;
            padding: 3rem;
            max-width: 520px;
            width: 100%;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }
        .error-icon {
            font-size: 4rem;
            color: #f59e0b;
            margin-bottom: 1.5rem;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.05); }
        }
        .error-code {
            font-size: 5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #f59e0b, #ef4444);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 1rem;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 0.75rem;
        }
        .error-desc {
            color: #94a3b8;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
            padding: 0.875rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.5);
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">
            <i class="ri-server-line"></i>
        </div>
        <div class="error-code">500</div>
        <h1 class="error-title">Error Interno del Servidor</h1>
        <p class="error-desc">
            Nuestros sistemas están experimentando una dificultad técnica temporal.
            Nuestro equipo ya fue notificado y estamos trabajando en resolverlo.
        </p>
        <a href="/" class="btn-home">
            <i class="ri-home-4-line"></i> Volver al Inicio
        </a>
    </div>
</body>
</html>
