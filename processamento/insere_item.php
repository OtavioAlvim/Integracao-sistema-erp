<?php
include('../login/verifica_login.php');
$pdo = new PDO('sqlite:../DB/' . $_SESSION['cnpj'] . '/DB-SIA/sia');
$db_prod = new PDO('sqlite:../DB/' . $_SESSION['cnpj'] . '/DB-SISTEMA/' . $_SESSION['ID'] . '');
date_default_timezone_set("America/Sao_Paulo");
$data_atual = date("Y-m-d");

if (empty($_POST['codbarra'])) {
    $_SESSION['produto_nao_encontrado'] = true;
    header('Location: ../venda.php');
    exit();
}

$verifica_cli = $db_prod->query("SELECT coalesce(i.DESCRICAO, 0) as resultado,p.NOME_CLIENTE,p.ID_PEDIDO  from TMPPEDIDOS p left join TMPITENS_PEDIDO i on p.ID_PEDIDO = i.ID_PEDIDO where p.VENDEDOR = '{$_SESSION['usuario']}' order by p.ID_PEDIDO DESC limit 1");
$resultado_verifica = $verifica_cli->fetchAll(PDO::FETCH_ASSOC);
foreach ($resultado_verifica as $chave => $registro) {
}
if ($registro['NOME_CLIENTE'] == 'CLIENTE INDEFINIDO') {
    $_SESSION['INSIRA_CLIENTE'] = true;
    header('Location: ../venda.php');
    exit();
}


$valor1 = $_POST['codbarra'];

