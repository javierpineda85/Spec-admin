<?php

require_once 'modelos/uniformes.modelo.php';
require_once 'modelos/uniformeitems.modelo.php';
require_once 'modelos/uniformecategorias.modelo.php';
require_once 'modelos/uniformeentregas.modelo.php';
require_once 'modelos/uniformedevoluciones.modelo.php';

class UniformesController
{
    /* VISTA PRINCIPAL: MI UNIFORME (empleado) */
    public static function vistaMiUniforme()
    {
        Auth::check('uniformes', 'vistaMiUniforme');

        // Nivel del usuario logueado
        $nivelSesion = isset($_SESSION['nivel']) ? (float) $_SESSION['nivel'] : 1.0;

        // Si viene un ID por GET y el usuario tiene permisos, usar ese
        if (isset($_GET['id']) && in_array($nivelSesion, [3.0, 4.0, 5.0, 99.0], true)) {
            $usuario_id = (int) $_GET['id'];
        } else {
            // Caso normal: vigilador viendo su propio uniforme
            $usuario_id = $_SESSION['idUsuario'];
        }


        // Talles actuales
        $uniforme = ModeloUniformes::buscarPorUsuario($usuario_id);

        // Items administrables
        $items = ModeloUniformeItems::listar(true);

        // Categorías
        $db = new Conexion;
        $categorias = $db->consultas("SELECT * FROM uniforme_categorias ORDER BY nombre");

        // Entregas del usuario
        $entregas = ModeloUniformeEntregas::listarPorUsuario($usuario_id);

        require 'vistas/paginas/datos/mi_uniforme.php';
    }

    /* GUARDAR TALLES */
    public static function guardarUniforme()
    {
        Auth::check('uniformes', 'vistaMiUniforme');

        $usuario_id = $_POST['usuario_id'];

        $datos = [
            'usuario_id'     => $usuario_id,
            'talle_pantalon' => $_POST['talle_pantalon'],
            'talle_remera'   => $_POST['talle_remera'],
            'talle_polar'    => $_POST['talle_polar'],
            'talle_campera'  => $_POST['talle_campera'],
            'talle_calzado'  => $_POST['talle_calzado']
        ];

        $existe = ModeloUniformes::buscarPorUsuario($usuario_id);
        $ok = $existe
            ? ModeloUniformes::actualizar($datos)
            : ModeloUniformes::insertar($datos);

        if ($ok) {
            ToastifyController::success('Talles guardados correctamente');
        } else {
            ToastifyController::error('Error al guardar los talles');
        }

        header("Location: ?r=mi_uniforme&id=" . $_POST['usuario_id']);
        exit;
    }

