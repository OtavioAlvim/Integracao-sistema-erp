<script>
function ClosePrint() {
      setTimeout(function () { window.print(); }, 500);
      window.onfocus = function () { 
        setTimeout(function () {
            window.location.href = "../menu.php"; 
        }, 500); 

    }
}
</script>

<?php

include('../DB/conexao.php');
include('../login/verifica_login.php');
$db_prodd = new PDO('sqlite:../DB/' . $_SESSION['cnpj'] . '/DB-SISTEMA/' . $_SESSION['ID'] . '');


$inicio = $_POST['inicio'];
$fim = $_POST['fim'];

if ($fim < $inicio) {
    // echo "periodo invalido";
} else {
    // echo "periodo valido";
}




$busca_total = $db_prodd->query("SELECT ID_FORMAPGTO,FORMA_PAG,sum(total) as total,VENDEDOR from TMPPEDIDOS where DATA_PESQ BETWEEN '{$inicio}' and '{$fim}' GROUP by FORMA_PAG");
$resultado_busca = $busca_total->fetchAll(PDO::FETCH_ASSOC);

foreach ($resultado_busca as $row => $total) {
}
?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/resumo_diario.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100&display=swap" rel="stylesheet">
    <title>RESUMO DIARIO</title>
</head>

<body onload="ClosePrint()">
    <div class="cupom">
        <br>
        <div class="informa">
            <h3 id="tel">RESUMO DIARIO</h3>
            <h3 id="tel">VENDEDOR :<?php echo $total['VENDEDOR'] ?></h3>
        </div>
        <p id="linha">--------------------------------------------------------------</p>
        <div class="container">
            <div class="informacoes">
                <?php
                foreach ($resultado_busca as $row => $total) { ?>
                    <p><?php echo $total['FORMA_PAG'] ?></p>
                <?php }
                ?>
            </div>
            <div class="valores">
                <?php
                foreach ($resultado_busca as $row => $total) { ?>
                    <p>R$ <?php echo number_format($total['total'],2,',',' ') ?></p>
                <?php }
                ?>



            </div>


        </div>
        <br>
        <p id="linha">--------------------------------------------------------------</p>
        <div class="finaliza">
            <h5 id="volte_sem">PARABENS PELO TRABALHO :D !</h5>
        </div>
        <p id="linha">--------------------------------------------------------------</p>
    </div>
</body>

</html>