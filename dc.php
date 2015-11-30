<?php
require 'config.php';

function print_r_level($data, $level = 5)
{
    static $innerLevel = 1;
    
    static $tabLevel = 1;
    
    static $cache = array();
    
    $self = __FUNCTION__;
    
    $type       = gettype($data);
    $tabs       = str_repeat('    ', $tabLevel);
    $quoteTabes = str_repeat('    ', $tabLevel - 1);
    
    $recrusiveType = array('object', 'array');
    
    // Recrusive
    if (in_array($type, $recrusiveType))
    {
        // If type is object, try to get properties by Reflection.
        if ($type == 'object')
        {
            if (in_array($data, $cache))
            {
                return "\n{$quoteTabes}*RECURSION*\n";
            }
            
            // Cache the data
            $cache[] = $data;
            
            $output     = get_class($data) . ' ' . ucfirst($type);
            $ref        = new \ReflectionObject($data);
            $properties = $ref->getProperties();
            
            $elements = array();
            
            foreach ($properties as $property)
            {
                $property->setAccessible(true);
                
                $pType = $property->getName();
                
                if ($property->isProtected())
                {
                    $pType .= ":protected";
                }
                elseif ($property->isPrivate())
                {
                    $pType .= ":" . $property->class . ":private";
                }
                
                if ($property->isStatic())
                {
                    $pType .= ":static";
                }
                
                $elements[$pType] = $property->getValue($data);
            }
        }
        // If type is array, just retun it's value.
        elseif ($type == 'array')
        {
            $output = ucfirst($type);
            $elements = $data;
        }
        
        // Start dumping datas
        if ($level == 0 || $innerLevel < $level)
        {
            // Start recrusive print
            $output .= "\n{$quoteTabes}(";
            
            foreach ($elements as $key => $element)
            {
                $output .= "\n{$tabs}[{$key}] => ";
                
                // Increment level
                $tabLevel = $tabLevel + 2;
                $innerLevel++;
                
                $output  .= in_array(gettype($element), $recrusiveType) ? $self($element, $level) : $element;
                
                // Decrement level
                $tabLevel = $tabLevel - 2;
                $innerLevel--;
            }
            
            $output .= "\n{$quoteTabes})\n";
        }
        else
        {
            $output .= "\n{$quoteTabes}*MAX LEVEL*\n";
        }
    }
    
    // Clean cache
    if($innerLevel == 1)
    {
        $cache = array();
    }
    
    return $output;
}// End function

function callgt($loc,$array,$i){
    $a=array(false,false,false,false);
    //echo "--> $loc $i ";
    $ts1=$array[$i];
    $ts1noge=!method_exists($ts1,'getElements');
    if(!$ts1noge) {    
        $ww=$ts1->getElements();
        $ts=$ww[0];
        if(method_exists($ts,'getText')) {            
            $v=$ts->getText();
            $va=trim($v);
            if(strlen($va)==0) $a[0]=$v; 
            else $a[0]=($va);
            $a[3]=$i;
            //echo "$i:'".$a[0]."'"; 
            //if($i==270) echo ord($a[0])." ";
            $fs=$ts->getFontStyle();
            $a[1]=$fs->getSize();
            $a[2]=$fs->isBold()?"B":"b";
            $a[2].=$fs->isItalic()?"I":"i";
            $a[2].=$fs->isSmallCaps()?"S":"s";
        }
    }
    //echo $a[2];
    return $a;
}

