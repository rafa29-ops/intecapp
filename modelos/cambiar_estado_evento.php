<?php
include($_SERVER['DOCUMENT_ROOT'] . '/intecapp/modelos/db.php');
include($_SERVER['DOCUMENT_ROOT'] . '/intecapp/controladores/session.php');

// Determina si un evento ya debe cerrarse automáticamente según su fecha y hora de finalización.
function debeFinalizarEvento($evento) {
    $hoy = new DateTime('today');
    $hoy->setTime(0, 0, 0);

    $fechaFin = new DateTime($evento['f_fin']);
    $fechaFin->setTime(0, 0, 0);

    if ($fechaFin < $hoy) {
        return true;
    }

    if ($fechaFin > $hoy) {
        return false;
    }

    $horaSalida = !empty($evento['hora_salida']) ? $evento['hora_salida'] : '23:59:59';
    $ahora = new DateTime();
    $ahoraComparar = new DateTime($ahora->format('Y-m-d') . ' ' . $horaSalida);

    return $ahora >= $ahoraComparar;
}

// Procesa un evento terminado: lo registra en asistencia y lo elimina de la lista activa de eventos.
function procesarEvento($conn, $id_evento) {
    $consulta_evento = "SELECT e.id_instructor, e.nombre_evento, e.f_inicio, e.f_fin, e.modalidad, e.Estatilla, e.hora_entrada, e.hora_salida, t.nombre_taller 
                        FROM eventos as e 
                        INNER JOIN talleres as t ON e.id_talleres = t.id 
                        WHERE e.id_eventos = ?";

    $stmt = $conn->prepare($consulta_evento);
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("i", $id_evento);
    $stmt->execute();
    $resultado_evento = $stmt->get_result();
    $stmt->close();

    if (!$resultado_evento || $resultado_evento->num_rows === 0) {
        return false;
    }

    $evento = $resultado_evento->fetch_assoc();

    $id_instructor   = $evento['id_instructor'];
    $fecha_evento    = $evento['f_inicio'];
    $nombre_taller   = $evento['nombre_taller'];
    $nombre_evento   = $evento['nombre_evento'];
    $modalidad       = $evento['modalidad'];
    $estatilla       = $evento['Estatilla'];
    $hora_entrada    = $evento['hora_entrada'];
    $hora_salida     = $evento['hora_salida'];

    // Inicia una transacción para asegurar que el evento se registre en asistencia y se elimine de forma consistente.
    $conn->begin_transaction();

    try {
        $estado_asistencia = "Terminado";

        // Registra el evento como asistencia para conservar el historial del instructor y del evento.
        $sql_asistencia = "INSERT INTO asistencia (id_usuario, fecha, nombre_taller, nombre_evento, modalidad, estatilla, hora_entrada, hora_salida, estado) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt_asistencia = $conn->prepare($sql_asistencia);
        if (!$stmt_asistencia) {
            throw new Exception("Error en la preparación de asistencia: " . $conn->error);
        }

        $stmt_asistencia->bind_param("issssssss", $id_instructor, $fecha_evento, $nombre_taller, $nombre_evento, $modalidad, $estatilla, $hora_entrada, $hora_salida, $estado_asistencia);

        if (!$stmt_asistencia->execute()) {
            throw new Exception("Error al insertar en la tabla asistencia: " . $stmt_asistencia->error);
        }
        $stmt_asistencia->close();

        // Elimina el evento de la tabla activa una vez que ya fue procesado en asistencia.
        $sql_delete = "DELETE FROM eventos WHERE id_eventos = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        if (!$stmt_delete) {
            throw new Exception("Error en la preparación del delete: " . $conn->error);
        }

        $stmt_delete->bind_param("i", $id_evento);
        if (!$stmt_delete->execute()) {
            throw new Exception("Error al eliminar el evento de la lista: " . $stmt_delete->error);
        }
        $stmt_delete->close();

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

// Si se recibe un id por URL, procesa ese evento de forma manual. Si no, revisa todos los eventos y cierra automáticamente los que ya finalizaron.
if (isset($_GET['id_eventos']) && !empty($_GET['id_eventos'])) {
    $id_evento = intval($_GET['id_eventos']);

    try {
        if (procesarEvento($conn, $id_evento)) {
            header("Location: ../vistas/ADMIN/EVENTOS.php");
            exit();
        }

        echo "<script>
                alert('El evento especificado no existe o ya fue procesado como terminado.');
                window.location.href = '../vistas/ADMIN/listas/eventos_list.php';
              </script>";
        exit();
    } catch (Exception $e) {
        echo "Hubo un problema crítico durante el procesamiento: " . $e->getMessage();
        exit();
    }
} else {
    $sql = "SELECT e.id_eventos, e.f_fin, e.hora_salida, e.id_instructor, e.nombre_evento, e.f_inicio, e.modalidad, e.Estatilla, e.hora_entrada, t.nombre_taller
            FROM eventos as e
            INNER JOIN talleres as t ON e.id_talleres = t.id";

    $query = $conn->query($sql);
    if ($query) {
        while ($row = $query->fetch_assoc()) {
            // Cierra automáticamente todos los eventos cuya fecha y hora de finalización ya expiraron.
            if (debeFinalizarEvento($row)) {
                procesarEvento($conn, intval($row['id_eventos']));
            }
        }
    }
}
?>