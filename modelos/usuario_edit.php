<?php
include('db.php');

$instructor           = $_POST['instructor']           ?? '0';
$id                   = $_POST['id']                   ?? '';
$nombre               = $_POST['nombre']               ?? '';
$telefono              = $_POST['telefono']             ?? '';
$cargo                = $_POST['cargo']                ?? '';
$nom_usuario          = $_POST['nom_usuario']          ?? '';
$correo               = trim($_POST['correo']          ?? '');
$estado               = $_POST['estado']                ?? '';
$area_especializacion = $_POST['area_especializacion'] ?? '';

// La página de edición vuelve a ir aquí si algo falla. Usamos un
// redirect normal (document.location) en vez de history.back(): un
// history.back() se clasifica como navegación "back_forward" y el
// guard de session.php cierra la sesión automáticamente al detectarlo,
// lo que expulsaba al usuario al login cada vez que había un error.
$backUrl = ($instructor == '1')
    ? '/intecapp/vistas/ADMIN/Editar_INSTRUCTOR.php?id=' . urlencode($id)
    : '/intecapp/vistas/ADMIN/Editar_USUARIO.php?id=' . urlencode($id);

// ── Validación del correo ──────────────────────────────────────────
// El correo es OBLIGATORIO al registrar (usuario_add.php), pero aquí,
// al editar, es opcional: si el formulario no lo envía (como pasa hoy
// en Editar_INSTRUCTOR.php / Editar_USUARIO.php, que no tienen ese
// campo) o lo dejan vacío, simplemente no se toca/valida el correo.
// Si SÍ mandan un valor, seguimos validando que tenga formato correcto
// y que no esté repetido.
if ($correo !== '') {
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('El correo electrónico no es válido.'); document.location='$backUrl';</script>";
        exit;
    }

    $stmtCheck = $conn->prepare("SELECT id FROM usuario WHERE correo = ? AND id <> ?");
    $stmtCheck->bind_param("si", $correo, $id);
    $stmtCheck->execute();
    $stmtCheck->store_result();
    if ($stmtCheck->num_rows > 0) {
        $stmtCheck->close();
        echo "<script>alert('Ese correo ya está registrado con otra cuenta.'); document.location='$backUrl';</script>";
        exit;
    }
    $stmtCheck->close();
}
// ────────────────────────────────────────────────────────────────────

if ($cargo !== 'Instructor') {
    $area_especializacion = '';
}

// Procesar foto si se subió una nueva
$foto_sql   = '';
$foto_param = null;

if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $tipo     = $_FILES['foto']['type'];
    $datos    = file_get_contents($_FILES['foto']['tmp_name']);
    $base64   = base64_encode($datos);
    $foto_param = 'data:' . $tipo . ';base64,' . $base64;
    $foto_sql = ', foto = ?';
}

// Si no mandaron correo, no lo tocamos en el UPDATE (así no se borra
// el correo que ya tenía el usuario cuando el formulario, como el de
// Instructores, no incluye ese campo).
$campos = "nombre = ?, telefono = ?, cargo = ?, nom_usuario = ?, estado = ?, area_especializacion = ?";
$tipos  = "ssssss";
$valores = [$nombre, $telefono, $cargo, $nom_usuario, $estado, $area_especializacion];

if ($correo !== '') {
    $campos .= ", correo = ?";
    $tipos  .= "s";
    $valores[] = $correo;
}

if ($foto_param !== null) {
    $campos .= ", foto = ?";
    $tipos  .= "s";
    $valores[] = $foto_param;
}

$tipos     .= "i";
$valores[]  = $id;

$stmt = $conn->prepare("UPDATE usuario SET $campos WHERE id = ?");
$stmt->bind_param($tipos, ...$valores);

if ($stmt->execute()) {
    echo "<script>alert('Registro actualizado correctamente.');</script>";
    if ($instructor == '1') {
        echo "<script>document.location='/intecapp/vistas/ADMIN/INSTRUCTORES.php'</script>";
    } else {
        echo "<script>document.location='/intecapp/vistas/ADMIN/USUARIO.php'</script>";
    }
} else {
    echo "<script>alert('Error al actualizar: " . addslashes($stmt->error) . "'); document.location='$backUrl';</script>";
}

$stmt->close();
$conn->close();
?>