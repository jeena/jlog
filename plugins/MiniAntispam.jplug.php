<?php
/**
 * @name:        MiniAntispam <jeenaparadies.net/projects/jlog/>
 * @author:      Jeena Paradies <jlog@jeenaparadies.net>
 * @version:     1.0
 * @date:        2006-05-10
 */

class MiniAntispam extends JlogPlugin {

    function hook_commentForm($form, $cf) {
        $uid = $this->generate_uid();
        $uid_inputs  = "\n     ".'<input type="hidden" name="privatkey" value="'.$uid.'" />';
        $uid_inputs .= "\n     ".'<input type="text" style="display: none;" name="publickey" value="'.$uid.'" />';

        return str_replace("<p class='xmp'>", "<p class='xmp'>".$uid_inputs, $form);
    }

    function hook_newComment($form) {
        if(empty($form['publickey']) OR $form['publickey'] != $form['privatkey']) {
            die("<h1>Hi spammer</h1>Please don't spam me.");
        }
        return $form;
    }

    function generate_uid($len = 40) {
        $acceptedChars = 'azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN0123456789.,-;_';
        $maxchar = strlen($acceptedChars)-1;
        $uid = '';
        mt_srand((double)microtime()*1000000);

        for($i=0; $i < $len; $i++) $uid .= $acceptedChars[mt_rand(0, $maxchar)];

        return $uid;
    }

}
?>
