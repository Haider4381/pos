<?php
// Safe URL parsing (prevents "Undefined array key 1")
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH);
$path = is_string($path) ? $path : '/';

// Break into segments safely (no query string)
$segments = [];
$trimmed = trim($path, '/');
if ($trimmed !== '') {
    $segments = explode('/', $trimmed);
}

// Safe segment access
$seg0 = $segments[0] ?? '';
$seg1 = $segments[1] ?? '';
$seg2 = $segments[2] ?? '';

// Run URL: last path segment (works for /, /alif, /alif/sale_add.php)
$run_url = $segments ? end($segments) : '';

// If you still need the legacy $url array, keep it as segments (no index assumptions)
$url = $segments;

// ---------------- Existing file content continues below ----------------
?>
<!-- Left panel : Navigation area -->
<!-- Note: This width of the aside area can be adjusted through LESS variables -->
<script type="text/javascript">
    function check_this(val, level='')
    {
        var x=new XMLHttpRequest();
        x.onreadystatechange=function()
        {	
            if(x.status==200 && x.readyState==4)
            {
                /*var menu=document.getElementById(val);
                menu.className='open';*/
                document.getElementById("widget-grid").innerHTML=this.responseText;
            }
        };
        x.open('GET', '<?= ASSETS_URL ?>/inc/ajax_show_manus.php?menu_id='+val+"&show_menus=active&menu_level="+level, true);
        x.setRequestHeader('X-Requested-With', 'show_menus');
        x.send();
    }
</script>
<aside id="left-panel">

    <!-- User info -->
    <div class="login-info">
        <span>
            <?php
            $u_id=$_SESSION['u_id'];
            ?>
            <a href="javascript:void(0);" id="show-shortcut" data-action="toggleShortcut">
                <span>
                    <?php
                    $Name=$_SESSION['u_NameFirst'];
                    echo $Name ?>
                </span>
                <i class="fa fa-angle-down"></i>
            </a> 
        </span>
    </div>
    <!-- end user info -->

    <nav>
    <?php 
    $user_where='';
    $branch_admin=$_SESSION['branch_admin'];
    
    if($branch_admin==1)
    {
        $user_where="WHERE  role.role_id=1";
    }
    else
    {
        $user_where="WHERE (userd.u_id='".$_SESSION['u_id']."' AND role.role_active='1' AND '".date('Y-m-d')."' BETWEEN role_datefrom and role_dateto)
                        OR 
                        (role.role_default='1' AND role.role_active='1' AND '".date('Y-m-d')."' BETWEEN role_datefrom and role_dateto)
                        ";
    }
    
    if(isset($p_rightstatus) &&  ($p_rightstatus=='active')) 
    {
        $where_action='';
        
        $Q="SELECT menu.menu_id, x.status ,x.roled_edit, x.roled_delete
        FROM  sys_menu AS menu 
        RIGHT OUTER JOIN  ( 
                SELECT DISTINCT(roled.menu_id), 'active' AS status , roled.roled_edit, roled.roled_delete
                FROM    sys_role AS role 
                        LEFT OUTER JOIN u_userd AS userd ON userd.role_id=role.role_id 
                        LEFT OUTER JOIN sys_roled AS roled ON role.role_id=roled.role_id
                        $user_where
                        ORDER BY roled.roled_edit asc, roled.roled_delete asc  
                        ) AS x ON menu.menu_id=x.menu_id 
        WHERE menu.menu_url='".$p_rightpagename."' $where_action ORDER BY CASE WHEN x.roled_edit=0 THEN 0 ELSE 1 END, CASE WHEN x.roled_delete=0 THEN 0 ELSE 1 END"; 
        //echo '<pre>'.$Q.'</pre>';
        
        $mQ=mysqli_query($con, $Q); 
        $rR=mysqli_num_rows($mQ); 
        
        if(empty($rR)) 
        { 
            ?>
            <script type="text/javascript">
             setTimeout(function(){window.location.href='login'},0);
            </script> 
            <?php
            exit(); 
        } 
        $resultQ=mysqli_fetch_object($mQ); 

        if($resultQ->status!='active') 
            { 
                ?>
         <script type="text/javascript">
         setTimeout(function(){window.location.href='login'},0);
         </script> 
        <?php
                exit(); 
            } 

    } 
