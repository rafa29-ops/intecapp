<?php
	include '../../modelos/db.php';

    $talleres = array();
    $participantes = array();

    $sql = "SELECT * FROM talleres";
    $query = $conn->query($sql);   

    while($row = $query->fetch_assoc()){
        array_push($talleres, $row['nombre_taller']);
        array_push($participantes, $row['participantes']);
    }

    $talleres = json_encode($talleres);
    $participantes = json_encode($participantes);
?>



<script>
    function eliminar(id){
        let text = "¿Está seguro de que quieres eliminar?";
        if (confirm(text) == true) {
            document.location="../../modelos/taller_delete.php?id="+id;
        } else {
            alert("Elemento no eliminado");
        }
    }  

    function editar(id){
        document.location="../../vistas/ADMIN/Editar_TALLER.php?id="+id; 
    }  
</script>

<script>
    // Espera a que el DOM esté completamente cargado
    window.onload = function() {

    // Gráfico de Barras (Bar Chart)
    var ctxBar = document.getElementById('barChart').getContext('2d');
    
    var barChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: <?php echo $talleres; ?>,
            datasets: [{
                label: 'Talleres',
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