function read_one_entry($ptr,$arrays)
{
    $i=$ptr;
    //echo "HOLA";
    $chomp=0;
    //find a NOGE.  
    do
    {
        $ts1=$arrays[$i];
        $ts1noge=!method_exists($ts1,'getElements');
        $ts2=$arrays[$i+1];
        $ts2noge=!method_exists($ts2,'getElements');
        if(!$ts2noge) {
            $ts2e=$ts2->getElements();
            $ts2ge0=count($ts2e)==0;
        }
        else $ts2ge0=true;
        $ts3stillok=$i+2<count($arrays);      
        $i++;  
        //echo "$i: noge=$ts1noge  +1 noge=$ts2noge c=$ts2ge0 ".PHP_EOL;

    } while(!$ts1noge  && !$ts2ge0 && $ts3stillok );
    $hw=$i+2;
    $chomp=2;    
    $NEXT="HEADWORD";    
    $tshw=$arrays[$hw]->getElements();
    $tshw=$tshw[0];
    $chomp++;
    //echo print_r_level($tshw,2);
    if(method_exists($tshw,'getText')) {
        $hwlen=strlen($tshw->getText());
        echo "HEADWORD: '".$tshw->getText()."';".PHP_EOL;
        $chomp++;
        $NEXT="MARCA";
              
                
        // reconocer marca.  ║
        do {$ts=callgt("@marca",$arrays,$i+$chomp);$chomp++;if($ts[0]==false) break;} 
        while($ts[2]!="bIs");
        echo "MARCA: '".$ts[0]."'"."id=".$ts[3].PHP_EOL;
        // ciclo de definiciones
        $ID=1;
        do{
            do {$ts=callgt("@#acep",$arrays,$i+$chomp);$chomp++;if($ts[0]==false) break;} 
            while($ts[2]!="Bis");
            if($ts[0]==false)break;
            $ts=callgt("@acep",$arrays,$i+$chomp);$chomp++;
            if($ts[0]=="║ ") {
                 do {$ts1=callgt("@marca-2",$arrays,$i+$chomp);$chomp++;if($ts1[0]==false) break;} 
                 while($ts1[2]!="bIs");
                 echo "MARCA: '".$ts1[0]."'"."id=".$ts1[3].PHP_EOL;
                 // find next acepcion
                do {$ts=callgt("@#acep-2",$arrays,$i+$chomp);$chomp++;if($ts[0]==false) break;} 
                while($ts[2]!="Bis");
                $ts=callgt("@acep-2",$arrays,$i+$chomp);$chomp++;                 
            }
            //if($ts[0]==" ") {echo "******";continue;}
            if($ts[0]==" ") {$ts=callgt("@next",$arrays,$i+$chomp);$chomp++;}
            if($ts[0]{0}=='S') {
                echo "SIN ";
                continue;
            }
            if($ts[0]{0}=='O') {
                /* ller obswervacion */
                echo "OBS ";
                continue;
            }
            if(ord($ts[0])==226) {//■
                //es un sublema
                /*echo "SUBLEMA: '".$ts[0]."' "."id=".$ts[3].PHP_EOL;
                do {$ts=callgt("@marca-sub",$arrays,$i+$chomp);$chomp++;if($ts[0]==false) break;} 
                while($ts[2]!="bIs");
                echo "  MARCA: '".$ts[0]."'"."id=".$ts[3].PHP_EOL;*/
            }
            if($ts[2]!="bis") {
                do {$ts=callgt("@acep-txt",$arrays,$i+$chomp);$chomp++;if($ts[0]==false) break;} 
                while($ts[2]!="bis");
            }
            if(strlen($ts[0])<=10) {$ID++;continue;}
            echo "ACEPCION $ID: '".$ts[0]."'"."id=".$ts[3].PHP_EOL;
            do {$ts=callgt("@ejem",$arrays,$i+$chomp);$chomp++;if($ts[0]==false) break;} 
            while($ts[2]!="bIs" && $ts[1]!="10");
            echo "  EJEMPLO: ".$ts[0]."'"."id=".$ts[3].PHP_EOL;
            echo PHP_EOL;
            $ID++;

        } while ($ts[0]!=false);
                
        //echo $hw." ".$tshw->getText(). "=>".PHP_EOL;
        /*do {
        
            $ts1=$arrays[$i+$chomp];
            $ts1noge=!method_exists($ts1,'getElements');
            if(!$ts1noge) {
                $ww=$ts1->getElements();
                $ts=$ww[0];
                if(method_exists($ts,'getText')) {
                    $jj=$i+$chomp;
                    echo $jj." ".$ts->getText(). "=>";
                    $fs=$ts->getFontStyle();
                    echo $fs->getName();
                    echo " ".$fs->getSize();
                    echo $fs->isBold()?"B":"b";
                    echo $fs->isItalic()?"I":"i";
                    echo $fs->isSmallCaps()?"S":"s";
                    echo PHP_EOL;
               }
            }
            $ts3stillok=$hw+2<count($arrays);      
            $chomp++;                    
        } while(!$ts1noge && $ts3stillok ); */
    }  
    
    //print headword    
    
    return $i+$chomp;;
}

