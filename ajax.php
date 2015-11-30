<?php
require 'config.php';

session_start();
//$u=$_SESSION['a'];
$logged=isset($_SESSION['a']);
$isadmin=(isset($_SESSION['l']) && $_SESSION['l']=="3");

/** Devuelve, en formato HTML-CSS en una cadena, y segùn la Planta, las acepciones 
    correspondientes al lema/sublema --$l--  suministrado. 
    Si en efecto corresponde a un sublema, $sublema deberà contener un valor no nulo
    para aplicar una variante en el sìmbolo separador de acepciones y reiniciar la numeraciòn.
**/
function _out_aceps($mysqli,$l,$sublema)
{
    $result = $mysqli->query("SELECT * from entry where parent=$l and type=3");
    $num=1;
    /* recorremos cada acepcion en el lema */
    while($row=$result->fetch_assoc()) {        
        $tt=$row['id'];
        $num=$row['number'];
        $cq="SELECT content_choice_options.content_value_abbr FROM content_choice_options INNER JOIN content ON  content_type_id = content_type AND content_choice_id = content_int WHERE entry_id =$tt and content_type=5";
        /* los sublemas utilizan otras categorias. Cargar las categorias respectivas*/
        if($sublema==1) {$cq="SELECT content_choice_options.content_value_abbr FROM content_choice_options INNER JOIN content ON  content_type_id = content_type AND content_choice_id = content_int WHERE entry_id =$tt and content_type=18";}
        
        // Se determina si hay categorìa asociada al lema
            $r1 = $mysqli->query($cq);
       $ro1=$r1->fetch_array();
       $r1->free();
       if($ro1==false) {
            // Si no, verificamos si tiene una subcategorìa.
            
            if($sublema==0) {    
                $r21 = $mysqli->query("SELECT content_choice_options.content_value_abbr FROM content_choice_options INNER JOIN content on content_choice_id = content_int WHERE entry_id =$tt and content_type=5 and content_type_id=6");
            //$html.="**SELECT content_choice_options.content_value_abbr FROM content_choice_options INNER JOIN content on content_choice_id = content_int WHERE entry_id =$tt and content_type_id=6**";
                $ro1=$r21->fetch_array();
                $r21->free();
            }
            if($ro1==false) {                
                $ro1=array("");
            }       
        }
        
        // guardamos el valor de la categorìa o subcategorìa
        if(isset($ro1[0])) {$cat=$ro1[0];}        
        if(strlen($cat)==0) {
             /** Si no tiene categorìa, determinar si ya la habìamos mostrado para la acepcion anterior    
                 Si fuera asì, no mostramos nada (la Planta supone que si ya lo escribimos, es la misma).
                 Si no hubiera una categorìa para la acepciòn anterior,debemos indicar que falta ese campo.
             **/
             if(strlen($lastcat)==0) $html.="<span style='color:gray;font-style:italic'>(falta cat. gram.)</span>";
        }        
        else {
            /** 
                Si en efecto tiene categorìa, y es la misma que la anterior, no mostrarla, 
                pues no se repiten datos.
                Si en efecto tiene categorìa y es diferente a la anterior, 
                mostrar en la vista previa la categorìa (con su formato adecuado)
                y actualizar la variable $lastcat que contiene el nombre de la ùtlima categorìa impresa
                para su uso por la siguiente acepciòn 
            **/
            if($cat==$lastcat) { }
            else {
                $html.="<span style='font-style:italic'>".$cat."</span>";
                $lastcat=$cat;
            } 
        }
                
        // mostramos el nùmero de acepción 
        $html.="&nbsp;<b>$num</b>&nbsp;";
        
        //obtenemos y mostramos las marcas 
        $r1 = $mysqli->query("SELECT content_choice_options.content_value_abbr FROM content_choice_options INNER JOIN content ON  content_type_id = content_type AND content_choice_id = content_int WHERE entry_id =$tt and content_type_id>=10 and content_type_id<>18 ");
        while($ro1=$r1->fetch_array()) {
            // las marcas se muestran en cursiva luego del número de acepciòn 
            $html.="<span style='font-style:italic'>".$ro1[0]."&nbsp;</span>";
        }
        $r1->free();
        
        // obtenemos la definicion
        $rs1 = $mysqli->query("SELECT * from content where entry_id=$tt and content_type=1");
        $ro1=$rs1->fetch_assoc();
        if($ro1['content_text']=="") 
            $html.="<span style='color:gray;'>(acepción vacía)</span>";
        else  
            $html.="<span>".$ro1['content_text']."</span>&nbsp;";       
                   
        // obtenemos el ejemplo y su fuente 
        $rs1 = $mysqli->query("SELECT * from content where entry_id=$tt and content_type=2");
        $ro1=$rs1->fetch_assoc();
        if($ro1['content_text']=="") 
            $html.="";
        else  
            $html.=":&nbsp;<span style='font-family:sans-serif'>".$ro1['content_text'].". (".$ro1['source']."). &nbsp;</span>";       

        // obtener la lista de sinònimos
        $rs1 = $mysqli->query("SELECT * from content where entry_id=$tt and content_type=17");
        $ro1=$rs1->fetch_assoc();
        if($ro1['content_text']=="") 
            $html.="";
        else  
            $html.="<span style='font-family:sans-serif'>[".$ro1['content_text']."].</span>&nbsp;";       
            
        // obtener observaciones para esta acepciòn
        $rs1 = $mysqli->query("SELECT * from content where entry_id=$tt and content_type=8");
        $ro1=$rs1->fetch_assoc();
        if($ro1['content_text']=="") 
            $html.="";
        else  
            $html.="<span style=''><b>Obs:</b>&nbsp;".$ro1['content_text'].".</span>&nbsp;";       
        
    }
    return $html;
}

