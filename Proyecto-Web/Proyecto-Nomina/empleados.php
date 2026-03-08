<?php
session_start();

//crear vector si no existe
if(!isset($_SESSION['empleados'])){
    $_SESSION['empleados'] = [];
}

//recibir datos
$cod = $_POST['cod'];
$nom = $_POST['nom'];
$ape = $_POST['ape'];
$tel = $_POST['tel'];
$em = $_POST['em'];
$fn = $_POST['fn'];

//crear empleado
$empleado = [
    "cod"=>$cod,
    "nom"=>$nom,
    "ape"=>$ape,
    "tel"=>$tel,
    "em"=>$em,
    "fn"=>$fn
];

//guardar en vector
array_push($_SESSION['empleados'], $empleado);
?>

<!DOCTYPE html>
<html>
<head>
<title>Lista de empleados</title>
</head>

<body>

<h2>Empleado Registrado</h2>

<?php
echo "Bienvenido ".$nom." ".$ape."<br><br>";
?>

<h2>Lista de Empleados</h2>

<table border="1">

<tr>
<th>Codigo</th>
<th>Nombre</th>
<th>Apellido</th>
<th>Telefono</th>
<th>Email</th>
<th>Fecha Nac</th>
</tr>

<?php
foreach($_SESSION['empleados'] as $emp){

echo "<tr>";
echo "<td>".$emp['cod']."</td>";
echo "<td>".$emp['nom']."</td>";
echo "<td>".$emp['ape']."</td>";
echo "<td>".$emp['tel']."</td>";
echo "<td>".$emp['em']."</td>";
echo "<td>".$emp['fn']."</td>";
echo "</tr>";

}
?>

</table>

<br><br>

<a href="index.html">Registrar otro empleado</a>

</body>
</html>