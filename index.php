<?php

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');



include_once "classes/autoload.php";
new Autoload();

$rota = new Rotas();
$rota->rotasPermitidas(
                        array('POST'), 
                        array(''),
                        '/oauth/token', 
                        'Usuarios::token', 
                        false
                    );

$rota->rotasPermitidas(
                        array('POST'), 
                        array('Content-Type' => 'application/x-www-form-urlencoded'),
                        '/lista/adicionar', 
                        'Listas::adicionar', 
                        true
                    );
                
$rota->rotasPermitidas(
                        array('GET'), 
                        array(''),
                        '/lista/consultar', 
                        'Listas::getAll', 
                        true
                    );

$rota->rotasPermitidas(
                        array('GET'), 
                        array(''),
                        '/lista/consultar/[VAR]', 
                        'Listas::get', 
                        true
                    );

$rota->rotasPermitidas(
                        array('PATCH','PUT'), 
                        array('Content-Type' => 'application/x-www-form-urlencoded'),
                        '/lista/atualizar/[VAR]', 
                        'Listas::update', 
                        true
                    );

$rota->rotasPermitidas(
                        array('DELETE'), 
                        array('Content-type' => 'application/x-www-form-urlencoded'),
                        '/lista/excluir/[VAR]', 
                        'Listas::delete',
                        true
                    );



//Chama a rota para execução da API
if(isset($_GET['path'])){
    $rota->rotaApi($_GET['path']);
}else{
    http_response_code(404);
    echo json_encode(["codigoErro" => "404", "mensagemErro" => "O recurso solicitado não está disponível"]);
}
