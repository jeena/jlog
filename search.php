<?php
 require_once('.'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');

 $c['meta']['robots']       = "noindex, follow";
 $c['meta']['title'] = $l['content_search_header'];
 $c['main'] = "<h2 class='search'>".$l['content_search_header']."</h2>";

 $searchstring = isset($_GET['q']) ? strip($_GET['q']) : '';
 $btnValue = htmlspecialchars($l['content_search']);

    $c['main'] .= '     <form id="searchform" action="'.JLOG_PATH.'/search.php" accept-charset="UTF-8">
      <p><input class="userdata" type="text" name="q" size="30" value="'.htmlspecialchars($searchstring, ENT_QUOTES).'" />
         <button class="send" value="'.$btnValue.'">'.$btnValue.'</button></p>
     </form>
     <script type="text/javascript">jlog_searchpage = true;</script>
';

if(strlen($searchstring) > 0) {

 $sql_searchstring = escape_for_mysql($searchstring);

 $sql = "
                SELECT
                    id,
                    url,
                    UNIX_TIMESTAMP(date) AS date_url,
                    0 AS comment_id,
                    topic,
                    UNIX_TIMESTAMP(date) AS date,
                    keywords,
                    teaser,
                    section,
                    comments,
                    MATCH ( topic, keywords, teaser, content ) AGAINST ('".$sql_searchstring."') AS scoring
                FROM ".JLOG_DB_CONTENT."
                WHERE
                  MATCH ( topic, keywords, teaser, content ) AGAINST ( '".$sql_searchstring."' )

                UNION
                SELECT
                    ".JLOG_DB_COMMENTS.".reference AS id,
                    ".JLOG_DB_CONTENT.".url AS url,
                    UNIX_TIMESTAMP(".JLOG_DB_CONTENT.".date) AS date_url,
                    ".JLOG_DB_COMMENTS.".id AS comment_id,
                    name AS topic,
                    UNIX_TIMESTAMP(".JLOG_DB_COMMENTS.".date) AS date,
                    'comment_keywords' AS keywords,
                    ".JLOG_DB_COMMENTS.".content AS teaser,
                    'comment',
                    2,
                    MATCH(name, city, email, homepage, ".JLOG_DB_COMMENTS.".content) AGAINST ('".$sql_searchstring."') AS scoring
                FROM ".JLOG_DB_COMMENTS.", ".JLOG_DB_CONTENT."
                WHERE
                  MATCH ( name, city, email, homepage, ".JLOG_DB_COMMENTS.".content ) AGAINST ( '".$sql_searchstring."' )
                  AND ".JLOG_DB_COMMENTS.".reference = ".JLOG_DB_CONTENT.".id
                  AND ".JLOG_DB_COMMENTS.".type = ''

                ORDER BY scoring desc
                LIMIT 40;";


     $search = new Query($sql);
     if($search->error()) {
        echo "<pre>\n";
        echo $search->getError();
        echo "</pre>\n";
        die();
     }

    if($search->numRows() < 1) {
        $c['main'] .= "<p>".$l['content_nothing_found']."</p>";
    }
    else {
        $cc = count_comments();
        $c['main'] .= "<ul class='search'>\n";
        while( $data = $search->fetch() ) {
            $c['main'] .= " <li>";
            if($data['comment_id'] == 0) $c['main'] .= do_teaser($data, $cc, '<h3>', '</h3>');
            else {
                $data['url'] = $data['url'].'#c'.$data['comment_id'];
                if(empty($data['topic'])) $data['topic'] = $l['comments_anonym'];
                $data['topic'] = $l['comments_by'].": ".$data['topic'];
    
                list($data['teaser']) = explode('|*|JLOG_BREAK|*|', wordwrap(str_replace("\n", ' ', html_entity_decode(strip_tags($bbcomments->parse(trim($data['teaser']))))), 300, ' ...|*|JLOG_BREAK|*|'));
    
                $c['main'] .= do_teaser($data, 0, '<h4>', '</h4>');
            }
            $c['main'] .= " </li>\n";
        }
        $c['main'] .= "</ul>\n";
    }
}
require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'do_template.php');
echo $body;
?>