    /* REGISTRAR ENTREGA  */
    public static function registrarEntregaMultiple()
    {
        Auth::check('uniformes', 'vistaMiUniforme');

        $entregadoPor = trim(
            ($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')
        );

        if (!isset($_POST['entregas']) || empty($_POST['entregas'])) {
            ToastifyController::error('No se recibieron ítems para registrar');
            header("Location: ?r=mi_uniforme");
            exit;
        }

        $usuario_id = $_POST['usuario_id'];
        $idsInsertados = [];
        $okGlobal = true;

        foreach ($_POST['entregas'] as $entrega) {

            // Normalizar campos opcionales
            $entrega['item_id']       = $entrega['item_id']       ?? null;
            $entrega['item_libre']    = $entrega['item_libre']    ?? null;
            $entrega['talle']         = $entrega['talle']         ?? null;
            $entrega['cantidad']      = $entrega['cantidad']      ?? 1;
            $entrega['observaciones'] = $entrega['observaciones'] ?? '';
            $entrega['entregado_por'] = $entregadoPor;

            // Agregar usuario_id
            $entrega['usuario_id'] = $usuario_id;

            // Insertar
            $ok = ModeloUniformeEntregas::insertar($entrega);

            if ($ok) {
                // Guardar ID insertado
                $idsInsertados[] = Conexion::conectar()->lastInsertId();
            } else {
                $okGlobal = false;
            }
        }

        if (!$okGlobal) {
            ToastifyController::error('Ocurrió un error al registrar algunas entregas');
            header("Location: ?r=mi_uniforme&id=" . $_POST['usuario_id']);
            exit;

        }

        // Redirigir al comprobante
        $ids = implode(',', $idsInsertados);
        header("Location: ?r=comprobante_entrega_uniforme&ids=$ids");
        exit;
    }

    /* REGISTRAR DEVOLUCIÓN */
    public static function registrarDevolucion()
    {
        Auth::check('uniformes', 'vistaMiUniforme');

        $datos = [
            'entrega_id'       => $_POST['entrega_id'],
            'fecha_devolucion' => $_POST['fecha_devolucion'],
            'estado_devolucion' => $_POST['estado_devolucion'],
            'recibido_por'     => $_POST['recibido_por'],
            'observaciones'    => $_POST['observaciones'] ?: ''
        ];

        $ok = ModeloUniformeDevoluciones::insertar($datos);

        if ($ok) {
            ToastifyController::success('Devolución registrada correctamente');
        } else {
            ToastifyController::error('Error al registrar la devolución');
        }

        header("Location: ?r=mi_uniforme&id=" . $_POST['usuario_id']);
        exit;
    }

    /* ADMINISTRACIÓN: LISTADO DE ITEMS */
    public static function adminItems()
    {
        Auth::check('uniformes', 'adminItems');

        $items = ModeloUniformeItems::listar(false);
        $categorias = ModeloUniformeCategorias::listar();

        require 'vistas/paginas/admin/uniformes/items.php';
    }

    /*  GUARDAR ITEM (crear o editar) */
    public static function guardarItem()
    {
        Auth::check('uniformes', 'adminItems');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?r=admin_items_uniforme');
            exit;
        }

        $datos = [
            'id'           => !empty($_POST['id']) ? (int) $_POST['id'] : null,
            'categoria_id' => (int) ($_POST['categoria_id'] ?? 0),
            'nombre'       => trim($_POST['nombre'] ?? ''),
            'descripcion'  => trim($_POST['descripcion'] ?? ''),
            'estado'       => in_array($_POST['estado'] ?? '', ['activo', 'inactivo'], true)
                ? $_POST['estado']
                : 'activo'
        ];

        if (!$datos['categoria_id'] || $datos['nombre'] === '' || !ModeloUniformeCategorias::buscar($datos['categoria_id'])) {
            ToastifyController::error('Completá una categoría y un nombre válidos');
            header('Location: ?r=admin_items_uniforme');
            exit;
        }

        try {
            $ok = $datos['id']
                ? ModeloUniformeItems::actualizar($datos)
                : ModeloUniformeItems::insertar($datos);

            $ok
                ? ToastifyController::success('Ítem guardado correctamente')
                : ToastifyController::error('No se pudo guardar el ítem');
        } catch (PDOException $e) {
            ToastifyController::error($e->getCode() === '23000'
                ? 'Ya existe un ítem con ese nombre'
                : 'No se pudo guardar el ítem');
        }

        header("Location: ?r=admin_items_uniforme");
        exit;
    }

    /* CAMBIAR ESTADO (activar / desactivar) */
    public static function cambiarEstadoItem()
    {
        Auth::check('uniformes', 'adminItems');

        $id = (int) ($_POST['id'] ?? 0);
        $estado = $_POST['estado'] ?? '';

        if ($id && in_array($estado, ['activo', 'inactivo'], true)) {
            ModeloUniformeItems::cambiarEstado($id, $estado);
            ToastifyController::success('Estado del ítem actualizado');
        } else {
            ToastifyController::error('No se pudo actualizar el estado del ítem');
        }

        header("Location: ?r=admin_items_uniforme");
        exit;
    }

