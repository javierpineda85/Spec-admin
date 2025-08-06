<?php

class LegajosController{
        static public function vistaLegajos()
    {
        //Auth::check('legajos', 'vistaLegajos');
        include __DIR__ . '/../vistas/paginas/usuario/legajos.php';
        return;
    }
}

?>