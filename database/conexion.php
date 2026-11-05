<?php

$servidor = "localhost";
$usuario = "root";
$contrasena = "";
$baseDatos = "gestion_pae_nuevo";

$conexion = new mysqli(
    $servidor,
    $usuario,
    $contrasena,
    $baseDatos
);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");

?>