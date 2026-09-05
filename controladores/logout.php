<?php
	session_start();

	// Vaciar todas las variables de sesión
	$_SESSION = array();

	// Borrar también la cookie de sesión del navegador. session_destroy()
	// por sí solo NO borra la cookie, así que sin esto el navegador podía
	// seguir mandando un id de sesión "de zombie".
	if (ini_get('session.use_cookies')) {
		$params = session_get_cookie_params();
		setcookie(
			session_name(), '', time() - 42000,
			$params['path'], $params['domain'],
			$params['secure'], $params['httponly']
		);
	}

	session_destroy();

	header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
	header('Pragma: no-cache');
	header('location: ../index.php');
	exit;
?>