if (strpos($valor1, ",") !== false) {
    $valor = str_replace(",", ".", "$valor1");
} else {
    $valor = $valor1;
}
if (strpos($valor, "/") !== false) {
    //***********************************************************************************"usaremos o codigo interno";
    if (strpos($valor, "*") !== false) {
        // "é um produto multiplicativo";
        // valida quando o produto tem fator multiplicativo
        $cod_produto = explode('*', str_replace("/", "", $valor));
        $qtd_processado = $cod_produto['0'];
        $cod_processado = $cod_produto['1'];
        $consulta_sqlite = $pdo->query("SELECT count(*) as total FROM produtos where produtos.ID_PRODUTO = '{$cod_processado}' and produtos.ID_EMPRESA = 1");
        $produto = $consulta_sqlite->fetchAll(PDO::FETCH_ASSOC);
        foreach ($produto as $row => $produtos) {
            $total = $produtos['total'];
        }
        if ($total == 0) {
            // "produto de balança não cadastrado";
            $_SESSION['produto_nao_encontrado'] = true;
            header('Location: ../venda.php');
            exit();
        } else if ($total == 1) {
            // "encontramos $total registro no banco de dados";
            $inclui_produto = $pdo->query("SELECT produtos.ID_PRODUTO,produtos.CODBARRA,produtos.DESCRICAO,produtos.UNIDADE,produtos.UNITARIO,produtos.VALOR_PROMOCAO,produtos.PRECOCUSTO,produtos.PRECOREVENDA,substr(produtos.FIMPROMOCAO,1,10) as FIMPROMOCAO,substr(produtos.INICIOPROMOCAO,1,10) as INICIOPROMOCAO FROM produtos where produtos.ID_PRODUTO = '{$cod_processado}' and produtos.ID_EMPRESA = 1");
            $executei = $inclui_produto->fetchAll(PDO::FETCH_ASSOC);
            foreach ($executei as $row => $produtos) {
                $id_produto = $produtos['ID_PRODUTO'];
                $codbarra = $produtos['CODBARRA'];
                $descricao = $produtos['DESCRICAO'];
                $unidade = $produtos['UNIDADE'];

                if ($produtos['FIMPROMOCAO'] >= $data_atual) {
                    $unitario = $produtos['VALOR_PROMOCAO'];
                } else {
                    $unitario = $produtos['UNITARIO'];
                }
                // $id_produto;
                // "<br>";
                // $codbarra;
                // "<br>";
                // $descricao;
                // "<br>";
                // $unidade;
                // "<br>";
                // $unitario;
                // "<br>";
                // $unidade;
                //*************************************BUSCA PERCENTUAL DO PERFIL DO CLIENTE CASO TENHA SIDO CONFIGURADO NO SIA

                $busca_valor_perfil = $db_prod->query("SELECT t.PORCENTAGEM_ACRESCIMO,t.PORCENTAGEM_DESCONTO from TMPPEDIDOS t where t.ID_PEDIDO = {$_SESSION['id_ultimo_pedido']}");
                $resultado_perfil = $busca_valor_perfil->fetchAll(PDO::FETCH_ASSOC);
                foreach ($resultado_perfil as $id => $percentual) {
                }
                //************************************VERIFICA SE TEM ACRESCIMO OU DESCONTO NO CADASTRO DO CLIENTE
                if ($percentual['PORCENTAGEM_ACRESCIMO'] <> 0) {
                    // "perfil de acrescimo encontrado";
                    $sql_busca_produto_banco_sia = $pdo->query("SELECT * FROM PRODUTOS where ID_PRODUTO = {$id_produto} and produtos.ID_EMPRESA = 1");
                    $resultado = $sql_busca_produto_banco_sia->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($resultado as $row => $registros) {
                    }
                    $valor_com_acrescimo = $unitario * $qtd_processado * $percentual['PORCENTAGEM_ACRESCIMO'] / 100;
                    $valor_total = $unitario * $qtd_processado + $valor_com_acrescimo;
                    $valor_unidade = $unitario * 1 * $percentual['PORCENTAGEM_ACRESCIMO'] / 100;
                    $valor_unitario_total = $unitario + $valor_unidade;
                } else if ($percentual['PORCENTAGEM_DESCONTO'] <> 0) {
                    // "perfil de desconto encontrado";
                    $sql_busca_produto_banco_sia = $pdo->query("SELECT * FROM PRODUTOS where ID_PRODUTO = {$id_produto} and produtos.ID_EMPRESA = 1");
                    $resultado = $sql_busca_produto_banco_sia->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($resultado as $row => $registros) {
                    }
                    $valor_com_desconto = $unitario * $qtd_processado * $percentual['PORCENTAGEM_DESCONTO'] / 100;
                    $valor_total = $unitario * $qtd_processado - $valor_com_desconto;
                    $valor_unidade = $unitario * 1 * $percentual['PORCENTAGEM_DESCONTO'] / 100;
                    $valor_unitario_total = $unitario - $valor_unidade;
                } else {
                    //"PODEMOS USAR O VALOR PADRÃO DA TABELA DE CLIENTES DO SIA SEM O PERFIL DE CLIENTES";
                    // SE CASO ELE NÃO ENCONTRAR NENHUM VALOR NA TABELA REFERENTE A ACRESCIMO OU DESCONTO, ELE VAI USAR O VALOR PADRAO DO PRODUTO PRESENTE NA TABELA PRODUTOS
                    $sql_busca_produto_banco_sia = $pdo->query("SELECT * FROM PRODUTOS where ID_PRODUTO = {$id_produto} and produtos.ID_EMPRESA = 1");
                    $resultado = $sql_busca_produto_banco_sia->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($resultado as $row => $registros) {
                    }
                    $valor_unitario_total = $unitario;
                    $valor_total = $qtd_processado * $unitario;
                }
            }
            $sql = "INSERT INTO TMPITENS_PEDIDO (ID_PEDIDO, ID_EMPRESA, ID_PRODUTO, QTD, UNITARIO, DESCONTO, TOTAL, DADOADICIONAL, DESCRICAO, PRECOINICIAL, ID_TONALIDADE, UNITARIOBASE, EMPROMOCAO, DESPESAS_BOLETO, VENDEDOR ) VALUES ({$_SESSION['id_ultimo_pedido']}, '1', {$id_produto}, {$qtd_processado}, {$valor_unitario_total}, '0.0', {$valor_total}, '', '{$descricao}', {$valor_unitario_total}, '0', {$valor_unitario_total}, 'N', '0', '{$_SESSION['usuario']}')";
            if ($db_prod->exec($sql)) {
                $_SESSION['reinicia'] = true;
                header('Location: ../venda.php');
                exit();
            }
        } else {
            $_SESSION['tamanho-campo-invalido'] = true;
            header('Location: ../venda.php');
            exit();
            // "Produto invalido, codbarra menor que o permitido!";
        }
    } else { //USA QUANDO O PRODUTO É UM PRODUTO NORMAL E NÃO É UM CODIGO DE BALANÇA OU PRODUTO MULTIPLICATIVO
        //"produto sem fator multiplicativo";
        $codigo_prod_completo = str_replace("/", "", $valor);
        $consulta_sqlite = $pdo->query("SELECT count(*) as total FROM produtos where produtos.ID_PRODUTO = '{$codigo_prod_completo}' and produtos.ID_EMPRESA = 1");
        $produto = $consulta_sqlite->fetchAll(PDO::FETCH_ASSOC);
        foreach ($produto as $row => $produtos) {
            $total = $produtos['total'];
        }
        if ($total == 0) {
            //"produto não cadastrado";
            $_SESSION['produto_nao_encontrado'] = true;
            header('Location: ../venda.php');
            exit();
        } else if ($total == 1) {
            //"encontramos $total registro no banco de dados";
            $inclui_produto = $pdo->query("SELECT produtos.ID_PRODUTO,produtos.CODBARRA,produtos.DESCRICAO,produtos.UNIDADE,produtos.UNITARIO,produtos.VALOR_PROMOCAO,produtos.PRECOCUSTO,produtos.PRECOREVENDA,substr(produtos.FIMPROMOCAO,1,10) as FIMPROMOCAO,substr(produtos.INICIOPROMOCAO,1,10) as INICIOPROMOCAO  FROM produtos where produtos.ID_PRODUTO = '{$codigo_prod_completo}' and produtos.ID_EMPRESA = 1");
            $executei = $inclui_produto->fetchAll(PDO::FETCH_ASSOC);
            foreach ($executei as $row => $produtos) {

                $id_produto = $produtos['ID_PRODUTO'];
                $codbarra = $produtos['CODBARRA'];
                $descricao = $produtos['DESCRICAO'];
                $unidade = $produtos['UNIDADE'];



                if ($produtos['FIMPROMOCAO'] >= $data_atual) {
                    $unitario = $produtos['VALOR_PROMOCAO'];
                } else {
                    $unitario = $produtos['UNITARIO'];
                }








                //*************************************BUSCA PERCENTUAL DO PERFIL DO CLIENTE CASO TENHA SIDO CONFIGURADO NO SIA

                $busca_valor_perfil = $db_prod->query("SELECT t.PORCENTAGEM_ACRESCIMO,t.PORCENTAGEM_DESCONTO from TMPPEDIDOS t where t.ID_PEDIDO = {$_SESSION['id_ultimo_pedido']}");
                $resultado_perfil = $busca_valor_perfil->fetchAll(PDO::FETCH_ASSOC);
                foreach ($resultado_perfil as $id => $percentual) {
                }
                //************************************VERIFICA SE TEM ACRESCIMO OU DESCONTO NO CADASTRO DO CLIENTE
                if ($percentual['PORCENTAGEM_ACRESCIMO'] <> 0) {
                    $sql_busca_produto_banco_sia = $pdo->query("SELECT * FROM PRODUTOS where ID_PRODUTO = {$id_produto} and produtos.ID_EMPRESA = 1");
                    $resultado = $sql_busca_produto_banco_sia->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($resultado as $row => $registros) {
                    }
                    $valor_com_acrescimo = $unitario * 1 * $percentual['PORCENTAGEM_ACRESCIMO'] / 100;
                    $valor_total = $unitario + $valor_com_acrescimo;
                } else if ($percentual['PORCENTAGEM_DESCONTO'] <> 0) {
                    $sql_busca_produto_banco_sia = $pdo->query("SELECT * FROM PRODUTOS where ID_PRODUTO = {$id_produto} and produtos.ID_EMPRESA = 1");
                    $resultado = $sql_busca_produto_banco_sia->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($resultado as $row => $registros) {
                    }
                    $valor_com_desconto = $unitario * 1 * $percentual['PORCENTAGEM_DESCONTO'] / 100;
                    $valor_total = $unitario - $valor_com_desconto;
                } else {
                    //"PODEMOS USAR O VALOR PADRÃO DA TABELA DE CLIENTES DO SIA SEM O PERFIL DE CLIENTES";
                    // SE CASO ELE NÃO ENCONTRAR NENHUM VALOR NA TABELA REFERENTE A ACRESCIMO OU DESCONTO, ELE VAI USAR O VALOR PADRAO DO PRODUTO PRESENTE NA TABELA PRODUTOS
                    $sql_busca_produto_banco_sia = $pdo->query("SELECT * FROM PRODUTOS where ID_PRODUTO = {$id_produto} and produtos.ID_EMPRESA = 1");
                    $resultado = $sql_busca_produto_banco_sia->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($resultado as $row => $registros) {
                    }
                    $valor_total = $unitario;
                }
            }
            $sql = "INSERT INTO TMPITENS_PEDIDO (ID_PEDIDO, ID_EMPRESA, ID_PRODUTO, QTD, UNITARIO, DESCONTO, TOTAL, DADOADICIONAL, DESCRICAO, PRECOINICIAL, ID_TONALIDADE, UNITARIOBASE, EMPROMOCAO, DESPESAS_BOLETO, VENDEDOR) VALUES ({$_SESSION['id_ultimo_pedido']}, {$_SESSION['ID_EMPRESA']}, {$id_produto}, 1, {$valor_total}, '0', {$valor_total}, '', '{$descricao}', {$valor_total}, '0', {$valor_total}, 'N', '0', '{$_SESSION['usuario']}')";
            if ($db_prod->exec($sql)) {
                $_SESSION['reinicia'] = true;
                header('Location: ../venda.php');
                exit();
            }
        };
    }
} else {
    // ******************************************************************************"usaremos codigo de barras";
    $completa_cod = '00000000000000';

    if (strpos($valor, "*") !== false) {
        //"é um produto multiplicativo";
        // valida quando o produto tem fator multiplicativo
        $cod_produto = explode('*', $valor);
        $qtd_processado = $cod_produto['0'];
        $cod_processado = $cod_produto['1'];
        $cod_processado_completo = substr($completa_cod . $cod_processado, -14);


        $consulta_sqlite = $db_prod->query("SELECT count(*) as total, c.CODBARRA,c.CODITEM  FROM CODIGOS c WHERE c.CODBARRA = '{$cod_processado_completo}'");
        $produto = $consulta_sqlite->fetchAll(PDO::FETCH_ASSOC);
        foreach ($produto as $row => $produtos) {
            $total = $produtos['total'];
            $coditem = $produtos['CODITEM'];
        }
        if ($total == 0) {
            //"produto  não cadastrado";
            $_SESSION['produto_nao_encontrado'] = true;
            header('Location: ../venda.php');
            exit();
        } else if ($total == 1) {
            // valida se no banco do sia, aquele id produto esta vinculado
            $consulta_se_existe_no_banco_sia = $pdo->query("SELECT count(*) as total FROM produtos where produtos.ID_PRODUTO = '{$coditem}' and produtos.ID_EMPRESA = 1");
            $produto = $consulta_se_existe_no_banco_sia->fetchAll(PDO::FETCH_ASSOC);
            foreach ($produto as $row => $res) {
                $res = $produtos['total'];
            }
            if ($res == 0) {
                //"produto não encontrado";
                $_SESSION['produto_nao_encontrado'] = true;
                header('Location: ../venda.php');
                exit();
            }
            //"encontramos $total registro no banco de dados";
            $inclui_produto = $pdo->query("SELECT produtos.ID_PRODUTO,produtos.CODBARRA,produtos.DESCRICAO,produtos.UNIDADE,produtos.UNITARIO,produtos.VALOR_PROMOCAO,produtos.PRECOCUSTO,produtos.PRECOREVENDA,substr(produtos.FIMPROMOCAO,1,10) as FIMPROMOCAO,substr(produtos.INICIOPROMOCAO,1,10) as INICIOPROMOCAO FROM produtos where produtos.ID_PRODUTO = '{$coditem}' and produtos.ID_EMPRESA = 1");
            $executei = $inclui_produto->fetchAll(PDO::FETCH_ASSOC);
            foreach ($executei as $row => $produtos) {
                $id_produto = $produtos['ID_PRODUTO'];
                $codbarra = $produtos['CODBARRA'];
                $descricao = $produtos['DESCRICAO'];
                $unidade = $produtos['UNIDADE'];

                if ($produtos['FIMPROMOCAO'] >= $data_atual) {
                    $unitario = $produtos['VALOR_PROMOCAO'];
                } else {
                    $unitario = $produtos['UNITARIO'];
                }
                // $id_produto;
                // "<br>";
                // $codbarra;
                // "<br>";
                // $descricao;
                // "<br>";
                // $unidade;
                // "<br>";
                // $unitario;
                // "<br>";
                // $unidade;
                //*************************************BUSCA PERCENTUAL DO PERFIL DO CLIENTE CASO TENHA SIDO CONFIGURADO NO SIA

                $busca_valor_perfil = $db_prod->query("SELECT t.PORCENTAGEM_ACRESCIMO,t.PORCENTAGEM_DESCONTO from TMPPEDIDOS t where t.ID_PEDIDO = {$_SESSION['id_ultimo_pedido']}");
                $resultado_perfil = $busca_valor_perfil->fetchAll(PDO::FETCH_ASSOC);
                foreach ($resultado_perfil as $id => $percentual) {
                }
                //************************************VERIFICA SE TEM ACRESCIMO OU DESCONTO NO CADASTRO DO CLIENTE
                if ($percentual['PORCENTAGEM_ACRESCIMO'] <> 0) {
                    // "perfil de acrescimo encontrado";
                    $sql_busca_produto_banco_sia = $pdo->query("SELECT * FROM PRODUTOS where ID_PRODUTO = {$id_produto} and produtos.ID_EMPRESA = 1");
                    $resultado = $sql_busca_produto_banco_sia->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($resultado as $row => $registros) {
                    }
                    $valor_com_acrescimo = $unitario * $qtd_processado * $percentual['PORCENTAGEM_ACRESCIMO'] / 100;
                    $valor_total = $unitario * $qtd_processado + $valor_com_acrescimo;
                    $valor_unidade = $unitario * 1 * $percentual['PORCENTAGEM_ACRESCIMO'] / 100;
                    $valor_unitario_total = $unitario + $valor_unidade;
                } else if ($percentual['PORCENTAGEM_DESCONTO'] <> 0) {
                    // "perfil de desconto encontrado";
                    $sql_busca_produto_banco_sia = $pdo->query("SELECT * FROM PRODUTOS where ID_PRODUTO = {$id_produto} and produtos.ID_EMPRESA = 1");
                    $resultado = $sql_busca_produto_banco_sia->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($resultado as $row => $registros) {
                    }
                    $valor_com_desconto = $unitario * $qtd_processado * $percentual['PORCENTAGEM_DESCONTO'] / 100;
                    $valor_total = $unitario * $qtd_processado - $valor_com_desconto;
                    $valor_unidade = $unitario * 1 * $percentual['PORCENTAGEM_DESCONTO'] / 100;
                    $valor_unitario_total = $unitario - $valor_unidade;
                } else {
                    //"PODEMOS USAR O VALOR PADRÃO DA TABELA DE CLIENTES DO SIA SEM O PERFIL DE CLIENTES";
                    // SE CASO ELE NÃO ENCONTRAR NENHUM VALOR NA TABELA REFERENTE A ACRESCIMO OU DESCONTO, ELE VAI USAR O VALOR PADRAO DO PRODUTO PRESENTE NA TABELA PRODUTOS
                    $sql_busca_produto_banco_sia = $pdo->query("SELECT * FROM PRODUTOS where ID_PRODUTO = {$id_produto} and produtos.ID_EMPRESA = 1");
                    $resultado = $sql_busca_produto_banco_sia->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($resultado as $row => $registros) {
                    }
                    $valor_unitario_total = $unitario;
                    $valor_total = $qtd_processado * $unitario;
                }
            }
            $sql = "INSERT INTO TMPITENS_PEDIDO (ID_PEDIDO, ID_EMPRESA, ID_PRODUTO, QTD, UNITARIO, DESCONTO, TOTAL, DADOADICIONAL, DESCRICAO, PRECOINICIAL, ID_TONALIDADE, UNITARIOBASE, EMPROMOCAO, DESPESAS_BOLETO, VENDEDOR ) VALUES ({$_SESSION['id_ultimo_pedido']}, '1', {$id_produto}, {$qtd_processado}, {$valor_unitario_total}, '0.0', {$valor_total}, '', '{$descricao}', {$valor_unitario_total}, '0', {$valor_unitario_total}, 'N', '0', '{$_SESSION['usuario']}')";
            if ($db_prod->exec($sql)) {
                $_SESSION['reinicia'] = true;
                header('Location: ../venda.php');
                exit();
            }
        } else {
            $_SESSION['tamanho-campo-invalido'] = true;
            header('Location: ../venda.php');
            exit();
            // "Produto invalido, codbarra menor que o permitido!";
        }
    } else {
        // VALIDAÇÃO CRIADA PARA PRODUTO DE BALANÇA 6 E CONTEM PREÇO
        $valida_balanca = substr($valor, 0, 1);

        // CONFIGURAÇÃO PARA 6 E CONTEM PREÇO
        if ($valida_balanca == 2) {
            //"esse produto é de balanca!";
            $completa_cod = '00000000000000';
            $gera_cod_prod = substr($valor, 1, 6);
            $prod_cod_sis = substr($completa_cod . $gera_cod_prod, -14); // codigo do produto para pesquisar sia
            $gera_val = substr($valor, -6);
            $gera_val1 = substr($valor, -6, -1);
            $gera_val2 = substr($valor, -3, -1); // centavos
            $gera_val3 = substr($valor, -6, -3); // real
            $gera_valor_final = "$gera_val3" . "." . "$gera_val2";
            $v_prod = ltrim($gera_valor_final, "0"); //valor do cupom

            $consulta_sqlite = $db_prod->query("SELECT count(*) as total, c.CODBARRA,c.CODITEM  FROM CODIGOS c WHERE c.CODBARRA = '{$prod_cod_sis}'"); //falta validar esse campo 
            $produto = $consulta_sqlite->fetchAll(PDO::FETCH_ASSOC);
            foreach ($produto as $row => $produtos) {
                $total = $produtos['total'];
                $coditemm = $produtos['CODITEM'];
            }
            if ($total == 0) {
                //"produto não encontrado";
                $_SESSION['produto_nao_encontrado'] = true;
                header('Location: ../venda.php');
                exit();
            } else if ($total == 1) {

                $consulta_se_existe_no_banco_sia = $pdo->query("SELECT count(*) as total FROM produtos where produtos.ID_PRODUTO = '{$coditemm}' and produtos.ID_EMPRESA = 1");
                $produto = $consulta_se_existe_no_banco_sia->fetchAll(PDO::FETCH_ASSOC);
                foreach ($produto as $row => $res) {
                    $res = $produtos['total'];
                }
                if ($res == 0) {
                    //"produto não encontrado";
                    $_SESSION['produto_nao_encontrado'] = true;
                    header('Location: ../venda.php');
                    exit();
                }

                //"produto encontrado no banco de dados";
                // "encontramos $total registro no banco de dados";
                $inclui_produto = $pdo->query("SELECT produtos.ID_PRODUTO,produtos.CODBARRA,produtos.DESCRICAO,produtos.UNIDADE,produtos.UNITARIO,produtos.VALOR_PROMOCAO,produtos.PRECOCUSTO,produtos.PRECOREVENDA,substr(produtos.FIMPROMOCAO,1,10) as FIMPROMOCAO,substr(produtos.INICIOPROMOCAO,1,10) as INICIOPROMOCAO FROM produtos where produtos.ID_PRODUTO = '{$coditemm}' and produtos.ID_EMPRESA = 1"); //falta validar esse campo
                $executei = $inclui_produto->fetchAll(PDO::FETCH_ASSOC);
                foreach ($executei as $row => $produtos) {
                    $id_produto = $produtos['ID_PRODUTO'];
                    $codbarra = $produtos['CODBARRA'];
                    $descricao = $produtos['DESCRICAO'];
                    $unidade = $produtos['UNIDADE'];

                    if ($produtos['FIMPROMOCAO'] >= $data_atual) {
                        $unitario = $produtos['VALOR_PROMOCAO'];
                    } else {
                        $unitario = $produtos['UNITARIO'];
                    }
                    //  $id_produto;
                    //  "<br>";
                    //  $codbarra;
                    //  "<br>";
                    //  $descricao;
                    //  "<br>";
                    //  $unidade;
                    //  "<br>";
                    //  $unitario;
                    //  "<br>";
                    //  $unidade;
                }
                $peso_prod =  round($v_prod / $unitario, 3);
                $sql = "INSERT INTO TMPITENS_PEDIDO (ID_PEDIDO, ID_EMPRESA, ID_PRODUTO, QTD, UNITARIO, DESCONTO, TOTAL, DADOADICIONAL, DESCRICAO, PRECOINICIAL, ID_TONALIDADE, UNITARIOBASE, EMPROMOCAO, DESPESAS_BOLETO, VENDEDOR) VALUES ({$_SESSION['id_ultimo_pedido']}, '{$_SESSION['ID_EMPRESA']}', {$id_produto}, {$peso_prod}, {$unitario}, '0.1', {$v_prod}, '', '{$descricao}', {$unitario}, '0', {$unitario}, 'N', '0', '{$_SESSION['usuario']}')";
                if ($db_prod->exec($sql)) {
                    $_SESSION['reinicia'] = true;
                    header('Location: ../venda.php');
                    exit();
                }
            };
        } else { //USA QUANDO O PRODUTO É UM PRODUTO NORMAL E NÃO É UM CODIGO DE BALANÇA OU PRODUTO MULTIPLICATIVO
            // "produto sem fator multiplicativo";
            $codigo_prod_completo = substr($completa_cod . $valor, -14);

            $consulta_sqlite = $db_prod->query("SELECT count(*) as total, c.CODBARRA,c.CODITEM  FROM CODIGOS c WHERE c.CODBARRA = '{$codigo_prod_completo}'");
            $produto = $consulta_sqlite->fetchAll(PDO::FETCH_ASSOC);
            foreach ($produto as $row => $produtos) {
                $total = $produtos['total'];
                $coditem = $produtos['CODITEM'];
            }
            if ($total == 0) {
                //"produto não cadastrado";
                $_SESSION['produto_nao_encontrado'] = true;
                header('Location: ../venda.php');
                exit();
            } else if ($total == 1) {
                // valida se no banco do sia, aquele id produto esta vinculado
                $consulta_se_existe_no_banco_sia = $pdo->query("SELECT count(*) as total FROM produtos where produtos.ID_PRODUTO = '{$coditem}' and produtos.ID_EMPRESA = 1");
                $produtos = $consulta_se_existe_no_banco_sia->fetchAll(PDO::FETCH_ASSOC);
                foreach ($produtos as $row => $res) {
                   $res = $produtos['total'];
                }
                if ($res == 0) {
                    echo "produto não encontrado";
                    $_SESSION['produto_nao_encontrado'] = true;
                    header('Location: ../venda.php');
                    exit();
                }
                $inclui_produto = $pdo->query("SELECT produtos.ID_PRODUTO,produtos.CODBARRA,produtos.DESCRICAO,produtos.UNIDADE,produtos.UNITARIO,produtos.VALOR_PROMOCAO,produtos.PRECOCUSTO,produtos.PRECOREVENDA,substr(produtos.FIMPROMOCAO,1,10) as FIMPROMOCAO,substr(produtos.INICIOPROMOCAO,1,10) as INICIOPROMOCAO FROM produtos where produtos.ID_PRODUTO = '{$coditem}' and produtos.ID_EMPRESA = 1");
                $executei = $inclui_produto->fetchAll(PDO::FETCH_ASSOC);
                foreach ($executei as $row => $produtos) {

                    $id_produto = $produtos['ID_PRODUTO'];
                    $codbarra = $produtos['CODBARRA'];
                    $descricao = $produtos['DESCRICAO'];
                    $unidade = $produtos['UNIDADE'];

                    if ($produtos['FIMPROMOCAO'] >= $data_atual) {
                        $unitario = $produtos['VALOR_PROMOCAO'];
                    } else {
                        $unitario = $produtos['UNITARIO'];
                    }
                    $unitario;
                    // $id_produto;
                    // "<br>";
                    // $codbarra;
                    // "<br>";
                    // $descricao;
                    // "<br>";
                    // $unidade;
                    // "<br>";
                    // $unitario;
                    // "<br>";
                    // $unidade;

                    //*************************************BUSCA PERCENTUAL DO PERFIL DO CLIENTE CASO TENHA SIDO CONFIGURADO NO SIA

                    $busca_valor_perfil = $db_prod->query("SELECT t.PORCENTAGEM_ACRESCIMO,t.PORCENTAGEM_DESCONTO from TMPPEDIDOS t where t.ID_PEDIDO = {$_SESSION['id_ultimo_pedido']}");
                    $resultado_perfil = $busca_valor_perfil->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($resultado_perfil as $id => $percentual) {
                    }
                    //************************************VERIFICA SE TEM ACRESCIMO OU DESCONTO NO CADASTRO DO CLIENTE
                    if ($percentual['PORCENTAGEM_ACRESCIMO'] <> 0) {
                        $sql_busca_produto_banco_sia = $pdo->query("SELECT * FROM PRODUTOS where ID_PRODUTO = {$id_produto} and produtos.ID_EMPRESA = 1");
                        $resultado = $sql_busca_produto_banco_sia->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($resultado as $row => $registros) {
                        }
                        $valor_com_acrescimo = $unitario * 1 * $percentual['PORCENTAGEM_ACRESCIMO'] / 100;
                        $valor_total = $unitario + $valor_com_acrescimo;
                    } else if ($percentual['PORCENTAGEM_DESCONTO'] <> 0) {
                        $sql_busca_produto_banco_sia = $pdo->query("SELECT * FROM PRODUTOS where ID_PRODUTO = {$id_produto} and produtos.ID_EMPRESA = 1");
                        $resultado = $sql_busca_produto_banco_sia->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($resultado as $row => $registros) {
                        }
                        $valor_com_desconto = $unitario * 1 * $percentual['PORCENTAGEM_DESCONTO'] / 100;
                        $valor_total = $unitario - $valor_com_desconto;
                    } else {
                        //"PODEMOS USAR O VALOR PADRÃO DA TABELA DE CLIENTES DO SIA SEM O PERFIL DE CLIENTES";
                        // SE CASO ELE NÃO ENCONTRAR NENHUM VALOR NA TABELA REFERENTE A ACRESCIMO OU DESCONTO, ELE VAI USAR O VALOR PADRAO DO PRODUTO PRESENTE NA TABELA PRODUTOS
                        $sql_busca_produto_banco_sia = $pdo->query("SELECT * FROM PRODUTOS where ID_PRODUTO = {$id_produto} and produtos.ID_EMPRESA = 1");
                        $resultado = $sql_busca_produto_banco_sia->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($resultado as $row => $registros) {
                        }
                        $valor_total = $unitario;
                    }
                }
                $sql = "INSERT INTO TMPITENS_PEDIDO (ID_PEDIDO, ID_EMPRESA, ID_PRODUTO, QTD, UNITARIO, DESCONTO, TOTAL, DADOADICIONAL, DESCRICAO, PRECOINICIAL, ID_TONALIDADE, UNITARIOBASE, EMPROMOCAO, DESPESAS_BOLETO, VENDEDOR) VALUES ({$_SESSION['id_ultimo_pedido']}, {$_SESSION['ID_EMPRESA']}, {$id_produto}, 1, {$valor_total}, '0', {$valor_total}, '', '{$descricao}', {$valor_total}, '0', {$valor_total}, 'N', '0', '{$_SESSION['usuario']}')";
                if ($db_prod->exec($sql)) {
                    $_SESSION['reinicia'] = true;
                    header('Location: ../venda.php');
                    exit();
                }
            };
        }
    }
}
