<?php
// Requiere la clase RouterosAPI (asegúrate de incluirla en tu proyecto)
require('core/routeros_api.class.php');
$mensaje = "";
$nombre = "";
// Verificar que el formulario haya sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger los datos del formulario
    $nombre = $_POST['nombre'];
    $password = $_POST['password'];
    $limitUptime = $_POST['limit_uptime'];
    $perfil = $_POST['perfil']; // Añadir el perfil elegido

    // Credenciales del Router MikroTik
    $routerIp = '192.168.0.199';
    $username = 'admin';
    $passwordRouter = '1234567890';

    // Crear una instancia del API de RouterOS
    $API = new RouterosAPI();

    // Conectar al router
    if ($API->connect($routerIp, $username, $passwordRouter)) {
        
        // Consultar todos los usuarios existentes
        $usuariosExistentes = $API->comm("/ip/hotspot/user/print");

        // Verificar si el nombre ya existe
        $usuarioExiste = false;
        foreach ($usuariosExistentes as $usuario) {
            if ($usuario['name'] == $nombre) {
                $usuarioExiste = true;
                break;
            }
        }

        if ($usuarioExiste) {
            $mensaje = "<div class='message error'>El usuario '$nombre' ya existe en MikroTik. Por favor, elige otro nombre.</div>";
        } else {
            // Crear el usuario si no existe
            $API->comm("/ip/hotspot/user/add", array(
                "name" => $nombre,
                "password" => $password,
                "limit-uptime" => $limitUptime,
                "profile" => $perfil, // Asignar el perfil seleccionado
                "disabled" => "no",
            ));
            $mensaje = "<div class='message success'><h1>$nombre</h1>
			<button onclick='shareViaSMS()' style='background-color: #28a745; color: white; border: none; padding: 10px 20px; font-size: 18px; border-radius: 5px; cursor: pointer; display: flex; align-items: center;'>
					<i class='fas fa-sms' style='font-size: 20px; margin-right: 10px;'></i> Via Mensaje
			</button>
			<button onclick='compartirWhatsapp()' style='float:left;  background-color: #28a745; color: white; border: none; padding: 10px 20px; font-size: 18px; border-radius: 5px; cursor: pointer; display: flex; align-items: center;'>
					<i class='fab fa-whatsapp' style='font-size: 20px; margin-right: 10px;'></i> Via WhatsApp
			</button>
	<br>Ah creado exitosamente con un límite de tiempo de <br>".$limitUptime/(3600)." hora.</div>";
        }

        // Cerrar la conexión
        $API->disconnect();
    } else {
        echo "<div class='message error'>No se pudo conectar al router MikroTik.</div>";
    }
}

// Crear una instancia del API para obtener los perfiles
$API = new RouterosAPI();
$routerIp = '192.168.0.199';
$username = 'admin';
$passwordRouter = '1234567890';

$API->connect($routerIp, $username, $passwordRouter);

// Obtener los perfiles de usuario
$perfiles = $API->comm("/ip/hotspot/user/profile/print");

// Cerrar la conexión
$API->disconnect();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Ticket en MikroTik</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f1f2f6;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }
        label {
            font-size: 16px;
            color: #444;
            display: block;
            margin: 10px 0 5px;
            text-align: left;
        }
        select, input[type="text"] {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            background-color: #f9f9f9;
            transition: all 0.3s ease;
        }
        select:focus, input[type="text"]:focus {
            border-color: #4CAF50;
            background-color: #fff;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 15px;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        .message {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Generador de Ticket en MikroTik</h2>
        
        <form method="POST" action="">
            <label for="limit_uptime">Seleccione un tiempo límite:</label>
            <select name="limit_uptime" id="limit_uptime" required onchange="generarNombre()">
                <option value="3600">1 Hora</option>
                <option value="7200">2 Horas</option>
                <option value="10800">3 Horas</option>
                <option value="14400">4 Horas</option>
                <option value="21600">6 Horas</option>
                <option value="43200">12 Horas</option>
                <option value="1296000">15 Días</option>
                <option value="2592000">30 Días</option>
            </select>
            <input type="hidden" name="perfil" id="perfil">
            <input type="hidden" name="nombre" id="nombre">
            
            <input type="hidden" name="password" id="password">
            
            <input type="submit" value="Crear Usuario">
        </form>

        <div class="message" id="message"><?php echo $mensaje; ?></div>
    </div>

    <script>
        // Función para generar el nombre de usuario basado en el limite de tiempo
function generarNombre() {
    const limitUptime = document.getElementById('limit_uptime').value;
    let prefix;
    let profile;

    // Determinar el prefijo basado en el limite de tiempo y el perfil correspondiente
    switch(limitUptime) {
        case '3600': // 1 Hora
            prefix = '1';
            profile = '4DIAS#filter-app#';
            break;
        case '7200': // 2 Horas
            prefix = '2';
            profile = '4DIAS#filter-app#';
            break;
        case '10800': // 3 Horas
            prefix = '3';
            profile = '4DIAS#filter-app#';
            break;
        case '14400': // 4 Horas
            prefix = '4';
            profile = '4DIAS#filter-app#';
            break;
        case '21600': // 6 Horas
            prefix = '6';
            profile = '4DIAS#filter-app#';
            break;
        case '43200': // 12 Horas
            prefix = '12';
            profile = '4DIAS#filter-app#';
            break;
        case '1296000': // 15 Días
            prefix = '15';
            profile = '15DIAS#filter-app#';
            break;
        case '2592000': // 30 Días
            prefix = '30';
            profile = '30DIAS#filter-app#';
            break;
        default:
            prefix = '0';
            profile = '4DIAS#filter-app#';
    }

    // Generar el nombre de usuario con un número aleatorio de 5 dígitos
    const randomDigits = Math.floor(10000 + Math.random() * 90000); // Número aleatorio de 5 dígitos
    const nombreUsuario = prefix + randomDigits;

    // Asignar el nombre generado al campo "nombre"
    document.getElementById('nombre').value = nombreUsuario;

    // Establecer automáticamente el perfil según el límite de tiempo seleccionado
    document.getElementById('perfil').value = profile;
}

// Generar el nombre de usuario al cargar la página por primera vez
window.onload = generarNombre;
	
	function shareViaSMS() {
            const message = '<?php echo $nombre; ?>'; // Your SMS message text here
            window.location.href = `sms:?body=${encodeURIComponent(message)}`;
        }
		
	function compartirWhatsapp() {
            const text = '<?php echo $nombre; ?>';
            const url = window.location.href; // URL actual de la página
            window.open(`https://wa.me/?text=${encodeURIComponent(text)} ${encodeURIComponent(url)}`, '_blank');
        }
    </script>

</body>
</html>

