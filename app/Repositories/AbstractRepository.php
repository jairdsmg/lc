<?php

namespace App\Repositories;
use Illuminate\Database\Eloquent\Model;


//Classe comum a todas que a extendem
abstract class AbstractRepository{


    public function __construct(Model $model){
        $this->model = $model;
    }

    public function selectAtributosRegistrosRelacionados($atributos){
        $this->model = $this->model->with($atributos);
        //a repetição "$this->model" é necessária para atualizar o proprio atributo...mais ou menos isso,rs.
    }

    public function filtro($filtros){
        $filtros = explode(';', $filtros);

            foreach($filtros as $key => $condicao) {

                $c = explode(':', $condicao);
                $this->model = $this->model->where($c[0], $c[1], $c[2]);
            }
    }


    public function selectAtributos($atributos){
        $this->model =  $this->model->selectRaw($atributos);
    }


    public function getResultado(){
        return $this->model->get();
    }
    

}





?>