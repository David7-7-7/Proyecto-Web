<?php
session_start();

//crear vector si no existe
if(!isset($_SESSION['empleados'])){
    $_SESSION['empleados'] = [];
}

//Creamos una variable para manejar las acciones
$accion = $_GET['accion'] ?? 'listar';

switch($accion){
    case 'guardar':
    guardar();
    break;
      
    case 'listar':
    listar();
    break;

    case 'editar':
    editar();
    break;

    case 'eliminar':
    eliminar(); 
    break;

    case 'actualizar':
    actualizar();
    break;
            
}

// FUNCOIONES

function guardar(){

    //Creamos variables para guardar los datos del formulario
    $codigo = $_POST['codigo'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];

    //Creamos un array para guardar los datos del empleado
    $empleado = [
        'codigo' => $codigo,
        'nombre' => $nombre,
        'apellido' => $apellido,
        'telefono' => $telefono,
        'email' => $email,
        'fecha_nacimiento' => $fecha_nacimiento
    ];

    //Metemos el empleado en el vector de empleados
    $_SESSION['empleados'][] = $empleado;//

    header ("Location: empleados.php?accion=listar");
}

function listar(){
    $empleados = $_SESSION['empleados'];

    echo "<h1>Lista de empleados</h1>";

    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>Codigo</th>";
    echo "<th>Nombre</th>";
    echo "<th>Apellido</th>";
    echo "<th>Telefono</th>";
    echo "<th>Email</th>";
    echo "<th>Fecha de nacimiento</th>";
    echo "<th>Acciones</th>";
    echo "</tr>";

    foreach($empleados as $index => $emp){
        echo "<tr>";
        echo "<td>".$emp['codigo']."</td>";
        echo "<td>".$emp['nombre']."</td>";
        echo "<td>".$emp['apellido']."</td>";
        echo "<td>".$emp['telefono']."</td>";
        echo "<td>".$emp['email']."</td>";
        echo "<td>".$emp['fecha_nacimiento']."</td>";
        //Creamos los botones de editar y eliminar
        echo "<td>
        <a href='empleados.php?accion=editar&index=$index'>Editar</a>
        <a href='empleados.php?accion=eliminar&index=$index'>Eliminar</a>
        </td>";

        echo "</tr>";

    }

    echo "</table>";

    echo "<a href='Frontend/index.html'>Nuevo empleado</a>";

}

function editar(){
    //Obtenemos el indice del empleado a editar
    $index = $_GET['index'];
    //Obtenemos el empleado a editar en la posicion del vector
    $empleado = $_SESSION['empleados'][$index];

    ?>
    <h2>Editar empleado</h2>
    <form action="empleados.php?accion=actualizar" method="POST">
        <input type="hidden" name="index" value="<?php echo $index; ?>">

        Codigo: <br>
        <input type="text" name="codigo" value="<?php echo $empleado['codigo']; ?>"><br>
        Nombre: <br>
        <input type="text" name="nombre" value="<?php echo $empleado['nombre']; ?>"><br>
        Apellido: <br>
        <input type="text" name="apellido" value="<?php echo $empleado['apellido']; ?>"><br>
        Telefono: <br>
        <input type="text" name="telefono" value="<?php echo $empleado['telefono']; ?>"><br>
        Email: <br>
        <input type="text" name="email" value="<?php echo $empleado['email']; ?>"><br>
        Fecha de nacimiento: <br>
        <input type="date" name="fecha_nacimiento" value="<?php echo $empleado['fecha_nacimiento']; ?>"><br>
        <input type="submit" value="Actualizar">
        <input type="button" value="Cancelar" onclick="window.location.href='empleados.php?accion=listar'">
    </form>
    <?php

}

function actualizar(){
    //Obtenemos el indice del empleado a actualizar
    $index = $_POST['index'];
    //Creamos un array para guardar los datos del empleado actualizado
    $empleado = [
        'codigo' => $_POST['codigo'], // 
        'nombre' => $_POST['nombre'],
        'apellido' => $_POST['apellido'],
        'telefono' => $_POST['telefono'],
        'email' => $_POST['email'],
        'fecha_nacimiento' => $_POST['fecha_nacimiento']
    ];

    //Actualizamos el empleado en el vector de empleados
    $_SESSION['empleados'][$index] = $empleado;


    header("Location: empleados.php?accion=listar");
}   

function eliminar(){
    //Obtenemos el indice del empleado a eliminar
    $index = $_GET['index'];
    //Eliminamos el empleado del vector de empleados
    unset($_SESSION['empleados'][$index]);

    header("Location: empleados.php?accion=listar");
}