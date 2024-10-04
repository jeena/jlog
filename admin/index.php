<?php
 include_once('.'.DIRECTORY_SEPARATOR.'auth.php');
 define("JLOG_ADMIN", true);
 require_once('..'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');
 require(JLOG_BASEPATH.'admin'.DIRECTORY_SEPARATOR.'blog.func.php');

 $get = strip($_GET);
 $yl = new Year_Links($get['y'] ?? null, JLOG_START_YEAR, add_session_id_to_url(JLOG_PATH."/admin/"), $l['admin']);

    if(isset($get['show']) AND $get['show'] == 'page') {
        $show_section = "<a href='".add_session_id_to_url("?show=weblog")."'>".$l['admin']['section_weblog']."</a> | <strong>".$l['admin']['section_page']."</strong>";
        $where = "section = 'page'";
	$year_menu = '';
    }
    else {
        $show_section = "<strong>".$l['admin']['section_weblog']."</strong> | <a href='".add_session_id_to_url("?show=page")."'>".$l['admin']['section_page']."</a>";
        $where = "YEAR(date) = '".escape_for_mysql($yl->get_selected_year())
                  ."' AND section = 'weblog'";
        $year_menu = "  <p>".$yl->get_admin_linklist()."</p>\n";

    }

 

 $c['meta']['title'] = $l['admin']['index_headline'];

 if (!isset($c['main'])) $c['main'] = '';

 $c['main'] .= output_admin_menu()." 
 <h2>".$l['admin']['admin_headline']."</h2>
  <p><strong>&raquo;&raquo;</strong> <a href='".add_session_id_to_url("new.php")."'>".$l['admin']['new_post']."</a></p>
  <p>".$l['admin']['section_show'].": ".$show_section."</p>".$year_menu."
  <table>
   <tr>
    <th>".$l['admin']['change']."</th>
    <th>".$l['admin']['delete']."</th>
    <th>".$l['admin']['date']."</th>
    <th>".$l['admin']['headline']."</th>
   </tr>";

    $sql = "SELECT
                    id,
                    date as mysql_date,
                    UNIX_TIMESTAMP(date) AS date,
                    topic
              FROM ".JLOG_DB_CONTENT."
              WHERE ".$where."
              ORDER BY mysql_date DESC;";

    $blog = new Query($sql);
     if($blog->error()) {
        echo "<pre>\n";
        echo $blog->getError();
        echo "</pre>\n";
        die();
     }

    while ($daten = $blog->fetch()) {
     $list = "
    <tr>
     <td><a href='".add_session_id_to_url("change.php?id=".$daten['id'])."'><img src='".JLOG_PATH."/img/JLOG_edit.png' alt='".$l['admin']['change']."' /></a></td>
     <td><a href='".add_session_id_to_url("change.php?id=".$daten['id'])."&amp;action=trash'><img src='".JLOG_PATH."/img/JLOG_trash.png' alt='".$l['admin']['delete']."' /></a></td>
     <td>".strftime(JLOG_DATE_SUBCURRENT, $daten['date'])."</td>
     <td>".htmlspecialchars($daten['topic'], ENT_QUOTES)."</td>
    </tr>";

		### Plugin Hook
		$c['main'] .= $plugins->callHook('adminList', $list, $daten);
    }
    
    $c['main'] .= "
    </table>
";

require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'do_template.php');
echo $body;

// eof