/** Devuelve una cadena con HTML+CSS que representa la aplicaciòn del formato necesario para el 
    lema --$l-- suministrado. Enumera los sublemas y las acepciones correspondientes.
 **/
function preview($mysqli,$l) {
    $lastcat="";$cat="";
    $result = $mysqli->query("SELECT * from entry where id=$l");
    $row=$result->fetch_assoc();
    $result->free();
    $html="<div style='font-size:11pt;'><span style='font-weight:bold'>".htmlentities($row['head'])."</span>&nbsp;";
    //enumerar la categoria, si tuviera
    /*$result = $mysqli->query("SELECT content_choice_options.content_value_abbr FROM content_choice_options INNER JOIN content ON  content_type_id = content_type AND content_choice_id = content_int WHERE entry_id =$l");
    $row=$result->fetch_array();
    if($row==false) {
        //ver si tiene subcategoria
        $result1 = $mysqli->query("SELECT content_choice_options.content_value_abbr FROM content_choice_options INNER JOIN content on content_choice_id = content_int WHERE entry_id =$l and content_type_id=6");
        $row=$result1->fetch_array();
        if($row==false) {
            $row=array("");
        }        
    }
    $result->free();*/
    //$html.="<span style='font-style:italic'>".$row[0]."</span>";
    
    
    
    
    // Se enumeran y muestran las acepciones del lema 
    $html.=_out_aceps($mysqli,$l,0);
    
    //se recorren los sublemas y se muestran las acepciones de cada sublema
    $rt = $mysqli->query("SELECT * from entry where parent=$l and type=2");        
    while($row=$rt->fetch_assoc()) {
        $html.="&nbsp;&#9632;&nbsp;<span style='font-weight:bold'>".htmlentities($row['head'])."</span>&nbsp;";
        $html.=_out_aceps($mysqli,$row['id'],1); 
    }
    
    $html.="</div>";
    return $html;
        
}

/* inicializaciòn de base de datos */
$mysqli = new mysqli($DB['host'], $DB['user'], $DB['pass'], $DB['name'], $DB['port'], $DB['sock']);
$mysqli->set_charset("utf8");

$m=$mysqli->real_escape_string($_GET['m']); // obtener el comando suministrado desde el cliente

/** CCO - No utilizado - Junio 2015 **/
if($m=="cco") {
    $id=$mysqli->real_escape_string($_GET['id']);
    $type=$mysqli->real_escape_string($_GET['type']);
    $parentcci=$mysqli->real_escape_string($_GET['cci']);
    $result = $mysqli->query("SELECT * from content_choice_options where content_parent_value=$parentcci");        
    while($row=$result->fetch_assoc()) {
        $cci=$row['content_choice_id'];
        $cv=$row['content_value'];
        $ca=$row['content_value_abbr'];
        echo "<li class='choose'><i><span></span></i><a href='javascript:choose(this,$id,$cci,\"$ca\",$type)'>".htmlentities($cv)."</a></li>";
    }
    //$row->free();
}

/** DN - Crear un diccionario **/
if($m=="dn") {
    $p1=$mysqli->real_escape_string($_GET['p1']); // PARAMETRO: Nombre del diccionario nuevo
    $mysqli->query("insert into dictionary(name) values ('$p1')");  
}

