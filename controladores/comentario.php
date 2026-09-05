<script>
    function eliminar(id){
        let text = "¿Está seguro de que quieres eliminar?";
        if (confirm(text) == true) {
            document.location="../../modelos/comentario_delete.php?id="+id;
        } else {
            alert("Elemento no eliminado");
        }
    }  
</script>
