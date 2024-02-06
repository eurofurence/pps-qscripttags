<?php

//TODO: Bugs:
// - lexer combines all plugin search strings. If you have too many replacements in a namespace (something between 500 and 1000),
//   the regex gets too long. This has to be changed to an action plugin to fix that, and we can combined regexes in chunks.

/**
 * QScript Tags DokuWiki plugin
 */
class syntax_plugin_qscripttags extends DokuWiki_Syntax_Plugin {
	private $subs = [];
	private $regexSubs = [];
	private $simpleSubs = [];
	private $didInit = false;

	// Values from the file
	static $ORIG = 0;
	static $TO = 1;
	static $IN = 2;
	static $TOOLTIP = 3;
	// Calculated values
	static $MATCH = 4;
	static $TEXT = 5;

	public function __construct() {

		/** @type helper_plugin_qscripttags $helper */
		$helper = plugin_load('helper', 'qscripttags');
		$cfg = $helper->loadConfigFile();

		// Convert the config into usable data.
		$lines = preg_split('/[\n\r]+/', $cfg);
		foreach ($lines as $line) {
			$line = trim($line);
			if (strlen($line)) {
				$data = str_getcsv($line);

				if (strlen($data[self::$ORIG]) ) {
					$orig = trim($data[self::$ORIG]);
					$s = [];
					$s[self::$ORIG] = $orig;
					$s[self::$TO] = trim($data[self::$TO]);
					// Add word breaks, and collapse one space (allows newlines).
					$s[self::$MATCH] = preg_replace('/ /', '\s', $orig);
					$this->subs[] = $s;
				}
			}
		}
	}


	/**
	 * @return string
	 */
	function getType() {
		return 'substition';
	}


	/**
	 * @return string
	 */
	function getPType() {
		return 'normal';
	}


	/**
	 * @return int
	 */
	function getSort() {
		// Try not to interfere with any other lexer patterns.
		return 999;
	}


	/**
	 * @param $mode
	 */
	function connectTo($mode) {
foreach ($this->subs as $s) {

		$this->Lexer->addSpecialPattern($s[self::$MATCH], $mode, 'plugin_qscripttags');
}
	}


	/**
	 * Handle the found text, and send it off to render().
	 *
	 * @param string $match - The found text, from addSpecialPattern.
	 * @param int $state - The DokuWiki event state.
	 * @param int $pos - The position in the full text.
	 * @param Doku_Handler $handler
	 * @return array|string
	 */
	function handle($match, $state, $pos, Doku_Handler $handler) {
		// Save initialization of regexSubs and simpleSubs until now. No reason to do all that pre-processing
		// if there aren't any substitutions to make.
		if (!$this->didInit) {
			$this->didInit = true;
			foreach ($this->subs as $s) {
				$orig = $s[self::$ORIG];
				// If the search string is not a regex, cache it right away, so we don't have to loop through
				// regexes later.
				if (!preg_match('/[\\\[?.+*^$]/', $orig)) {
					$this->simpleSubs[$orig] = $s;
					$this->simpleSubs[$orig][self::$TEXT] = $orig;
				}
				else {
					$this->regexSubs[] = $s;
				}
			}
		}

		// Load from cache
		if (isset($this->simpleSubs[$match])) {
			return $this->simpleSubs[$match];
		}

		// Annoyingly, there's no way (I know of) to determine which match sent us here, so we have to loop through the
		// whole list.
		foreach ($this->regexSubs as &$s) {
			if (preg_match('/^' . $s[self::$MATCH] . '$/', $match)) {
				// Add all found matches to simpleSubs, so we don't have to loop more than once for the same string.
				$mod = null;
				if (!isset($this->simpleSubs[$match])) {
					$mod = $s;
					$mod[self::$TEXT] = $match;
					$this->simpleSubs[$match] = $mod;
				}

				return $mod;
			}
		}

		return $match;
	}


	/**
	 * Render the replaced tags.
	 *
	 * @param string $mode
	 * @param Doku_Renderer|Doku_Renderer_metadata $renderer
	 * @param array|string $data - Data from handle()
	 * @return bool
	 */
	function render($mode, Doku_Renderer $renderer, $data) {
		if ($mode == 'xhtml') {			
			$renderer->doc .= $data[self::$TO];
			return true;
		}
		return false;
	}
}
