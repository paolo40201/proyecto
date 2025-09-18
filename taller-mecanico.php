<?php
// Configuración de la base de datos
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "taller_mecanico";

// Crear conexión
$conn = new mysqli($host, $user, $pass);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Crear base de datos si no existe
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql)) {
    $conn->select_db($dbname);
} else {
    die("Error creando base de datos: " . $conn->error);
}

// Crear tablas según el MER
$sql_tablas = array(
    "CREATE TABLE IF NOT EXISTS Usuario (
        ID_Usuario INT AUTO_INCREMENT PRIMARY KEY,
        Email VARCHAR(100) NOT NULL UNIQUE,
        Contrasena VARCHAR(255) NOT NULL,
        Rol ENUM('administrador', 'taller', 'cliente') NOT NULL,
        Fecha_Registro DATE NOT NULL
    )",
    
    "CREATE TABLE IF NOT EXISTS Cliente (
        ID_Cliente INT AUTO_INCREMENT PRIMARY KEY,
        ID_Usuario INT,
        Nombre VARCHAR(100) NOT NULL,
        Apellido VARCHAR(100) NOT NULL,
        DNI VARCHAR(20) NOT NULL UNIQUE,
        Telefono VARCHAR(20),
        Direccion TEXT,
        FOREIGN KEY (ID_Usuario) REFERENCES Usuario(ID_Usuario) ON DELETE CASCADE
    )",
    
    "CREATE TABLE IF NOT EXISTS Taller (
        ID_Taller INT AUTO_INCREMENT PRIMARY KEY,
        Nombre_Taller VARCHAR(100) NOT NULL,
        Direccion_Taller TEXT,
        Telefono_Taller VARCHAR(20)
    )",
    
    "CREATE TABLE IF NOT EXISTS Vehiculo (
        ID_Vehiculo INT AUTO_INCREMENT PRIMARY KEY,
        ID_Cliente INT NOT NULL,
        Matricula VARCHAR(20) NOT NULL UNIQUE,
        Modelo VARCHAR(50) NOT NULL,
        Marca VARCHAR(50) NOT NULL,
        Ano INT,
        Fecha_Compra DATE,
        Fecha_Fin_Garantia DATE,
        Imagen_URL VARCHAR(255),
        Estado_Garantia ENUM('Activa', 'Expirada', 'No aplica') DEFAULT 'No aplica',
        FOREIGN KEY (ID_Cliente) REFERENCES Cliente(ID_Cliente) ON DELETE CASCADE
    )",
    
    "CREATE TABLE IF NOT EXISTS TipoServicio (
        ID_Tipo INT AUTO_INCREMENT PRIMARY KEY,
        Nombre VARCHAR(100) NOT NULL,
        Descripcion TEXT,
        Modelos_Aplicables VARCHAR(255),
        Requiere_Garantia BOOLEAN DEFAULT FALSE
    )",
    
    "CREATE TABLE IF NOT EXISTS ServicioRealizado (
        ID_Servicio INT AUTO_INCREMENT PRIMARY KEY,
        ID_Vehiculo INT NOT NULL,
        ID_Taller INT NOT NULL,
        ID_Tipo INT NOT NULL,
        Fecha DATE NOT NULL,
        Descripcion TEXT,
        Costo DECIMAL(10,2) DEFAULT 0.00,
        Garantia_Cubierta BOOLEAN DEFAULT FALSE,
        Estado ENUM('Pendiente', 'En Proceso', 'Completado') DEFAULT 'Pendiente',
        FOREIGN KEY (ID_Vehiculo) REFERENCES Vehiculo(ID_Vehiculo) ON DELETE CASCADE,
        FOREIGN KEY (ID_Taller) REFERENCES Taller(ID_Taller) ON DELETE CASCADE,
        FOREIGN KEY (ID_Tipo) REFERENCES TipoServicio(ID_Tipo) ON DELETE CASCADE
    )",
    
    "CREATE TABLE IF NOT EXISTS Administrador (
        ID_Administrador INT AUTO_INCREMENT PRIMARY KEY,
        ID_Usuario INT,
        Departamento VARCHAR(100),
        FOREIGN KEY (ID_Usuario) REFERENCES Usuario(ID_Usuario) ON DELETE CASCADE
    )",
    
    "CREATE TABLE IF NOT EXISTS TipoInsumo (
        ID_TipoInsumo INT AUTO_INCREMENT PRIMARY KEY,
        Nombre VARCHAR(100) NOT NULL,
        Categoria VARCHAR(50),
        Especificaciones TEXT
    )",
    
    "CREATE TABLE IF NOT EXISTS Insumo (
        ID_Insumo INT AUTO_INCREMENT PRIMARY KEY,
        ID_TipoInsumo INT NOT NULL,
        ID_Administrador INT,
        Cantidad DECIMAL(10,2) DEFAULT 0,
        Costo_Unitario DECIMAL(10,2) DEFAULT 0.00,
        Fecha_Ingreso DATE,
        FOREIGN KEY (ID_TipoInsumo) REFERENCES TipoInsumo(ID_TipoInsumo) ON DELETE CASCADE,
        FOREIGN KEY (ID_Administrador) REFERENCES Administrador(ID_Administrador) ON DELETE SET NULL
    )",
    
    "CREATE TABLE IF NOT EXISTS ServicioInsumo (
        ID_ServicioInsumo INT AUTO_INCREMENT PRIMARY KEY,
        ID_Servicio INT NOT NULL,
        ID_Insumo INT NOT NULL,
        Cantidad_Utilizada DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (ID_Servicio) REFERENCES ServicioRealizado(ID_Servicio) ON DELETE CASCADE,
        FOREIGN KEY (ID_Insumo) REFERENCES Insumo(ID_Insumo) ON DELETE CASCADE
    )"
);

