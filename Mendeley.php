<?php

# Alert the user that this is not a valid entry point to MediaWiki, if they try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<< EOT
To install my extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/MendeleyMwExtension/Mendeley.php" );
EOT;
	exit( 1);
}

######################################
# Credits of the Mendeley Extension. #
######################################
$wgExtensionCredits[ 'specialpage' ][ ] = array(
'path' => __FILE__, # Extension path.
'name' => 'Mendeley', # Extension name.
'author' => 'Andreas Stahlhofen', # Author of the extension.
'url' => 'http://astahlhofen.mendeley.com',
# URL of the extension page.
'description' => 'Let users integrate mendeley content inside a wiki page.',
# Description of the extension.
'version' => '1.0.0' # Version of the extension.
);

##################################
# Extension configuration stuff. #
##################################
$dir = __DIR__ . '/';
$wgExtensionMessagesFiles[ 'Mendeley' ] = $dir . 'Mendeley.i18n.php';
$wgExtensionMessagesFiles[ 'MendeleyAlias' ] = $dir . 'Mendeley.alias.php';

$wgAutoloadClasses[ 'SpecialMendeley' ] = $dir . 'SpecialMendeley.php';
$wgAutoloacClasses[ 'MendeleyDbApi' ] = $dir . 'MendeleyDbApi.php';

$wgSpecialPages[ 'Mendeley' ] = 'SpecialMendeley';
$wgSpecialPageGroups[ 'Mendeley' ] = 'wiki';

$wgExtensionFunctions[ ] = 'wfMendeleyFunctions';

$wgHooks[ 'ParserAfterTidy' ][ ] = 'addJavascript';
$wgHooks[ 'ParserAfterTidy' ][ ] = 'restoreDivTags';
$wgHooks[ 'ParserAfterTidy' ][ ] = 'tidyWikiCiteKeys';

$wgHooks[ 'LanguageGetMagic' ][ ] = 'wfMendeleyFunctionsLanguageGetMagic';

# Schema updates for update.php
$wgHooks[ 'LoadExtensionSchemaUpdates' ][ ] = 'wfCreateMendeleyDBTable';

#############
# Includes. #
#############

/**
 * Mendeley API access. You need an API key for access.
 */
include_once 'lib/MendeleyAPI/MendeleyAPI.php';

/**
 * Bibtex Entry Wrapper class.
 */
include_once 'BibtexEntry.php';

// TODO Become independent from Bibtex extension.
/**
 * Bibtex extension.
 */
include_once 'lib/Bibtex/bibtex.php';

/**
 * DB Wrapper for MediaWiki.
 */
include_once 'MendeleyDbApi.php';

/**
 * Mendeley API Configuration.
 */
include_once 'lib/MendeleyAPI/Configuration.php';

/**
 * Utility class.
 */
include_once 'Utils.php';

################
# Definitions. #
################

define( 'MENDELEY_IS_DEBUG', false );

define( 'MENDELEY_NAMESPACE', 'mendeley' );

define( 'MENDELEY_FUNC_FOLDER', 'folder' );
define( 'MENDELEY_FUNC_MAPPING', 'mapping' );
define( 'MENDELEY_FUNC_AUTOMAPPING', 'auto_mapping' );
define( 'MENDELEY_FUNC_MAPPING_INFO', 'mapping_info' );
define( 'MENDELEY_FUNC_REF', MENDELEY_NAMESPACE . ':ref' );
define( 'MENDELEY_FUNC_REFERENCES', MENDELEY_NAMESPACE . ':references' );

define( 'MENDELEY_OPT_NAME', 'name' );
define( 'MENDELEY_OPT_ID', 'id' );
define( 'MENDELEY_OPT_MW_ID', 'mw_id' );
define( 'MENDELEY_OPT_MENDELEY_DOC_ID', 'md_doc_id' );
define( 'MENDELEY_OPT_HEADER', 'header' );
define( 'MENDELEY_OPT_FOLDER_NAME', 'folder' );
define( 'MENDELEY_OPT_GROUP', "group" );
define( 'MENDELEY_OPT_REFSET', "refset" );

