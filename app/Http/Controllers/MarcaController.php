<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage; //linha adicionada para que possamos remover as imagens
use App\Models\Marca;
use Illuminate\Http\Request;
use App\Repositories\MarcaRepository;


class MarcaController extends Controller
{

    //esse construtor foi adicionado para que os metodos index, update, store, destroy, enfim, todos os métodos trabalhem mais uniformemente com o model. Poderia trabalhar da aoutra forma, mas o autor preferiu assim. Aula 295.
    public function __construct(Marca $marca) {
        $this->marca = $marca;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $marcaRepository = new MarcaRepository($this->marca);

        if($request->has('atributos_modelos')){
            $atributos_modelos = 'modelos:id,'.$request->atributos_modelos;
            $marcaRepository->SelectAtributosRegistrosRelacionados($atributos_modelos);
        }else{
            $marcaRepository->SelectAtributosRegistrosRelacionados('modelos');
        }


        if($request->has('filtro')){
            $marcaRepository->filtro($request->filtro);      
        }


        if($request->has('atributos')){
            $marcaRepository->selectAtributos($request->atributos);  
        }

        return response()->json($marcaRepository->getResultado(), 200);
        

        //filtros usados antes do repositories---funcionava perfeitamente
        //porém todo esse código era copiado no metodo index de outro controller como ModeloController
        /*
        $marcas = array();

       
        if($request->has('atributos_modelos')){
            $atributos_modelos = $request->atributos_modelos;
            $marcas = $this->marca->with('modelos:id,'.$atributos_modelos);
        }else{
            $marcas = $this->marca->with('modelos');
        }


        if($request->has('filtro')){
            $filtros = explode(';', $request->filtro);
            foreach($filtros as $key => $condicao) {

                $c = explode(':', $condicao);
                $marcas = $marcas->where($c[0], $c[1], $c[2]);
            }
                  
        }


        if($request->has('atributos')){
            $atributos = $request->atributos;
            $marcas = $marcas->selectRaw($atributos)->get();
        }else{
            $marcas = $marcas->get();
        }

        return response()->json($marcas, 200);
        */


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // linha de codigo para utilizar a validação criada no model marca
        //qualquer método aqui que quiser usar(store ou update), é só fazer a declaração abaixo
        $request->validate($this->marca->rules(), $this->marca->feedback());

        //dd($request->get('nome'));
        //dd($request->file('imagem'));
        $image = $request->file('imagem'); //=nome da imagem armazenada
        $imagem_urn = $image->store('imagens', 'public');
        //dd($imagem_urn);
        //$marca = Marca::create($request->all()); metodo antigo
        $marca = $this->marca->create([
            'nome'=> $request->nome,
            'imagem'=> $imagem_urn
        ]); /*OU poderia ser conforme abaixo
        $marca->nome = $request->nome;
        $marca->imagem = $imagem_urn;
        $marca->save(); */
        return response()->json($marca, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //echo 'chegamos até aqui(Show)';

        $marca = $this->marca->with('modelos')->find($id);
        if($marca === null){
            return response()->json(['erro' => 'Recurso pesquisado não existe'], 404);
        }
        return response()->json($marca, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Marca  $marca
     * @return \Illuminate\Http\Response
     */
    public function edit(Marca $marca)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        /*
        echo 'chegamos até aqui (update)';
        print_r($request->all()); //os dados atualizados
        echo'<hr>';
        print_r($marca->getAttributes()); // os dados antigos
        */
        //$marca->update($request->all());

        $marca = $this->marca->find($id);

        if($marca === null){
            return response()->json(['erro' => 'Impossível realizar a atualização. O recurso solicitado não existe'], 404);
        }


        //Se a atualização nao for completa (PUT), mas for parcial(PATCH).
        if($request->method() === 'PATCH'){
            $regrasDinamicas = array();
            //$teste = '';
            //Percorrendo todas as regras definidas no model
            foreach($marca->rules() as $input => $regra){
                //$teste.= 'Input: '.$input.'|Regra: '.$regra.'<br>';
                //com o codigo acima coletamos todas as regras com o return. A ideia agora é coletar
                //apenas as regras aplicaveis  aos parametros parciais da requisição PATCH  passada
                if(array_key_exists($input, $request->all())){
                    $regrasDinamicas[$input] = $regra;
                }
            }
            //dd($request->all());
           // return $teste;
           //return($regrasDinamicas);
            $request->validate($regrasDinamicas, $marca->feedback());
        }else{

            $request->validate($marca->rules(), $marca->feedback());
        }

        //remove o arquivo antigo caso um novo tenha sido enviado no request
        if($request->file('imagem')){
            Storage::disk('public')->delete($marca->imagem);
        }

        
        $imagem = $request->file('imagem'); //=nome da imagem 
        $imagem_urn = $imagem->store('imagens', 'public');

        //preenchero o objeto marca com os dados do request
       // dd($request->all());
        $marca->fill($request->all());
        //dd($marca->getAttributes());
        $marca->imagem = $imagem_urn;
        $marca->save();

        //dd($imagem_urn);
       /* $marca->update([
            'nome'=> $request->nome,
            'imagem'=> $imagem_urn
        ]);*/
        return response()->json($marca, 200);  
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       /* Antes do código da exclusão mencionado abaixo, em si, fizemos esses testes
        print_r($marca->getAttributes()); //para visualizarmos no postman se o laravel encontrou e instanciou o objeto passado pela requisição
        return'Chegamos até aqui (delete)';
        */
        
        $marca = $this->marca->find($id);
        if($marca === null){
            return response()->json( ['erro' => 'Impossível realizar a exclusão. O recurso solicitado não existe'],404);
        }

        //remove o arquivo que estava armazenado
        Storage::disk('public')->delete($marca->imagem);
        


        $marca->delete();
        return response()->json(['msg' =>'A marca foi removida com sucesso!'], 200); //Aqui o Laravel interpreta o array e retorna em json para quem requisitou.
    }
}