foreach ($sql_tablas as $sql) {
    if (!$conn->query($sql)) {
        echo "Error creando tabla: " . $conn->error;
    }
}

// Insertar datos de ejemplo si las tablas están vacías
$check_data = $conn->query("SELECT COUNT(*) as total FROM Usuario");
$row = $check_data->fetch_assoc();
if ($row['total'] == 0) {
    // Insertar datos de ejemplo
    $sql_datos = array(
        "INSERT INTO Usuario (Email, Contrasena, Rol, Fecha_Registro) VALUES 
        ('admin@taller.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'administrador', CURDATE()),
        ('taller1@taller.com', '" . password_hash('taller123', PASSWORD_DEFAULT) . "', 'taller', CURDATE()),
        ('cliente1@email.com', '" . password_hash('cliente123', PASSWORD_DEFAULT) . "', 'cliente', CURDATE())",
        
        "INSERT INTO Cliente (ID_Usuario, Nombre, Apellido, DNI, Telefono, Direccion) VALUES
        (3, 'Juan', 'Pérez', '12345678', '555-1234', 'Calle Falsa 123')",
        
        "INSERT INTO Taller (Nombre_Taller, Direccion_Taller, Telefono_Taller) VALUES
        ('Taller Central', 'Av. Principal 456', '555-1000'),
        ('Taller Norte', 'Calle Norte 789', '555-2000')",
        
        "INSERT INTO Vehiculo (ID_Cliente, Matricula, Modelo, Marca, Ano, Fecha_Compra, Fecha_Fin_Garantia, Estado_Garantia) VALUES
        (1, 'ABC123', 'Civic', 'Honda', 2020, '2020-05-15', '2023-05-15', 'Expirada'),
        (1, 'XYZ789', 'Corolla', 'Toyota', 2022, '2022-01-20', '2025-01-20', 'Activa')",
        
        "INSERT INTO TipoServicio (Nombre, Descripcion, Modelos_Aplicables, Requiere_Garantia) VALUES
        ('Cambio de aceite', 'Reemplazo de aceite y filtro', 'Todos', FALSE),
        ('Reparación de motor', 'Reparación general del motor', 'Todos', TRUE),
        ('Alineación y balanceo', 'Alineación de ruedas y balanceo de neumáticos', 'Todos', FALSE)",
        
        "INSERT INTO ServicioRealizado (ID_Vehiculo, ID_Taller, ID_Tipo, Fecha, Descripcion, Costo, Garantia_Cubierta, Estado) VALUES
        (1, 1, 1, '2023-02-10', 'Cambio de aceite sintético', 45.00, FALSE, 'Completado'),
        (2, 1, 3, '2023-03-15', 'Alineación completa', 60.00, TRUE, 'Completado')",
        
        "INSERT INTO Administrador (ID_Usuario, Departamento) VALUES (1, 'Gerencia')",
        
        "INSERT INTO TipoInsumo (Nombre, Categoria, Especificaciones) VALUES
        ('Aceite 10W40', 'Lubricantes', 'Aceite sintético para motor'),
        ('Filtro de aceite', 'Filtros', 'Filtro de aceite estándar'),
        ('Líquido de frenos', 'Fluidos', 'DOT 4')",
        
        "INSERT INTO Insumo (ID_TipoInsumo, ID_Administrador, Cantidad, Costo_Unitario, Fecha_Ingreso) VALUES
        (1, 1, 50, 15.00, '2023-01-10'),
        (2, 1, 30, 8.50, '2023-01-10'),
        (3, 1, 20, 12.00, '2023-01-15')",
        
        "INSERT INTO ServicioInsumo (ID_Servicio, ID_Insumo, Cantidad_Utilizada) VALUES
        (1, 1, 1),
        (1, 2, 1),
        (2, 3, 0.5)"
    );
    
    foreach ($sql_datos as $sql) {
        if (!$conn->query($sql)) {
            echo "Error insertando datos: " . $conn->error;
        }
    }
}

// Inicializar variables
$mensaje = "";
$tipo_mensaje = "";
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$seccion_activa = isset($_GET['section']) ? $_GET['section'] : 'inicio';

// Procesar formularios
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Procesar cliente
    if (isset($_POST['cliente-nombre'])) {
        $nombre = $conn->real_escape_string($_POST['cliente-nombre']);
        $apellido = $conn->real_escape_string($_POST['cliente-apellido']);
        $dni = $conn->real_escape_string($_POST['cliente-dni']);
        $telefono = $conn->real_escape_string($_POST['cliente-telefono'] ?? '');
        $direccion = $conn->real_escape_string($_POST['cliente-direccion'] ?? '');
        
        if (isset($_POST['cliente-id']) && !empty($_POST['cliente-id'])) {
            $cliente_id = intval($_POST['cliente-id']);
            $sql = "UPDATE Cliente SET Nombre='$nombre', Apellido='$apellido', DNI='$dni', Telefono='$telefono', Direccion='$direccion' WHERE ID_Cliente=$cliente_id";
        } else {
            $sql = "INSERT INTO Cliente (Nombre, Apellido, DNI, Telefono, Direccion) VALUES ('$nombre', '$apellido', '$dni', '$telefono', '$direccion')";
        }
        
        if ($conn->query($sql)) {
            $mensaje = isset($_POST['cliente-id']) ? "Cliente actualizado correctamente." : "Cliente agregado correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error: " . $conn->error;
            $tipo_mensaje = "danger";
        }
    }
    
    // Procesar vehículo
    if (isset($_POST['vehiculo-matricula'])) {
        $cliente_id = intval($_POST['vehiculo-cliente']);
        $matricula = $conn->real_escape_string($_POST['vehiculo-matricula']);
        $marca = $conn->real_escape_string($_POST['vehiculo-marca']);
        $modelo = $conn->real_escape_string($_POST['vehiculo-modelo']);
        $ano = intval($_POST['vehiculo-ano'] ?? 0);
        $fecha_compra = $conn->real_escape_string($_POST['vehiculo-fecha-compra'] ?? '');
        $fecha_fin_garantia = $conn->real_escape_string($_POST['vehiculo-fecha-fin-garantia'] ?? '');
        
        // Calcular estado de garantía
        $estado_garantia = 'No aplica';
        if (!empty($fecha_fin_garantia)) {
            $hoy = date('Y-m-d');
            $estado_garantia = ($fecha_fin_garantia >= $hoy) ? 'Activa' : 'Expirada';
        }
        
        if (isset($_POST['vehiculo-id']) && !empty($_POST['vehiculo-id'])) {
            $vehiculo_id = intval($_POST['vehiculo-id']);
            $sql = "UPDATE Vehiculo SET ID_Cliente=$cliente_id, Matricula='$matricula', Marca='$marca', Modelo='$modelo', Ano=$ano, Fecha_Compra='$fecha_compra', Fecha_Fin_Garantia='$fecha_fin_garantia', Estado_Garantia='$estado_garantia' WHERE ID_Vehiculo=$vehiculo_id";
        } else {
            $sql = "INSERT INTO Vehiculo (ID_Cliente, Matricula, Marca, Modelo, Ano, Fecha_Compra, Fecha_Fin_Garantia, Estado_Garantia) VALUES ($cliente_id, '$matricula', '$marca', '$modelo', $ano, '$fecha_compra', '$fecha_fin_garantia', '$estado_garantia')";
        }
        
        if ($conn->query($sql)) {
            $mensaje = isset($_POST['vehiculo-id']) ? "Vehículo actualizado correctamente." : "Vehículo agregado correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error: " . $conn->error;
            $tipo_mensaje = "danger";
        }
    }
    
    // Procesar servicio
    if (isset($_POST['servicio-vehiculo'])) {
        $vehiculo_id = intval($_POST['servicio-vehiculo']);
        $taller_id = intval($_POST['servicio-taller']);
        $tipo_servicio = intval($_POST['servicio-tipo']);
        $descripcion = $conn->real_escape_string($_POST['servicio-descripcion'] ?? '');
        $fecha = $conn->real_escape_string($_POST['servicio-fecha']);
        $costo = floatval($_POST['servicio-costo'] ?? 0);
        $garantia_cubierta = isset($_POST['servicio-garantia']) ? 1 : 0;
        $estado = $conn->real_escape_string($_POST['servicio-estado'] ?? 'Pendiente');
        
        if (isset($_POST['servicio-id']) && !empty($_POST['servicio-id'])) {
            $servicio_id = intval($_POST['servicio-id']);
            $sql = "UPDATE ServicioRealizado SET ID_Vehiculo=$vehiculo_id, ID_Taller=$taller_id, ID_Tipo=$tipo_servicio, Descripcion='$descripcion', Fecha='$fecha', Costo=$costo, Garantia_Cubierta=$garantia_cubierta, Estado='$estado' WHERE ID_Servicio=$servicio_id";
        } else {
            $sql = "INSERT INTO ServicioRealizado (ID_Vehiculo, ID_Taller, ID_Tipo, Descripcion, Fecha, Costo, Garantia_Cubierta, Estado) VALUES ($vehiculo_id, $taller_id, $tipo_servicio, '$descripcion', '$fecha', $costo, $garantia_cubierta, '$estado')";
        }
        
        if ($conn->query($sql)) {
            $mensaje = isset($_POST['servicio-id']) ? "Servicio actualizado correctamente." : "Servicio agregado correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error: " . $conn->error;
            $tipo_mensaje = "danger";
        }
    }
}

