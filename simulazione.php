<?php
require('mysql/db_define_mysqli.php');

session_start();

//Carico automaticamente il codice documento incrementato di uno dal DB.
$sql = "SELECT id_simulazione FROM tb_dati_t ORDER BY id_simulazione DESC LIMIT 1";
$result = $db->query($sql);

if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {
        $id_simulazione = (int)substr($row["id_simulazione"], -5);
    }

    $nuovo_id_integer = $id_simulazione + 1;
    $anno = substr((string)date("Y"), 2);
    $nuovo_id = 'CF' . $anno . substr('00000' . (string)$nuovo_id_integer, -5);

} else {
    $anno = substr((string)date("Y"), -2);
    $nuovo_id = 'CF' . $anno . '00001';

    $db->close();

}

?>
<!doctype html>
<html>
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <meta http-equiv="content-type" content="text/html"/>

    <script type="text/javascript" src="js/jquery_331.js"></script>
    <title>Inserimento dati</title>

    <!--CSS -->
    <link rel="stylesheet" type="text/css" href="css/form.css">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">

</head>

<body>

<div id="contenitore">
    <header>
        <div id="logoGEN"><img src="img/gen.png" width="250px" alt="Generali"/></div>
        <div id="logoCF"><img src="img/cf.png" width="250px" alt="Generali"/></div>
    </header>

    <div id="row">
        <div class="col-md-3 col-xs-offset-9">
            <label class="btn btn-sm btn-info btn-block" onClick="VaiAPagina('../trattativa/menu.php')">
                <i class="glyphicon glyphicon-menu-hamburger"></i>
                <span id="btnMenu">Torna a Menù</span>
            </label>
        </div>
    </div>
    </br>
    </br>

    <div id="contenuto">

        <form class="well form-horizontal" method="post" id="contact_form">
            <fieldset>

                <!-- Form Name -->
                <legend>Vivere di rendita</legend>

                <div class="form-group">
                    <label class="col-md-9 control-label">Cod. Documento</label>
                    <div class="col-md-3 inputGroupContainer">
                        <div class="input-group">
                            <span id="recupero" onClick="caricaDoc();" role="button"
                                  title="Dopo aver indicato un documento valido, cliccare qui per caricare i dati"
                                  class="input-group-addon"><i class="glyphicon glyphicon-tags"></i></span>
                            <input id="id_simulazione" name="id_simulazione" placeholder="Codice" class="form-control"
                                   type="text" maxlength="9" value="<?php echo $nuovo_id; ?>"
                                   onkeypress="return soloLettereMaiuscENumeri(event);">
                        </div>
                    </div>
                </div>
                <br>

                <!-- Text input-->

                <div class="form-group">
                    <label class="col-md-2 control-label">Nome</label>
                    <div class="col-md-4 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                            <input id="nome" placeholder="Inserire il Nome" class="form-control" type="text" autofocus>
                        </div>
                    </div>
                    <label class="col-md-2 control-label">Cognome</label>
                    <div class="col-md-4 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                            <input id="cognome" placeholder="Inserire il Cognome" class="form-control" type="text">
                        </div>
                    </div>
                </div>

                <!-- Text input-->

                <div class="form-group">
                    <label class="col-md-2 control-label">E-Mail</label>
                    <div class="col-md-7 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                            <input id="email" placeholder="Inserire l&acute;email" class="form-control" type="text">
                        </div>
                    </div>
                    <label class="col-md-1 control-label">Et&agrave;</label>
                    <div class="col-md-2 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                            <input id="eta" class="form-control" type="text" maxlength="2"
                                   onkeypress="return soloNumeri(event);">
                        </div>
                    </div>
                </div>

                <!-- Text input-->

                <div class="form-group">
                    <label class="col-md-2 control-label">% Decumulo</label>
                    <div class="col-md-2 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-pushpin"></i></span>
                            <input id="percdecum" class="form-control" type="text" value="5.0" maxlength="4"
                                   onkeypress="return soloPercentuali(event);">
                        </div>
                    </div>
                    <label class="col-md-3 control-label">% Interesse Decumulo</label>
                    <div class="col-md-2 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-pushpin"></i></span>
                            <input id="percinterdecum" class="form-control" type="text" value="2.5" maxlength="4"
                                   onkeypress="return soloPercentuali(event);">
                        </div>
                    </div>
                    <label class="col-md-1 control-label">Scadenza</label>
                    <div class="col-md-2 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                            <input id="etascadenza" class="form-control" type="text" maxlength="2"
                                   onkeypress="return soloNumeri(event);">
                        </div>
                    </div>
                </div>

                <!-- Text input-->

                <div class="form-group">
                    <label class="col-md-2 control-label">Importo Decumulo</label>
                    <div class="col-md-3 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-pushpin"></i></span>
                            <input id="importodecum" name="importodecum" class="form-control" type="text" value="0.00"
                                   maxlength="12" onkeypress="return soloValuta(event);"
                                   onChange="ElaboraImpPerc('importo');">
                        </div>
                    </div><!--
                <div class="col-md-1">
                    <button type="button" id="aggiorna" name="aggiorna" class="btn btn-group btn-group-sm" title="Cliccare per aggiornare i dati"><span class="glyphicon glyphicon-refresh" onClick="ElaboraImpPerc('percentuale');"></span></button>
              </div>-->
                </div>

                <br/>

                <!-- Button-->

                <div class="form-group">
                    <label class="col-md-2 control-label"></label>
                    <div class="col-md-1">
                        <button type="button" id="salva" name="salva" class="btn btn-warning btn-lg"
                                title="Cliccare per salvare i dati">Salva <span
                                    class="glyphicon glyphicon-floppy-disk"></span></button>
                    </div>
                    <label class="col-md-3 control-label"></label>
                    <div class="col-md-1">
                        <button type="button" id="anteprima" name="anteprima" class="btn btn-info btn-lg"
                                title="Cliccare per visualizzare i dati in anteprima">Anteprima <span
                                    class="glyphicon glyphicon-zoom-in"></span></button>
                    </div>
                    <label class="col-md-2 control-label"></label>
                    <div class="col-md-1">
                        <button type="button" id="sviluppa" name="sviluppa" class="btn btn-info btn-lg"
                                title="Cliccare per inviare i dati via email al cliente">Invia Email <span
                                    class="glyphicon glyphicon-envelope"></span></button>
                    </div>
                </div>

            </fieldset>
        </form>

        <div class="col-md-6">
            <div class="card-title">
                <h3 class="text-center">Premio annuo (PA)</h3>
            </div>
            <button id="btnAddSx" type="submit" class="btn btn-lg btn-success btn-block">
                <i class="glyphicon glyphicon-plus"></i>&nbsp;
                <span id="payment-button-amount">Aggiungi Piano</span>
            </button>
            <table id="tblDataSx">
                <thead>
                <tr>
                    <th width="85px">N&ordm; ANNI</th>
                    <th width="100px">IMPORTO</th>
                    <th width="80px">INTER %</th>
                    <th width="85px">COSTO %</th>
                    <th width="80px"></th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <div class="card-title">
                <h3 class="text-center">Premio unico (PU)</h3>
            </div>
            <button id="btnAddDx" type="submit" class="btn btn-lg btn-success btn-block">
                <i class="glyphicon glyphicon-plus"></i>&nbsp;
                <span id="payment-button-amount">Aggiungi Piano</span>
            </button>
            <table id="tblDataDx">
                <thead>
                <tr>
                    <th width="85px">ANNO</th>
                    <th width="100px">IMPORTO</th>
                    <th width="80px">INTER %</th>
                    <th width="85px">COSTO %</th>
                    <th width="80px"></th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>

    </div><!--fine content -->

    <footer>&copy; Centrofriuli 2019 - Tutti i diritti sono riservati
    </footer>

</div><!--fine contenitore -->

<!--javascript -->
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/funzioni_table_sx.js"></script>
<script type="text/javascript" src="js/funzioni_table_dx.js"></script>
<script type="text/javascript" src="js/funzioni_mysql.js"></script>
</body>
</html>
