<?php
require 'config.php';

session_start();
$logged=isset($_SESSION['a']);
$mysqli = new mysqli($DB['host'], $DB['user'], $DB['pass'], $DB['name'], $DB['port'], $DB['sock']);
$mysqli->set_charset("utf8");

function _out_aceps($mysqli,$l,$sublema)
{
    $html="";
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
    $html="<div style='font-size:11pt;'><span style='font-weight:bold'>".$row['head']."</span>&nbsp;";
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
        $html.="&nbsp;&#9632;&nbsp;<span style='font-weight:bold'>".$row['head']."</span>&nbsp;";
        $html.=_out_aceps($mysqli,$row['id'],1); 
    }
    
    $html.="</div>";
    return $html;
        
}


if(!$logged) {
    header('Location: index.php');
    die();
}
$json=file_get_contents("dict.json");
$CA=json_decode($json);

function rtgl($s)
{
    $h=trim($s);
    return substr($h, -1); 
}


function spaces($s){
    $s1=trim($s);
    if(strlen($s1)==0)return str_replace(' ','·',$s);
    else
    //return str_replace(' ','·',$s);
    return $s;
}
?>
<!DOCTYPE html>
<html>
    <head>
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="style.css"/>
        <script src="js/jquery-2.1.3.min.js" ></script>
        <style>      
        .e00 { background-color: white;color:black;}  
        .active {color:red;}
        .e00:hover { background-color: #d0f3ee;}  
        #e {float:left;width:39%;font-family:serif;}
        #p {width:39%;float:left; padding:10px;}
        #l {float:left;width:19%;}
        #cmd a.hl {background-color: #d3d4d4;color:black;}
        #l a {
            display:block;
            width:100%;
            height:20px;
            line-height:20px;
            padding-left:16px;
            text-decoration:none;
            color:black;
            background-color:white;
           }
        #l a:hover {
            background-color:#d3d4d4;
        }
        </style>
        <script>
            var current, first, last;  
            $(document).ready(function(){
               $(".e00").click(function(){
                    if(current!=null)$(current).removeClass('active');
                    current=this;                    
                    $("#fid").text("ID: "+current.id);
                    $(current).addClass('active');                          
                        $(".ft").removeClass('hl');
                        var d=$(current).data("type");
                        $("#ft"+d).addClass('hl');  
                                      
                       
               }); 
            });
            function settw(t) {
                /*if(current!=null) {
                    $(".ft").removeClass('hl');
                    $(current).data("type",t);
                    $("#ft"+t).addClass('hl'); 
                    $.get('ajax.php',{m:'8159',i:current.id,t:t}, function(data)  { });     

                }*/
            }
            function nextw(i) {
                if(current==null) {
                    first=document.getElementById("e").firstChild;
                    last=document.getElementById("e").lastChild;
                    current=first;
                    $("#fid").text("ID: "+current.id);
                    $(current).addClass('active');                           
                        $(".ft").removeClass('hl');
                        var d=$(current).data("type");
                        $("#ft"+d).addClass('hl');                     
                    //$("#s_prev").hide();                    
                }
                else {
                    $(current).removeClass('active');
                    if(i==-1 && current!=first) {
                        current=current.previousSibling;
                        $("#fid").text("ID: "+current.id);
                        $(current).addClass('active');       
                        $(".ft").removeClass('hl');
                        var d=$(current).data("type");
                        $("#ft"+d).addClass('hl');
                        /*if(last==current) $("#s_next").hide();
                        else  $("#s_next").show();
                        if(first==current) $("#s_prev").hide();
                        else  $("#s_prev").show();*/
                    }
                    if(i==1 && current!=last) {
                        current=current.nextSibling;
                        $(current).addClass('active');   
                        $("#fid").text("ID: "+current.id);
                        $(".ft").removeClass('hl');
                        var d=$(current).data("type");
                        $("#ft"+d).addClass('hl');                 
                        /*if(last==current) $("#s_next").hide();
                        else  $("#s_next").show();
                        if(first==current) $("#s_prev").hide();
                        else  $("#s_prev").show();*/

                    }
                }
            }
        </script>
