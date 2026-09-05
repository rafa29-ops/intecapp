<?php
	session_start();
	include 'db.php';
	include 'password_helper.php';

	if(isset($_POST['login'])){
		$nom_usuario = trim($_POST['nom_usuario'] ?? '');
		$contraseña  = $_POST['contraseña'] ?? '';

		// Consulta preparada: evita inyección SQL (antes se concatenaba
		// $nom_usuario directo en el SQL, lo que permitía bypass tipo
		// nom_usuario = admin' -- )
		$stmt = $conn->prepare("SELECT * FROM usuario WHERE nom_usuario = ?");
		$stmt->bind_param("s", $nom_usuario);
		$stmt->execute();
		$query = $stmt->get_result();

		if($query->num_rows < 1){
			// Cualquier intento fallido limpia la sesión: si ya existía una
			// sesión admin_intecap activa (por ejemplo por caché del navegador),
			// no se queda "colgada" dejando entrar sin validar nada.
			session_regenerate_id(true);
			unset($_SESSION['admin_intecap']);
			$_SESSION['error'] = 'Usuario no encontrado';
		}
		else{
			$row = $query->fetch_assoc();
			if(verificarPasswordSeguro($contraseña, $row['contraseña'], $conn, (int)$row['id'])){
				// Login correcto: regenerar el ID de sesión evita
				// ataques de "fijación de sesión" (session fixation).
				session_regenerate_id(true);
				$_SESSION['admin_intecap'] = $row['id'];
				unset($_SESSION['error']);
			}
			else{
				session_regenerate_id(true);
				unset($_SESSION['admin_intecap']);
				$_SESSION['error'] = 'La contraseña es incorrecta';
			}
		}
		$stmt->close();
	}
	else{
		unset($_SESSION['admin_intecap']);
		$_SESSION['error'] = 'Ingrese las credenciales de administrador';
	}

	header('location: ../index.php');
	exit; // CRÍTICO: sin este exit, el script seguía ejecutándose después
	      // de pedir la redirección (aquí no pasaba nada más, pero es la
	      // misma causa raíz del problema en session.php / index.php)
?>