function rtgl($s)
{
    $h=trim($s);
    return substr($h, -1); 
}

$i=0;
$chomp=2;

function NEXT_TS($arrays){
    global $i, $chomp;
    $a=callgt("",$arrays,$i+$chomp);
    //echo ".";
    $chomp++;
    if($a[0]==false) return $a;
    if($a[0]==" ") $a=NEXT_TS($arrays);
    
        return $a;
   
    
}

function read_one_entry_3($ptr, $arrays){
   global $i, $chomp;
     $i=$ptr;
    //echo "HOLA";
    $chomp=0;
    //find a NOGE.  
    do
    {
        $ts1=$arrays[$i];
        $ts1noge=!method_exists($ts1,'getElements');
        $ts2=$arrays[$i+1];
        $ts2noge=!method_exists($ts2,'getElements');
        if(!$ts2noge) {
            $ts2e=$ts2->getElements();
            $ts2ge0=count($ts2e)==0;
        }
        else $ts2ge0=true;
        $ts3stillok=$i+2<count($arrays);      
        $i++;  
        //echo "$i: noge=$ts1noge  +1 noge=$ts2noge c=$ts2ge0 ".PHP_EOL;

    } while(!$ts1noge  && !$ts2ge0 && $ts3stillok );
    $hw=$i+2;
    $chomp=2;        
    $tshwge=method_exists($arrays[$hw],'getElements');
    if($tshwge==false) {echo PHP_EOL;return $i+$chomp;}
    $tshw=$arrays[$hw]->getElements();
    $tshw=$tshw[0];
    $chomp++;
    $ID=1;
    $NEXT="MARCA";
    $NEXT1="";
    $fm=false;
    $fa=false;
    $fe=false;
    $fsl=false;
    $fslm=false;
    //echo print_r_level($tshw,2);
    if(method_exists($tshw,'getText')) {
        $hwlen=strlen($tshw->getText());
        echo "HEADWORD: '".$tshw->getText()."';".PHP_EOL;                
        $ts=NEXT_TS($arrays);
        while ($ts[0]!=false) {  
            //echo ":: NEXT=$NEXT TXT='".$ts[0]."' ord{0}=".ord($ts[0]{0})."*".ord($ts[0]{1})." ".$ts[2]." "."id=".$ts[3].PHP_EOL;
            $NEXT1="";
            if( ord($ts[0]{0})==226  && ord($ts[0]{1})==149) $NEXT1="MARCA";                    
            if($NEXT=="EJSINOBS" && $ts[2]=="Bis") $NEXT="ACEPCION"; 
            if($NEXT=="DEFINICION" && $ts[2]=="Bis") $NEXT="ACEPCION"; 
                           
            if($NEXT=="MARCA" &&  $ts[2]=="bIs")  {$NEXT1="ACEPCION";$fm=true;}
            if($NEXT=="MARCA" && $ts[2]=="Bis") {
                //echo "ACEPCION $ID: ".PHP_EOL; 
                $NEXT="ACEPCION"; $ID++;
            }
            if($NEXT=="MARCA" && $ts[2]=="bis") {
                 $NEXT="DEFINICION"; $ID++;
            } 
            if($NEXT=="SUBMARCA" &&  $ts[2]=="Bis")  {   $NEXT1="SUBMARCA";$fsl=true;  }
            if($NEXT=="SUBMARCA" &&  $ts[2]=="bIs")  {  $NEXT1="DEFINICION";$fslm=true;}            
            if($NEXT=="SUBMARCA" &&  $ts[2]=="bis")  {    $NEXT="DEFINICION"; $ID++;  }                        
            if($NEXT=="ACEPCION" && ord($ts[0]{0})==226 && ord($ts[0]{1})==150) {   $NEXT1="SUBMARCA";$fsl=true; }
//            if($NEXT=="ACEPCION" && $NEXT1=="" && $ts[0]{0}=='S'  ) {$NEXT1="SINONIMO"; }
            if($NEXT=="ACEPCION" && $NEXT1=="" && $ts[0]{0}=='O') {$NEXT1="OBSERVACION"; }            
            if($NEXT=="ACEPCION" && ord($ts[0]{0})!=226 && $NEXT1=="" && $ts[2]=="bis"){   $NEXT="DEFINICION"; $ID++;}
            if($NEXT=="ACEPCION" && ord($ts[0]{0})!=226 && $NEXT1=="" ){   $NEXT1="DEFINICION"; $ID++; $NEXT1="DEFINICION";}
            
//            if($NEXT=="ACEPCION" && ord($ts[0])!=226){ $NEXT1="ACEPCION $ID"; $ID++; $NEXT1="DEFINICION";}
            if($NEXT=="DEFINICION"&& $ts[2]!="bIs" )  {$fa=true;  if(rtgl($ts[0])==':') $NEXT1="EJSINOBS"; else $NEXT1="DEFINICION"; }
            if($NEXT=="DEFINICION" && $ts[2]=="bIs") {$fe=true;  $NEXT1="EJSINOBS"; }
            if($NEXT=="EJSINOBS" && $ts[2]=="bIs") {$fe=true;   $NEXT1="EJSINOBS"; }
            if($NEXT=="EJSINOBS" && $NEXT1=="" && $ts[0]{0}=='S') {$NEXT1="SINONIMO"; }
            if($NEXT=="EJSINOBS" && $NEXT1=="" && $ts[0]{0}=='O') {$NEXT1="OBSERVACION"; }
            if($NEXT=="EJSINOBS" && $NEXT1=="") {$NEXT1="ACEPCION";}
            if($NEXT=="SINONIMO" && $ts[2]=="bIs") {  $NEXT1="EJSINOBS";} 
            if($NEXT=="SINONIMO" && $ts[2]!="bIs") {$NEXT1="SINONIMO";}
            if($NEXT=="OBSERVACION" && $ts[2]=="bis") {  $NEXT1="EJSINOBS";} 
            if($NEXT1=="") $NEXT1=$NEXT;
            //echo "     --> $NEXT1 ".PHP_EOL;;
            $ts=NEXT_TS($arrays);   
            $NEXT=$NEXT1;
        }
                
    }

     return $i+$chomp-2;
}