<meta charset="utf-8"/>
    </head>
    <body>
    <body>
        <nav id="tabs">
            <img src="inlexpo.png">
            </nav>            
               <nav id="cmd">
                <div id="t2" class="bar" style='display:block;padding-left:200px;'>    
                <a href="javascript:nextw(-1)" id="s_prev"><i class="fa fa-backward"></i></a>
                <a href="javascript:nextw(1)" id="s_next"><i class="fa fa-forward"></i></a>                
                <a href="javascript:settw(1)" class="ft" id="ft1">HW</a>
                <a href="javascript:settw(2)" class="ft" id="ft2">MA</a>
                <a href="javascript:settw(3)" class="ft" id="ft3">SL</a>
                <a href="javascript:settw(10)" class="ft" id="ft10">MSL</a>                                
                <a href="javascript:settw(4)" class="ft" id="ft4">AC</a>
                <a href="javascript:settw(5)" class="ft" id="ft5">DE</a>
                <a href="javascript:settw(6)" class="ft" id="ft6">EJ</a>
                <a href="javascript:settw(7)" class="ft" id="ft7">SI</a>
                <a href="javascript:settw(8)" class="ft" id="ft8">OB</a>                
                <a href="javascript:settw(9)" class="ft" id="ft9">(saltar)</a>
                <a href="javascript:nextw(0)" class="ft" id="fid"></a>
            </div>
            </nav>
            <div id="z0">
            <div id="l">
            <?php
                //enumerar las palabras
                $c=1;
                $cd=0;
                for($i=2;$i<count($CA);$i++)
                {
                    $i2b=is_bool($CA[$i-2][0]);
                    $i1b=is_bool($CA[$i-1][0]);
                    $i0s=is_string($CA[$i][0]);    
                    if($i2b && $i1b && $i0s) {
                        $w=$CA[$i][0];
                        //exists in diccionary? 
                        $rs2=$mysqli->query("select * from entry where d_id=2 and parent=-1 and head='$w'");
                        $ro2=$rs2->fetch_assoc();
                        $rs2->free();
                        $style="";
                        if($ro2!=false) { $style="font-weight:bold;"; $cd++;}
                        else {
                        echo "<a href='load.php?w=" . htmlentities($i) . "' style='$style'>$c. " . htmlentities($w) . "</a>";
                        }
                        $c++;
                    }
                }
                echo "<br>$cd de $c";
                ?>
            </div>
            <div id="e">
                <?php 
                if(isset($_GET['w'])) {
    // mostrar la entrada.
    $_marca=""; $_acep="";
    $i=$mysqli->real_escape_string($_GET['w']);
    $NEXT1="";
    $NEXT="HEADWORD";
    $o="";
    $t="";
    $ID=0;
    $q="";
    $OTYPE="";
    $headid=0;
    $lemaid=0;
    $marca=0;
    $acepid=0;
    $cv="";
    $cc=0;
    do{ 
                
        
        /* analizar este elemento */        
            $ts=$CA[$i];            
            $NEXT1="";
            //ver si hay dato guardado en BD
            $result = $mysqli->query("SELECT * from import_temp where id=$i");
            $row=$result->fetch_assoc();
            $TYPE=$row['type'];
            $result->free();

        if($row!=false) {
            $text=trim($ts[0]);
            if($TYPE!=$OTYPE && ($OTYPE>=5 && $OTYPE<=8)) {
                //BREAK. SEND TO DB.
                if($acepid==0) {
                    // crear la acepcion para este caso.
                    $ID++;
                   $ow=$mysqli->real_escape_string($_SESSION['a']);
                   $p1=2;                    // id de diccionario 
                   $p2=''; 
                   $p3=3;  // PARAMETRO: tipo (1=lema, 3=acepcion, 2=lema)                
                   $p4=$headid;  // PARAMETRO: superior jeràrquico: (-1 si es lema, o el id del lema si es una acepciòn o subl  ema)    
                   if($lemaid>0) $p4=$lemaid;
                   $p5=$ID;
                   $mysqli->query("insert into entry(d_id,lang,type,head,parent,number,owner) values ($p1,'es',$p3,'$p2',$p4,$p5,'$ow')");  
                   $acepid=$mysqli->insert_id;                         
                   if($marca!=0) {
                        $mysqli->query("insert into content(entry_id,lang,content_type,content_int) values($acepid,'es',5,$marca)");
                        $marca=0;
                   }

                }
                /* ya fijo acepid existe */ 
                    
                
                $mysqli->query("insert into content(entry_id,lang,content_type,content_text) values($acepid,'es',$cc,'$cv')");
                $cc=0;
                $cv="";
            }
            
            if($TYPE=="1") {
               $ow=$mysqli->real_escape_string($_SESSION['a']);
               $p1=2;                   
               $p2=$text;
               $p3=1;  // PARAMETRO: tipo (1=lema, 2=acepcion, 3=lema) 
               $p4=-1;  // PARAMETRO: superior jeràrquico: (-1 si es lema, o el id del lema si es una acepciòn o sublema) 
               $p5=-1;
               $mysqli->query("insert into entry(d_id,lang,type,head,parent,number,owner) values ($p1,'es',$p3,'$p2',$p4,$p5,'$ow')");  
               $headid=$mysqli->insert_id; 
            }
            
            
            if($TYPE=="2") {
                //$t.="CONTENT TYPE=5 $text ";
                $rs2=$mysqli->query("select * from content_choice_options where content_type_id!=18 and content_value_abbr='$text' ");
                $ro2=$rs2->fetch_assoc();
                $rs2->free();
                $CCI=0;
                if($ro2==false) {
                    $rs3=$mysqli->query("select max(content_choice_id) from content_choice_options");
                    $ro3=$rs3->fetch_array(MYSQLI_NUM);
                    $CCI=$ro3[0]+1;
                    $rs3->free();
                    //$t.=" ADD into CCO <br>";
                    $mysqli->query("INSERT INTO `content_choice_options`(`lang`, `content_type_id`, `content_choice_id`, `content_value`, `content_value_abbr`, `content_parent_value`, `content_choice_visible`) VALUES ('es',5,$CCI,'$text','$text',-1,0)");
                } else {$CCI=$ro2['content_choice_id'];}     
                 
                //$t.="INT=".$CCI."<br>";
                $marca=$CCI;
                //$theid=$acepid;             
                //$mysqli->query("insert into content(entry_id,lang,content_type,content_int) values($theid,'es',5,$CCI)");
                
             }
            if($TYPE=="3")
            {
               $ow=$mysqli->real_escape_string($_SESSION['a']);
               $p1=2;                   
               $p2=$text;
               $p3=2;  // PARAMETRO: tipo (1=lema, 2=acepcion, 3=lema) 
               $p4=$headid;  // PARAMETRO: superior jeràrquico: (-1 si es lema, o el id del lema si es una acepciòn o sublema) 
               $p5=-1;
               $mysqli->query("insert into entry(d_id,lang,type,head,parent,number,owner) values ($p1,'es',$p3,'$p2',$p4,$p5,'$ow')");  
               $lemaid=$mysqli->insert_id;            
            
            }
            if($TYPE=="10") {
                $rs2=$mysqli->query("select * from content_choice_options where content_type_id=18 and content_value_abbr='$text' ");
                $ro2=$rs2->fetch_assoc();
                $rs2->free();
                $CCI=0;
                if($ro2==false) {
                    $rs3=$mysqli->query("select max(content_choice_id) from content_choice_options");
                    $ro3=$rs3->fetch_array(MYSQLI_NUM);
                    $CCI=$ro3[0]+1;
                    $rs3->free();
                    //$t.=" ADD into CCO <br>";
                    $mysqli->query("INSERT INTO `content_choice_options`(`lang`, `content_type_id`, `content_choice_id`, `content_value`, `content_value_abbr`, `content_parent_value`, `content_choice_visible`) VALUES ('es',18,$CCI,'$text','$text',-1,0)");
                } else {$CCI=$ro2['content_choice_id'];}     
                 
                //$t.="INT=".$CCI."<br>";
                $mysqli->query("insert into content(entry_id,lang,content_type,content_int) values($acepid,'es',18,$CCI)");


            
            
              
            }
            if($TYPE=="4") {$ID++; 
            
               $ow=$mysqli->real_escape_string($_SESSION['a']);
               $p1=2;                   
               $p2=$text;
               $p3=3;  // PARAMETRO: tipo (1=lema, 3=acepcion, 2=lema)                
               $p4=$headid;  // PARAMETRO: superior jeràrquico: (-1 si es lema, o el id del lema si es una acepciòn o sublema) 
               if($lemaid>0) $p4=$lemaid;
               $p5=$ID;
               $mysqli->query("insert into entry(d_id,lang,type,head,parent,number,owner) values ($p1,'es',$p3,'$p2',$p4,$p5,'$ow')");  
               $acepid=$mysqli->insert_id;                         
                if($marca!=0) {
                    $mysqli->query("insert into content(entry_id,lang,content_type,content_int) values($acepid,'es',5,$marca)");
                    $marca=0;
                   }

                }
            
            //$q="insert into content(entry_id,lang,content_type,content_int) values($eid,'es',$c,$v)";
            // $q="insert into content(entry_id,lang,content_type,content_text) values($aid,'es',$c,'$v')";
            if($TYPE=="5") {
                if($OTYPE==$TYPE) $cv.=" $text";
                else {$cv=$text;$cc=1;}
            }
            if($TYPE=="6")  {
                if($OTYPE==$TYPE) $cv.=" $text";
                else {$cv=$text;$cc=2;}
            }
            if($TYPE=="7")  {
                if($OTYPE==$TYPE) $cv.=" $text";
                else {$cv=$text;$cc=17;}
                }
            if($TYPE=="8")  {
                if($OTYPE==$TYPE) $cv.=" $text";
                else {$cv=$text;$cc=8;}
                }
            if($TYPE=="9") {}
            //else {
                $OTYPE=$TYPE;
            //}                       
        }
        
        /* mostrar en el seleccionador */ 
        if(is_string($CA[$i][0])) {
            $style="border-right: dashed 1px gray;";
            if($CA[$i][2]{0}=="B") $style.="font-weight:bold;";
            if($CA[$i][2]{1}=="I") $style.="font-style:italic;";
            if($CA[$i][2]{2}=="S") $style.="font-variant: small-caps;";
            $l=strlen($CA[$i][0]);
            $o.="<span class='e00' style='$style' id='f$i' data-type='$TYPE'>".spaces($CA[$i][0])."</span>";
        }
        /* fin de mostrar */ 
        
        $nextb=is_bool($CA[$i+1][0]);
        $nextnextb=is_bool($CA[$i+2][0]);
        $i++;
    }while(!($nextb && $nextnextb)); //!$nextb&&!$nextnextb);
    /* falta algo por ingresar? */ 
    if($cc!=0) {
                //BREAK. SEND TO DB.
                $mysqli->query("insert into content(entry_id,lang,content_type,content_text) values($acepid,'es',$cc,'$cv')");
                $cc=0;
                $cv="";
            }
   $t=preview($mysqli,$headid); 
}                


                echo htmlentities($o);?>
                <!--Definición: <br>
                <input type="text" size="30" value="certificado otorgado a persona"/>
                <br><br>
                Ejemplo:  <br>
                <input type="text" size="30" value="Tengo un ~ de ingeniero"/>
                <br><br> --> 
            </div>
            <div id="p"><?php echo htmlentities($t);?></div>
  </body>
</html>
