<?php
    define('TRY_FIX',0775); //PUt it to 0 if you don't want to mess with permissions.
    ini_set('max_allowed_memory','60M');
    echo "<b>Username of php process:</b>";
    echo exec('whoami')."<br />";
    $startpath = $_SERVER['DOCUMENT_ROOT'];

     function scan( $dir ,$level = 0){

        $infected = array();
        $dir_infected = array();
        $dirs = array_diff( scandir( $dir ), Array( ".", ".." ));
        $dir_array = Array();
        if($level == 0){
            $perm = fileperms($dir);
            $perm = substr(sprintf('%o', $perm), -3);
            if($perm == 777){
                $fixed = 0;
                if(TRY_FIX)
                    $fixed = chmod($dir,TRY_FIX);
                $dir_infected[$dir] = ($fixed) ? "FIXED" : "STILL BROKEN";
            }
        }
        foreach( $dirs as $d ){
            $is_dir = is_dir($dir."/".$d);
            $perm = fileperms($dir."/".$d);

            $perm = substr(sprintf('%o', $perm), -3);

            if($perm == 777 and $is_dir){
                $fixed = 0;
                if(TRY_FIX)
                    $fixed = chmod($dir."/".$d,TRY_FIX);
                
                $dir_infected[$dir."/".$d] = ($fixed) ? "FIXED" : "STILL BROKEN";

            } else if ($perm == 777 and !$is_dir) {
                if(!array_key_exists($dir, $infected) or !is_array($infected[$dir]))
                    $infected[$dir] = array();
                $fixed = 0;
                if(TRY_FIX)
                    $fixed = chmod($dir."/".$d,TRY_FIX);
          
                $infected[$dir][$dir."/".$d] = ($fixed) ? "FIXED" : "STILL BROKEN";;
            }
            if($is_dir){
                $res = scan($dir."/".$d,$level+1);
                $infected = array_merge($res[0],$infected);
                $dir_infected = array_merge($res[1],$dir_infected);
            }
        }
        return array($infected,$dir_infected);
     }
     $res = scan($startpath);
     foreach($res[0] as $dir=>$files){
        $color = array_key_exists($dir, $res[1]) ? "#700" : "#070";
        $status = (array_key_exists($dir, $res[1])) ? $res[1][$dir] : "";

        echo "<span style='color:".$color."; text-weight:bold;'>$dir [$status]</span>";
        echo "<ul class='files'>";
        foreach($files as $file => $status)
            echo "<li style='color:#700'>$file [$status]</li>";
        echo "</ul>";
     }
?>