define( 'MENDELEY_DB_TABLE_NAME', 'mendeley_id_mappings' );
define( 'MENDELEY_DB_TABLE_COL_MENDELEY_ID', 'mendeley_id' );
define( 'MENDELEY_DB_TABLE_COL_MW_ID', 'mediawiki_id' );
define( 'MENDELEY_DB_TABLE_COL_DOC_VERSION', 'doc_version' );
define( 'MENDELEY_DB_TABLE_COL_CACHED_DOC', 'cached_doc' );

define( 'MENDELEY_ARR_CITEKEY_ID', "citeKey" );
define( 'MENDELEY_ARR_REFCOUNTER_ID', "refCounter" );

/**
 * Global variable, which represents the instance of the  Mendeley Extension.
*/
$wgExtMendeley = NULL;

function restoreDivTags( &$parser, &$text ) {
	$text = preg_replace( "/&lt;(.*?)&gt;/", "<$1>", $text );
	return true;
}


function addJavascript( &$parser, &$text ) {
	global $wgScriptPath;
	var_dump( $wgScriptPath );
	$text = '<script type="text/javascript" src="' . $wgScriptPath .
	 			'/extensions/MendeleyMwExtension/js/mendeley.js"></script>' . $text;
	return true;
}

/**
 * Checks, if the mendeley class instance is already initialized for the current wiki page. This functio must be called in
 * every function, where you want to use the Mendeley class.
 */
function checkMendeleyInstance( ) {
	global $wgExtMendeley;

	// Check, if mendeley extension class variable is already initialized.
	if ( !isset( $wgExtMendeley ) ) {

		global $wgMendeleyConsumerKey, $wgMendeleyConsumerSecret;

		// First thing to do: Initialize the mendeley API with consumer key and consumer secret.
		if ( !isset( $wgMendeleyConsumerKey ) ) {
			die( 'You must specify a Mendeley Consumer Key inside your LocalSettings.php, using variable "$wgMendeleyConsumerKey".' );
		}

		if ( !isset( $wgMendeleyConsumerSecret ) ) {
			die( 'You must specify a Mendeley Consumer Secret inside your LocalSettings.php, using variable "$wgMendeleyConsumerSecret".' );
		}

		Configuration::$wgMendeleyConsumerKey = $wgMendeleyConsumerKey;
		Configuration::$wgMendeleyConsumerSecret = $wgMendeleyConsumerSecret;

		// Initialize Mendeley class.
		$wgExtMendeley = new Mendeley();
	}
}

function tidyWikiCiteKeys( &$parser, &$text ) {
	global $wgExtMendeley;

	checkMendeleyInstance();
	$wgExtMendeley->tidyWikiCiteKeys( $text );

	return true;
}

function wfCreateMendeleyDBTable( DatabaseUpdater $updater ) {
	global $wgDBprefix;

	$filename = 'sql/mendeley_db.sql';
	$sqlfile = dirname( __FILE__ ) . '/' . $filename;

	$handle = fopen( $sqlfile, 'w' ) or die( 'Cannot open file:  ' . $sqlfile );
	$query = "CREATE TABLE " . $wgDBprefix . MENDELEY_DB_TABLE_NAME . " (\n" . "	" . MENDELEY_DB_TABLE_COL_MW_ID .
	" VARCHAR(256) NOT NULL,\n" . "	" . MENDELEY_DB_TABLE_COL_MENDELEY_ID . " VARCHAR(256) NOT NULL,\n" . "	" .
	MENDELEY_DB_TABLE_COL_DOC_VERSION . " INTEGER NOT NULL,\n" . "	" .
	MENDELEY_DB_TABLE_COL_CACHED_DOC . " LONGBLOB,\n" .
	"	PRIMARY KEY(" . MENDELEY_DB_TABLE_COL_MW_ID . ")\n" . ");\n\n" . "CREATE INDEX " .
	MENDELEY_DB_TABLE_COL_MENDELEY_ID . " ON " . $wgDBprefix . MENDELEY_DB_TABLE_NAME . " (" .
	MENDELEY_DB_TABLE_COL_MENDELEY_ID . ");";

	fwrite( $handle, $query );
	fclose( $handle );

	$updater
	->addExtensionUpdate(
			array(
					'addTable', MENDELEY_DB_TABLE_NAME, dirname( __FILE__ ) . '/' . $filename, true
			) );

	return true;
}

