<?php
/**
 * @name:        CommentCloser <jeenaparadies.net/projects/jlog/>
 * @author:      Robert Bienert <robertbienert@gmx.net>
 * @version:     1.3
 * @date:        2009-01-04
 *
 * Dieses Plugin schliesst nach einer bestimmten, festzulegenden
 * Laufzeit die Kommentare eines Artikels.
 *
 * Konstanten-Praefix: COMMENT_CLOSER
 */

define('COMMENT_CLOSER_CFG_FILE', JLOG_BASEPATH . 'personal' .
		DIRECTORY_SEPARATOR . 'settings.CommentCloser.inc.php');

if (@file_exists(COMMENT_CLOSER_CFG_FILE))
	include_once COMMENT_CLOSER_CFG_FILE;

class CommentCloser extends JlogPlugin {
	var $_units = array('SECOND', 'MINUTE', 'HOUR', 'DAY', 'WEEK',
		'MONTH', 'YEAR',
	);
	// Dieses Array kann lokalisiert werden:
	var $_unit_desc = array('Sekunden', 'Minuten', 'Stunden',
		'Tagen', 'Wochen', 'Monaten', 'Jahren'
	);

	// Konfigurationsoberflaeche
	function hook_adminContent($output) {
		$lifeTime = defined('COMMENT_CLOSER_LIFETIME') ?
				COMMENT_CLOSER_LIFETIME : '';
		$timeUnit = defined('COMMENT_CLOSER_TIMEUNIT') ?
				COMMENT_CLOSER_TIMEUNIT : '';
		$self = htmlspecialchars($_SERVER['REQUEST_URI']);

		if (array_key_exists('commclose_do', $_POST)) {
			$lifeTime = $_POST['commclose_lifetime'];
			$timeUnit = $_POST['commclose_unit'];
			// Einheiten pruefen
			if (! in_array($timeUnit, $this->_units))
				$timeUnit = '';

			// keine (negative) Zeit XXX implicit cast
			if ($lifeTime + 0 <= 0)
				$lifeTime = 0;

			# XXX MySQL bug: WEEK wird bei mir nicht
			# erkannt, deshalb manuelles Umrechnen:
			if ($timeUnit == 'WEEK') {
				$lifeTime *= 7;
				$timeUnit = 'DAY';
			}

			$mask = umask(0);

			if (($f = @fopen(COMMENT_CLOSER_CFG_FILE, 'wb')))
			{
				@fwrite($f, "<?php\n");
				@fwrite($f, 'define(\'COMMENT_CLOSER_'.
					"LIFETIME', $lifeTime);\n");
				@fwrite($f, 'define(\'COMMENT_CLOSER_'.
					"TIMEUNIT', '$timeUnit');\n");
				@fwrite($f, '?>');
				@fclose($f);
			}

			umask($mask);
		}

		$output = <<<EOT
<form action="$self" method="post">
<p><label>Kommentare nach
	<input type="text" name="commclose_lifetime" size="5" value="$lifeTime" /></label>
	<label><select name="commclose_unit" size="1">
EOT;

		$units = count($this->_units);

		for ($i = 0; $i < $units; ++$i) {
			$output .= '<option value="';
			$output .= $this->_units[$i];

			if ($this->_units[$i] == $timeUnit)
				$output .= '" selected="selected';

			$output .= "\">{$this->_unit_desc[$i]}</option>";
		}
		
		$output .= '</select> '.add_session_id_input_tag();
        
		$output .= <<<EOT
automatisch</label>
<button name="commclose_do" value="schlie&#223;en">schlie&#223;en</button></p>
<p>Nach der angegeben Zeit k&#246;nnen Beitr&#228;ge nicht mehr kommentiert werden.</p>
</form>
EOT;

		return $output;
	}

	// Pruefen auf zu schliessende Beitraege
	function hook_onUpdate($data) {
		// Kein Schliessen gewuenscht, Plugin nicht konfiguriert
		// oder ungueltige Werte gesetzt:
		if (!defined('COMMENT_CLOSER_LIFETIME') ||
			COMMENT_CLOSER_LIFETIME+0 <= 0 ||
			!defined('COMMENT_CLOSER_TIMEUNIT') ||
			!in_array(COMMENT_CLOSER_TIMEUNIT,
				$this->_units))
		{
			return $data;
		}

		$q = new Query('UPDATE ' . JLOG_DB_PREFIX .
				'content SET comments=0 ' .
			'WHERE date < DATE_SUB(NOW(), INTERVAL ' .
				COMMENT_CLOSER_LIFETIME . ' ' .
				COMMENT_CLOSER_TIMEUNIT .
			') AND comments=1');
		# TODO error handling

		return $data;
	}
}
?>