    public static function eliminarItem()
    {
        Auth::check('uniformes', 'adminItems');

        $id = (int) ($_POST['id'] ?? 0);
        if (!$id || !ModeloUniformeItems::buscar($id)) {
            ToastifyController::error('El ítem indicado no existe');
        } elseif (ModeloUniformeItems::tieneEntregas($id)) {
            ToastifyController::error('El ítem tiene entregas registradas; podés desactivarlo, pero no eliminarlo');
        } elseif (ModeloUniformeItems::eliminar($id)) {
            ToastifyController::success('Ítem eliminado correctamente');
        } else {
            ToastifyController::error('No se pudo eliminar el ítem');
        }

        header('Location: ?r=admin_items_uniforme');
        exit;
    }

    public static function guardarCategoria()
    {
        Auth::check('uniformes', 'adminItems');

        $id = !empty($_POST['id']) ? (int) $_POST['id'] : null;
        $nombre = trim($_POST['nombre'] ?? '');

        if ($nombre === '') {
            ToastifyController::error('Ingresá el nombre de la categoría');
            header('Location: ?r=admin_items_uniforme');
            exit;
        }

        try {
            $ok = $id
                ? ModeloUniformeCategorias::actualizar($id, $nombre)
                : ModeloUniformeCategorias::insertar($nombre);

            $ok
                ? ToastifyController::success('Categoría guardada correctamente')
                : ToastifyController::error('No se pudo guardar la categoría');
        } catch (PDOException $e) {
            ToastifyController::error($e->getCode() === '23000'
                ? 'Ya existe una categoría con ese nombre'
                : 'No se pudo guardar la categoría');
        }

        header('Location: ?r=admin_items_uniforme');
        exit;
    }

    public static function eliminarCategoria()
    {
        Auth::check('uniformes', 'adminItems');

        $id = (int) ($_POST['id'] ?? 0);
        if (!$id || !ModeloUniformeCategorias::buscar($id)) {
            ToastifyController::error('La categoría indicada no existe');
        } elseif (ModeloUniformeCategorias::tieneItems($id)) {
            ToastifyController::error('La categoría tiene ítems asociados y no puede eliminarse');
        } elseif (ModeloUniformeCategorias::eliminar($id)) {
            ToastifyController::success('Categoría eliminada correctamente');
        } else {
            ToastifyController::error('No se pudo eliminar la categoría');
        }

        header('Location: ?r=admin_items_uniforme');
        exit;
    }

    /* LISTADO GENERAL DE UNIFORMES  */
    public static function vistaListadoUniformes()
    {
        Auth::check('uniformes', 'vistaListadoUniformes');

        $uniformes = ModeloUniformes::mdlListarUniformes();

        include 'vistas/paginas/admin/uniformes/listado_uniformes.php';
    }

    /* IMPRESION DE COMPROBANTES */
    public static function comprobanteEntrega()
    {
        Auth::check('uniformes', 'vistaMiUniforme');

        if (!isset($_GET['ids']) || empty($_GET['ids'])) {
            ToastifyController::error('No se encontraron ítems para imprimir');
            header("Location: ?r=mi_uniforme");
            exit;
        }

        $ids = explode(',', $_GET['ids']);

        $entregas = [];
        foreach ($ids as $id) {
            $fila = ModeloUniformeEntregas::buscar($id);
            if ($fila) {
                $entregas[] = $fila;
            }
        }

        if (empty($entregas)) {
            ToastifyController::error('No se encontraron entregas válidas');
            header("Location: ?r=mi_uniforme");
            exit;
        }

        $usuario_id = $entregas[0]['usuario_id'];
        $db = new Conexion;
        $usuario = $db->consultas("SELECT nombre, apellido, dni FROM usuarios WHERE idUsuario = $usuario_id LIMIT 1")[0] ?? null;

        require 'vistas/paginas/datos/comprobante_uniforme.php';
    }
}
