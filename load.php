<?php
require 'config.php';

session_start();
$logged=isset($_SESSION['a']);
$mysqli = new mysqli($DB['host'], $DB['user'], $DB['pass'], $DB['name'], $DB['port'], $DB['sock']);
$mysqli->set_charset("utf8");

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
                if(current!=null) {
                    $(".ft").removeClass('hl');
                    $(current).data("type",t);
                    $("#ft"+t).addClass('hl'); 
                    $.get('ajax.php',{m:'8159',i:current.id,t:t}, function(data)  { });     

                }
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
                <a href="save.php?w=<?php echo $mysqli->real_escape_string($_GET['w']);?>">Guardar</a>
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
                        echo "<a href='load.php?w=" . htmlentities($i) . "' style='$style'>$c. " . htmlentities($w) . "</a>";
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
    do{ 
                
        
        /* analizar este elemento */        
            $ts=$CA[$i];            
            $NEXT1="";
            //ver si hay dato guardado en BD
            $result = $mysqli->query("SELECT * from import_temp where id=$i");
            $row=$result->fetch_assoc();
            $result->free();
            if($row==false) {
                // si no, suponemos que es 9.             
                $TYPE="";
            } 
            else {
                // tiene tipo. cargar este
                $TYPE=$row['type'];
            }
            

            if($ts[0]==" ") {$NEXT1=$NEXT;$NEXT=""; }
            if( ord($ts[0]{0})==226  && ord($ts[0]{1})==149) $NEXT1="MARCA";                    
            if($NEXT=="EJSINOBS" && $ts[2]=="Bis") $NEXT="ACEPCION"; 
            if($NEXT=="DEFINICION" && $ts[2]=="Bis") $NEXT="ACEPCION"; 
                           
            if($TYPE=="" && $NEXT=="MARCA" &&  $ts[2]=="bIs")  {$TYPE="2";$t.= "MARCA: '".$ts[0]."'"."id=".$ts[3]."<br>"; $NEXT1="ACEPCION";$fm=true;}
            if($NEXT=="MARCA" && $ts[2]=="Bis") {
                //echo "ACEPCION $ID: ""<br>"; 
                $NEXT="ACEPCION"; $ID++;
            }
            if($TYPE=="" && $NEXT=="HEADWORD" && $ts[2]=="Bis"){ $TYPE="1";$t.="HEADWORD: ".$ts[0]."<br>"; $NEXT1="MARCA";}
            if($TYPE=="" && $NEXT=="MARCA" && $ts[2]=="bis") {
                $TYPE="4";
                $t.= "ACEPCION $ID: "."<br>"; $NEXT="DEFINICION"; $ID++;
            } 
            if($TYPE=="" && $NEXT=="SUBMARCA" &&  $ts[2]=="Bis")  {$TYPE="3";$t.= "SUBLEMA: '".$ts[0]."'"."id=".$ts[3]."<br>";  $NEXT1="SUBMARCA";$fsl=true;  }
            if($TYPE=="" && $NEXT=="SUBMARCA" &&  $ts[2]=="bIs")  {$TYPE="10";$t.= "MARCA SUBLEMA: '".$ts[0]."'"."id=".$ts[3]."<br>"; $NEXT1="DEFINICION";$fslm=true;}            
            if($TYPE=="" && $NEXT=="SUBMARCA" &&  $ts[2]=="bis")  {$TYPE="4";  $t.= "ACEPCION $ID: "."<br>"; $NEXT="DEFINICION"; $ID++;  }                        
            if($TYPE=="" && $NEXT=="ACEPCION" && ord($ts[0]{0})==226 && ord($ts[0]{1})==150) {$TYPE="3";$t.= "SUBLEMA: '".$ts[0]."'"."id=".$ts[3]."<br>";  $NEXT1="SUBMARCA";$fsl=true; }
//            if($NEXT=="ACEPCION" && $NEXT1=="" && $ts[0]{0}=='S'  ) {$NEXT1="SINONIMO"; }
            if($NEXT=="ACEPCION" && $NEXT1=="" && $ts[0]{0}=='O') {$NEXT1="OBSERVACION"; }            
            if($TYPE=="" && $NEXT=="ACEPCION" && ord($ts[0]{0})!=226 && $NEXT1=="" && $ts[2]=="bis"){$TYPE="4"; $t.= "ACEPCION $ID: "."<br>"; $NEXT="DEFINICION"; $ID++;}
            if($TYPE=="" && $NEXT=="ACEPCION" && ord($ts[0]{0})!=226 && $NEXT1=="" ){$TYPE="4"; $t.= "ACEPCION $ID: "."<br>"; $NEXT1="DEFINICION"; $ID++; $NEXT1="DEFINICION";}
            
//            if($NEXT=="ACEPCION" && ord($ts[0])!=226){ $NEXT1="ACEPCION $ID"; $ID++; $NEXT1="DEFINICION";}
            if($TYPE=="" && $NEXT=="DEFINICION"&& $ts[2]!="bIs" )  {$TYPE="5";$fa=true; $t.= "&nbsp;&nbsp;DEFINICION: '".$ts[0]."'"."id=".$ts[3]."<br>"; if(rtgl($ts[0])==':') $NEXT1="EJSINOBS"; else $NEXT1="DEFINICION"; }
            if($TYPE=="" && $NEXT=="DEFINICION" && $ts[2]=="bIs") {$TYPE="6";$fe=true; $t.= "&nbsp;&nbsp;EJEMPLO: '".$ts[0]."'"."id=".$ts[3]."<br>";  $NEXT1="EJSINOBS"; }
            if($TYPE=="" && $NEXT=="EJSINOBS" && $ts[2]=="bIs") {$TYPE="6";$fe=true; $t.= "&nbsp;&nbsp;EJEMPLO: '".$ts[0]."'"."id=".$ts[3]."<br>";  $NEXT1="EJSINOBS"; }
            if($NEXT=="EJSINOBS" && $NEXT1=="" && $ts[0]{0}=='S') {$NEXT1="SINONIMO"; }
            if($NEXT=="EJSINOBS" && $NEXT1=="" && $ts[0]{0}=='O') {$NEXT1="OBSERVACION"; }
            if($NEXT=="EJSINOBS" && $NEXT1=="") {$NEXT1="ACEPCION";}
            if($TYPE=="" && $NEXT=="SINONIMO" && $ts[2]=="bIs") {$TYPE="7";$t.= "&nbsp;&nbsp;SINONIMO: '".$ts[0]."'"."id=".$ts[3]."<br>"; $NEXT1="EJSINOBS";} 
            if($NEXT=="SINONIMO" && $ts[2]!="bIs") {$NEXT1="SINONIMO";}
            if($TYPE=="" && $NEXT=="OBSERVACION" && $ts[2]=="bis") {$TYPE="8";$t.= "&nbsp;&nbsp;OBSERVACION: '".$ts[0]."'"."id=".$ts[3]."<br>"; $NEXT1="EJSINOBS";} 
            if($NEXT1=="") $NEXT1=$NEXT;
            //echo "     --> $NEXT1 ""<br>";;
            //$ts=NEXT_TS($arrays);   
            $NEXT=$NEXT1;
            
            // si status=1, actualizar dato with $TYPE, set status=2
            if($row==false) {
                if($TYPE=="") $TYPE=9;
                 $text=$ts[0];
                 $size=$ts[1];
                 if($size=="") $size="0";
                 $style=$ts[2];
                 $q="insert into import_temp values($i,'$text',$size,'$style',$TYPE,1)";
                 $mysqli->query($q); 
            } 
         
        
        /* fin del analisis */
        
        /* Si ya hicimos analisis, crear estructura DB */ 
        if($row!=false) {
            $text=trim($ts[0]);
            if($TYPE!=$OTYPE && ($OTYPE>=5 && $OTYPE<=8)) $t.="<br>";
            
            if($TYPE=="1") $t.="ENTRY TYPE=1 HEAD=$text<br>";
            if($TYPE=="2") {$t.="CONTENT TYPE=5 $text ";
                $rs2=$mysqli->query("select * from content_choice_options where content_type_id!=18 and content_value_abbr='$text' ");
                $ro2=$rs2->fetch_assoc();
                $rs2->free();
                if($ro2==false) {$t.=" ADD into CCO <br>";}
                else { $t.="INT=".$ro2['content_choice_id']."<br>";}
             }
            if($TYPE=="3") $t.="ENTRY TYPE=2 HEAD=$text<br>";
            if($TYPE=="10") {
                $t.="CONTENT TYPE=18 $text ";
                $rs2=$mysqli->query("select * from content_choice_options where content_type_id=18 and content_value_abbr='$text' ");
                $ro2=$rs2->fetch_assoc();
                $rs2->free();
                if($ro2==false) {$t.=" ADD into CCO <br>";}
                else { $t.="INT=".$ro2['content_choice_id']."<br>";}
            }
            if($TYPE=="4") {$ID++; $t.="ENTRY TYPE=3 NUMBER=$ID<br>";            }
            
            if($TYPE=="5") {
                if($OTYPE==$TYPE) $t.=" $text";
                else $t.="CONTENT TYPE=1 $text";
            }
            if($TYPE=="6")  {
                if($OTYPE==$TYPE) $t.=" $text";
                else $t.="CONTENT TYPE=2 $text";
            }
            if($TYPE=="7")  {
                if($OTYPE==$TYPE) $t.=" $text";
                else $t.="CONTENT TYPE=17 $text";
                }
            if($TYPE=="8")  {
                if($OTYPE==$TYPE) $t.=" $text";
                else  $t.="CONTENT TYPE=8 $text";
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
            $o.="<span class='e00' style='$style' id='f$i' data-type='$TYPE'>".htmlentities(spaces($CA[$i][0]))."</span>";
        }
        /* fin de mostrar */ 
        
        $nextb=is_bool($CA[$i+1][0]);
        $nextnextb=is_bool($CA[$i+2][0]);
        $i++;
    }while(!($nextb && $nextnextb)); //!$nextb&&!$nextnextb);
}                
                echo $o;?>
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
