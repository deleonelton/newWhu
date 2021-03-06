<?php
include_once("host.php");
include_once(INCPATH . "template.inc");
include_once(INCPATH . "class.Properties.php");
include_once(INCPATH . "class.ViewBase.php");
include_once(INCPATH . "class.DBBase.php");

include_once("class.Things.php");
include_once("class.Pages.php");
// include_once("class.Geo.php");				// after Pages

include_once(INCPATH . "jfdbg.php");
$noDbg = 1;//NODBG_DFLT;

date_default_timezone_set('America/Los_Angeles');		// now required by PHP

// ---------------- Properties Class, to add useful functions -------------

class WhuProps extends Properties
{
	function __construct($props, $over = array())		// do overrides in one call
	{
		parent::__construct(array_merge($props, $over));
	}

	static function parseKeys($str)					// just return names
	{
		$ret = array();
		$vals = explode(',', $str);
		for ($i = 0; $i < sizeof($vals); $i++) 
		{
			$val = explode('=', trim($vals[$i]));
			$ret[] = trim($val[0]);
		}
		sort($ret);
		return $ret;
	}
	static function parseParms($str)				// return key/value pairs
	{
		$ret = array();
		$vals = explode(',', $str);
// dumpVar($vals, "explode(', ', $str)");
		for ($i = 0; $i < sizeof($vals); $i++) 
		{
			$val = explode('=', trim($vals[$i]));
			if (sizeof($val) < 2)
				continue;
			$ret[$val[0]] = trim($val[1]);
		}
		return $ret;
	}
	// collects the so-frequent date(fmt, strtotime(str)) in one place
	function dateFromString($fmt, $str)		{ 		return date($fmt, strtotime($str)); 		}	
}
class StyleProps extends WhuProps 
{
	var $palette = 'UNSET';
	function __construct($props, $over = array())		// little hack, overload the $over array to pass the palette name
	{
		$this->palette = $over;
		// dumpVar($over, "Set palette $over");
		parent::__construct($props);
	}

	function pageBackColor() { return $this->getDefault("bbackcolor", "#fff"); }
	function pageLineColor() { return $this->getDefault("bodycolor" , "#000"); }
	function contBackColor() { return $this->getDefault("backcolor" , $this->pageBackColor()); }
	function contLineColor() { return $this->getDefault("linecolor" , $this->pageLineColor()); }
	function allFontColor()  { return $this->getDefault("fontcolor" , $this->contLineColor()); }
	function boldFontColor() { return $this->getDefault("boldcolor" , $this->allFontColor()); }
	function linkColor()     { return $this->getDefault("linkcolor" , $this->allFontColor()); }
	function linkHover()     { return $this->getDefault("linkhover" , $this->allFontColor()); }
}

// ---------------- Template Class, for nothing just yet -------------

class WhuTemplate extends VwTemplate
{}

// ---------------- Start Code ---------------------------------------------

// session_start();			// always make this the first thing!

$defaults = array(
	'page' => 'home', 
	'type' => 'home', 
	'key'	 =>	'', 
);

$props = new WhuProps($defaults);		// default settings
$props->set($_REQUEST);							// absorb all web parms
$props->dump('props');

$curpage = $props->get('page');
$curtype = $props->get('type');

switch ("$curpage$curtype") 
{
	case 'homehome':		$page = new HomeHome($props);			break;		
	case 'homelook':		$page = new HomeLook($props);				break;
	case 'homeread':		$page = new HomeRead($props);				break;
	case 'homeorient':	$page = new HomeOrient($props);			break;
	case 'homebrowse':	$page = new HomeBrowse($props);			break;	
	
	case 'spotid':			$page = new OneSpot($props);			break;	
	case 'daydate':			$page = new OneDay($props);			break;	

	case 'picsid':			$page = new TripPictures($props);		break;	
	case 'picsdate':		$page = new DateGallery($props);		break;	
	case 'picdate':			$page = new OnePic($props);					break;	
	
	case 'logid':				$page = new OneTripLog($props);		break;	
	case 'mapid':				$page = new OneMap($props);			break;	
	case 'mapnear':			$page = new SpotMap($props);			break;	
	
	case 'txtsid':			$page = new TripStories($props);			break;	
	case 'txtwpid':			$page = new TripStory($props);				break;
	case 'txtdate':			$page = new TripStoryByDate($props);	break;
	
	case 'tripshome':		$page = new AllTrips($props);			break;	
	case 'searchhome':	$page = new Search($props);			break;
	case 'spotshome':		$page = new SpotsHome($props);			break;
	case 'abouthome':		$page = new About($props);			break;	
	
	default: 
		dumpVar("$curpage$curtype", "Unknown page/type:");
		echo "No Page Handler: <b>$curpage$curtype</b>";
		exit;
}

$templates = array("main" => 'container.ihtml', "the_content" => $page->file);
$page->startPage($templates);
$page->setStyle($curpage);
$savepage = $page;
$page->key = $props->get('key');		// just for convenience, everyone needs it
$page->showPage();
if (!is_object($page))
{
	$page = $savepage;
	dumpVar("NOTICE that \$page got f---d up by calling Wordpress.");
}
$page->setCaption();
$page->endPage();
?>