/** DL - Obtener la lista de diccionarios **/
if($m=="dl") {
    $result = $mysqli->query("SELECT * from dictionary");        
    while($row=$result->fetch_assoc()) {
        $p2=$row['name'];
        $p1=$row['id'];        
        // el link llama a fll(..) para cargar los lemas respectivos. 
        echo "<a onclick='fll(this,$p1,\"$p2\");return false;' href='#'>$p2</a>"; 
        
    }
    // si hay usuario activo, mosttar la opcion de nuevo diccionario 
    if($logged) echo "<span id='dn'><a onclick='dni();return false;' href='#'>Nuevo diccionario</a></span>";
    //$row->free();
}

/** EN - ç (sea lema, sublema o acepciòn) **/
if($m=="en") {
    $p1=$mysqli->real_escape_string($_GET['p1']);  // PARAMETRO: ID de Diccionario
    $p2=$mysqli->real_escape_string($_GET['p2']);  // PARAMETRO: término del lema (headword)
    $p3=$mysqli->real_escape_string($_GET['p3']);  // PARAMETRO: tipo (1=lema, 2=acepcion, 3=lema)
    $p4=$mysqli->real_escape_string($_GET['p4']);  // PARAMETRO: superior jerárquico: (-1 si es lema, o el id del lema si es una acepción o sublema)
    $p5=-1;
    if(isset($_GET['p5'])) $p5=$_GET['p5'];
    $ow=$mysqli->real_escape_string($_SESSION['a']);
    $mysqli->query("insert into entry(d_id,lang,type,head,parent,number,owner) values ($p1,'es',$p3,'$p2',$p4,$p5,'$ow')");     
    echo $mysqli->insert_id; 
}

/** SE - Realizar la bùsqueda por lema **/ 
if($m=="se"){
    $p1=$mysqli->real_escape_string($_GET['p1']);  // PARAMETRO: tèrmino de bùsqueda
    $result = $mysqli->query("SELECT * from entry where head like '%$p1%' and parent=-1 order by head");        
    while($row=$result->fetch_assoc()) {
        $p1=$row['id'];
        $p2=$row['head'];
        $p3=$row['d_id'];
        echo "<li class='menu'><a href='javascript:load_d_l($p3,$p1)'>".htmlentities($p2)."</a></li>";
    }            
}

/** LL - Lista de todos los lemas para un diccionario particular **/
if($m=="ll") {
    $p1=$mysqli->real_escape_string($_GET['p1']);  // PARAMETRO: ID del diccionario
    $result = $mysqli->query("SELECT * from entry where d_id=$p1 and parent=-1 order by head");        
    while($row=$result->fetch_assoc()) {
        $p1=$row['id'];
        $p2=$row['head'];
        // el el cliente, se llama a load_l (..) para cargar el lema en pantalla 
        echo "<li class='menu'><a href='javascript:load_l($p1)'>".htmlentities($p2)."</a></li>";
    }
    //$row->free();
}

