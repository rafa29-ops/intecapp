<?php
	// Evita que este archivo se ejecute más de una vez en la misma petición.
	// Algunas páginas incluyen session.php de forma indirecta más de una vez
	// (por ejemplo: header.php lo incluye, y luego un modelo como
	// cambiar_estado_evento.php lo vuelve a incluir). Sin esta guardia, la
	// segunda ejecución intenta llamar header() cuando ya se envió HTML al
	// navegador (el <!DOCTYPE...> de header.php), causando el error
	// "Cannot modify header information - headers already sent".
	if (defined('INTECAP_SESSION_LOADED')) {
		return;
	}
	define('INTECAP_SESSION_LOADED', true);

	if (session_status() === PHP_SESSION_NONE) session_start();
	include($_SERVER['DOCUMENT_ROOT'] . '/intecapp/modelos/db.php');
	include($_SERVER['DOCUMENT_ROOT'] . '/intecapp/modelos/config.php');
	date_default_timezone_set('America/Guatemala');

	// Evita que el navegador guarde en caché (bfcache/"volver con las
	// flechas") las páginas protegidas. Sin esto, al presionar "atrás" el
	// navegador mostraba una copia guardada de la página sin volver a
	// preguntarle al servidor si la sesión seguía siendo válida.
	header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
	header('Pragma: no-cache');
	header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');

	if(!isset($_SESSION['admin_intecap']) || trim($_SESSION['admin_intecap']) == ''){
		// IMPORTANTE: ruta ABSOLUTA (BASE_URL), no relativa.
		// Este archivo se incluye desde páginas a distinta profundidad
		// (vistas/ADMIN/principal.php, vistas/ADMIN/listas/..., etc.), y
		// un redirect relativo como '../index.php' se resuelve según la
		// carpeta de la página que el navegador está mostrando, no según
		// la carpeta real de session.php. Eso causaba el error 404
		// "Extraviado" al volver con las flechas del navegador.
		header('location: ' . BASE_URL . '/index.php');
		exit; // CRÍTICO: sin esto, el resto de la página protegida se
		      // seguía generando y enviando aunque se pidiera el redirect.
	}

	$stmt = $conn->prepare("SELECT * FROM usuario WHERE id = ?");
	$stmt->bind_param("i", $_SESSION['admin_intecap']);
	$stmt->execute();
	$query = $stmt->get_result();
	$user = $query->fetch_assoc();
	$stmt->close();

	if (!$user) {
		// El id en sesión ya no existe en la BD (usuario eliminado, etc.)
		session_destroy();
		header('location: ' . BASE_URL . '/index.php');
		exit;
	}

	$id_sesion = $user['id'];

	// ── Bloquear "reingreso" con los botones atrás/adelante ─────────────
	// El servidor no puede saber por sí solo si una petición vino de un
	// clic normal o de los botones atrás/adelante del navegador; eso solo
	// lo sabe el navegador. Por eso este script (usa la Navigation API)
	// revisa cómo se cargó la página: si el tipo de navegación es
	// "back_forward", significa que el usuario llegó aquí con atrás o
	// adelante, y entonces:
	//   1) cerramos la sesión de inmediato (logout.php), y
	//   2) mandamos al login.
	// Así, aunque la sesión del servidor siguiera siendo válida, no se
	// puede "reentrar" a una pantalla ya autenticada navegando solo con
	// el historial del navegador — hay que iniciar sesión de nuevo.
	echo '<script>
	(function () {
		var entradasNav = performance.getEntriesByType("navigation");
		var tipoNav = entradasNav.length ? entradasNav[0].type : null;
		if (tipoNav === "back_forward") {
			fetch("' . BASE_URL . '/controladores/logout.php", { cache: "no-store", keepalive: true });
			window.location.replace("' . BASE_URL . '/index.php");
		}
	})();
	</script>';
?>