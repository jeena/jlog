<?php
// call database class
    class Query {
    // Variablen
     var $_sql = "";
     var $_result = 0;
     var $_errno = 0;
     var $_error = "";

       //Konstruktor
        function __construct($sql)
        {
	  global $mysql;
          // Query in der Klasse speichern
          $this->_sql = trim($sql);
          $this->_result = mysqli_query($mysql, $this->_sql);
          if(!$this->_result) {
            $this->_errno = mysqli_errno($mysql);
            $this->_error = mysqli_error($mysql);
          }
        }

       //Methoden
        function error()
        {
        // Result-ID in einer tmp-Variablen speichern
         $tmp = $this->_result;
        // Variable in boolean umwandeln
         $tmp = (bool)$tmp;
        // Variable invertieren
         $tmp = !$tmp;
        // und zurückgeben
         return $tmp;
        }

        function getError() {
         if($this->error()) {
            $str  = "request:\n".$this->_sql."\n";
            $str .= "response:\n".$this->_error."\n";
            $str .= "Errorcode: ".$this->_errno;
         } 
         else $str = "No error.";
         return $str;
        }
        function getErrno() {
         return $this->_errno;
        }

        function fetch() {
         global $mysql;
         if($this->error()) {
            echo "An Error has occurred, please check your MySQL-Query.";
            $return = null;
         } 
         else $return = mysqli_fetch_assoc($this->_result);
         return $return;
        }

        function numRows() {
         if($this->error()) {
            $return = -1;
         }
         else $return = mysqli_num_rows($this->_result);
         return $return;
        }
        
        function free() {
        // Speicher freimachen
         mysqli_free_result($this->_result);
        }

    }
?>