function wfMendeleyFunctions() {
	global $wgParser, $wgExtMendeley;

	// First, check if the Mendeley class instance is initialized.
	checkMendeleyInstance();

	$wgParser->setFunctionHook( MENDELEY_NAMESPACE, array(
			&$wgExtMendeley, 'parseMendeley'
	) );
	$wgParser->setHook( MENDELEY_FUNC_REF, array(
			&$wgExtMendeley, 'parseMendeleyRefTag'
	) );
	$wgParser->setHook( MENDELEY_FUNC_REFERENCES, array(
			&$wgExtMendeley, 'parseMendeleyReferencesTag'
	) );
}

function wfMendeleyFunctionsLanguageGetMagic( &$magicWords, $langCode ) {
	switch ( $langCode ) {
		default:
			$magicWords[ MENDELEY_NAMESPACE ] = array(
			0, MENDELEY_NAMESPACE
			);
			$magicWords[ MENDELEY_FUNC_REF ] = array(
					0, MENDELEY_FUNC_REF
			);
			$magicWords[ MENDELEY_FUNC_REFERENCES ] = array(
					0, MENDELEY_FUNC_REFERENCES
			);
	}
	return true;
}

/**
 * Class, which represents the mendeley extension.
 *
 * @author Andreas Stahlhofen
 *
 */
class Mendeley {

	/**
	 * Counts all cites on a wiki site, so that the references are numbered right.
	 */
	private $citecounter;

	/**
	 * Mendeley version cache.
	 */
	private $arrCachedVersions;

	/**
	 * Array, which holds all references of a wiki site.
	 * <pre>
	 * Format:
	 * arr (
	 * 		<refset> => array (
	 * 			<reference>*
	 * 		),
	 * 		"default" => array (
	 * 			<reference>*
	 * 		)
	 * )
	 * </pre>
	 */
	private $arrCollectedRefs;

	/**
	 * Default refset identifier.
	 */
	private $defaultRefSet = "default";

	/**
	 * Mendeley API.
	 */
	private $mendeleyAPI;

	/**
	 * Bibtex parser.
	 */
	private $bibtex;


	private $bibtexEntry;


	public function __construct() {
		// Initialize array with collected references.
		$this->arrCollectedRefs = array( );
		$this->arrCollectedRefs[ $this->defaultRefSet ] = array( );

		// Cite counter starts by 1.
		$this->citecounter = 1;

		// Initialize the document cache, so that all versions and mendeley ids are available.
		$this->arrCachedVersions = array( );
		$documentList = $this->getMendeleyDocumentList( );
		foreach ( $documentList as $document ) {
			$this->arrCachedVersions[ $document->{ 'id' } ] = $document->{ 'version' };
		}
	}

	private function getMendeleyAPI() {
		if ( !$this->mendeleyAPI ) {
			$this->mendeleyAPI = new MendeleyAPI();
		}
		return $this->mendeleyAPI;
	}

	private function getBibtexEntry() {
		if ( !$this->bibtexEntry ) {
			$this->bibtexEntry = new BibtexEntry();
		}
		return $this->bibtexEntry;
	}