/** LELD - Genera el HTML necesario para visualizar y editar un lema **/
if($m=="leld") {
    header('Content-Type: application/json');
    //cargar detalles de lema. cargar editor de datos generales de lema, no cargar acepcio alguna aun.
    // si c=-2, cargar lema anterior, si c=-1, cargar lema siguiente al actual. actual esta een $l, dic=$d
    //ver que tipo de entradas puedo crear en esta
    //$n=$mysqli->real_escape_string($_GET['n']);
    
    /* Primero, obtener el tipo de entradas que pueden crearse */
    $r1=$mysqli->query("select * from entry_type where type_id=1");
    $rw1=$r1->fetch_assoc();    
    $pc=explode(',',$rw1['permitted_children']);
    /* y el tipo de contenido que puede colocarse */
    $pcc=explode(',',$rw1['permitted_content']);
    $r1->close();
    $c=$mysqli->real_escape_string($_GET['c']); // PARAMETRO: ID de lema
    $l=$mysqli->real_escape_string($_GET['l']); // PARAMETRO: NO UTILIZADO
    $d=$mysqli->real_escape_string($_GET['d']); // PARAMETRO: ID de diccionario
    $OUT=array();
    if($c>0){
        $result = $mysqli->query("SELECT * from entry where id=$c");
        $row=$result->fetch_assoc();
        // verificamos los permisos de edición de la entrada
        $editable=$row['owner']==$_SESSION['a'];
        
        //Vemos si es administrador. El administrador puede editar todo.
        if($_SESSION['l']=="3") {
            $editable=true;
        }
        
        // veamos si tiene el usuario bloqueado. Aunque esté ingresado, no puede editar, aun siendo el dueño de la entrada.
        if($_SESSION['l']=="0") {
            $editable=false;
        }
//        $ve=$row['owner'].'=='.$_SESSION['a'];   
        $result->free();
        /* utilizamos un arreglo JSON para enviar los datos */
        $OUT['name']=$row['head'];
        $p1=$row['id'];
        $p2=$row['head'];
        $OUT['acep']='';
        $OUT['subl']='';
        $OUT['pare']='';
        // Barra de botones: coloco en 'subl' la lista de sublemas encontrados 
        $result = $mysqli->query("SELECT * from entry where parent=$c and type=2");
        $num=1;
        while($row=$result->fetch_assoc()) {
            $tt=$row['id'];
            $num=$row['number'];
            $na=$row['head'];
            $OUT['subl'].="<a href='javascript:load_s($tt,this)' id='su$tt'>".htmlentities($na)."</a>";
            
        }
        //Barra de botones: coloco en 'acep' la lista de acepciones para este lema 
        $result = $mysqli->query("SELECT * from entry where parent=$c and type=3");
        $num=1;
        $ct=0;
        while($row=$result->fetch_assoc()) {
            $tt=$row['id'];
            $num=$row['number'];
            $ct++;
            $OUT['acep'].="<a href='javascript:load_ac($tt,this)' id='ac$tt'>$num</a>";
            
        }
        $OUT['num']=$ct;
        /* si el usuario està logueado, mostrar las opciones para crear nuevas entradas */
        if($logged){        
            $OUT['acep'].="<a href='javascript:load_ac(0,null)' id='ac_new'>Nueva acepción</a>";
            $OUT['subl'].="<a href='javascript:load_s(0,null)' id='s_new'>Nuevo sublema</a>";
            $OUT['pare'].="<a href='javascript:load_p(0,null)' id='p_new'>Nueva paremia</a>";            
        }
 
        if($logged && $editable){
            /* FORMULARIO: se crea el espacio para modificar la palabra asociada al lema 
               Se utiliza el modo save (type=10) par actualizar            
               $p1 contiene el id de lema
               $p2 contiene el texto del lema
               
               Si el usuario puede editar, ponemos el formulario
              */
            $OUT['form']="<h4>Palabra</h4><input type='text' id='l_e_head' value='$p2' onfocus='hidei(this)' onblur='save(10,this,$p1)'><i></i>";
            }            
        // FORMULARIO: Se crean los controles de selecciòn apropiados -- Junio 2015 NO SE USA 
        $result = $mysqli->query("SELECT * from content_types where parent=-1 and control=2");
        while($row=$result->fetch_assoc() && $logged && editable) {
            $id=$row['type_id'];
            if(!in_array($id,$pcc)) continue;
            $lb=$row['label'];
            $OUT['form'].= "<h4 id='h$id' data-lb='$lb'>".htmlentities($lb)."</h4><i></i>";
            $l1="<ul class='choose'>";
            $r2 = $mysqli->query("SELECT * from content_choice_options where content_type_id=$id and content_parent_value=-1");
            while($rw1=$r2->fetch_assoc()) {
                $cci=$rw1['content_choice_id'];
                $cv=$rw1['content_value'];
                $ca=$rw1['content_value_abbr'];
                $l1.="<li class='choose'><i><span></span></i><a href='javascript:choose(this,$id,$cci,\"$ca\",0 )'>".htmlentities($cv)."</a></li>";

            }
            $l1.="</ul><ul class='choose' id='s$id'></ul>";
            $OUT['form'].= "<div id='f$id' class='mc'>".htmlentities($l1)."</div>";
        }
        if($logged && !$editable){
            /*  Si el usuario no puede editar el lema, mostramos un mensaje y desactivamos las demas "opciones"
                del menú principal.*/
             $OUT['form']="<h4>No tiene permisos para modificar esta entrada</h4>";
             $OUT['acep']='';
            $OUT['subl']='';
            $OUT['pare']='';
        }
        $OUT['logged']=$logged?1:0;
        /* Se genera la vista previa del lema */
        $OUT['preview']=preview($mysqli,$p1);
    }
    echo json_encode($OUT);
}

