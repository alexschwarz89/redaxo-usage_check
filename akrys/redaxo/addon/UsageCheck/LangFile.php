<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace akrys\redaxo\addon\UsageCheck;

require_once __DIR__.'/RedaxoCall.php';

/**
 * Datei für ...
 *
 * @version       1.0 / 2015-10-27
 * @package       new_package
 * @subpackage    new_subpackage
 * @author        akrys
 */

/**
 * Description of LangFile
 *
 * @author akrys
 */
class LangFile
{
	/**
	 * Locale to copy
	 * @var string
	 */
	private $lang;

	/**
	 * construcotr
	 * @param string $lang
	 */
	public function __construct($lang)
	{
		$this->lang = $lang;
	}

	/**
	 * ISO file creation, if needed
	 * @return boolean
	 * @throws Exception\LangFileGenError
	 */
	public function createISOFile()
	{
		//Die ISO-Dateien sollten immer im Release enthalten sein,
		//also sollten sie auch immer generiert werden.
//		if (stristr($GLOBALS['REX']['LANG'], '_utf8')) {
//			//nothing needs to happen here
//			return true;
//		}
		//Im Live-Betrieb sollte sich an den Dateien nichts mehr ändern.
		//Wäre doof, wenn im Falle des Falles immer der Hinweis erscheint, dass
		//die Sparchdatei nicht geschrieben werden konnte.
//		if (Config::RELEASE_STATE == Config::RELEASE_STATE_LIVE) {
//			return true;
//		}

		switch (\akrys\redaxo\addon\UsageCheck\RedaxoCall::getRedaxoVersion()) {
			case \akrys\redaxo\addon\UsageCheck\RedaxoCall::REDAXO_VERSION_4:
				$langPath = $GLOBALS['REX']['INCLUDE_PATH'].'/addons/'.Config::NAME.'/lang/';
				$convertToIso = true;
				break;
			case \akrys\redaxo\addon\UsageCheck\RedaxoCall::REDAXO_VERSION_5:
				$langPath = \rex_path::addon(Config::NAME).'/lang/';
				$convertToIso = false;
				break;
		}

		$isoFile = $langPath.$this->lang.'.lang';
		$utfFile = $langPath.$this->lang.'_utf8.lang';

		if (!file_exists($isoFile)) {
			$timeISO = 0;
		} else {
			$timeISO = filemtime($isoFile);
		}

		$timeUTF = filemtime($utfFile);

		if ($timeUTF > $timeISO) {
			if (!is_writeable($langPath) && !file_exists($isoFile)) {
				require_once __DIR__.'/Exception/LangFileGenError.php';
				throw new Exception\LangFileGenError('"lang"-Directory not writable. Language file cannot be created (Redaxo 4 ISO-8859-1 file or Redaxo 5 UTF-8)');
			} else if (file_exists($isoFile) && !is_writeable($isoFile)) {
				require_once __DIR__.'/Exception/LangFileGenError.php';
				throw new Exception\LangFileGenError('Language files cannot be updated as they are not writable (Redaxo 4 ISO-8859-1 file or Redaxo 5 UTF-8)');
			}

			$content = file_get_contents($utfFile);
			if ($convertToIso) {
				$content = mb_convert_encoding($content, 'ISO-8859-1', 'utf-8');
			}
			file_put_contents($isoFile, $content);
		}
		return true;
	}
}