	public function parseMendeley( &$parser, $function ) {
		$parser->disableCache();

		// Extract parameters.
		$opts = array();

		// Argument 0 is $parser, so begin iterating at 1
		for ( $i = 2; $i < func_num_args(); $i++ ) {
			$opts[ ] = func_get_arg( $i );
		}

		//The $opts array now looks like this:
		//      [0] => 'foo=bar'
		//      [1] => 'apple=orange'
		//Now we need to transform $opts into a more useful form...
		$options = $this->extractOptions( $opts );

		if ( MENDELEY_IS_DEBUG ) {
			$output = "namspace:mendeley<br>";
			$output .= "function:$function";
			foreach ( $options as $key => $value ) {
				$output .= "<br>$key" . "=" . "$value";
			}
			$output .= "<br><br>";
		}

		switch ( $function ) {
			case MENDELEY_FUNC_FOLDER:
				$output .= "\n" . $this->renderMendeleyFolder( $options );
				break;
			case MENDELEY_FUNC_MAPPING:
				$output .= "\n" . $this->addMapping( $options );
				break;
			case MENDELEY_FUNC_MAPPING_INFO:
				$output .= "\n" . $this->getMappingInformation( $options );
				break;
			case MENDELEY_FUNC_AUTOMAPPING:
				$output .= "\n" . $this->generateAutoMapping( $options );
				break;
			default:
				$output .= Utils::getErrorString( "Function \"#mendeley:" . $function . "\" is not defined." );
		}

		return $output;
	}

	public function parseMendeleyRefTag( $input, array $args, Parser $parser, PPFrame $frame ) {
		$parser->disableCache();
		return $this->getRef( $args );
	}

	public function parseMendeleyReferencesTag( $input, array $args, Parser $parser, PPFrame $frame ) {
		global $wgScriptPath;

		$output = '';
		$parser->disableCache();
		$this->getReferencesBlock( $args, $output );
		return $output;
	}

	private function getReferencesBlock( $options, &$output ) {
		if ( count( $this->arrCollectedRefs ) == 0 ) {
			return '';
		}

		$refset = (array_key_exists(MENDELEY_OPT_REFSET, $options )) ? $options[ MENDELEY_OPT_REFSET ] : null;
		$referenceBlock = array();

		// Check if the refset option is specified.
		if ( isset( $refset ) ) {
			// Add the specified refsets.
			$referenceBlock = $this->arrCollectedRefs[ $refset ];
		} else {
			// Per default, only add the default refset.
			$referenceBlock = $this->arrCollectedRefs[ $this->defaultRefSet ];
		}

		// Check, if the specified refset exists.
		if ( !isset( $referenceBlock ) ) {
			$output .= Utils::getWarningString( "The specified refset with id \"$refset\" does not exists. Correct your typing failure, remove the refset option or set refset id to \"default\"." );
			return;
		}

		// Check, if the refset is empty.
		if ( count( $referenceBlock ) == 0 ) {
			$output .= Utils::getWarningString( "The specified refset with id \"$refset\" is empty. Please add references to your wiki page, corresponding to this refset, change the refset id to a none empty refset or remove this reference block." );
			return;
		}

		$output = "<ol class=\"references\">" . PHP_EOL;

		$ts1 = time();
		foreach ( $referenceBlock as $mendeleyId => $refCounter ) {
			$documentVersion = $this->arrCachedVersions[ $mendeleyId ];
			$document = MendeleyDbApi::getInstance()->getDocumentByMendeleyId($mendeleyId, $documentVersion);

			$bib = $this->getBibtexEntry()->parseMendeleyJson( $document );
			$output .= "<li id=\"mendeley-note-$mendeleyId\">";
			$output .= "<span class=\"mw-cite-backlink\" class=\"reference\">";
			$output .= "<a href=\"#mendeley-ref-$mendeleyId\">↑</a>";
			$output .= "</span>";
			// TODO Become independent from bibtex extension...
			$output .= renderBibtex( $bib );
			// FIXME <dd>-Tag is not good, because <dl>-Tag is already closed inside the function "renderBibtext( )".
			$output .= "<dd><a href=\"javascript:openInMendeley('http://open.mendeley.com/library/document/" .
					$mendeleyId . "')\">Mendeley</a></dd>";
			$output .= "</li>";
		}

		$output .= "</ol>";
	}