/** SULD - Cargar editor de sublema. Funciona de forma anàloga al LELD **/
if($m=="suld") {
    header('Content-Type: application/json');
    $r1=$mysqli->query("select * from entry_type where type_id=1");
    $rw1=$r1->fetch_assoc();
    //echo "<h2>".$rw1['type_name']."</h2>";
    $pc=explode(',',$rw1['permitted_children']);
    $pcc=explode(',',$rw1['permitted_content']);
    $r1->close();
    $c=$mysqli->real_escape_string($_GET['c']); // PARAMETRO: ID de sublema
    $l=$mysqli->real_escape_string($_GET['l']); // PARAMETRO: NO UTILIZADO - Junio 2015
    $d=$mysqli->real_escape_string($_GET['d']); // PARAMETRO: ID de diccionario
    $OUT=array();
    if($c>0){
        $result = $mysqli->query("SELECT * from entry where id=$c");
        $row=$result->fetch_assoc();
        $result->free();
        $OUT['name']=$row['head'];
        $p1=$row['id'];
        $p2=$row['head'];
        $OUT['acep']='';
        //Barra de botones: se cargan las acepciones del sublema. 
        // No se varía la barra de botones de sublemas
        $result = $mysqli->query("SELECT * from entry where parent=$c and type=3");
        $num=1;
         $ct=0;
        while($row=$result->fetch_assoc()) {
            $tt=$row['id'];
            $num=$row['number'];
            $ct++;
            $OUT['acep'].="<a href='javascript:load_ac($tt,this)' id='ac$tt'>".htmlentities($num)."</a>";
            
        }
                $OUT['num']=$ct;
        if($logged){
            $OUT['acep'].="<a href='javascript:load_ac(0,null)' id='ac_new'>Nueva acepción</a>";            
        }
       // FORMULARIO: se crea el espacio para editar el términdo asociado al sublema
        if($logged) $OUT['form']="<h4>Sublema</h4><input type='text' id='l_s_head' value='$p2' onfocus='hidei(this)' onblur='save(10,this,$p1)'><i></i>";     
        $OUT['logged']=$logged?1:0;
    }
    echo json_encode($OUT);
    
}

