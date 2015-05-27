<!DOCTYPE html>
<?= $Head ?>

<body class="skin-blue">
<link rel="stylesheet" href="<?= base_url() ?>public/js/DropDown/docsupport/prism.css">
<link rel="stylesheet" href="<?= base_url() ?>public/js/DropDown/chosen.css">
<script src="<?= base_url() ?>public/js/DropDown/chosen.jquery.js" type="text/javascript"></script>
<script src="<?= base_url() ?>public/js/DropDown/docsupport/prism.js" type="text/javascript" charset="utf-8"></script>
<script src="<?= base_url() ?>public/js/DropDown/docsupport/combo.js"></script>
<!--Spin.js-->
<script src="<?= base_url() ?>public/plugins/spin/spin.min.js"></script>
<!--Jvalidator-->
<script src="<?= base_url() ?>public/plugins/Jvalidator/Jvalidator.js"></script>
<link rel="stylesheet" href="<?= base_url() ?>public/plugins/Jvalidator/Jvalidator.css">
<!-- iCheck for checkboxes and radio inputs -->
<link href="<?= base_url() ?>public/plugins/iCheck/square/purple.css" rel="stylesheet" type="text/css"/>
<script src="<?= base_url() ?>public/plugins/iCheck/icheck.js"></script>

<script src="<?= base_url() ?>public/plugins/priceFormat/priceFormat.js"></script>


<div class="wrapper">
    <?= $Header ?>
    <!-- Left side column. contains the logo and sidebar -->
    <?= $Sidebar ?>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1 style="color:#3c8dbc;text-align: center;margin-top: 20px;"><span style="font-size: 25pt;"
                                                                                 class="ios ion-ios-paper"></span>
                Informe Acreedor
            </h1>
        </section>
        <!-- Main content -->
        <div class="container">
            <form method="post" class="form-horizontal col-md-8" role="form" action="ImprimeInformeAcreedor" style="margin-left: 20%;" >
                <hr style="border: 1px solid #3c8dbc;"/>
                <br>

                <div class="form-group">
                    <label class="col-lg-3 control-label">Desde</label>
                        <div class="col-lg-3">
                    <input type="date" name="DESDE" value="<?= date('Y-m-d')?>" class="form-control"/>
                        </div>
                    <label class="col-lg-1 control-label">Hasta</label>

                    <div class="col-lg-3">
                    <input type="date" name="HASTA" value="<?= date('Y-m-d')?>"  class="form-control"/>
                        </div>
                    </div>

                    <div class="form-group">
                    <label class="col-lg-3 control-label">Acreedor:</label>

                    <div class="col-lg-9">
                        <select name="ID_ACREEDOR" class="form-control chosen-select"
                                style="width:320px;">
                            <?= $Acreedores ?>
                        </select>
                    </div>
                </div>

                <br>
                <!--Envíar-->
                <div class="form-group">
                    <div class="col-lg-offset-5 col-lg-10">
                        <button type="submit" class="btn btn-success btn-lg"><span
                                class="glyphicon glyphicon-print"></span>&nbsp; Imprimir
                        </button>
                    </div>
                </div>
                <div id="spin" style="text-align: center;position: static;"></div>
            </form>
        </div>
        <br/><br/>
    </div>
</div>

<?= $Footer ?>

</body>
</html>