function read_one_entry_2($ptr, $arrays){
   global $i, $chomp;
     $i=$ptr;
    //echo "HOLA";
    $chomp=0;
    //find a NOGE.  
    do
    {
        $ts1=$arrays[$i];
        $ts1noge=!method_exists($ts1,'getElements');
        $ts2=$arrays[$i+1];
        $ts2noge=!method_exists($ts2,'getElements');
        if(!$ts2noge) {
            $ts2e=$ts2->getElements();
            $ts2ge0=count($ts2e)==0;
        }
        else $ts2ge0=true;
        $ts3stillok=$i+2<count($arrays);      
        $i++;  
        //echo "$i: noge=$ts1noge  +1 noge=$ts2noge c=$ts2ge0 ".PHP_EOL;

    } while(!$ts1noge  && !$ts2ge0 && $ts3stillok );
    $hw=$i+2;
    $chomp=2;        
    $tshwge=method_exists($arrays[$hw],'getElements');
    if($tshwge==false) {echo PHP_EOL;return $i+$chomp;}
    $tshw=$arrays[$hw]->getElements();
    $tshw=$tshw[0];
    $chomp++;
    $ID=1;
    $NEXT="MARCA";
    $NEXT1="";
    $fm=false;
    $fa=false;
    $fe=false;
    $fsl=false;
    $fslm=false;
    //echo print_r_level($tshw,2);
    if(method_exists($tshw,'getText')) {
        $hwlen=strlen($tshw->getText());
        echo "HEADWORD: '".$tshw->getText()."';".PHP_EOL;                
        $ts=NEXT_TS($arrays);
        while ($ts[0]!=false) {  
            //echo ":: NEXT=$NEXT TXT='".$ts[0]."' ord{0}=".ord($ts[0]{0})."*".ord($ts[0]{1})." ".$ts[2]." "."id=".$ts[3].PHP_EOL;
            $NEXT1="";
            if( ord($ts[0]{0})==226  && ord($ts[0]{1})==149) $NEXT1="MARCA";                    
            if($NEXT=="EJSINOBS" && $ts[2]=="Bis") $NEXT="ACEPCION"; 
            if($NEXT=="DEFINICION" && $ts[2]=="Bis") $NEXT="ACEPCION"; 
                           
            if($NEXT=="MARCA" &&  $ts[2]=="bIs")  {echo "MARCA: '".$ts[0]."'"."id=".$ts[3].PHP_EOL; $NEXT1="ACEPCION";$fm=true;}
            if($NEXT=="MARCA" && $ts[2]=="Bis") {
                //echo "ACEPCION $ID: ".PHP_EOL; 
                $NEXT="ACEPCION"; $ID++;
            }
            if($NEXT=="MARCA" && $ts[2]=="bis") {
                echo "ACEPCION $ID: ".PHP_EOL; $NEXT="DEFINICION"; $ID++;
            } 
            if($NEXT=="SUBMARCA" &&  $ts[2]=="Bis")  {echo "SUBLEMA: '".$ts[0]."'"."id=".$ts[3].PHP_EOL;  $NEXT1="SUBMARCA";$fsl=true;  }
            if($NEXT=="SUBMARCA" &&  $ts[2]=="bIs")  {echo "MARCA SUBLEMA: '".$ts[0]."'"."id=".$ts[3].PHP_EOL; $NEXT1="DEFINICION";$fslm=true;}            
            if($NEXT=="SUBMARCA" &&  $ts[2]=="bis")  {  echo "ACEPCION $ID: ".PHP_EOL; $NEXT="DEFINICION"; $ID++;  }                        
            if($NEXT=="ACEPCION" && ord($ts[0]{0})==226 && ord($ts[0]{1})==150) {echo "SUBLEMA: '".$ts[0]."'"."id=".$ts[3].PHP_EOL;  $NEXT1="SUBMARCA";$fsl=true; }
//            if($NEXT=="ACEPCION" && $NEXT1=="" && $ts[0]{0}=='S'  ) {$NEXT1="SINONIMO"; }
            if($NEXT=="ACEPCION" && $NEXT1=="" && $ts[0]{0}=='O') {$NEXT1="OBSERVACION"; }            
            if($NEXT=="ACEPCION" && ord($ts[0]{0})!=226 && $NEXT1=="" && $ts[2]=="bis"){ echo "ACEPCION $ID: ".PHP_EOL; $NEXT="DEFINICION"; $ID++;}
            if($NEXT=="ACEPCION" && ord($ts[0]{0})!=226 && $NEXT1=="" ){ echo "ACEPCION $ID: ".PHP_EOL; $NEXT1="DEFINICION"; $ID++; $NEXT1="DEFINICION";}
            
//            if($NEXT=="ACEPCION" && ord($ts[0])!=226){ $NEXT1="ACEPCION $ID"; $ID++; $NEXT1="DEFINICION";}
            if($NEXT=="DEFINICION"&& $ts[2]!="bIs" )  {$fa=true; echo "  DEFINICION: '".$ts[0]."'"."id=".$ts[3].PHP_EOL; if(rtgl($ts[0])==':') $NEXT1="EJSINOBS"; else $NEXT1="DEFINICION"; }
            if($NEXT=="DEFINICION" && $ts[2]=="bIs") {$fe=true; echo "  EJEMPLO: '".$ts[0]."'"."id=".$ts[3].PHP_EOL;  $NEXT1="EJSINOBS"; }
            if($NEXT=="EJSINOBS" && $ts[2]=="bIs") {$fe=true; echo "  EJEMPLO: '".$ts[0]."'"."id=".$ts[3].PHP_EOL;  $NEXT1="EJSINOBS"; }
            if($NEXT=="EJSINOBS" && $NEXT1=="" && $ts[0]{0}=='S') {$NEXT1="SINONIMO"; }
            if($NEXT=="EJSINOBS" && $NEXT1=="" && $ts[0]{0}=='O') {$NEXT1="OBSERVACION"; }
            if($NEXT=="EJSINOBS" && $NEXT1=="") {$NEXT1="ACEPCION";}
            if($NEXT=="SINONIMO" && $ts[2]=="bIs") {echo "  SINONIMO: '".$ts[0]."'"."id=".$ts[3].PHP_EOL; $NEXT1="EJSINOBS";} 
            if($NEXT=="SINONIMO" && $ts[2]!="bIs") {$NEXT1="SINONIMO";}
            if($NEXT=="OBSERVACION" && $ts[2]=="bis") {echo "  OBSERVACION: '".$ts[0]."'"."id=".$ts[3].PHP_EOL; $NEXT1="EJSINOBS";} 
            if($NEXT1=="") $NEXT1=$NEXT;
            //echo "     --> $NEXT1 ".PHP_EOL;;
            $ts=NEXT_TS($arrays);   
            $NEXT=$NEXT1;
        }
                
    }
    echo "STATUS:    MARCA=$fm  ACEPCION=$fa= EJEMPLO=$fe  SUBLEMA=$fsl SUBLEMA MARCA=$fslm".PHP_EOL;;
     return $i+$chomp-2;
}

