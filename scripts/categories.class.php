<?php
$categories = new Categories($l);

class Categories {

    var $categories = array();
    var $l = array();

    function Categories($l) {
    
        $this->l = $l;
    
        $this->get_categories();
    }

    function get($id, $data) {
        return $this->categories[$id][$data];
    }

    function get_id($url) {
        foreach($this->categories AS $cat) {
            if($cat['url'] == $url) return $cat['id'];
        }

    }
    
    function get_categories() {
        if(!defined("JLOG_UPDATE") AND !defined("JLOG_LOGIN")) {
            $sql = "SELECT id, name, url, description FROM ".JLOG_DB_CATEGORIES;
            $cat = new Query($sql);
           if($cat->error()) {
                echo "<pre>\n";
                echo $cat->getError();
                echo "</pre>\n";
                die();
           }
           while($c = $cat->fetch()) {
            $this->categories[$c['id']] =
             array('id' => $c['id'], 'name' => $c['name'], 'url' => $c['url'], 'description' => $c['description'] );
           }
        }
    }
    
    function get_assigned_categories($id) {
        $sql = "SELECT cat_id FROM ".JLOG_DB_CATASSIGN." WHERE content_id = '".$id."'";
        $assigned = new Query($sql);
       if($assigned->error()) {
            echo "<pre>\n";
            echo $assigned->getError();
            echo "</pre>\n";
            die();
       }
       $ids = array();
       while($a = $assigned->fetch()) {
        $ids[] = $a['cat_id'];
       }
       return $ids;
    }

    function output_select($catassign) {
    // $catassign is an array which contains all assigned ids
    
        if(count($this->categories) > 0) {
            $output = "     <p><label for='categories'>".$this->l['admin']['categories']."</label><br />\n"
                        ."      <select id='categories' name='categories[]' size='4' multiple='multiple'>\n"
                        ."       <option value='no_categories'>".$this->l['admin']['no_categories']."</option>\n";
    
           foreach($this->categories AS $id => $data) {
                if(is_array($catassign)) if(in_array($id, $catassign)) $selected = " selected='selected'";
                else unset($selected);
                $output .= "       <option".$selected." value='".$id."'>".$data['name']."</option>\n";
            }
    
            $output .= "      </select>\n     </p>";
    
            return $output;
        }
    }

    function output_rss($id) {
        $ids = $this->get_assigned_categories($id);
        if(is_array($ids)) {
            foreach($ids AS $i) {
                $output .= "        <category>".$this->get($i, 'name')."</category>\n";
            }
        }
        return $output;
    }

    function output_assigned_links($ids) {
        if(!is_array($ids)) $ids = $this->get_assigned_categories($ids);
        if(is_array($ids)) {
            foreach($ids as $id) {
                $output .= $this->link($id)." ";
            }
        }
        if(isset($output)) return " <span title='".$this->l['content_cat_linklist']."' class='catlinklist'>&raquo; ".$output."</span>";
    }
    
    function output_whole_list($_before = " <ul id='categorieslist'>\n", $_after = " </ul>\n", $before = "  <li>", $after = "</li>\n") {
        if(is_array($this->categories) AND count($this->categories)) {
            $output = $_before;
            foreach($this->categories AS $id => $tmp) {
               $output .= $before.$this->link($id).$after;
            }
            $output .= $_after;
            return $output;
        }
        else return false;
    }
    
    function link($id) {
        if(JLOG_CLEAN_URL) return "<a title='".$this->l['content_cat_link']."' href='".JLOG_PATH."/cat/".$this->categories[$id]['url']."/'>".$this->categories[$id]['name']."</a>";
        else return "<a title='".$this->l['content_cat_link']."' href='".JLOG_PATH."/archive.php?cat=".$this->categories[$id]['url']."'>".$this->categories[$id]['name']."</a>";
    }
    
    function output_whole_list_admin() {
        $output = "
        <table>
         <tr>
          <th>".$this->l['admin']['change']."</th>
          <th>".$this->l['admin']['delete']."</th>
          <th>".$this->l['admin']['cat_name']."</th>
         </tr>";
    
         foreach($this->categories AS $id => $tmp) {
            $output .= "
         <tr>
          <td><a href='".add_session_id_to_url("?action=change&amp;id=".$id)."'><img src='".JLOG_PATH."/img/JLOG_edit.png' alt='".$this->l['admin']['change']."' /></a></td>
          <td><a href='".add_session_id_to_url("?action=trash&amp;id=".$id)."'><img src='".JLOG_PATH."/img/JLOG_trash.png' alt='".$this->l['admin']['delete']."' /></a></td>
          <td>".$this->link($id)."</td>
         </tr>\n";
         }

        $output .= "        </table>\n";
    
        return $output;
    }
    
