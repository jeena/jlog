<?php
 require_once('.'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');

 $get = strip($_GET);
 if(isset($get['cat'])) $cat_y_link = "cat=".$get['cat'];
 $yl = new Year_Links($get['y'], JLOG_START_YEAR, archive(), $l, $cat_y_link);
 if(isset($get['show'])) $p = (int) escape_for_mysql($get['show']);
 else $p = 0;
 $amount = 5;

 $c['meta']['robots']       = "noindex, follow";
    
    if(isset($get['cat'])) {


      $c['meta']['title'] = $categories->get($categories->get_id($get['cat']), 'name');
      $c['main'] .= "<h2>".$l['content_categories_header']." ".$categories->get($categories->get_id($get['cat']), 'name')." ".$yl->get_selected_year()."</h2>";
      $c['main'] .= "<p>".$categories->get($categories->get_id($get['cat']), 'description')."</p>";

        $sql_archive = "
         SELECT
          ".JLOG_DB_CONTENT.".*,
          ".JLOG_DB_CONTENT.".date as mysql_date,
          DATE_FORMAT(".JLOG_DB_CONTENT.".date, '%c') AS month,
          DATE_FORMAT(".JLOG_DB_CONTENT.".date, '%Y') AS year,
          UNIX_TIMESTAMP(".JLOG_DB_CONTENT.".date) AS date
          FROM ".JLOG_DB_CONTENT."
          LEFT JOIN ".JLOG_DB_CATASSIGN." 
           ON ".JLOG_DB_CONTENT.".id = ".JLOG_DB_CATASSIGN.".content_id
            WHERE ".JLOG_DB_CATASSIGN.".cat_id = '".escape_for_mysql($categories->get_id($get['cat']))."'
            AND YEAR(".JLOG_DB_CONTENT.".date) = '".escape_for_mysql($yl->get_selected_year())."'
          ORDER BY mysql_date DESC;";

        $c['main'] .= "<p>".$yl->get_linklist()."</p>";

    }
    elseif(empty($get['y'])) {
        $c['meta']['title'] = $l['content_archive_header'];
        $c['main'] = "<h2>".$c['meta']['title']."</h2>";
        
        $sql_archive = "SELECT id, url, topic,
                    date as mysql_date,
                    DATE_FORMAT(date, '%c') AS month,
                    DATE_FORMAT(".JLOG_DB_CONTENT.".date, '%Y') AS year,
                    UNIX_TIMESTAMP(date) AS date,
		    teaser, teaserpic, teaserpiconblog, keywords, content,
		    comments, allowpingback, section
              FROM ".JLOG_DB_CONTENT."
              WHERE section = 'weblog'
              ORDER BY mysql_date DESC
              LIMIT ".$p.", ".$amount.";";

        $sql_count = "SELECT count(*) AS count FROM ".JLOG_DB_CONTENT." WHERE section = 'weblog'";

        $count_query = new Query($sql_count);
            if($count_query->error()) {
                echo "<pre>\n";
                echo $count_query->getError();
                echo "</pre>\n";
                die();
            }
        $_count = $count_query->fetch();
        $count_query->free();
        $count = $_count['count'];
    }
    else {
        if(!empty($get['m'])) $where_month = " AND MONTH(date) = '".escape_for_mysql($get['m'])."'";
        $c['meta']['title'] = $l['content_archive_header'];
        $c['main'] = "<h2>".$c['meta']['title']." ".$yl->get_selected_year()."</h2>";

        $sql_archive = "SELECT id, url, topic,
                    date as mysql_date,
                    DATE_FORMAT(date, '%c') AS month,
                    DATE_FORMAT(".JLOG_DB_CONTENT.".date, '%Y') AS year,
                    UNIX_TIMESTAMP(date) AS date,
		    teaser, teaserpic, teaserpiconblog, keywords, content,
		    comments, allowpingback, section
              FROM ".JLOG_DB_CONTENT.$where_from."
              WHERE
               YEAR(date) = '".escape_for_mysql($yl->get_selected_year())."'
              ".$where_month."
               AND section = 'weblog'
              ORDER BY mysql_date;";

        $c['main'] .= "<p>".$yl->get_linklist()."</p>";
    }
 
 $cc = count_comments();

    $archive = new Query($sql_archive);
     if($archive->error()) {
        echo "<pre>\n";
        echo $archive->getError();
        echo "</pre>\n";
        die();
     }

$months = array_flip($l['months']);

if($archive->numRows() > 0) {
    // initialise variables to keep track of last posts month and year
    $last_month = false;
    $last_year = false;
    
    while ($daten = $archive->fetch()) {
     if(empty($daten)) break 1;
     
     // did we already reach a new month or year?
     if (($last_month != $daten['month']) OR ($last_year != $daten['year'])) {
            if ($last_month) { $c['main'] .=  "    </div>\n"; }
            $c['main'] .= "   <h3>".array_search($daten['month'], $months)." ".$daten['year']."</h3>\n";
            $c['main'] .= "    <div class='archive'>\n";
            // set last month and year to values of current post
            $last_month = $daten['month'];
            $last_year = $daten['year'];
     }
      $c['main'] .= do_teaser($daten, $cc, "<h4>", "</h4>");
    }
    if(empty($get['y'])) {
        $c['main'] .= "<p class='archivenavigation'>";
        if(($p - $amount) >= 0) {
            $c['main'] .= "<a href='?show=".($p - $amount)."'><strong>&lt;&mdash;</strong> ".$l['content_archive_preview']."</a>";
            $c['meta']['aditionalheader'] .= '  <link rel="prev" href="?show='.($p - $amount).'" title="'.$l['content_archive_preview'].'" />'."\n";
        }
        if((($p - $amount) >= 0) && (($p + $amount) < $count)) $c['main'] .= " | ";
        if(($p + $amount) < $count) {
            $c['main'] .= "<a href='?show=".($p + $amount)."'>".$l['content_archive_next']." <strong>&mdash;&gt;</strong></a>";
            $c['meta']['aditionalheader'] .= '  <link rel="next" href="?show='.($p + $amount).'" title="'.$l['content_archive_next'].'" />'."\n";
        }
        $c['main'] .= "</p>";
     }
     $c['main'] .= "</div>\n";
}


require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'do_template.php');
echo $body;
?>
