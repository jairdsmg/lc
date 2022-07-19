<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request){
        //dd($request->all(['email','password']));
        $credenciais = $request->all(['email','password']);
       $token =  auth('api')->attempt($credenciais);
       //dd($token);
        //return 'login';

        if($token){
            return response()->json(['token'=> $token]);
        }else{
            return response()->json(['Usuário e/ou senha inválido(s)!'], 403);
            //403 = forbidden -> proibido (login inválido)

            //temos outro código que é o 401 = Unauthorized -> nao autorizado, ou seja quando o cliente ja foi autenticado mas não foi autorizado para um determinado caminho
            //para o caso aqui, que é login, o correto é usar o 403 acima.
        }
    }

    public function logout(){
        auth('api')->logout();
        return response()->json(['msg' => 'Logout realizado com sucesso!']);
    }

    public function refresh(){
        $token = auth('api')->refresh();
        //aqui é necessário indicar o driver do usuario 'api' e possuir um jwt válido
        return response()->json(['token' => $token]);
    }

    public function me(){
        return response()->json(auth()->user());
        //traz as informações do usuário que logou, excento as informacoes mais sensíveis como senha
    }

}