// Procesar eliminaciones
if ($accion == 'eliminar' && $id > 0 && !empty($tipo)) {
    $tabla = "";
    switch($tipo) {
        case 'cliente': $tabla = "Cliente"; $id_field = "ID_Cliente"; break;
        case 'vehiculo': $tabla = "Vehiculo"; $id_field = "ID_Vehiculo"; break;
        case 'servicio': $tabla = "ServicioRealizado"; $id_field = "ID_Servicio"; break;
    }
    
    if (!empty($tabla)) {
        $sql = "DELETE FROM $tabla WHERE $id_field=$id";
        if ($conn->query($sql)) {
            $mensaje = "Registro eliminado correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al eliminar: " . $conn->error;
            $tipo_mensaje = "danger";
        }
    }
}

// Obtener datos para formularios
$clientes = $conn->query("SELECT * FROM Cliente ORDER BY Nombre, Apellido");
$vehiculos = $conn->query("SELECT v.*, CONCAT(c.Nombre, ' ', c.Apellido) as Cliente FROM Vehiculo v JOIN Cliente c ON v.ID_Cliente = c.ID_Cliente ORDER BY v.Marca, v.Modelo");
$talleres = $conn->query("SELECT * FROM Taller ORDER BY Nombre_Taller");
$tipos_servicio = $conn->query("SELECT * FROM TipoServicio ORDER BY Nombre");
$servicios = $conn->query("SELECT s.*, v.Matricula, v.Marca, v.Modelo, t.Nombre_Taller, ts.Nombre as Tipo_Servicio 
                          FROM ServicioRealizado s 
                          JOIN Vehiculo v ON s.ID_Vehiculo = v.ID_Vehiculo 
                          JOIN Taller t ON s.ID_Taller = t.ID_Taller 
                          JOIN TipoServicio ts ON s.ID_Tipo = ts.ID_Tipo 
                          ORDER BY s.Fecha DESC");

// Obtener datos para edición
$cliente_editar = null;
$vehiculo_editar = null;
$servicio_editar = null;

if ($accion == 'editar' && $id > 0) {
    switch($tipo) {
        case 'cliente':
            $result = $conn->query("SELECT * FROM Cliente WHERE ID_Cliente=$id");
            if ($result->num_rows > 0) $cliente_editar = $result->fetch_assoc();
            break;
        case 'vehiculo':
            $result = $conn->query("SELECT * FROM Vehiculo WHERE ID_Vehiculo=$id");
            if ($result->num_rows > 0) $vehiculo_editar = $result->fetch_assoc();
            break;
        case 'servicio':
            $result = $conn->query("SELECT * FROM ServicioRealizado WHERE ID_Servicio=$id");
            if ($result->num_rows > 0) $servicio_editar = $result->fetch_assoc();
            break;
    }
}

// Cerrar conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión - Taller Mecánico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #d32f2f;
            --secondary-color: #f5f5f5;
            --accent-color: #ffc107;
            --dark-color: #333;
            --light-color: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
        }
        
        .header {
            background: linear-gradient(to right, #2c3e50, #4a6580);
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        
        .logo i {
            color: var(--accent-color);
            margin-right: 10px;
            font-size: 28px;
        }
        
        .sidebar {
            min-height: calc(100vh - 80px);
            background-color: #2c3e50;
            color: white;
            padding-top: 20px;
        }
        
        .sidebar .nav-link {
            color: #b8c7ce;
            padding: 12px 20px;
            border-radius: 0;
            margin-bottom: 5px;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: #3c8dbc;
            color: white;
            border-left: 4px solid var(--accent-color);
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .main-content {
            background-color: white;
            min-height: calc(100vh - 80px);
            padding: 20px;
        }
        
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: none;
            border-radius: 5px;
        }
        
        .card-header {
            background: linear-gradient(to right, #4a6580, #2c3e50);
            color: white;
            font-weight: bold;
            border-bottom: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #b71c1c;
            border-color: #b71c1c;
        }
        
        .btn-warning {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: var(--dark-color);
        }
        
        .stats-card {
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-card .card-body {
            padding: 25px 15px;
        }
        
        .stats-card .card-text {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .table thead th {
            background-color: #4a6580;
            color: white;
        }
        
        .badge-success {
            background-color: #28a745;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }
        
        .badge-secondary {
            background-color: #6c757d;
        }
        
        .hero-section {
            background: url('https://images.unsplash.com/photo-1493238792000-8113da705763?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center;
            background-size: cover;
            padding: 60px 0;
            margin-bottom: 30px;
            border-radius: 5px;
            color: white;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.7);
        }
        
        .hero-content {
            background-color: rgba(44, 62, 80, 0.85);
            padding: 30px;
            border-radius: 5px;
        }
        
        .footer {
            background-color: var(--dark-color);
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-top: 30px;
        }
        
        .feature-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .service-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: var(--accent-color);
            color: var(--dark-color);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="logo">
                        <i class="bi bi-tools"></i>
                        <span>Taller Mecánico AM Sport</span>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <span class="me-3"><i class="bi bi-telephone"></i> (555) 123-4567</span>
                    <span><i class="bi bi-envelope"></i> info@talleramsport.com</span>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <nav class="nav flex-column">
                    <a class="nav-link <?= $seccion_activa == 'inicio' ? 'active' : '' ?>" href="?section=inicio">
                        <i class="bi bi-house-door"></i> Inicio
                    </a>
                    <a class="nav-link <?= $seccion_activa == 'clientes' ? 'active' : '' ?>" href="?section=clientes">
                        <i class="bi bi-people"></i> Clientes
                    </a>
                    <a class="nav-link <?= $seccion_activa == 'vehiculos' ? 'active' : '' ?>" href="?section=vehiculos">
                        <i class="bi bi-car-front"></i> Vehículos
                    </a>
                    <a class="nav-link <?= $seccion_activa == 'servicios' ? 'active' : '' ?>" href="?section=servicios">
                        <i class="bi bi-tools"></i> Servicios
                    </a>
                    <a class="nav-link <?= $seccion_activa == 'talleres' ? 'active' : '' ?>" href="?section=talleres">
                        <i class="bi bi-building"></i> Talleres
                    </a>
                    <a class="nav-link" href="#">
                        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                    </a>
                </nav>
            </div>

            <!-- Main content -->
            <div class="col-md-9 col-lg-10 main-content">
                <h2 class="mb-4">Sistema de Gestión de Taller Mecánico</h2>
                
                <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
                    <?= $mensaje ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <!-- Sección de Inicio -->
                <?php if ($seccion_activa == 'inicio'): ?>
                <div class="hero-section">
                    <div class="hero-content">
                        <h1>Bienvenido al Sistema de Gestión</h1>
                        <p class="lead">Gestiona clientes, vehículos y servicios de tu taller mecánico de forma eficiente</p>
                        <div class="row mt-4">
                            <div class="col-md-4 text-center">
                                <div class="feature-icon">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <h4>Gestión de Clientes</h4>
                                <p>Administra la información de tus clientes de manera centralizada</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="feature-icon">
                                    <i class="bi bi-car-front-fill"></i>
                                </div>
                                <h4>Control de Vehículos</h4>
                                <p>Registra y haz seguimiento a los vehículos de tus clientes</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="feature-icon">
                                    <i class="bi bi-tools"></i>
                                </div>
                                <h4>Servicios Realizados</h4>
                                <p>Lleva el control de todos los servicios realizados en el taller</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <h5 class="card-title">Clientes</h5>
                                <p class="card-text"><?= $clientes->num_rows ?></p>
                                <a href="?section=clientes" class="btn btn-primary">Gestionar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <h5 class="card-title">Vehículos</h5>
                                <p class="card-text"><?= $vehiculos->num_rows ?></p>
                                <a href="?section=vehiculos" class="btn btn-primary">Gestionar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <h5 class="card-title">Servicios</h5>
                                <p class="card-text"><?= $servicios->num_rows ?></p>
                                <a href="?section=servicios" class="btn btn-primary">Gestionar</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Servicios Recientes</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Vehículo</th>
                                                <th>Taller</th>
                                                <th>Tipo</th>
                                                <th>Costo</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $servicios_recientes = $servicios;
                                            if ($servicios_recientes->num_rows > 0):
                                                $count = 0;
                                                while($servicio = $servicios_recientes->fetch_assoc()):
                                                    if ($count >= 5) break;
                                            ?>
                                            <tr>
                                                <td><?= date('d/m/Y', strtotime($servicio['Fecha'])) ?></td>
                                                <td><?= $servicio['Marca'] ?> <?= $servicio['Modelo'] ?> (<?= $servicio['Matricula'] ?>)</td>
                                                <td><?= $servicio['Nombre_Taller'] ?></td>
                                                <td><?= $servicio['Tipo_Servicio'] ?></td>
                                                <td>$<?= number_format($servicio['Costo'], 2) ?></td>
                                                <td><span class="badge bg-<?= 
                                                    $servicio['Estado'] == 'Completado' ? 'success' : 
                                                    ($servicio['Estado'] == 'En Proceso' ? 'warning' : 'secondary') 
                                                ?>"><?= $servicio['Estado'] ?></span></td>
                                            </tr>
                                            <?php 
                                                    $count++;
                                                endwhile;
                                            else:
                                            ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No hay servicios registrados</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Sección de Clientes -->
                <?php if ($seccion_activa == 'clientes'): ?>
                <div class="row">
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header">
                                <h5><?= $cliente_editar ? 'Editar' : 'Agregar' ?> Cliente</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <?php if ($cliente_editar): ?>
                                    <input type="hidden" name="cliente-id" value="<?= $cliente_editar['ID_Cliente'] ?>">
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <label for="cliente-nombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="cliente-nombre" name="cliente-nombre" 
                                            value="<?= $cliente_editar ? $cliente_editar['Nombre'] : '' ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cliente-apellido" class="form-label">Apellido</label>
                                        <input type="text" class="form-control" id="cliente-apellido" name="cliente-apellido" 
                                            value="<?= $cliente_editar ? $cliente_editar['Apellido'] : '' ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cliente-dni" class="form-label">DNI</label>
                                        <input type="text" class="form-control" id="cliente-dni" name="cliente-dni" 
                                            value="<?= $cliente_editar ? $cliente_editar['DNI'] : '' ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cliente-telefono" class="form-label">Teléfono</label>
                                        <input type="text" class="form-control" id="cliente-telefono" name="cliente-telefono" 
                                            value="<?= $cliente_editar ? $cliente_editar['Telefono'] : '' ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="cliente-direccion" class="form-label">Dirección</label>
                                        <textarea class="form-control" id="cliente-direccion" name="cliente-direccion" rows="2"><?= $cliente_editar ? $cliente_editar['Direccion'] : '' ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary"><?= $cliente_editar ? 'Actualizar' : 'Agregar' ?> Cliente</button>
                                    <?php if ($cliente_editar): ?>
                                    <a href="?section=clientes" class="btn btn-secondary">Cancelar</a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header">
                                <h5>Lista de Clientes</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Apellido</th>
                                                <th>DNI</th>
                                                <th>Teléfono</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if ($clientes->num_rows > 0):
                                                while($cliente = $clientes->fetch_assoc()):
                                            ?>
                                            <tr>
                                                <td><?= $cliente['Nombre'] ?></td>
                                                <td><?= $cliente['Apellido'] ?></td>
                                                <td><?= $cliente['DNI'] ?></td>
                                                <td><?= $cliente['Telefono'] ?></td>
                                                <td>
                                                    <a href="?section=clientes&accion=editar&tipo=cliente&id=<?= $cliente['ID_Cliente'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                                    <a href="?section=clientes&accion=eliminar&tipo=cliente&id=<?= $cliente['ID_Cliente'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este cliente?')">Eliminar</a>
                                                </td>
                                            </tr>
                                            <?php 
                                                endwhile;
                                            else:
                                            ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No hay clientes registrados</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Sección de Vehículos -->
                <?php if ($seccion_activa == 'vehiculos'): ?>
                <div class="row">
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header">
                                <h5><?= $vehiculo_editar ? 'Editar' : 'Agregar' ?> Vehículo</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <?php if ($vehiculo_editar): ?>
                                    <input type="hidden" name="vehiculo-id" value="<?= $vehiculo_editar['ID_Vehiculo'] ?>">
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <label for="vehiculo-cliente" class="form-label">Cliente</label>
                                        <select class="form-select" id="vehiculo-cliente" name="vehiculo-cliente" required>
                                            <option value="">Seleccionar cliente</option>
                                            <?php 
                                            $clientes_lista = $clientes;
                                            if ($clientes_lista->num_rows > 0):
                                                while($cliente = $clientes_lista->fetch_assoc()):
                                            ?>
                                            <option value="<?= $cliente['ID_Cliente'] ?>" <?= ($vehiculo_editar && $vehiculo_editar['ID_Cliente'] == $cliente['ID_Cliente']) ? 'selected' : '' ?>>
                                                <?= $cliente['Nombre'] ?> <?= $cliente['Apellido'] ?>
                                            </option>
                                            <?php 
                                                endwhile;
                                            endif;
                                            ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="vehiculo-matricula" class="form-label">Matrícula</label>
                                        <input type="text" class="form-control" id="vehiculo-matricula" name="vehiculo-matricula" 
                                            value="<?= $vehiculo_editar ? $vehiculo_editar['Matricula'] : '' ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="vehiculo-marca" class="form-label">Marca</label>
                                        <input type="text" class="form-control" id="vehiculo-marca" name="vehiculo-marca" 
                                            value="<?= $vehiculo_editar ? $vehiculo_editar['Marca'] : '' ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="vehiculo-modelo" class="form-label">Modelo</label>
                                        <input type="text" class="form-control" id="vehiculo-modelo" name="vehiculo-modelo" 
                                            value="<?= $vehiculo_editar ? $vehiculo_editar['Modelo'] : '' ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="vehiculo-ano" class="form-label">Año</label>
                                        <input type="number" class="form-control" id="vehiculo-ano" name="vehiculo-ano" 
                                            value="<?= $vehiculo_editar ? $vehiculo_editar['Ano'] : '' ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="vehiculo-fecha-compra" class="form-label">Fecha de Compra</label>
                                        <input type="date" class="form-control" id="vehiculo-fecha-compra" name="vehiculo-fecha-compra" 
                                            value="<?= $vehiculo_editar ? $vehiculo_editar['Fecha_Compra'] : '' ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="vehiculo-fecha-fin-garantia" class="form-label">Fecha Fin de Garantía</label>
                                        <input type="date" class="form-control" id="vehiculo-fecha-fin-garantia" name="vehiculo-fecha-fin-garantia" 
                                            value="<?= $vehiculo_editar ? $vehiculo_editar['Fecha_Fin_Garantia'] : '' ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary"><?= $vehiculo_editar ? 'Actualizar' : 'Agregar' ?> Vehículo</button>
                                    <?php if ($vehiculo_editar): ?>
                                    <a href="?section=vehiculos" class="btn btn-secondary">Cancelar</a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header">
                                <h5>Lista de Vehículos</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Matrícula</th>
                                                <th>Marca</th>
                                                <th>Modelo</th>
                                                <th>Cliente</th>
                                                <th>Garantía</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $vehiculos_lista = $vehiculos;
                                            if ($vehiculos_lista->num_rows > 0):
                                                while($vehiculo = $vehiculos_lista->fetch_assoc()):
                                            ?>
                                            <tr>
                                                <td><?= $vehiculo['Matricula'] ?></td>
                                                <td><?= $vehiculo['Marca'] ?></td>
                                                <td><?= $vehiculo['Modelo'] ?></td>
                                                <td><?= $vehiculo['Cliente'] ?></td>
                                                <td><span class="badge bg-<?= 
                                                    $vehiculo['Estado_Garantia'] == 'Activa' ? 'success' : 
                                                    ($vehiculo['Estado_Garantia'] == 'Expirada' ? 'warning' : 'secondary') 
                                                ?>"><?= $vehiculo['Estado_Garantia'] ?></span></td>
                                                <td>
                                                    <a href="?section=vehiculos&accion=editar&tipo=vehiculo&id=<?= $vehiculo['ID_Vehiculo'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                                    <a href="?section=vehiculos&accion=eliminar&tipo=vehiculo&id=<?= $vehiculo['ID_Vehiculo'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este vehículo?')">Eliminar</a>
                                                </td>
                                            </tr>
                                            <?php 
                                                endwhile;
                                            else:
                                            ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No hay vehículos registrados</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Sección de Servicios -->
                <?php if ($seccion_activa == 'servicios'): ?>
                <div class="row">
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header">
                                <h5><?= $servicio_editar ? 'Editar' : 'Agregar' ?> Servicio</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <?php if ($servicio_editar): ?>
                                    <input type="hidden" name="servicio-id" value="<?= $servicio_editar['ID_Servicio'] ?>">
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <label for="servicio-vehiculo" class="form-label">Vehículo</label>
                                        <select class="form-select" id="servicio-vehiculo" name="servicio-vehiculo" required>
                                            <option value="">Seleccionar vehículo</option>
                                            <?php 
                                            $vehiculos_lista = $vehiculos;
                                            if ($vehiculos_lista->num_rows > 0):            
                                                while($vehiculo = $vehiculos_lista->fetch_assoc()):
                                            ?>
                                            <option value="<?= $vehiculo['ID_Vehiculo'] ?>" <?= ($servicio_editar && $servicio_editar['ID_Vehiculo'] == $vehiculo['ID   _Vehiculo']) ? 'selected' : '' ?>>
                                                <?= $vehiculo['Marca'] ?> <?= $vehiculo['Modelo'] ?> (<?= $vehiculo['Matricula'] ?>)
                                            </option>
                                                                                <?php 
                                                                                    endwhile;               
                                                                                endif;
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="servicio-taller" class="form-label">Taller</label>
                                                                            <select class="form-select" id="servicio-taller" name="servicio-taller" required>
                                                                                <option value="">Seleccionar taller</option>
                                                                                <?php 
                                                                                $talleres_lista = $talleres;
                                                                                if ($talleres_lista->num_rows > 0):
                                                                                    while($taller = $talleres_lista->fetch_assoc()):
                                                                                ?>
                                                                                <option value="<?= $taller['ID_Taller'] ?>" <?= ($servicio_editar && $servicio_editar['ID_Taller'] == $taller['ID_Taller']) ? 'selected' : '' ?>>
                                                                                    <?= $taller['Nombre_Taller'] ?>
                                                                                </option>
                                                                                <?php 
                                                                                    endwhile;
                                                                                endif;
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="servicio-tipo" class="form-label">Tipo de Servicio</label>
                                                                            <select class="form-select" id="servicio-tipo" name="servicio-tipo" required>
                                                                                <option value="">Seleccionar tipo</option>
                                                                                <?php 
                                                                                $tipos_servicio_lista = $tipos_servicio;
                                                                                if ($tipos_servicio_lista->num_rows > 0):
                                                                                    while($tipo_serv = $tipos_servicio_lista->fetch_assoc()):
                                                                                ?>
                                                                                <option value="<?= $tipo_serv['ID_Tipo'] ?>" <?= ($servicio_editar && $servicio_editar['ID_Tipo'] == $tipo_serv['ID_Tipo']) ? 'selected' : '' ?>>
                                                                                    <?= $tipo_serv['Nombre'] ?>
                                                                                </option>
                                                                                <?php 
                                                                                    endwhile;
                                                                                endif;
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="servicio-descripcion" class="form-label">Descripción</label>
                                                                            <textarea class="form-control" id="servicio-descripcion" name="servicio-descripcion" rows="2"><?= $servicio_editar ? $servicio_editar['Descripcion'] : '' ?></textarea>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="servicio-fecha" class="form-label">Fecha</label>
                                                                            <input type="date" class="form-control" id="servicio-fecha" name="servicio-fecha" value="<?= $servicio_editar ? $servicio_editar['Fecha'] : '' ?>" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="servicio-costo" class="form-label">Costo</label>
                                                                            <input type="number" step="0.01" class="form-control" id="servicio-costo" name="servicio-costo" value="<?= $servicio_editar ? $servicio_editar['Costo'] : '' ?>">
                                                                        </div>
                                                                        <div class="mb-3 form-check">
                                                                            <input type="checkbox" class="form-check-input" id="servicio-garantia" name="servicio-garantia" <?= ($servicio_editar && $servicio_editar['Garantia_Cubierta']) ? 'checked' : '' ?>>
                                                                            <label class="form-check-label" for="servicio-garantia">Garantía cubierta</label>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="servicio-estado" class="form-label">Estado</label>
                                                                            <select class="form-select" id="servicio-estado" name="servicio-estado">
                                                                                <option value="Pendiente" <?= ($servicio_editar && $servicio_editar['Estado'] == 'Pendiente') ? 'selected' : '' ?>>Pendiente</option>
                                                                                <option value="En Proceso" <?= ($servicio_editar && $servicio_editar['Estado'] == 'En Proceso') ? 'selected' : '' ?>>En Proceso</option>
                                                                                <option value="Completado" <?= ($servicio_editar && $servicio_editar['Estado'] == 'Completado') ? 'selected' : '' ?>>Completado</option>
                                                                            </select>
                                                                        </div>
                                                                        <button type="submit" class="btn btn-primary"><?= $servicio_editar ? 'Actualizar' : 'Agregar' ?> Servicio</button>
                                                                        <?php if ($servicio_editar): ?>
                                                                        <a href="?section=servicios" class="btn btn-secondary">Cancelar</a>
                                                                        <?php endif; ?>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5>Lista de Servicios</h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="table-responsive">
                                                                        <table class="table table-striped table-hover">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Fecha</th>
                                                                                    <th>Vehículo</th>
                                                                                    <th>Taller</th>
                                                                                    <th>Tipo</th>
                                                                                    <th>Costo</th>
                                                                                    <th>Estado</th>
                                                                                    <th>Acciones</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php 
                                                                                $servicios_lista = $conn->query("SELECT s.*, v.Matricula, v.Marca, v.Modelo, t.Nombre_Taller, ts.Nombre as Tipo_Servicio 
                                                                                    FROM ServicioRealizado s 
                                                                                    JOIN Vehiculo v ON s.ID_Vehiculo = v.ID_Vehiculo 
                                                                                    JOIN Taller t ON s.ID_Taller = t.ID_Taller 
                                                                                    JOIN TipoServicio ts ON s.ID_Tipo = ts.ID_Tipo 
                                                                                    ORDER BY s.Fecha DESC");
                                                                                if ($servicios_lista && $servicios_lista->num_rows > 0):
                                                                                    while($servicio = $servicios_lista->fetch_assoc()):
                                                                                ?>
                                                                                <tr>
                                                                                    <td><?= date('d/m/Y', strtotime($servicio['Fecha'])) ?></td>
                                                                                    <td><?= $servicio['Marca'] ?> <?= $servicio['Modelo'] ?> (<?= $servicio['Matricula'] ?>)</td>
                                                                                    <td><?= $servicio['Nombre_Taller'] ?></td>
                                                                                    <td><?= $servicio['Tipo_Servicio'] ?></td>
                                                                                    <td>$<?= number_format($servicio['Costo'], 2) ?></td>
                                                                                    <td><span class="badge bg-<?= 
                                                                                        $servicio['Estado'] == 'Completado' ? 'success' : 
                                                                                        ($servicio['Estado'] == 'En Proceso' ? 'warning' : 'secondary') 
                                                                                    ?>"><?= $servicio['Estado'] ?></span></td>
                                                                                    <td>
                                                                                        <a href="?section=servicios&accion=editar&tipo=servicio&id=<?= $servicio['ID_Servicio'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                                                                        <a href="?section=servicios&accion=eliminar&tipo=servicio&id=<?= $servicio['ID_Servicio'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este servicio?')">Eliminar</a>
                                                                                    </td>
                                                                                </tr>
                                                                                <?php 
                                                                                    endwhile;
                                                                                else:
                                                                                ?>
                                                                                <tr>
                                                                                    <td colspan="7" class="text-center">No hay servicios registrados</td>
                                                                                </tr>
                                                                                <?php endif; ?>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    <!-- Sección de Talleres -->
                                                    <?php if ($seccion_activa == 'talleres'): ?>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5>Lista de Talleres</h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="table-responsive">
                                                                        <table class="table table-striped table-hover">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Nombre</th>
                                                                                    <th>Dirección</th>
                                                                                    <th>Teléfono</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php 
                                                                                $talleres_lista = $conn->query("SELECT * FROM Taller ORDER BY Nombre_Taller");
                                                                                if ($talleres_lista && $talleres_lista->num_rows > 0):
                                                                                    while($taller = $talleres_lista->fetch_assoc()):
                                                                                ?>
                                                                                <tr>
                                                                                    <td><?= $taller['Nombre_Taller'] ?></td>
                                                                                    <td><?= $taller['Direccion_Taller'] ?></td>
                                                                                    <td><?= $taller['Telefono_Taller'] ?></td>
                                                                                </tr>
                                                                                <?php 
                                                                                    endwhile;
                                                                                else:
                                                                                ?>
                                                                                <tr>
                                                                                    <td colspan="3" class="text-center">No hay talleres registrados</td>
                                                                                </tr>
                                                                                <?php endif; ?>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <footer class="footer">
                                            <div class="container">
                                                <span>&copy; <?= date('Y') ?> Taller Mecánico AM Sport. Todos los derechos reservados.</span>
                                            </div>
                                        </footer>
                                        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
                                    </body>
                                    </html>