	private function getRef( $options ) {
		$id = ( array_key_exists( MENDELEY_OPT_ID, $options ) ) ? $options[ MENDELEY_OPT_ID ] : null;
		$refset = ( array_key_exists( MENDELEY_OPT_REFSET, $options ) ) ? $options[ MENDELEY_OPT_REFSET ] : null;
		$mendeleyId = ( array_key_exists( MENDELEY_OPT_MENDELEY_DOC_ID, $options ) ) ? $options[ MENDELEY_OPT_MENDELEY_DOC_ID ] : null;
		$mediawikiId = ( array_key_exists( MENDELEY_OPT_MW_ID, $options ) ) ? $options[ MENDELEY_OPT_MW_ID ] : null;

		if ( isset( $mendeleyId ) ) {
		} else if ( isset( $mediawikiId ) ) {
			$mendeleyId = MendeleyDbApi::getInstance()->getMappingByMediawikiId( $mediawikiId );
			if ( $mendeleyId == -1 ) {
				$output = "\n" . Utils::getErrorString( "There is no mendeley id mapped by name \"$mediaWikiId\"." );
				return $output;
			}
		} else if ( isset( $id ) ) {
			$mendeleyId = MendeleyDbApi::getInstance()->getMappingByMediawikiId( $id );
			if ( $mendeleyId == -1 ) {
				$output = "\n" .
						$this
						->getErrorString(
								"There is no mendeley id mapped by id \"$id\". For parameter \"" .
								MENDELEY_OPT_ID . "\"" );
				return $output;
			}
		}

		if ( !isset( $mendeleyId ) ) {
			$output = "\n" .
					$this
					->getErrorString(
							"You must specify a reference id. You can use \"" . MENDELEY_OPT_MW_ID .
							"\" or \"" . MENDELEY_OPT_ID .
							"\" for internal, which are mapped to a mendeley id or \"" .
							MENDELEY_OPT_MENDELEY_DOC_ID . "\" to specify directly the mendeley id." );
			return $output;
		}

		if ( isset( $refset ) ) {
			$collectedReferences = &$this->arrCollectedRefs[ $refset ];

			if ( !isset( $collectedReferences ) ) {
				$this->arrCollectedRefs[ $refset ] = array( );
				$collectedReferences = &$this->arrCollectedRefs[ $refset ];
			}
		} else {
			$collectedReferences = &$this->arrCollectedRefs[ $this->defaultRefSet ];
		}

		$ref =  ( array_key_exists( $mendeleyId, $collectedReferences ) ) ? $collectedReferences[ $mendeleyId ] : null;
		if ( !isset( $ref ) ) {
			$ref = array(
					MENDELEY_ARR_CITEKEY_ID => $this->citecounter,
					MENDELEY_ARR_REFCOUNTER_ID => 0
			);
		}

		$ref[ MENDELEY_ARR_REFCOUNTER_ID ]++;
		$collectedReferences[ $mendeleyId ] = $ref;

		$output = '<span id="mendeley-ref-' . $mendeleyId . '" class="reference">' . '<a href="#mendeley-note-' .
				$mendeleyId . '">[' . $ref[ MENDELEY_ARR_CITEKEY_ID ] . ',' . $ref[ MENDELEY_ARR_REFCOUNTER_ID ] . ']</a>' . '</span>';

		if ( $ref[ MENDELEY_ARR_REFCOUNTER_ID ] == 1 ) {
			$this->citecounter++;
		}

		return $output;
	}

	private function renderCompleteLibrary() {
		$userLibrary = $this->getMendeleyAPI()->get( "", array(
				'items' => 1000
		) );
		return $this->renderDocumentList( $userLibrary->{'document_ids'}, "User Library" );
	}

	private function renderMendeleyFolder( $options ) {
		if ( !array_key_exists( MENDELEY_OPT_NAME, $options ) ) {
			return "You must specify at least a foldername with parameter \"" . MENDELEY_OPT_NAME .
			"=<folder_name>\"" . "to use function \"" . MENDELEY_NAMESPACE . ":" . MENDELEY_FUNC_FOLDER .
			"\".";
		}

		$folderName = array_key_exists( MENDELEY_OPT_NAME, $options ) ? $options[ MENDELEY_OPT_NAME ] : null;
		$header = array_key_exists( MENDELEY_OPT_HEADER, $options ) ? $option[ MENDELEY_OPT_HEADER ] : null;

		$output = '';
		$documentList = $this->getMendeleyDocumentList( $output, $options );
		if ( isset( $header ) ) {
			return $this->renderDocumentList( $documentList, $header );
		} else {
			return $this->renderDocumentList( $documentList, $folderName );
		}

		return $this
		->getErrorString( "Folder with name=\"$folderName\" was " . "not found in your mendeley library..." );
	}

