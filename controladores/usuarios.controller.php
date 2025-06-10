<?php

require_once('modelos/usuarios.modelo.php');
require_once('modelos/bajas.modelo.php');
require_once('archivos.controller.php');

class ControladorUsuarios
{
    /* GUARDAR USUARIOS */
    static public function crtGuardarUsuario()
    {
        Auth::check('usuarios', 'crtGuardarUsuario');
        if (isset($_POST["nombre"])) {
            try {
                $conexion = Conexion::conectar();

                // Iniciar transacción si no hay una activa
                if (!$conexion->inTransaction()) {
                    $conexion->beginTransaction();
                }

                $tabla = "usuarios";

                $nombre   = trim($_POST["nombre"]);
                $apellido = trim($_POST["apellido"]);
                $dni      = trim($_POST["dni"]);
                $pass     = password_hash($_POST["pass"], PASSWORD_DEFAULT);
                $f_nac    = $_POST["f_nac"];
                $telefono = $_POST["telefono"];
                $tel_emergencia  = $_POST["tel_emergencia"];
                $nombre_contacto = $_POST["nombre_contacto"];
                $parentesco      = $_POST["parentesco"];
                $domicilio       = $_POST["domicilio"];
                $provincia       = $_POST["provincia"];
                $rol        = $_POST["rol"];

                // Normalizar nombre de archivo (sin espacios)
                $nombreArchivo = preg_replace('/\s+/', '', $nombre . $apellido);

                // --- Aquí llamamos a ControladorArchivos::guardarArchivo() ---

                $imgPerfil = ControladorArchivos::guardarArchivo(
                    $_FILES["imgPerfil"],
                    "img/perfil/",
                    $nombreArchivo . "Perfil"
                );

                $imgRepriv = ControladorArchivos::guardarArchivo(
                    $_FILES["imgRepriv"],
                    "img/repriv/",
                    $nombreArchivo . "Repriv"
                );
                // ------------------------------------------------------------

                // --- BLOQUE NUEVO: Validación opcional de imgRepriv ---
                if (isset($_FILES['imgRepriv']) && $_FILES['imgRepriv']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $tmp  = $_FILES['imgRepriv']['tmp_name'];
                    $name = uniqid() . '_' . basename($_FILES['imgRepriv']['name']);
                    $dest = __DIR__ . '/../uploads/docs/' . $name;

                    if (move_uploaded_file($tmp, $dest)) {
                        // Actualizar sólo el campo imgRepriv en la BD
                        $sql = "UPDATE usuarios SET imgRepriv = ? WHERE idUsuario = ?";
                        // Si es un INSERT recién hecho, usamos lastInsertId(); si es UPDATE, reemplazar por el ID que corresponda
                        $newId = $conexion->lastInsertId();
                        $stmt = $conexion->prepare("UPDATE usuarios SET imgRepriv = ? WHERE idUsuario = ?");
                        $stmt->execute([$dest, $newId]);
                    } else {
                        $_SESSION['error_message'] = "No se pudo guardar el documento.";
                    }
                }
                // -------------------------------------------------------

                $datos = array(
                    "nombre"         => $nombre,
                    "apellido"       => $apellido,
                    "dni"            => $dni,
                    "pass"           => $pass,
                    "f_nac"          => $f_nac,
                    "telefono"       => $telefono,
                    "tel_emergencia" => $tel_emergencia,
                    "nombre_contacto" => $nombre_contacto,
                    "parentesco"     => $parentesco,
                    "domicilio"      => $domicilio,
                    "provincia"      => $provincia,
                    "rol"            => $rol,
                    "imgPerfil"      => $imgPerfil,   // puede ser null si no subieron nada
                    "imgRepriv"      => $imgRepriv,   // idem
                    "resetPass"      => 1,
                    "activo"         => 1
                );

                $respuesta = ModeloUsuarios::mdlGuardarUsuario($tabla, $datos);

                if ($respuesta === "ok") {
                    $conexion->commit();
                    $_SESSION['success_message'] = "Usuario registrado correctamente.";
                } else {
                    // Si el modelo devolvió “error” u otro string, hacemos rollback
                    throw new Exception("Error al guardar en la base de datos.");
                }
            } catch (Exception $e) {
                // Rollback y mostrar mensaje
                if ($conexion->inTransaction()) {
                    $conexion->rollBack();
                }
                $_SESSION['error_message'] = $e->getMessage();
                return false;
            }
        }
    }