$Q="SELECT DISTINCT(roled.menu_id), md.menu_id, md.menu_name, md.menu_icon, md.menu_level, md.menu_parent, md.menu_position, md.menu_submenu, md.menu_url, md.iilmenu_id, md.iilmenu_name, md.iilmenu_icon, md.iilmenu_level, md.iilmenu_parent, md.iilmenu_position, md.iilmenu_submenu, md.iilmenu_url, md.iilmenu_icon, md.iiilmenu_id, md.iiilmenu_name, md.iiilmenu_icon, md.iiilmenu_level, md.iiilmenu_parent, md.iiilmenu_position, md.iiilmenu_submenu, md.iiilmenu_url
        FROM u_userd AS userd 
        RIGHT OUTER JOIN sys_role AS role ON userd.role_id=role.role_id 
        INNER JOIN sys_roled AS roled ON role.role_id=roled.role_id
            LEFT OUTER JOIN ( 
            SELECT il.menu_id, il.menu_name, il.menu_icon, il.menu_level, il.menu_parent, il.menu_position, il.menu_submenu, il.menu_url, x.iilmenu_id, x.iilmenu_name, x.iilmenu_icon, x.iilmenu_level, x.iilmenu_parent, x.iilmenu_position, x.iilmenu_submenu, x.iilmenu_url, x.iiilmenu_id, x.iiilmenu_name, x.iiilmenu_icon, x.iiilmenu_level, x.iiilmenu_parent, x.iiilmenu_position, x.iiilmenu_submenu, x.iiilmenu_url
            FROM sys_menu AS il 
            LEFT OUTER JOIN   ( 
                SELECT iil.menu_id AS iilmenu_id,
                        iil.menu_name AS iilmenu_name,
                        iil.menu_icon AS iilmenu_icon,
                        iil.menu_level AS iilmenu_level,
                        iil.menu_parent AS iilmenu_parent,								
                        iil.menu_position AS iilmenu_position, 
                        iil.menu_submenu AS iilmenu_submenu, 
                        iil.menu_url AS iilmenu_url,
                        iiil.menu_id AS iiilmenu_id, 
                        iiil.menu_name AS iiilmenu_name, 
                        iiil.menu_icon AS iiilmenu_icon, 
                        iiil.menu_level AS iiilmenu_level, 
                        iiil.menu_parent AS iiilmenu_parent, 
                        iiil.menu_position AS iiilmenu_position, 
                        iiil.menu_submenu AS iiilmenu_submenu, 
                        iiil.menu_url AS iiilmenu_url
                        FROM sys_menu AS iil 
                        LEFT OUTER JOIN sys_menu AS iiil ON iil.menu_id=iiil.menu_parent AND iiil.menu_active='1' 
                        WHERE iil.menu_level='2' AND iil.menu_active='1' ) AS x ON il.menu_id=x.iilmenu_parent
            WHERE il.menu_level='1' AND il.menu_active='1' 
            ORDER by il.menu_level, il.menu_position, x.iilmenu_level, x.iilmenu_parent, x.iilmenu_position, x.iiilmenu_level, x.iiilmenu_parent, x.iiilmenu_position ) AS md ON (roled.menu_id=md.menu_id OR roled.menu_id=md.iilmenu_id OR roled.menu_id=md.iiilmenu_id) 
        $user_where 
        ORDER by md.menu_level, md.menu_position, md.iilmenu_level, md.iilmenu_parent, md.iilmenu_position, md.iiilmenu_level, md.iiilmenu_parent, md.iiilmenu_position";

        ?> <ul>
        <?php  $mQ=mysqli_query($con, $Q) or die(mysqli_error($con)); 
               $rQ=mysqli_num_rows($mQ); 
               $selectedmenu_1='???'; 
               $liactive_selectedmenu_1=''; 
               $ulactive_selectedmenu_1=''; 
               $selectedmenu_2='???'; 
               $liactive_selectedmenu_2=''; 
               $ulactive_selectedmenu_2=''; 
               $selectedmenu_3='???';
               
               $open_ilmenu_name='???'; 
               for($i=0; $i<=$rQ; $i++) 
                  { 
                      if($i<$rQ)
                       { 
                           $result=mysqli_fetch_object($mQ); 
                           $_selectedmenu_1=$result->menu_id; 
                           $_selectedmenu_2=$result->iilmenu_id; 
                           $_selectedmenu_3=$result->iiilmenu_id; 
                       } 
                       else 
                           { 
                               $_selectedmenu_1='end'; 
                               $_selectedmenu_2='end'; 
                               $_selectedmenu_3='end'; 
                           } 
                           if($_selectedmenu_1!=$selectedmenu_1) 
                           { 
                               if(!empty($i)) { if($ulactive_selectedmenu_2=='yes') 
                               { 
                                   echo '</ul>'; $ulactive_selectedmenu_2=''; 
                               } 
                               if($liactive_selectedmenu_2=='yes') 
                               { 
                                   echo '</li>'; $liactive_selectedmenu_2=''; 
                               } 
                               if($ulactive_selectedmenu_1=='yes') 
                               { 
                                   echo '</ul>'; $ulactive_selectedmenu_1=''; 
                               } 
                               if($liactive_selectedmenu_1=='yes') 
                               { 
                                   echo '</li>'; $liactive_selectedmenu_1=''; 
                               } 
                           } 
                           if($i<$rQ) 
                           { 
                               echo '<li id="'.$result->menu_id.'" class="';
                               if(!empty($result->menu_submenu) && ($open_ilmenu_name==$result->menu_name && $active_il==''))
                                {
                                    echo 'open';
                                }
                                else if($result->menu_url==$run_url)
                                {
                                    echo 'active';
                                }
                               echo '">'; 
                               $liactive_selectedmenu_1='yes'; 
                               echo '<a href="'; 
                               if(!empty($result->menu_submenu)) 
                               { 
                                   echo 'javascript:'; 
                               } 
                               else 
                               {
                                   echo $result->menu_url;
                               } 
                               echo '"';
                               if(!empty($result->menu_submenu)) { echo 'onclick="check_this('.$result->menu_id.', 1)"';  }
                               
                               echo ' title="'.$result->menu_name.'"><i class="'.$result->menu_icon.'"></i><span class="'; 
                               if(!empty($result->menu_submenu)) 
                               { 
                                   echo 'menu-item-parent'; 
                               } 
                               echo '">'.$result->menu_name.'</span></a>'; 
                               if(!empty($result->menu_submenu)) 
                               { 
                                   echo '<ul>'; $ulactive_selectedmenu_1='yes'; 
                               } 
                           } 
                           $selectedmenu_1=$_selectedmenu_1; 
                       } 
                       if(!empty($result->iilmenu_name) || $ulactive_selectedmenu_2=='yes') 
                        { 
                            if($_selectedmenu_2!=$selectedmenu_2) 
                             { 
                                 if($ulactive_selectedmenu_2=='yes') 
                                { 
                                    echo '</ul>'; $ulactive_selectedmenu_2=''; 
                                } 
                                if($liactive_selectedmenu_2=='yes') 
                                { 
                                    echo '</li>'; 
                                    $liactive_selectedmenu_2=''; 
                                } 
                                if($i<$rQ) 
                                { 
                                    if(!empty($result->iilmenu_name)) 
                                    { 
                                        echo '<li id="'.$result->iilmenu_id.'" class="';
                                        if(!empty($result->iilmenu_submenu) && ($open_iilmenu_name==$result->menu_name && $active_iil==''))
                                        {
                                            echo 'open';
                                        }
                                        else if($result->iilmenu_url==$run_url)
                                        {
                                            echo 'active';
                                        }
                                        echo '">'; 
                                        $liactive_selectedmenu_2='yes'; 
                                        echo '<a href="'; 
                                        if(!empty($result->iilmenu_submenu)) 
                                        { 
                                            echo 'javascript:'; 
                                        } 
                                        else 
                                        {
                                            echo $result->iilmenu_url; 
                                        } 
                                        echo '"'; if(!empty($result->iilmenu_submenu)){ echo ' onclick="check_this('.$result->iilmenu_id.', 2)" '; }
                                        echo ' title="'.$result->iilmenu_name.'"><i class="'.$result->iilmenu_icon.'"></i><span class="'; 
                                        if(!empty($result->iilmenu_submenu)) 
                                        { 
                                            echo 'menu-item-parent'; 
                                        } 
                                        echo '">'.$result->iilmenu_name.'</span></a>'; 
                                        if(!empty($result->iilmenu_submenu)) 
                                        { 
                                            echo '<ul>'; $ulactive_selectedmenu_2='yes'; 
                                        } 
                                    } 
                                    $selectedmenu_2=$_selectedmenu_2; 
                                } 
                            } 
                        } 
                        if($i<$rQ) 
                        { 
                            if(!empty($result->iiilmenu_name)) 
                            { 
                                echo '<li class="';
                                if($result->iiilmenu_url==$run_url)
                                {
                                    echo 'active';
                                }
                                echo '">'; 
                                
                                    echo '<a href="'.$result->iiilmenu_url.'" title="'.$result->iiilmenu_name.'"><i class="'.$result->iiilmenu_icon.'"></i><span class="';
                                
                                echo '">'.$result->iiilmenu_name.'</span></a>'; 
                                echo '</li>'; 
                            } 
                        } 
                    }
        ?>
        
        <?php

        ?>
    </nav>

    <span class="minifyme" data-action="minifyMenu"> <i class="fa fa-arrow-circle-left hit"></i> </span>

</aside>
<!-- END NAVIGATION -->