	/**
	 * This function search inside a wiki text for mendeley references and check the reference key. If a found reference is only referenced
	 * on times on this page, then the reference key [1,1] is transformed to [1].
	 */
	public function tidyWikiCiteKeys( &$text ) {

		foreach ( $this->arrCollectedRefs as $refset) {
			foreach ($refset as $mendeleyId => $ref) {

				if ( $ref[ MENDELEY_ARR_REFCOUNTER_ID ] == 1 ) {
					$text = preg_replace( "/(<a href=\"#mendeley-note-" . $mendeleyId . "\">)\[.*?\]/",
							"$1[" . $ref[ MENDELEY_ARR_CITEKEY_ID ] . "]", $text );
				}

			}
		}

	}

	private function getMendeleyIdByName( $request, $name ) {
		$list = $this->getMendeleyAPI()->get( $request );

		foreach ( $list as $item ) {
			if ( $item->{"name"} == $name ) {
				return $item->{'id'};
			}
		}

		return -1;
	}

	private function renderDocumentList( $documentList, $header ) {

		$output = "= $header =\n";
		foreach ( $documentList as $docId ) {
			$output .= $this->renderMendeleyDocument( $docId );
		}

		return $output;
	}

	private function renderMendeleyDocument( $mendeleyId, $groupId = NULL ) {
		if ( isset( $groupId ) ) {
			$mendeleyId = $groupId . "/" . $mendeleyId;
		}

		$documentVersion = $this->arrCachedVersions[ $mendeleyId ];
		$document = MendeleyDbApi::getInstance()->getDocumentByMendeleyId($mendeleyId, $documentVersion);

		if ( !isset( $document ) ) {
			return $this
			->getErrorString(
					"Document with id=\"$mendeleyId\" was not found in your " . "mendeley library..." );
		}

		$title = $document->title;

		$authorList = "";
		if ( isset( $document->authors ) ) {
			foreach ( $document->authors as $author ) {
				$authorList .= $author->{'surname'} . ", " . $author->{'forename'} . "; ";
			}
		} else {
			$authorList = "not specified.";
		}

		$authorList = preg_replace( "/(.*)\; /", "$1", $authorList );

		$urls = "";
		if ( isset( $document->url ) ) {
			$urls .= $document->url . "\n\n";
		}
		$urls .= "[http://open.mendeley.com/library/document/" . $mendeleyId . " Open in Mendeley]";

		$content = "";
		if ( !isset( $document->notes ) ) {
			$content = "'''TODO: Inhaltsbeschreibung'''";
		} else {
			$content = $document->notes;
		}
		// Replace mendeley formatting tags.
		$content = preg_replace( "/<m:linebreak>.*?<\/m:linebreak>/", "\n", $content );

		$output = "";
		$output .= "==== $title ====\n";
		$output .= "{| cellspan=\"0\" border=\"1\" cellpadding=\"10\" \n";
		$output .= "!Autor\n";
		$output .= "| $authorList\n";
		$output .= "|-\n";
		$output .= "!URL(s)\n";
		$output .= "|$urls\n";
		$output .= "|}\n";
		$output .= "===== Inhalt =====\n";
		$output .= "$content";
		$output .= "<br>\n";

		return $output;
	}

