<?php
session_start();

//crear vector si no existe
if(!isset($_SESSION['empleados'])){
    $_SESSION['empleados'] = [];
}

//Creamos una variable para manejar las acciones
$accion = $_GET['accion'] ?? 'listar';

// Inyectamos el estilo Bootstrap para que todo se vea bien
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<style>
    body { background-color: #f8f9fa; padding: 40px; font-family: sans-serif; }
    .card { border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 12px; padding: 25px; background: white; }
    table { border-collapse: separate; border-spacing: 0; width: 100%; }
    th { background-color: #212529 !important; color: white !important; }
</style>";

echo "<div class='container'>";

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

echo "</div>";

// FUNCiONES

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
        'fecha_nacimiento' => $fecha_nacimiento,
        'cargo' => $_POST['cargo'],
        'salario' => $_POST['salario'],
        'dias' => $_POST['dias'],
        'nocturnos' => $_POST['nocturnos'],
        'nivel_arl' => $_POST['nivel_arl'],
        'eps' => $_POST['eps'],
        'transporte' => 'auto'
    ];
    
    //Metemos el empleado en el vector de empleados
    $_SESSION['empleados'][] = $empleado;//
    
    header ("Location: empleados.php?accion=listar");
}

function listar(){
    $empleados = $_SESSION['empleados'];
    
    echo "<h1 class='mb-4 fw-bold'>Lista de empleados</h1>";
    
    echo "<div class='card'>";
    echo "<table class='table table-hover'>";
    echo "<thead><tr>";
    echo "<th>Documento</th>";
    echo "<th>Nombre</th>";
    echo "<th>Apellido</th>";
    echo "<th>Rol</th>";
    echo "<th>Telefono</th>";
    echo "<th>Email</th>";
    echo "<th>Nacimiento</th>";
    echo "<th>Salario</th>";
    echo "<th>Acciones</th>";
    echo "</tr></thead><tbody>";
    
    foreach($empleados as $index => $emp){
        echo "<tr>";
        echo "<td>".$emp['codigo']."</td>";
        echo "<td>".$emp['nombre']."</td>";
        echo "<td>".$emp['apellido']."</td>";
        echo "<td><span class='badge bg-info text-dark'>".($emp['cargo'] ?? 'No asignado')."</span></td>";
        echo "<td>".$emp['telefono']."</td>";
        echo "<td>".$emp['email']."</td>";
        echo "<td>".$emp['fecha_nacimiento']."</td>";
        echo "<td>$ ".number_format($emp['salario'], 0, ',', '.')."</td>";
        //Creamos los botones de editar y eliminar
        echo "<td>
        <a href='empleados.php?accion=editar&index=$index' class='btn btn-sm btn-primary'>Editar</a>
        <a href='empleados.php?accion=eliminar&index=$index' class='btn btn-sm btn-danger' onclick='return confirm(\"¿Eliminar?\")'>Eliminar</a>
        </td>";
        
        echo "</tr>";
        
    }
    
    echo "</tbody></table>";
    echo "</div>";
    
    echo "<div class='mt-4'>";
    echo "<a href='Frontend/index.html' class='btn btn-secondary me-2'>Nuevo empleado</a>";
    echo "<a href='nomina.php' class='btn btn-success px-4 fw-bold'>Calcular Nómina</a>";
    echo "</div>";
    
}

function editar(){
    //Obtenemos el indice del empleado a editar
    $index = $_GET['index'];
    //Obtenemos el empleado a editar en la posicion del vector
    $empleado = $_SESSION['empleados'][$index];
    //Cambiar el cargo del empleado
    $cargo_actual = $empleado['cargo'] ?? '';
    
    ?>
    <div class="row justify-content-center">
        <div class="col-md-6 card">
            <h2 class="mb-4 text-primary">Editar empleado</h2>
            <form action="empleados.php?accion=actualizar" method="POST" class="row g-3">
                <input type="hidden" name="index" value="<?php echo $index; ?>">

                <div class="col-md-6">
                    <label class="form-label">Codigo:</label>
                    <input type="text" name="codigo" class="form-control" value="<?php echo $empleado['codigo']; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nombre:</label>
                    <input type="text" name="nombre" class="form-control" value="<?php echo $empleado['nombre']; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Apellido:</label>
                    <input type="text" name="apellido" class="form-control" value="<?php echo $empleado['apellido']; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Telefono:</label>
                    <input type="text" name="telefono" class="form-control" value="<?php echo $empleado['telefono']; ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Email:</label>
                    <input type="text" name="email" class="form-control" value="<?php echo $empleado['email']; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fecha de nacimiento:</label>
                    <input type="date" name="fecha_nacimiento" class="form-control" value="<?php echo $empleado['fecha_nacimiento']; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Rol:</label>
                    <select name="cargo" class="form-select">
                        <option value="Administrador" <?php echo ($cargo_actual == 'Administrador') ? 'selected' : ''; ?>>Administrador</option>
                        <option value="Operario" <?php echo ($cargo_actual == 'Operario') ? 'selected' : ''; ?>>Operario</option>
                        <option value="Gerente" <?php echo ($cargo_actual == 'Gerente') ? 'selected' : ''; ?>>Gerente</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Salario:</label>
                    <input type="number" name="salario" class="form-control" value="<?php echo $empleado['salario'] ?? ''; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Días:</label>
                    <input type="number" name="dias" class="form-control" value="<?php echo $empleado['dias'] ?? ''; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nocturnos:</label>
                    <input type="number" name="nocturnos" class="form-control" value="<?php echo $empleado['nocturnos'] ?? ''; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nivel ARL:</label>
                    <input type="number" name="nivel_arl" class="form-control" value="<?php echo $empleado['nivel_arl'] ?? ''; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">EPS:</label>
                    <input type="text" name="eps" class="form-control" value="<?php echo $empleado['eps'] ?? ''; ?>">
                </div>
                
                <div class="col-12 mt-4">
                    <input type="submit" value="Actualizar" class="btn btn-primary px-4">
                    <input type="button" value="Cancelar" class="btn btn-outline-secondary ms-2" onclick="window.location.href='empleados.php?accion=listar'">
                </div>
            </form>
        </div>
    </div>
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
        'fecha_nacimiento' => $_POST['fecha_nacimiento'],
        'cargo' => $_POST['cargo'],
        'cargo' => $_POST['cargo'],
        'salario' => $_POST['salario'],
        'dias' => $_POST['dias'],
        'nocturnos' => $_POST['nocturnos'],
        'nivel_arl' => $_POST['nivel_arl'],
        'eps' => $_POST['eps'],
        'transporte' => 'auto'
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