<?php 
session_start();
$logged=isset($_SESSION['a']);
$isadmin=(isset($_SESSION['l']) && $_SESSION['l']=="3");
?>
<!DOCTYPE html>
<html>
    <head>
        
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width"> 
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="style.css"/>
        <script src="js/jquery-2.1.3.min.js" ></script>
        <script src="ui.js" ></script>
        <script>
            
            var ld;
            var d_id=-1;
            var div_p;
            var div_e;
            var ac_active,ac_next,ac_prev;
            var l_next,l_active,l_prev;
	var l_id=1, sl_id=-1,a_id=-1,p_id=-1;
	var d_n="",l_n="",sl_n="";            
            $(document).ready(function() {
                //link obiects
                ld=document.getElementById('tl');
                div_p=document.getElementById('p');
                div_e=document.getElementById('e');
                //ac_active=document.getElementById('ac_active');
                //ac_next=document.getElementById('ac_next');
                //ac_prev=document.getElementById('ac_prev');
                l_active=document.getElementById('l_active');
                l_prev=document.getElementById('l_prev');
                l_next=document.getElementById('l_next');
                $(div_p).hide();
                //link events                  
                $("#tabs li").click(function() {
                    $("#tabs li.sel").removeClass("sel");
                    $(this).addClass("sel");
                    //show the right tb
                    var f=$(this).data("f");
                    $("#cmd div.bar").hide();
                    $("#"+f).show();
                } );
                //call initializers

                fld();
            });
        </script>
    </head>
    <body>
        <nav id="tabs">
            <img src="inlexpo.png">
            <form method='post' action='log.php'>
                <?php if($logged) { $UNAME=$_SESSION['n'];
                    echo "$UNAME <button id='logout'>Salir</button>            ";
                } else {?>
                <input type="hidden" name="key" value="<?php echo date('Ymd'); ?>">
                <input type="text" name="k0" placeholder="usuario"/>
                <input type="password" name="k7" placeholder="contraseña"/>    
                <button id="login">Entrar</button>           
                <?php } ?>
            </form>
            <ul>
                <li data-f="tl">Diccionarios <i class="fa fa-caret-down"></i> </li>
                <li data-f="t0" class="sel">Buscar</li>
                <?php if($logged)  { ?>
                <li data-f="t1">Lema</li>
                <li data-f="t2">Sublema</li>
                <!-- <li data-f="t3">Paremia</li>  -->
                <li data-f="t4">Acepción</li>
                <li data-f="t5" id="rt5">Exportar entradas</li>
                <?php if($logged)  { ?>
                <li data-f="t6" id="rt6">Usuarios</li>
                <?php  } ?>
                <?php  } ?>
                <!--<li>Ejemplo</li>
                <li>Traducción</li>
                <li>Vista previa y respaldos</li> -->
            </ul>
        </nav>
        <nav id="cmd">
            <div id="tl" class="bar">
                
            </div>
            <div id="t0" class="bar" style="display:block;">
                <input type="text" id="search"/>
                <a href="javascript:do_search()">Buscar</a>
            </div>
            <div id="t1" class="bar">    
                <!--<a href="javascript:load_l(-2)" id="l_prev"><i class="fa fa-backward"></i></a> -->
                <a href="javascript:load_l(l_id)" id="l_active">Lema activo: no hay lemas</a>
                <!--<a href="javascript:load_l(-1)" id="l_next"><i class="fa fa-forward"></i></a>                -->
                <a href="javascript:load_l(0)">Nuevo lema</a>
            </div>            
            <div id="t2" class="bar">    
                <a href="javascript:load_s(-2)" id="s_prev"><i class="fa fa-backward"></i></a>
                <a href="javascript:load_s(s_id)" id="s_active">Sublema activo: no hay sublemas</a>
                <a href="javascript:load_s(-1)" id="s_next"><i class="fa fa-forward"></i></a>                
                <a href="javascript:load_s(0)">Nuevo sublema</a>
            </div>            
            <!--<div id="t3" class="bar">    
                <a href="javascript:load_p(-2)" id="p_prev"><i class="fa fa-backward"></i></a>
                <a href="javascript:load_p(p_id)" id="p_active">Paremia activa: no hay paremias</a>
                <a href="javascript:load_p(-1)" id="p_next"><i class="fa fa-forward"></i></a>                
                <a href="javascript:load_p(0)">Nueva paremia</a>
            </div>    -->
            <div id="t4" class="bar">    
                <a href="javascript:load_ac(-2)" id="ac_prev"><i class="fa fa-backward"></i></a>
                <a href="javascript:load_ac(a_id)" id="ac_active">Acepción activa: no hay acepciones</a>
                <a href="javascript:load_ac(-1)" id="ac_next"><i class="fa fa-forward"></i></a>                
                <a href="javascript:load_ac(0)" id="ac_new" >Nueva acepción</a>               
            </div>            
             <div id="t5" class="bar">    
                <a href="javascript:exportar('pdf')"><i class="fa fa-file-pdf-o"></i> Exportar como PDF</a>
                <a href="javascript:exportar('doc')"><i class="fa fa-file-word-o"></i> Exportar para Word</a>
                <a href="javascript:exportar('html')"><i class="fa fa-globe"></i> Exportar como HTML</a>
                <a href="javascript:exportar('latex')"><i class="fa fa-code"></i> Exportar Latex</a>
            </div>            
            <div id="t6" class="bar">    
                <a href="javascript:listau()">Ver lista de usuarios</a>
                <a href="javascript:nuevou()">Nuevo usuario</a>
            </div>
            
            
        </nav>
        <div id="z0">
            <div id="e">&nbsp;
                <!--Definición: <br>
                <input type="text" size="30" value="certificado otorgado a persona"/>
                <br><br>
                Ejemplo:  <br>
                <input type="text" size="30" value="Tengo un ~ de ingeniero"/>
                <br><br> --> 
            </div>
            <div id="p">
            <span class="p00">título</span> <span class="p01">1.</span> <span class="p02">(sust.)</span> <span class="p03">certificado otorgado a persona:</span> <span class="p04">Tengo un <u>título</u> de ingeniero</span></div>
        </div>
    </body>
</html>