    /* MODIFICAR USUARIOS */
    static public function crtModificarUsuario()
    {
        Auth::check('usuarios', 'crtModificarUsuario');
        if (isset($_POST["idUsuario"])) {
            try {
                $conexion = Conexion::conectar();

                if (!$conexion->inTransaction()) {
                    $conexion->beginTransaction();
                }

                $tabla      = "usuarios";
                $id_usuario = $_POST["idUsuario"];
                $nombre     = trim($_POST["nombre"]);
                $apellido   = trim($_POST["apellido"]);
                $dni        = trim($_POST["dni"]);
                $f_nac      = $_POST["f_nac"];
                $telefono   = $_POST["telefono"];
                $tel_emergencia  = $_POST["tel_emergencia"];
                $nombre_contacto = $_POST["nombre_contacto"];
                $parentesco      = $_POST["parentesco"];
                $domicilio       = $_POST["domicilio"];
                $provincia       = $_POST["provincia"];
                $rol             = $_POST["rol"];
                $resetPass = isset($_POST["resetPass"]) ? 0 : 1; // 0: NO restaurar, 1: sí
                $activo    = isset($_POST["activo"])    ? 0 : 1; // 0: inactivo, 1: activo

                // Nombre base para los archivos nuevos (sin espacios)
                $nombreArchivo = preg_replace('/\s+/', '', $nombre . $apellido);

                // Si suben nueva imgPerfil, la guardamos; si no, tomamos la ruta actual que venga oculta en el form
                $imgPerfil = !empty($_FILES["imgPerfil"]["name"])
                    ? ControladorArchivos::guardarArchivo(
                        $_FILES["imgPerfil"],
                        "img/perfil/",
                        $nombreArchivo . "Perfil"
                    )
                    : $_POST["imgPerfilActual"];

                // Lo mismo para imgRepriv
                $imgRepriv = !empty($_FILES["imgRepriv"]["name"])
                    ? ControladorArchivos::guardarArchivo(
                        $_FILES["imgRepriv"],
                        "img/repriv/",
                        $nombreArchivo . "Repriv"
                    )
                    : $_POST["imgReprivActual"];

                $datos = array(
                    "id_usuario"     => $id_usuario,
                    "nombre"         => $nombre,
                    "apellido"       => $apellido,
                    "dni"            => $dni,
                    "f_nac"          => $f_nac,
                    "telefono"       => $telefono,
                    "tel_emergencia" => $tel_emergencia,
                    "nombre_contacto" => $nombre_contacto,
                    "parentesco"     => $parentesco,
                    "domicilio"      => $domicilio,
                    "provincia"      => $provincia,
                    "rol"            => $rol,
                    "imgPerfil"      => $imgPerfil,
                    "imgRepriv"      => $imgRepriv,
                    "resetPass"      => $resetPass,
                    "activo"         => $activo
                );

                $respuesta = ModeloUsuarios::mdlModificarUsuario($tabla, $datos);

                $activo = isset($_POST["activo"]) ? 0 : 1; // 0 = inactivo (baja)



                if ($respuesta === "ok") {
                    // 2) Si marcó “dar de baja” insertamos en bajas
                    if ($activo === 0) {
                        // validar que haya motivo
                        $motivo = trim($_POST['motivo'] ?? '');
                        if ($motivo === '') {
                            throw new Exception("Debe indicar un motivo para la baja.");
                        }
                        // preparar datos de baja
                        $datosBaja = [
                            'usuario_id'    => $datos['id_usuario'],
                            'motivo'        => $motivo,
                            'fecha'         => date('Y-m-d'),
                            'eliminado_por' => $_SESSION['idUsuario']
                        ];
                        $resBaja = ModeloBajas::mdlCrearBaja('bajas', $datosBaja);
                        if ($resBaja !== 'ok') {
                            throw new Exception("Error al registrar la baja en la base de datos.");
                        }
                    }
                    $conexion->commit();
                    $_SESSION['success_message'] = "Usuario modificado correctamente.";
                } else {
                    throw new Exception("Error al modificar en la base de datos.");
                }
            } catch (Exception $e) {
                if ($conexion->inTransaction()) {
                    $conexion->rollBack();
                }
                $_SESSION['error_message'] = $e->getMessage();
                return false;
            }
        }
    }

    /*REACTIVAR USUARIO */
    static public function crtReactivarUsuario()
    {
        Auth::check('usuarios', 'crtReactivarUsuario');
        if (isset($_POST['idReactivar'])) {
            $id = intval($_POST['idReactivar']);
            try {
                $db = Conexion::conectar();
                if (!$db->inTransaction()) {
                    $db->beginTransaction();
                }
                $res = ModeloUsuarios::mdlReactivarUsuario('usuarios', $id);
                if ($res === 'ok') {
                    $db->commit();
                    $_SESSION['success_message'] = 'Usuario reactivado.';
                } else {
                    $db->rollBack();
                    $_SESSION['success_message'] = 'No se pudo reactivar el usuario.';
                }
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
            }
        }
    }

    static public function vistaListadoUsuarios()
    {
        Auth::check('usuarios', 'vistaListadoUsuarios');
        $db = new Conexion;
        $sql = "SELECT * FROM usuarios WHERE activo = 1 ORDER BY rol ";
        $usuarios = $db->consultas($sql);
        include __DIR__ . '/../vistas/paginas/usuario/listado-usuarios.php';
        return;
    }

    static public function vistaListadoUsuariosInactivos()
    {
        Auth::check('usuarios', 'vistaListadoUsuariosInactivos');
        $db = new Conexion;
        $sql = "SELECT u.idUsuario, CONCAT(u.apellido, ' ', u.nombre) AS empleado, b.motivo, b.fecha, CONCAT(e.apellido, ' ', e.nombre) AS eliminado_por
                FROM usuarios u
                JOIN bajas b ON u.idUsuario = b.usuario_id
                JOIN usuarios e ON b.eliminado_por = e.idUsuario
                WHERE u.activo = 0
                ORDER BY empleado ";
        $usuarios = $db->consultas($sql);
        include __DIR__ . '/../vistas/paginas/usuario/listado-usuarios-inactivos.php';
        return;
    }
    static public function vistaCrearUsuario()
    {
        Auth::check('usuarios', 'vistaListadoUsuariosInactivos');
        include __DIR__ . '/../vistas/paginas/usuario/crear-usuario.php';
        return;
    }
    static public function vistaPerfilUsuario()
    {
        Auth::check('usuarios', 'vistaPerfilUsuario');
        include __DIR__ . '/../vistas/paginas/usuario/perfil-usuario.php';
        return;
    }
}
