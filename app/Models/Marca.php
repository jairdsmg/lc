<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    use HasFactory;
    protected $fillable = ['nome', 'imagem'];

    public function rules(){
        return  [
            //aqui passamos o terceiro parametro .$this->id para que o unique pesquise por todos os registros, exceto o desse id. Isso porque no update precisaremos, as vezes atualizar outros dados mas manter o mesmo nome.
            'nome' => 'required|unique:marcas,nome,'.$this->id.'|min:3',
            'imagem' => 'required|file|mimes:png'
        ];

    }

    public function feedback(){
        return [
            'required' => 'O campo :attribute é obrigatório',
            'imagem.mimes' => 'O arquivo deve ser uma imagem do tipo PNG',
            'nome.unique' => 'O nome da marca já existe',
            'nome.min' => 'O nome deve ter no mínimo 3 caracteres'
        ];

    }

    //Relacionamento entre marca e modelo - Uma marca possui muitos modelos
    public function modelos(){
        return $this->hasMany('App\Models\Modelo');
        //criadas essa função aqui e em Modelo.php, voltamos ao ModeloController e MarcaController, nos métodos show de cada um, e, antes do find(), acrescentamos a instrução ->with('marca')
    }

}