require_once 'vendor/phpoffice/phpword/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();
$fileName = "/var/www/dc.docx";
$phpWord = \PhpOffice\PhpWord\IOFactory::load($fileName);
$sections = $phpWord->getSections();
$section = $sections[0]; // le document ne contient qu'une section
$arrays = $section->getElements();
/*$json=file_get_contents("dict.json");
$CA=json_decode($json);
for($jj=847;$jj<853;$jj++)
{
    
    print_r($CA[$jj]);

}*/

  // echo(gettype($CA[$jj][0]).PHP_EOL);
/* convert array into flat array */
/* inicializaciòn de base de datos */

$mysqli = new mysqli($DB['host'], $DB['user'], $DB['pass'], $DB['name'], $DB['port'], $DB['sock']);
$mysqli->set_charset("utf8");

for($jj=0;$jj<count($arrays);$jj++)
{
    $ts1=$arrays[$jj];
    $a=array(false,false,false,false);
    $ts1noge=!method_exists($ts1,'getElements');    
    if(!$ts1noge) {    
        $ww=$ts1->getElements();
        if(count($ww)>0) {
            $ts=$ww[0];
            if(method_exists($ts,'getText')) {            
                $v=$ts->getText();
                $va=trim($v);
                if(strlen($va)==0) $a[0]=$v; 
                else $a[0]=($va);
                $a[3]=$jj;
                //echo "$i:'".$a[0]."'"; 
                //if($i==270) echo ord($a[0])." ";
                $fs=$ts->getFontStyle();
                $a[1]=$fs->getSize();
                $a[2]=$fs->isBold()?"B":"b";
                $a[2].=$fs->isItalic()?"I":"i";
                $a[2].=$fs->isSmallCaps()?"S":"s";
            }
        }
    }
    //$CA[]=$a;
    $q="";
    $text=$a[0];
    $size=$a[1];
    $style=$a[2];
    if($a[0]==false) {$q="insert into import_temp values($jj,'',0,'',-1,0)";}
    else {$q="insert into import_temp values($jj,'$text',$size,'$style',-1,1)";}
    $mysqli->query($q); 
    if($jj%100==0)  echo "$jj of ".count($arrays).PHP_EOL;
}
/*$json = json_encode($CA);
if($json==false){
    echo json_last_error_msg();
}
else {
    file_put_contents('dict.json', $json);
}*/
$idx=50; //128 abandonar  166 absoluto  192 abrir--278   12077 cuerpo      'santiago vargas'
/*for($jj=0;$jj<2088;$jj++){
   echo $jj." ".$idx. " ";
   $idx=read_one_entry_3($idx,$arrays);
}*/
/*
50 + = 5984 'bacteria'
50 + 250 = 6073 'bajo, ja'
50 + 1000 = 19848 'gusto'
5984 + 1000 = 24631 'mantener'
24000 + 865 = 42574 'zona'
2087 terminos en  diccionario
for ($i=120;$i<180;$i++){
    $ts=$arrays[$i];
    if(method_exists($ts,'getElements')) {   
        $ts=$arrays[$i]->getElements();
        if(count($ts)==0) {echo $i."GE=0".PHP_EOL; continue;};
        $ts=$ts[0];
    }
    else {echo $i."NOGE".PHP_EOL; continue;}
    if(method_exists($ts,'getText')) {
        echo $i." ".$ts->getText(). "=>";
        $fs=$ts->getFontStyle();
        echo $fs->getName();
        echo " ".$fs->getSize();
        echo $fs->isBold()?"B":"b";
        echo $fs->isItalic()?"I":"i";
        echo $fs->isSmallCaps()?"S":"s";
   } 
   else {echo $i."NOGT".PHP_EOL; continue;}

    
//    echo print_r($fs);
    echo PHP_EOL;
    //echo print_r_level($ts[0],2);
}*/

?>
