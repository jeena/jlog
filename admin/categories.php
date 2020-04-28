<?php
 include_once('.'.DIRECTORY_SEPARATOR.'auth.php');
 define("JLOG_ADMIN", true);
 require_once('..'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');
 require(JLOG_BASEPATH.'admin'.DIRECTORY_SEPARATOR.'blog.func.php');

 $get = strip($_GET);
 $form_input = strip($_POST);

 $c['meta']['title'] = $l['admin']['cat_title'];
 $c['main'] = output_admin_menu()."<h2>".$l['admin']['cat_title']."</h2>\n";
 
	switch ($get['action']) {
	
		case 'new':
			if(isset($form_input['form_submit'])) {
				if(!is_array($errors = $categories->validate($form_input))) {
					$categories->new_cat($form_input);
					$categories->get_categories();
					$c['main'] .= "<p><strong>&raquo;&raquo;</strong> <a href='".add_session_id_to_url("?action=new")."'>".$l['admin']['cat_new']."</a></p>
									 ".$categories->output_whole_list_admin();
				}
				else {
					$c['main'] .= error_output($errors);
					$c['main'] .= $categories->output_form($form_input, 'new', $l['admin']['cat_new']);
				}
			}
			else $c['main'] .= $categories->output_form(array('id' => NULL, 'name' => NULL, 'url' => NULL, 'description' => NULL), 'new', $l['admin']['cat_new']);
			break;

		case 'change':
			if(isset($form_input['form_submit'])) {
				if(!is_array($errors = $categories->validate($form_input))) {
					$categories->change_cat($form_input);
					$categories->get_categories();
					$c['main'] .= "<p>".$l['admin']['cat_new_ok']."</p>".$categories->output_whole_list_admin();
				}
				else {
					$c['main'] .= error_output($errors);
					$c['main'] .= $categories->output_form($form_input, 'new', $l['admin']['cat_new']);
				}
			}
			else {
				$form_input['name'] = $categories->get($get['id'], 'name');
				$form_input['id'] = $get['id'];
				$form_input['url'] = $categories->get($get['id'], 'url');
				$form_input['description'] = $categories->get($get['id'], 'description');
				
				$c['main'] .= $categories->output_form($form_input, 'change', $l['admin']['cat_change']);
			}
		  	break;

		case 'trash':
			if($form_input['form_submit'] == $l['admin']['yes']) {
				$categories->trash_cat($form_input['id']);
				$categories->get_categories();
				$c['main'] .= "<p>".$l['admin']['cat_trash_ok']."
									 <a href='".add_session_id_to_url("categories.php")."'>".$l['admin']['cat_admincenter']."</a></p>";
			}
			else {
				$c['main'] .= "<form action='?action=trash' method='POST' accept-charset='UTF-8'>
				                <p>".$l['admin']['cat_really_trash']."</p>
				                <h3>".$categories->link($get['id'])."</h3>
				                <p><input type='submit' name='form_submit' value='".$l['admin']['yes']."' />
				                   <input type='hidden' name='id' value='".$get['id']."' />
				                   ".add_session_id_input_tag()."
				                   <a href='".add_session_id_to_url("categories.php")."'>".$l['admin']['no']."</a></p>
				               </form>";
			}
			break;
		
		default:
			$c['main'] .= "<p><strong>&raquo;&raquo;</strong> <a href='".add_session_id_to_url("?action=new")."'>".$l['admin']['cat_new']."</a></p>
							  ".$categories->output_whole_list_admin();
	}

require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'do_template.php');
echo $body;
?>