/** ACLD - Cargar editor de acepciones **/
if($m=="acld") {
    header('Content-Type: application/json');
    $r1=$mysqli->query("select * from entry_type where type_id=3");
    $rw1=$r1->fetch_assoc();
    /* Se obtiene el tipo de contenido agregable a la acepciòn, segùn base de datos */
    $pc=explode(',',$rw1['permitted_children']);
    $pcc=explode(',',$rw1['permitted_content']);
    $r1->close();
    $c=$mysqli->real_escape_string($_GET['e']); // PARAMETRO: ID de acepción
    $l=$mysqli->real_escape_string($_GET['l']); // PARAMETRO: NO UTILIZADO - Junio 2015
    //$d=$_GET['d'];
    $OUT=array();
    if($c>0){        
        /* se obtienen los datos de la acepciòn, de la base de datos */ 
        $result = $mysqli->query("SELECT * from entry where id=$c");
        $row=$result->fetch_assoc();
        $result->free();
        $OUT['form']="";       
        
        /* Se enumeran los campos disponibles en la base de datos */
        $result = $mysqli->query("SELECT * from content_types where parent=-1 ");
        while($row=$result->fetch_assoc()) {    
            $id=$row['type_id'];    
            /* si el campo es permitido, lo colocamos en el formulario */
            if(!in_array($id,$pcc)) continue;            
            $p11='';
            $lb=$row['label']; // $lb contiene el nombre del campo            
            
            /* Si hay dato en la base de datos para la acepciòn, debemos mostrar el dato en el campo*/
            $val="onblur='save(12,this,-1,\"\")' ";
            $vals="onblur='save(13,this,-1,\"\")' ";            
            $r1=$mysqli->query("select * from content where entry_id=$c and content_type=$id");
            $ro1=$r1->fetch_assoc();
            $p11=$ro1['content_text'];
            $p12=$ro1['source'];
            $r1->free();
            if($ro1==false) {}
            else {
                $val="value='".$p11."' onblur='save(12,this,".$ro1['id'].")' ";              
                $vals="value='".$p12."' onblur='save(13,this,".$ro1['id'].")' ";
            }
            /* Si es un campo tipo textbox, se crea el control respectivo, y se asocia
               el evento blur con el comando save, para su actualizaciòn dinàmica 
               Si control=1, es un textbox normal, 
               Si control=2, es una seleccion ùnica
               Si control=3, es un campo de fuente
             */
            if($row['control']==1) $OUT['form'].= "<h4>".htmlentities($lb)."</h4><input type='text' id='t$id' onfocus='hidei(this)' $val><i></i><br/><br/>";
            if($row['control']==3) $OUT['form'].= "<h4>".htmlentities($lb)."</h4><input type='text' id='t$id' onfocus='hidei(this)' $val><i></i><br/>&nbsp;&nbsp;Fuente:<input type='text' id='s$id'  onfocus='hidei(this)' $vals><i></i ><br/><br/>";
        }
        $result->close();
        
        // Generar el cuadro de selecciòn de categorìas gramaticales y marcas
        $op="class='open'";$hi="";

        $result = $mysqli->query("SELECT * from content_types where parent=-1 and control=2");
        $html_tabs="<ul id='sidemenu'>";
        $html_content="<div id='content'>";
        $r1=$mysqli->query("select * from content where entry_id=$c and content_type=6");
        $ro1=$r1->fetch_assoc();
        $p11so=-1;
        if($ro1!=false) {
             $p11so=$ro1['content_int'];
         }
         $r1->free();
         while($row=$result->fetch_assoc()) {
            /* Para cada campo de selecciòn ùnica, enumerar las opciones disponibles 
               y 'marcar' el valor actual seleccionado en la acepcion.
             */
            $id=$row['type_id'];
            if(!in_array($id,$pcc)) continue;
            
            // obtener valor seleccionado actualmente
            $r1=$mysqli->query("select * from content where entry_id=$c and content_type=$id");
            $ro1=$r1->fetch_assoc();
            $p11=$ro1['content_int'];
            //$pid=$ro1['id'];
            $r1->free();
            $lb=$row['label'];
            /* se genera la vista de 'fichas' */
            $html_tabs.="<li><a href='#h$id' $op onclick='changetab(event,this)'><strong>".htmlentities($lb)."</strong></a></li>";
            $op="";
            $html_content.="<div id='h$id' class='contentblock $hi'>"; //<ul class='choose'>";
            $hi="hidden";
            //$OUT['form'].= "<h4 id='h$id' data-lb='$lb'>".htmlentities($lb)."</h4><i></i>";
            //$l1="<ul class='choose'>";
            $r2 = $mysqli->query("SELECT * from content_choice_options where content_type_id=$id and content_parent_value=-1 and content_choice_visible=1 ");
            
            /* enumerar las acciones, y colocarlas dentro de la ficha */
            while($rw1=$r2->fetch_assoc()) {
                $cci=$rw1['content_choice_id'];
                $cv=$rw1['content_value'];
                $ca=$rw1['content_value_abbr'];
                //$html_content.="<li class='choose'><a href='javascript:choose(this,$id,$cci,\"$ca\",10 )'>".htmlentities($cv)."</a></li>";
               $ch="";
               if($p11==$cci) $ch="checked='checked'";
               if($id==5) {
                $disp="none";
                    $html_so="";
                // ver si tiene subopciones.  
                    $r3=$mysqli->query("SELECT * from content_choice_options where content_type_id=6 and content_parent_value=$cci and content_choice_visible=1 ");      
                    while($rw3=$r3->fetch_assoc()) {
                         $cci3=$rw3['content_choice_id'];
                         $cv3=$rw3['content_value'];
                         $ca3=$rw3['content_value_abbr'];
                           $chs="";
                           if($p11==$cci3) {$chs="checked='checked'";$disp="block";}
                         $html_so.="<label style='display:block;'><input type='radio' value='$cci3' $chs id='r$cci3' onclick='choose(11,this,5)'  name='c5'><span class='lbl padding-8' >".htmlentities($cv3)."</span></label>";
                         
                    }
                    if($html_so=="")  
                         $html_content.="<label style='display:block;'><input type='radio' $ch value='$cci' id='r$cci' name='c$id' onclick='choose(11,this,$id)' ><span class='lbl padding-8'>".htmlentities($cv)."</span></label>";
                    else $html_content.="<a href='#' style='display:block' onclick='expand(\"so$cci\")'>$cv</a><div id='so$cci' style='display:$disp;'>$html_so</div>";
                
                
               } else { // mostrar opcion asi como esta. Es un radiobutton
                    /* $ch indica si estaba marcado o no por el contenido actual de la acepcion
                       $cci es el valor numerìco de la opcion
                       $id es el nùmero del campo para su actualizaciòn en la base de datos
                       el comando choose() en cliente guarda el dato generado.
                    */
                   $html_content.="<label style='display:block;'><input type='radio' $ch value='$cci' id='r$cci' name='c$id' onclick='choose(11,this,$id)'>";
                   $html_content.="<span class='lbl padding-8'>".htmlentities($cv)."</span></label>";
               }               

            }
            //$html_content.="</ul><ul class='choose' id='s$id'></div>";
            $html_content.="</div>";
            //$l1.="</ul><ul class='choose' id='s$id'></ul>";
            //$OUT['form'].= "<div id='f$id' class='mc'>".htmlentities($l1)."</div>";
            
        }   
        $html_tabs.="</ul>";
        $html_content.="</div>";
        $OUT['form'].=$html_tabs.$html_content;
        
        /* al cargar detalles de acepcion, no se muestra la vista previa, ya se cargò con el lema*/


    }
    echo json_encode($OUT);
    //salida json name=lema lid=id  form=<form> preview=vista previa lema completo
}

