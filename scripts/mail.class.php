<?php

/**
 * Mail class
 * 
 * This class is used for sending emails from Jlog, e.g. for notifying
 * the admin about a new comment.
 * 
 * @category  Jlog
 * @package   Jlog_Mail
 * @license   GNU General Public License
 *
 */
class Jlog_Mail
{
    /**
    * Additional headers
    * 
    * Headers for the email in an array where the key is the
    * header field and the value is the value of that header.            
    *
    * @access private
    * @var array
    */
    var $_header = array();
    
    /**
    * E-Mail From
    *
    * @access private
    * @var string
    */
    var $_from = '';
    
    /**
    * E-Mail Subject
    *
    * @access private
    * @var string
    */
    var $_subject = '';
    
    /**
    * Content of the E-Mail
    *
    * @access private
    * @var string
    */
    var $_text = '';
    
    /**
    * Flag to drop mail
    * 
    * This flag can be set, e.g. by a plugin, to make the 
    * send() method drop this mail            
    *
    * @access private
    * @var boolean
    */
    var $_dropped = false;
    
    /**
    * Jlog_Mail() - constructor
    *
    */
    function Jlog_Mail()
    {
        $this->_from = 'no-reply@' . $_SERVER['SERVER_NAME'];
        $this->_subject = 'Kein Betreff - No Subject';
        $this->addHeader('MIME-Version', '1.0');
        $this->addHeader('Content-Type', 'text/plain; charset=UTF-8');
        $this->addHeader('Content-Transfer-Encoding', '8bit');
        $this->addHeader('X-Mailer', 'Jlog with PHP ' . phpversion());
    }
    
    /**
    * setFrom() - sets from value
    *
    * @access public
    * @param string $email
    * @param string $name    
    * @return void
    */
    function setFrom($email, $name) 
    {
        if (!empty($email) and !empty($name)) {
            $this->_from = "$name <$email>";
        }
    }
    
    /**
    * getFrom() - gets from value
    *     
    * @access public
    * @return string
    */
    function getFrom()
    {
        return $this->_from;
    }
    
    /**
    * setSubject() - sets subject
    * 
    * @access public
    * @param string $text
    * @return void
    */
    function setSubject($text)
    {
        if (strlen($text) > 0) {
            $this->_subject = $text;
        }
    }
    
    /**
    * getSubject() - gets subject
    * 
    * @access public
    * @return string
    */
    function getSubject()
    {
        return $this->_subject;
    }
    
    /**
    * setText() - sets content of email
    * 
    * @access public
    * @param string $text
    * @return void
    */
    function setText($text) 
    {
        $this->_text = $text;
    }
    
    /**
    * appendText() - appends content to the email
    * 
    * @access public
    * @param string $text
    * @return void
    */
    function appendText($text) 
    {
        $this->_text .= $text;
    }
    
    /**
    * getText() - gets content of email
    * 
    * @access public
    * @return string
    */
    function getText()
    {
        return $this->_text;
    }
    
    /**
    * addHeader() - adds an additional header
    * 
    * Adds a header, by replacing any earlier headers of the same name.
    * If no value is passed, the key is interpreted as a header line and
    * split into key and value.        
    *
    * @access public
    * @param string $key
    * @param string $value    
    * @return void
    */
    function addHeader($key, $value = null)
    {
        if ($value === null) {
            $data = explode(':', $key, 1);
            if (count($data) !== 2) return false;
            $key = $data[0];
            $value = $data[1];
        }
        if (strlen($key) < 1 or strlen($value) < 1) return false;
        $this->_header[$key] = $value;
    }
    
    /**
    * unsetHeader() - deletes a header
    * 
    * @access public
    * @param string $key
    * @return void
    */
    function unsetHeader($key)
    {
        if (isset($this->_header[$key])) unset($this->_header[$key]);
    }
    
    /**
    * getHeader() - gets a header
    * 
    * @access public
    * @return string|null
    */
    function getHeader($key)
    {
        if (isset($this->_header[$key])) return $this->_header[$key];
        return null;
    }
    
    /**
    * getHeaders() - gets all headers
    * 
    * @access public
    * @return array
    */
    function getHeaders()
    {
        return $this->_header;
    }
    
    /**
     * getCleanHeaderString() - gets sanitized header string
     * 
     * @access public     
     * @return string
     */                   
    function getCleanHeaderString()
    {
        $headers = '';
        foreach ($this->_header as $key => $value) {
            // remove all non alpha-numeric chars, except for dash
            $key = preg_replace('/[^a-zA-Z0-9-]/', '', $key);
            // remove line breaks to prevent header injection
            $value = str_replace(array("\r", "\n"), '', $value);
            // add header
            $headers .= "$key: $value\r\n";
        }
        return $headers;
    }
    
    /**
    * dropMail() - sets drop mail flag
    * 
    * Sets a flag that causes the send() method to skip sending this
    * email, so that this mail is actualy dropped.    
    *
    * @access public
    * @return void
    */
    function dropMail()
    {
        $this->_dropped = true;
    }
    
    /**
    * send() - sends mail
    * 
    * @access public
    * @param string $to
    * @return boolean
    */
    function send($to)
    {
        if ($this->_dropped) return false;
        
        $this->addHeader('From', $this->_from);
        
        $safe_mode = strtolower(ini_get('safe_mode'));
        if ($safe_mode == 1 or $safe_mode == 'on' or $safe_mode == 'yes') {
            @mail($to, $this->_subject, $this->_text, $this->getCleanHeaderString());
        }
        else {
            @mail($to, $this->_subject, $this->_text, $this->getCleanHeaderString(), "-f".JLOG_EMAIL);
        }
        return true;
    }
}

// eof