    function output_form($form_input = "", $action = 'new', $legend) {
        $output = "
        <form id='entryform' action='?action=".$action."' method='POST'>
         <fieldset><legend>".$legend."</legend>
         <p><label for='name'>".$this->l['admin']['cat_name']."</label><br />
          <input id='name' name='name' class='long' maxlength='255' size='60' type='text' value='".$form_input['name']."' /></p>
         <p><label for='url'>".$this->l['admin']['cat_url']."</label><br />
          <input id='url' name='url' class='long' maxlength='100' size='60' type='text' value='".$form_input['url']."' />
          <input name='id' type='hidden' value='".$form_input['id']."' /></p>
         <p><label for='description'>".$this->l['admin']['cat_description']."</label><br />
          <textarea id='description' name='description' class='short'>".$form_input['description']."</textarea></p>
         <p><input type='submit' name='form_submit' value='".$this->l['admin']['submit']."' />
          <a href='".add_session_id_to_url("categories.php")."'>".$this->l['admin']['cancel']."</a>
          ".add_session_id_input_tag()."</p>
        </fieldset>
        </form>";

        return $output;
    }

    function new_cat($form_input) {
    
        $form_input = escape_for_mysql($form_input);
    
        $sql = "INSERT INTO ".JLOG_DB_CATEGORIES." (name, url, description) VALUES
                    ('".$form_input['name']."',
                     '".$form_input['url']."',
                     '".$form_input['description']."');";

        $new = new Query($sql);
    
        if($new->error()) {
         echo "<pre>\n";
         echo $new->getError();
         echo "</pre>\n";
         die();
        }
    }
    
    function change_cat($form_input) {

        $form_input = escape_for_mysql($form_input);
    
        $sql = "UPDATE ".JLOG_DB_CATEGORIES."
                  SET
                    name            = '".$form_input['name']."',
                    url         = '".$form_input['url']."',
                    description = '".$form_input['description']."'
                  WHERE
                   id = '".$form_input['id']."' LIMIT 1;";

        $change = new Query($sql);
    
        if($change->error()) {
         echo "<pre>\n";
         echo $change->getError();
         echo "</pre>\n";
         die();
        }
    }
    
    function trash_cat($id) {
    
        $sql = "DELETE FROM ".JLOG_DB_CATEGORIES." WHERE id = '".escape_for_mysql($id)."' LIMIT 1";
        $trash = new Query($sql);
       if($trash->error()) {
        echo "<pre>\n";
        echo $trash->getError();
        echo "</pre>\n";
        die();
       }

        $sql = "DELETE FROM ".JLOG_DB_CATASSIGN." WHERE cat_id = '".escape_for_mysql($id)."' LIMIT 1";
        $trash = new Query($sql);
       if($trash->error()) {
        echo "<pre>\n";
        echo $trash->getError();
        echo "</pre>\n";
        die();
       }

    
    }
    
    function validate($form_input) {
        if(empty($form_input['name'])) $errors[] = $this->l['admin']['cat_noname'];

        if(empty($form_input['url'])) $errors[] = $this->l['admin']['no_url'];
        elseif(!preg_match("/^[a-z0-9\-_\.,]+$/", $form_input['url'])) $errors[] = $this->l['admin']['false_url_letters'];
        else {
            $sql = "SELECT id FROM ".JLOG_DB_CATEGORIES." WHERE url = '".escape_for_mysql($form_input['url'])."';";
    
            $check_url = new Query($sql);

            if($check_url->error()) {
             echo "<pre>\n";
             echo $check_url->getError();
             echo "</pre>\n";
             die();
            }

            if($check_url->numRows() > 0) {
                $c = $check_url->fetch();
                if($c['id'] != $form_input['id']) $errors[] = $this->l['admin']['cat_duplicate'];
            }
        }
    
        return $errors;
    }
}
?>