/** UC - NO SE UTILIZA - Junio 2015**/
if($m=="uc") {
    //update from chooser
    //{'m':'uc','c':'t'+id,'v':cci,'t':11,'e':l_id}    
    //$id=$mysqli->real_escape_string($_GET['id']);
    $eid=$mysqli->real_escape_string($_GET['e']);
    $lemma=$mysqli->real_escape_string($_GET['l']);
    $v=$mysqli->real_escape_string($_GET['v']);
    $t=$mysqli->real_escape_string($_GET['t']);
    $c=$mysqli->real_escape_string(substr($_GET['c'],1));
    if($t==11){
        $res=$mysqli->query("select * from content where entry_id=$eid and content_type=$c");
        $row=$res->fetch_assoc();
        if($row==false) $q="insert into content(entry_id,lang,content_type,content_int) values($eid,'es',$c,$v)";
        else $q="update content set content_int=$v where entry_id=$eid and  content_type=$c";
        $mysqli->query($q);    
    }    
    echo preview($mysqli,$lemma);
}

/** U - Se llama desde el cliente para actualizar un valor de texto **/
if($m=="u") {
   //update from text box. ajax.php',{'m':'u','id':id,'v':input.value,'t':type,'c':input.id} 10=head 12=content
    $id=$mysqli->real_escape_string($_GET['id']); // PARAMETRO: ID de entrada (lema, sublema o acepción)
    $eid=$mysqli->real_escape_string($_GET['e']); // PARAMETRO: NO SE UTILIZA - Junio 2015
    $aid=$mysqli->real_escape_string($_GET['a']); // PARAMETRO: ID de acepción
    $v=$mysqli->real_escape_string($_GET['v']); // PARAMETRO: texto a colocar
    $t=$mysqli->real_escape_string($_GET['t']); // PARAMETRO: tipo de actualización
    $c=$mysqli->real_escape_string(substr($_GET['c'],1)); // PARAMETRO: número de campo
    if($t==10) {
        /* Actualizamos el valor de HEAD (palabra del lema) para la entrada indicada */
        $mysqli->query("update entry set head='$v' where id=$id");        
    }
    else if($t==12) {
        //Queremos actualizar/crear el campo content_text de la acepciòn suministrada
        $q="update content set content_text='$v' where id=$id";
        if($id==-1) {
            $q="insert into content(entry_id,lang,content_type,content_text) values($aid,'es',$c,'$v')";
        }        
        $mysqli->query($q);        
    }
    else  if($t==13) {
        // Queremos actualizar/crear el campo content_source de la acepciòn suministrada
        $q="update content set source='$v' where id=$id";
        if($id==-1) {
            $q="insert into content(entry_id,lang,content_type,source) values($aid,'es',$c,'$v')";
        }        
        $mysqli->query($q);        
    }
    
    
    /* En todo caso, hubo un cambio y debemos actualizar la vista previa */
    echo preview($mysqli,$eid);    
}

/** UR - Actualiza desde casilla de selecciòn ùnica (categorìas y marcas ) **/
if($m=="ur") {
    $eid=$mysqli->real_escape_string($_GET['e']); // PARAMETRO: NO SE UTILIZA - Junio 2015
    $aid=$mysqli->real_escape_string($_GET['a']); // PARAMETRO: ID de acepción
    $v=$mysqli->real_escape_string($_GET['v']);// PARAMETRO: valor a colocar
    $t=$mysqli->real_escape_string($_GET['t']);// PARAMETRO: NO SE UTILIZA - Junio 2015
    $c=$mysqli->real_escape_string($_GET['c']);// PARAMETRO: Número de campo
    
    //intentamos actualizar
    $qu="update content set content_int=$v where entry_id=$aid and content_type=$c";
    $mysqli->query($qu);  
    if($mysqli->affected_rows==0) {
        // si no se pudo, es que el dato no existe y debemos crearlo 
        $qi="insert into content(entry_id,lang,content_type,content_int) values($aid,'es',$c,'$v')";
         $mysqli->query($qi);          
    }
    echo preview($mysqli,$eid);    
}