	private function getMendeleyDocumentList( &$message = "", $options = array( )) {
		$request = NULL;
		$name = NULL;

		if ( isset( $options[ MENDELEY_OPT_FOLDER_NAME ] ) ) {
			$request = 'folders';
			$name = $options[ MENDELEY_OPT_FOLDER_NAME ];
		} else if ( isset( $options[ MENDELEY_OPT_NAME ] ) ) {
			$request = 'folders';
			$name = $options[ MENDELEY_OPT_NAME ];
		} else if ( isset( $options[ MENDELEY_OPT_GROUP ] ) ) {
			$request = 'groups';
			$name = $options[ MENDELEY_OPT_GROUP ];
		}

		if ( isset( $request ) ) {
			$id = $this->getMendeleyIdByName( $request, $name );
			if ( $id != -1 ) {
				$request .= "/$id/";
			} else {
				$message .= "<br>" .
						Utils::getErrorString( "Object with name=\"$name\" was " . "not found in your mendeley." );
			}
		} else {
			$request = '';
		}

		$mendeleyResult = $this->getMendeleyAPI()->get( $request, array(
				'items' => '1000'
		) );

		if ( !$mendeleyResult ) {
			$message .= "<br>" . Utils::getErrorString( "Something went wrong while asking the Mendeley API." );
		}

		return $mendeleyResult->{ 'documents' };
	}

	private function getMappingInformation( $options ) {
		$output = "= Mendeley Mapping Informations (mendeley_id) -> (mw_id*)) =\n";
		$documentList = $this->getMendeleyDocumentList( $output, $options );

		foreach ( $documentList as $documentShape ) {

			$mendeleyId = $documentShape->{ 'id' };
			$documentVersion = $documentShape->{ 'version' };

			$document = MendeleyDbApi::getInstance()->getDocumentByMendeleyId($mendeleyId, $documentVersion);
			$output .= "==== $document->title ( $mendeleyId ) -> (";

			$mappedIds = MendeleyDbApi::getInstance()->getMappingsByMendeleyId( $mendeleyId );
			foreach ( $mappedIds as $mwid ) {
				$output .= "$mwid, ";
			}
			$output = preg_replace( "/, $/", "", $output );
			$output .= ") ====\n\n";
		}

		return $output;
	}

	private function generateAutoMapping( $options ) {
		$output = '';

		$documentList = $this->getMendeleyDocumentList( $output, $options );
		$values = array();

		$internalDocumentIdList = MendeleyDbApi::getInstance()->getMendeleyIds();

		foreach ( $documentList as $documentShape ) {

			$mendeleyId = $documentShape->{ 'id' };
			$documentVersion = $documentShape->{ 'version' };

			// If there already exists a mapping, then don't generate an auto mapping.
			if ( in_array( $mendeleyId, $internalDocumentIdList ) ) {
				continue;
			}

			MendeleyDbApi::getInstance()->addNewDocument( $mendeleyId );
		}

		return $output;
	}

	private function addMapping( $options ) {
		$mediaWikiId = $options[ MENDELEY_OPT_MW_ID ];
		$mendeleyId = $options[ MENDELEY_OPT_MENDELEY_DOC_ID ];

		if ( !isset( $mediaWikiId ) || !isset( $mendeleyId ) ) {
			return "\n" .
					$this
					->getErrorString(
							"Both parameters \"" . MENDELEY_OPT_MENDELEY_DOC_ID . "\" and \"" .
							MENDELEY_OPT_MW_ID . "\" must be set to define a mapping." );
		}

		if ( $this->getMappingByMediawikiId( $mediaWikiId ) != -1 ) {
			return '';
		}

		MendeleyDbApi::getInstance()->addNewDocument( $mendeleyId, $mediaWikiId );
	}

	/**
	 * Converts an array of values in form [0] => "name=value" into a real
	 * associative array in form [name] => value
	 *
	 * @param array string $options
	 * @return array $results
	 */
	private function extractOptions( array $options ) {
		$results = array();

		foreach ( $options as $option ) {
			$pair = explode( '=', $option );
			if ( count( $pair ) == 2 ) {
				$name = trim( $pair[ 0 ] );
				$value = trim( $pair[ 1 ] );
				$results[ $name ] = $value;
			}
		}
		//Now you've got an array that looks like this:
		// 		[foo] => bar
		//      [apple] => orange
		return $results;
	}

	/**
	 * Formats a given string as an error message.
	 *
	 * @param unknown $errorMsg error message.
	 * @return string Returned a formatted string.
	 */
	private function getErrorString( $errorMsg ) {
		return "<span style=\"color:red\">ERROR: ".$errorMsg."</span>";
	}
}