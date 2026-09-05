<script>
    function eliminar(id){
        let text = "¿Está seguro de que quieres eliminar?";
        if (confirm(text) == true) {
            document.location="../../modelos/mantenimiento_delete.php?id="+id;
        } else {
            alert("Elemento no eliminado");
        }
    }  

    function editar(id){
        document.location="../../vistas/ADMIN/Editar_MANTENIMIENTO.php?id="+id;    
    }   
    
    function comentario(id){
        document.location="../../vistas/ADMIN/COMENTARIOS.php?id="+id;    
    }
    
    function mensaje(id){
        document.location="../../vistas/ADMIN/COMENTARIOS.php?id="+id;    
    }
</script>