/** UL - Genera un listado de usuarios del sistema, para su posible edición. Sólo para administradores **/
if($m=="ul" && $isadmin)
{

    $result = $mysqli->query("SELECT * from users order by name");        
    while($row=$result->fetch_assoc()) {
        $p1=$row['login'];
        $p2=$row['name'];
        if(strlen($p2)==0) {$p2="Sin nombre";}
        $p3=$row['lastname'];     
        if(strlen($p3)==0) {$p3="Sin apellido";}
        echo "<li class='menu'><a href='javascript:editu(\"$p1\")'>".htmlentities($p2)." ".htmlentities($p3)." (<i>".htmlentities($p1)."</i>)</a></li>";
    }  
}

/** UE - Plantilla para editar los datos de un usuario **/
if($m=="ue" && $isadmin)
{
    $login=$mysqli->real_escape_string($_GET['l']); // PARAMETRO: LOGIN del usuario a editar - Octubre 2015
    
    //ver si existe. Si No, lo creamos.
    $result = $mysqli->query("SELECT count(*) from users where login='$login'"); 
    $row=$result->fetch_array();
    
    if($row[0]==0){
        $mysqli->query("insert into users(login,level,password) values ('$login',0,SHA1('INLEXPO2015'))"); 
    } 
    $result->free();
    
    //cargamos los datos para editar
    $result = $mysqli->query("SELECT * from users where login='$login'");        
    while($row=$result->fetch_assoc()) {
        $p1=$row['login'];
        $p2=$row['name'];
        $p3=$row['lastname'];
        $p4=$row['email'];
        $p5=$row['level'];     
        $s0=$p5=='0'?" selected='selected'":""; 
        $s1=$p5=='1'?" selected='selected'":"";
        $s2=$p5=='2'?" selected='selected'":"";
        $s3=$p5=='3'?" selected='selected'":"";
        // Creamos un formulario para editar la información de los usuarios 
        echo "<h3>Editando accesos para <span style='font-family:monospace'>".htmlentities($p1)."</span></h3>";
        echo "<h4>Nombre</h4><input type='text' id='t2' onfocus='hidei(this)' value='$p2' onblur='saveu(this,
\"$p1\")'><i></i><br/><br/>";
        echo "<h4>Apellido</h4><input type='text' id='t3' onfocus='hidei(this)' value='$p3' onblur='saveu(this,\"$p1\")'><i></i><br/><br/>";
        echo "<h4>Correo electrónico</h4><input type='text' id='t4' onfocus='hidei(this)' value='$p4' onblur='saveu(this,\"$p1\")'><i></i><br/><br/>";
        echo "<h4>contraseña</h4><input type='password' id='t0' onfocus='hidei(this)' value='' onblur='saveu(this,\"$p1\")'><i></i><br/><br/>";
        // Creamos el "dropdown" para permisos.
        echo "<h4>Permisos</h4>";
        echo "<select id='t5' onfocus='hidei(this)' onchange='saveu(this,\"$p1\")'>";
        echo "<option value='0' $s0> Cuenta deshabilitada</option>";
        echo "<option value='1' $s1> Usuario registrado </option>";
        echo "<option value='2' $s2> Autor de entradas</option>";
        echo "<option value='3' $s3> Administrador </option>";
        echo "</select><i></i>";
    }  
}

/** US - Guarda un campo en la tabla usuarios. Sólo para administradores.  **/
if($m=="us" && $isadmin)
{
    $login=$mysqli->real_escape_string($_GET['l']); // PARAMETRO: LOGIN del usuario a editar - Octubre 2015
    $v=$mysqli->real_escape_string($_GET['v']); // PARAMETRO: valor del campo - Octubre 2015
    $c=$mysqli->real_escape_string($_GET['c']); // PARAMETRO: campo a editar - Octubre 2015
    $field="";
    if($c=="t2") {$field="name"; $v="'$v'";}
    if($c=="t3") {$field="lastname"; $v="'$v'";}
    if($c=="t4") {$field="email"; $v="'$v'";}
    if($c=="t0") {$field="password";$v="SHA1('$v')"; }
    if($c=="t5") {$field="level"; $v="$v";}
    if(strlen($field)>1) {
        $mysqli->query("update users set $field=$v where login='$login'");              
    }
}

/** COMANDO 8159. Utilizado por la rutina de importación para almacenar un cambio en la tabla temporal **/
if($m=="8159" && $isadmin) 
{
    $i=intval(substr($mysqli->real_escape_string($_GET['i']),1));
    $t=intval($mysqli->real_escape_string($_GET['t']));
    $mysqli->query("update import_temp set type=$t where id=$i");      
}


?>
