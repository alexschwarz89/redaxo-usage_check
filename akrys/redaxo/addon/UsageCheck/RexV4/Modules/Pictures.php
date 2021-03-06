<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace akrys\redaxo\addon\UsageCheck\RexV4\Modules;

require_once __DIR__.'/../../Modules/Pictures.php';

/**
 * Datei für ...
 *
 * @version       1.0 / 2016-05-05
 * @package       new_package
 * @subpackage    new_subpackage
 * @author        akrys
 */

/**
 * Description of Pictures
 *
 * @author akrys
 */
class Pictures
	extends \akrys\redaxo\addon\UsageCheck\Modules\Pictures
{

	/**
	 * XFormTables holen
	 *
	 * @return array
	 * @param array &$return
	 */
	protected function getXFormSQL(&$return)
	{
		$tables = array();
		$rexSQL = new \rex_sql;
		if (!\OOAddon::isAvailable('xform')) {
			return $tables;
		}

		if (!\OOPlugin::isAvailable('xform', 'manager')) {
			return $tables;
		}

		$xformTableTable = \akrys\redaxo\addon\UsageCheck\RedaxoCall::getTable('xform_table');
		$xformFieldTable = \akrys\redaxo\addon\UsageCheck\RedaxoCall::getTable('xform_field');

		$xformtable = $rexSQL->getArray("show table status like '$xformTableTable'");
		$xformfield = $rexSQL->getArray("show table status like '$xformFieldTable'");
		$sql = <<<SQL
select f.table_name, t.name as table_out,f.f1,f.f2,f.type_name
from $xformFieldTable f
left join $xformTableTable t on t.table_name=f.table_name
where type_name in ('be_mediapool','be_medialist','mediafile')
SQL;

		$xformtableExists = count($xformfield) > 0;
		$xformfieldExists = count($xformtable) > 0;

		if ($xformfieldExists <= 0 || $xformtableExists <= 0) {
			return $tables;
		}

		if ($xformfieldExists && $xformtableExists) {
			$tables = $rexSQL->getArray($sql);
		}
		return $tables;
	}

	/**
	 * Überprüfen, ob eine Datei existiert.
	 *
	 * @global type $REX
	 * @param array $item
	 * @return boolean
	 */
	public function exists($item)
	{
		return file_exists($GLOBALS['REX']['MEDIAFOLDER'].DIRECTORY_SEPARATOR.$item['filename']);
	}

	/**
	 * Spezifisches SQL für redaxo 4
	 * @param string $additionalSelect
	 * @param string $additionalJoins
	 * @return string
	 */
	protected function getPictureSQL($additionalSelect, $additionalJoins)
	{
//Keine integer oder Datumswerte in einem concat!
//Vorallem dann nicht, wenn MySQL < 5.5 im Spiel ist.
// -> https://stackoverflow.com/questions/6397156/why-concat-does-not-default-to-default-charset-in-mysql/6669995#6669995

		$fileTable = \akrys\redaxo\addon\UsageCheck\RedaxoCall::getTable('file');
		$articleSliceTable = \akrys\redaxo\addon\UsageCheck\RedaxoCall::getTable('article_slice');
		$articleTable = \akrys\redaxo\addon\UsageCheck\RedaxoCall::getTable('article');

		$sql = <<<SQL
SELECT f.*,count(s.id) as count,
group_concat(distinct concat(
	cast(s.id as char),"\\t",
	cast(s.article_id as char),"\\t",
	a.name,"\\t",
	cast(s.clang as char),"\\t",
	cast(s.ctype as char)
) Separator "\\n") as slice_data

$additionalSelect

FROM $fileTable f
left join `$articleSliceTable` s on (
    s.file1=f.filename
 OR s.file2=f.filename
 OR s.file3=f.filename
 OR s.file4=f.filename
 OR s.file5=f.filename
 OR s.file6=f.filename
 OR s.file7=f.filename
 OR s.file8=f.filename
 OR s.file9=f.filename
 OR s.file10=f.filename
 OR find_in_set(f.filename, s.filelist1)
 OR find_in_set(f.filename, s.filelist2)
 OR find_in_set(f.filename, s.filelist3)
 OR find_in_set(f.filename, s.filelist4)
 OR find_in_set(f.filename, s.filelist5)
 OR find_in_set(f.filename, s.filelist6)
 OR find_in_set(f.filename, s.filelist7)
 OR find_in_set(f.filename, s.filelist8)
 OR find_in_set(f.filename, s.filelist9)
 OR find_in_set(f.filename, s.filelist10)
)

left join $articleTable a on (a.id=s.article_id and a.clang=s.clang)

$additionalJoins

SQL;
		return $sql;
	}

	public function getMedium($item)
	{
		if (!$GLOBALS['REX']['USER']->isAdmin() && !$GLOBALS['REX']['USER']->hasPerm('media['.$item['category_id'].']')) {
			//keine Rechte am Medium
		} else {
			//Das Medium wird später gebraucht.
			/* @var $medium OOMedia */
			$medium = \OOMedia::getMediaByFileName($item['filename']);
			return $medium;
		}
		throw new \akrys\redaxo\addon\UsageCheck\Exception\FunctionNotCallableException();
	}

	/**
	 * Bildvorschau ausgeben
	 *
	 * @return void
	 * @param array $item Ein Element der Ergebnismenge
	 */
	public function outputImagePreview($item)
	{
		if (stristr($item['filetype'], 'image/')) {
			?>

			<img alt="" src="../index.php?rex_img_type=rex_mediapool_detail&amp;rex_img_file=<?php echo $item['filename']; ?>" style="max-width:150px;max-height: 150px;" />
			<br /><br />

			<?php
		}
	}

	/**
	 * Menü ausgeben
	 * @return void
	 * @param string $subpage
	 * @param string $showAllParam
	 * @param string $showAllLinktext
	 */
	public function outputMenu($subpage, $showAllParam, $showAllLinktext)
	{
		?>

		<p class="rex-tx1">
			<a href="index.php?page=<?php echo \akrys\redaxo\addon\UsageCheck\Config::NAME; ?>&subpage=<?php echo $subpage; ?><?php echo $showAllParam; ?>"><?php echo $showAllLinktext; ?></a>
		</p>
		<p class="rex-tx1"><?php echo \akrys\redaxo\addon\UsageCheck\RedaxoCall::i18nMsg('akrys_usagecheck_images_intro_text'); ?></p>

		<?php
	}
}
