<script>
    function eliminar(id){
        let text = "¿Está seguro de que quieres eliminar?";
        if (confirm(text) == true) {
            document.location="../../modelos/usuario_delete.php?id="+id;
        } else {
            alert("Elemento no eliminado");
        }
    }  

    function editar(id){
        document.location="../../vistas/ADMIN/Editar_USUARIO.php?id="+id; 
    }  
    
    function editarPass(id){
        document.location="../../vistas/ADMIN/Editar_USUARIO_pass.php?id="+id; 
    }  

    function comparar() {
        pass1 = document.getElementById('contraseña').value;
        pass2 = document.getElementById('contraseña1').value;

        if (pass1 != pass2) {
            document.getElementById("error").classList.add("mostrar");
            document.getElementById("add").disabled = true;
        } else {
            document.getElementById("error").classList.remove("mostrar");
            document.getElementById("add").disabled = false;
        }
    }
</script>