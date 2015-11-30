<?php require 'config.php'; ?>
<!DOCTYPE html>
<html>
    <head><meta charset="utf-8"/>
    </head>
    <body>
<?php 
/*** Este código genera una versiòn HTML+CSS del diccionario.
     Se siguen las mismas reglas que el subproceso de generaciòn de vistas previas
     descrito en el archivo ajax.php, salvo que se ejecuta para todos los lemas
     pertenecientes al diccionario suministrado.
 */
$html="";
function _out_aceps($mysqli,$l,$sublema)
{
  $result = $mysqli->query("SELECT * from entry where parent=$l and type=3");
    $num=1;
    while($row=$result->fetch_assoc()) {
        $tt=$row['id'];
        $num=$row['number'];
        $cq="SELECT content_choice_options.content_value_abbr FROM content_choice_options INNER JOIN content ON  content_type_id = content_type AND content_choice_id = content_int WHERE entry_id =$tt and content_type=5";
        if($sublema==1) {$cq="SELECT content_choice_options.content_value_abbr FROM content_choice_options INNER JOIN content ON  content_type_id = content_type AND content_choice_id = content_int WHERE entry_id =$tt and content_type=18";}
        //enumerar la categoria.
            $r1 = $mysqli->query($cq);
       $ro1=$r1->fetch_array();
       $r1->free();
       if($ro1==false) {
                //ver si tiene subcategoria
            
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
        if(isset($ro1[0])) {$cat=$ro1[0];}
        if(strlen($cat)==0) {
             // si no tiene, ver si hay anterior. Si hay, colocar "", si no, colocar GRIS        
             if(strlen($lastcat)==0) $html.="<span style='color:gray;font-style:italic'>(falta cat. gram.)</span>";
        }        
        else {
            // else (tiene), si es igual, colocar "", sino, colocar "ro" y actualizar lastcat
            if($cat==$lastcat) { }
            else {
                $html.="<span style='font-style:italic'>".$cat."</span>";
                $lastcat=$cat;
            } 
        }
                
        
        $html.="&nbsp;<b>$num</b>&nbsp;";
        //obtener las marcas linguisticas
        $r1 = $mysqli->query("SELECT content_choice_options.content_value_abbr FROM content_choice_options INNER JOIN content ON  content_type_id = content_type AND content_choice_id = content_int WHERE entry_id =$tt and content_type_id>=10 and content_type_id<>18 ");
        while($ro1=$r1->fetch_array()) {
            $html.="<span style='font-style:italic'>".$ro1[0]."&nbsp;</span>";
        }
        $r1->free();
        // obtener la definicion
        $rs1 = $mysqli->query("SELECT * from content where entry_id=$tt and content_type=1");
        $ro1=$rs1->fetch_assoc();
        if($ro1['content_text']=="") 
            $html.="<span style='color:gray;'>(acepción vacía)</span>";
        else  
            $html.="<span>".$ro1['content_text']."</span>&nbsp;";       
        //$num++;           
        // obtener el ejemplo
        $rs1 = $mysqli->query("SELECT * from content where entry_id=$tt and content_type=2");
        $ro1=$rs1->fetch_assoc();
        if($ro1['content_text']=="") 
            $html.="";
        else  
            $html.=":&nbsp;<span style='font-family:sans-serif'>".$ro1['content_text'].". (".$ro1['source']."). &nbsp;</span>";       
        //$num++;           
        // obtener sininumos
        $rs1 = $mysqli->query("SELECT * from content where entry_id=$tt and content_type=17");
        $ro1=$rs1->fetch_assoc();
        if($ro1['content_text']=="") 
            $html.="";
        else  
            $html.="<span style='font-family:sans-serif'>[".$ro1['content_text']."].</span>&nbsp;";       
        // obtener observacion
        $rs1 = $mysqli->query("SELECT * from content where entry_id=$tt and content_type=8");
        $ro1=$rs1->fetch_assoc();
        if($ro1['content_text']=="") 
            $html.="";
        else  
            $html.="<span style=''><b>Obs:</b>&nbsp;".$ro1['content_text'].".</span>&nbsp;";       
        
    }
    return $html;
}

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
    
    
    
    
    //enumerar las acepciones principales
    $html.=_out_aceps($mysqli,$l,0);
    $rt = $mysqli->query("SELECT * from entry where parent=$l and type=2");        
    while($row=$rt->fetch_assoc()) {
        $html.="&nbsp;&#9632;&nbsp;<span style='font-weight:bold'>".$row['head']."</span>&nbsp;";
        $html.=_out_aceps($mysqli,$row['id'],1); 
    }
    
    $html.="</div>";
    return $html;
        
}

$mysqli = new mysqli($DB['host'], $DB['user'], $DB['pass'], $DB['name'], $DB['port'], $DB['sock']);
$mysqli->set_charset("utf8");

    $p1=$mysqli->real_escape_string($_GET['id']);  //id de diccionario
    $result = $mysqli->query("SELECT id from entry where d_id=$p1 and parent=-1 order by head asc");        
    while($row=$result->fetch_assoc()) {
        echo preview($mysqli,$row['id']);
    }


?>
</body>
</div>
