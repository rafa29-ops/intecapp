<?php
	include '../../modelos/db.php';
    
    function redireccionarrrr() {
        if ($user['cargo']=="Admin") {

        }else{
            echo "<script>document.location='../ADMIN/principal.php'</script>";
        }
    }

    $cargo = $user['cargo'];
    $cargo = json_encode($cargo);

    $nombre_evento = array();
    $participantes = array();

    $sql = "SELECT * FROM eventos as e INNER JOIN talleres as t ON e.id_talleres = t.id";
    $query = $conn->query($sql);
    
    while($row = $query->fetch_assoc()){
        array_push($nombre_evento, $row['nombre_evento']);
        array_push($participantes, $row['participantes']);
    }

    $nombre_evento = json_encode($nombre_evento);
    $participantes = json_encode($participantes);

?>

<script>

    function redireccionar() {
        if ( <?php echo $cargo; ?> == "Instructor" ) {

        }else{
            document.location='../ADMIN/principal.php';
        }
    }

    function eliminar(id_eventos){
        //redireccionar();
        let text = "¿Está seguro de que quieres eliminar?";
        if (confirm(text) == true) {
            document.location="../../modelos/evento_delete.php?id_eventos="+id_eventos;
        } else {
            alert("Elemento no eliminado");
        }
    }  

    function editar(id_eventos){
        //redireccionar();
        document.location="../../vistas/ADMIN/Editar_EVENTO.php?id_eventos="+id_eventos;
    }   
</script>

<script>
// Espera a que el DOM esté completamente cargado
window.onload = function() {
    //redireccionar();
   // Gráfico de Barras (Bar Chart)
   var ctxBar = document.getElementById('barChart').getContext('2d');
   
   var barChart = new Chart(ctxBar, {
       type: 'bar',
       data: {
           labels: <?php echo $nombre_evento; ?>,
           datasets: [{
               label: 'Eventos',
               data: <?php echo $participantes; ?>,
               backgroundColor: '#1E90FF',  // Azul brillante para el fondo de las barras
               borderColor: '#4682B4',      // Azul más oscuro para el borde
               borderWidth: 1
           }]
       },
       options: {
           responsive: true,
           maintainAspectRatio: false,  // Esto permite que el gráfico se ajuste al tamaño del contenedor
           scales: {
               y: {
                   beginAtZero: true
               }
           }
       }
   });
}
</script>