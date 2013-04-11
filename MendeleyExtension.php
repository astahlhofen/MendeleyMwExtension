<?php

include_once 'Mendeley.php';
include_once 'BibtexEntry.php';
include_once 'bibtex.php';

define( 'IS_DEBUG', false );

define( 'MENDELEY_NAMESPACE', 'mendeley' );

define( 'MENDELEY_FUNC_FOLDER', 'folder' );
define( 'MENDELEY_FUNC_MAPPING', 'mapping' );
define( 'MENDELEY_FUNC_AUTOMAPPING', 'automapping' );
define( 'MENDELEY_FUNC_MAPPING_INFO', 'mapping_info' );
define( 'MENDELEY_FUNC_REF', 'mendeley:ref' );
define( 'MENDELEY_FUNC_REFERENCES', 'mendeley:references');

define( 'MENDELEY_OPT_NAME', 'name' );
define( 'MENDELEY_OPT_ID', 'id');
define( 'MENDELEY_OPT_MW_ID', 'mw_id' );
define( 'MENDELEY_OPT_MENDELEY_DOC_ID', 'md_doc_id');
define( 'MENDELEY_OPT_HEADER', 'header' );
define( 'MENDELEY_OPT_FOLDER_NAME', 'folder' );
define( 'MENDELEY_OPT_GROUP', "group" );

define( 'MENDELEY_DB_TABLE_NAME', 'mendeley_id_mappings' );
define( 'MENDELEY_DB_TABLE_COL_MENDELEY_ID', 'mendeley_id' );
define( 'MENDELEY_DB_TABLE_COL_MW_ID', 'mediawiki_id' );

$wgExtensionFunctions[] = 'wfMendeleyFunctions';

$wgHooks['ParserAfterTidy'][] = 'restoreDivTags';

$wgExtensionCredits['parserhook'][] = array(
		'name' => 'MendeleyExtension',
		'version' => '1.0.0',
		'url' => 'http://astahlhofen.mendeley.com',
		'author' => 'Andreas Stahlhofen',
		'description' => 'Let users integrate mendeley content inside a wiki page.'
);

$wgHooks['LanguageGetMagic'][] = 'wfMendeleyFunctionsLanguageGetMagic';

# Schema updates for update.php
$wgHooks['LoadExtensionSchemaUpdates'][] = 'wfCreateMendeleyDBTable';

function restoreDivTags( &$parser, &$text ) {
	$text = preg_replace("/&lt;(.*?)&gt;/", "<$1>", $text);
	return true;
}

function wfCreateMendeleyDBTable( DatabaseUpdater $updater ) {
	global $wgDBprefix;

	$filename = 'mendeley_db.sql';
	$sqlfile = dirname( __FILE__ ).'/'.$filename;

	$handle = fopen( $sqlfile, 'w' ) or die( 'Cannot open file:  '.$sqlfile );
	$query = "CREATE TABLE ".$wgDBprefix.MENDELEY_DB_TABLE_NAME." (\n".
			"	".MENDELEY_DB_TABLE_COL_MW_ID." VARCHAR(256) NOT NULL,\n".
			"	".MENDELEY_DB_TABLE_COL_MENDELEY_ID." VARCHAR(256) NOT NULL,\n".
			"	PRIMARY KEY(".MENDELEY_DB_TABLE_COL_MW_ID.")\n".
			");\n\n".
			"CREATE INDEX ".MENDELEY_DB_TABLE_COL_MENDELEY_ID." ON ".$wgDBprefix.MENDELEY_DB_TABLE_NAME." (".MENDELEY_DB_TABLE_COL_MENDELEY_ID.");";

	fwrite( $handle, $query );
	fclose( $handle );

	$updater->addExtensionUpdate( array( 'addTable', MENDELEY_DB_TABLE_NAME,
			dirname( __FILE__ ) . '/'.$filename, true ) );

	return true;
}

function wfMendeleyFunctions() {
	global $wgParser, $wgExtMendeley;

	$wgExtMendeley = new ExtMendeley();

	$wgParser->setFunctionHook( MENDELEY_NAMESPACE, array( &$wgExtMendeley, 'parseMendeley' ) );
	$wgParser->setHook ( MENDELEY_FUNC_REF, array( &$wgExtMendeley, 'parseMendeleyRefTag') );
	$wgParser->setHook ( MENDELEY_FUNC_REFERENCES, array( &$wgExtMendeley, 'parseMendeleyReferencesTag') );
}

function wfMendeleyFunctionsLanguageGetMagic( &$magicWords, $langCode ) {
	switch ( $langCode ) {
		default:
			$magicWords[MENDELEY_NAMESPACE]   = array( 0, MENDELEY_NAMESPACE );
			$magicWords[MENDELEY_FUNC_REF]	  = array( 0, MENDELEY_FUNC_REF );
			$magicWords[MENDELEY_FUNC_REFERENCES]	  = array( 0, MENDELEY_FUNC_REFERENCES );
	}
	return true;
}

class ExtMendeley {

	private $citecounter;
	private $arr_collected_refs = array();

	private $mendeley;
	private $bibtex;
	private $bibtexEntry;
	
	private $isInitialization = true;

	public function __construct() {
		$this->citecounter = 1;
	}

	private function getMendeley() {
		if ( !$this->mendeley ) {
			$this->mendeley = new Mendeley();
		}
		return $this->mendeley;
	}

	private function getBibtex() {
		if ( !$this->bibtex ) {
			$this->bibtex = new BibTex();
		}
		return $this->bibtex;
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
			$opts[] = func_get_arg( $i );
		}

		//The $opts array now looks like this:
		//      [0] => 'foo=bar'
		//      [1] => 'apple=orange'
		//Now we need to transform $opts into a more useful form...
		$options = $this->extractOptions( $opts );

		if ( IS_DEBUG ) {
			$output = "namspace:mendeley<br>";
			$output .= "function:$function";
			foreach ( $options as $key => $value ) {
				$output .= "<br>$key"."="."$value";
			}
			$output .= "<br><br>";
		}


		switch ( $function ) {
			case MENDELEY_FUNC_FOLDER:
				$output .= "\n".$this->renderMendeleyFolder( $options );
				break;
			case MENDELEY_FUNC_MAPPING:
				$output .= "\n".$this->addMapping( $options );
				break;
			case MENDELEY_FUNC_MAPPING_INFO:
				$output .= "\n".$this->getMappingInformation( $options );
				break;
			case MENDELEY_FUNC_AUTOMAPPING:
				$output .= "\n".$this->generateAutoMapping( $options );
				break;
			default:
				$output .= $this->getErrorString( "Function \"#mendeley:".$function."\" is not defined." );
		}

		return $output;
	}

	public function parseMendeleyRefTag( $input, array $args, Parser $parser, PPFrame $frame ) {
		$parser->disableCache();
		return "\n".$this->getRef( $args );
	}

	public function parseMendeleyReferencesTag( $input, array $args, Parser $parser, PPFrame $frame ) {
		global $wgScriptPath;
		
		$output = '';
		if ( $this->isInitialization ) {
			$this->isInitialization = false;
			$output .= '<script language="javascript" src="'.$wgScriptPath.'/extensions/Mendeley/mendeley.js"></script>'."\n";
		}
		
		$parser->disableCache();
		return $output."\n".$this->getReferencesBlock( $args );
	}

	private function getReferencesBlock( $options ) {
		if ( count( $this->arr_collected_refs ) == 0 ) {
			return '';
		}

		$output = "<ol class=\"references\">".PHP_EOL;

		foreach ( $this->arr_collected_refs as $documentId => $refCounter ) {
			$document = MendeleyDoc::constructWithDocumentId($documentId);
			$bib = $this->getBibtexEntry()->parseMendeleyJson($document);
						
			$output .= "<li id=\"mendeley-note-$documentId\">";
			$output .= "<span class=\"mw-cite-backlink\" class=\"reference\">";
			$output .= "<a href=\"#mendeley-ref-$documentId\">↑</a>";
			$output .= "</span>";
			$output .= renderBibtex($bib);
			$output .= "<dd><a href=\"javascript:openInMendeley('http://open.mendeley.com/library/document/".$documentId."')\">Open in Mendeley</a></dd>";
			$output .= "</li>";
		}

		$output .= "</ol>";
				
		return $output;
	}

	private function getRef( $options ) {
		$id = $options[MENDELEY_OPT_ID];
		$mendeleyId = $options[MENDELEY_OPT_MENDELEY_DOC_ID];
		$mediawikiId = $options[MENDELEY_OPT_MW_ID];

		if (isset( $mendeleyId ) ) {
		}
		else if ( isset( $mediawikiId) ) {
			$mendeleyId = $this->getMappingByMediawikiId($mediawikiId);
			if ( $mendeleyId == -1 ) {
				$output = "\n".$this->getErrorString("There is no mendeley id mapped by name \"$mediaWikiId\".");
				return $output;
			}
		}
		else if ( isset( $id )) {
			$mendeleyId = $this->getMappingByMediawikiId($id);
			if ( $mendeleyId == -1 ) {
				$output = "\n".$this->getErrorString("There is no mendeley id mapped by name \"$mediaWikiId\". For parameter \"".MENDELEY_OPT_ID.
						"\" only internal ids are valid.");
				return $output;
			}
		}

		if ( !isset( $mendeleyId ) ) {
			$output = "\n".$this->getErrorString("You must specify a reference id. You can use \"".
					MENDELEY_OPT_MW_ID."\" or \"".MENDELEY_OPT_ID."\" for internal, which are mapped to a mendeley id or \"".
					MENDELEY_OPT_MENDELEY_DOC_ID."\" to specify directly the mendeley id."  );
			return $output;
		}


		$refCounter = $this->arr_collected_refs[$mendeleleyId];
		if ( !isset( $refCounter ) ) {
			$refCounter = 0;
		}
		$refCounter++;
		$this->arr_collected_refs[$mendeleyId] = $refCounter;

		$output = '<sup id="mendeley-ref-'.$mendeleyId.'" class="reference">'.
				'<a href="#mendeley-note-'.$mendeleyId.'">['.$this->citecounter.','.$refCounter.']</a>'.
				'</sup>';

		if ($refCounter == 1) {
			$this->citecounter++;
		}

		return $output;
	}

	private function renderCompleteLibrary() {
		$userLibrary = $this->GetMendeley()->get( "", array( 'items' => 1000 ) );
		return $this->renderDocumentList( $userLibrary->{'document_ids'}, "User Library" );
	}

	private function renderMendeleyFolder( $options ) {
		if ( !isset( $options[MENDELEY_OPT_NAME] ) ) {
			return "You must specify at least a foldername with parameter \"".MENDELEY_OPT_NAME."=<folder_name>\""
					."to use function \"".MENDELEY_NAMESPACE.":".MENDELEY_FUNC_FOLDER."\".";
		}

		$folderName = $options[MENDELEY_OPT_NAME];
		$header = $option[MENDELEY_OPT_HEADER];

		$output = '';
		$documentList = $this->getMendeleyDocumentList( $output, $options );
		if ( isset( $header ) ) {
			return $this->renderDocumentList( $documentList, $header );
		} else {
			return $this->renderDocumentList( $documentList, $folderName );
		}

		return $this->getErrorString( "Folder with name=\"$folderName\" was ".
				"not found in your mendeley library..." );
	}


	private function getMendeleyIdByName( $request, $name ) {
		$list = $this->getMendeley()->get( $request );

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

	private function renderMendeleyDocument( $documentId, $groupId = NULL ) {
		if ( isset( $groupId ) ) {
			$documentId = $groupId."/".$documentId;
		}

		$document = MendeleyDoc::constructWithDocumentId( $documentId );

		if ( !isset( $document ) ) {
			return $this->getErrorString( "Document with id=\"$documentId\" was not found in your ".
					"mendeley library..." );
		}

		$title = $document->title;

		$authorList = "";
		if ( isset( $document->authors ) ) {
			foreach ( $document->authors as $author ) {
				$authorList .= $author->{'surname'}.", ".$author->{'forename'}."; ";
			}
		} else {
			$authorList = "not specified.";
		}

		$authorList = preg_replace( "/(.*)\; /", "$1", $authorList );

		$urls = "";
		if ( isset( $document->url ) ) {
			$urls .= $document->url."\n\n";
		}
		$urls .= "[http://open.mendeley.com/library/document/".$documentId." Open in Mendeley]";

		$content = "";
		if (!isset( $document->notes ) ) {
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

	private function getMendeleyDocumentList( &$message, $options ) {
		$request = NULL;
		$name = NULL;

		if ( isset( $options[MENDELEY_OPT_FOLDER_NAME] ) ) {
			$request = 'folders';
			$name = $options[MENDELEY_OPT_FOLDER_NAME];
		}
		else if ( isset ( $options[MENDELEY_OPT_NAME] ) ) {
			$request = 'folders';
			$name = $options[MENDELEY_OPT_NAME];
		}
		else if ( isset( $options[MENDELEY_OPT_GROUP] ) ) {
			$reqiest = 'groups';
			$name = $options[MENDELEY_OPT_GROUP];
		}

		if ( isset($request) ) {
			$id = $this->getMendeleyIdByName( $request, $name );
			if ( $id != -1 ) {
				$request .= "/$id/";
			}
			else {
				$message .= "<br>".$this->getErrorString( "Object with name=\"$name\" was ".
						"not found in your mendeley." );
			}
		}
		else {
			$request = '';
		}

		$mendeleyResult = $this->getMendeley()->get( $request , array( 'items' => '1000' ) );

		if ( !$mendeleyResult ) {
			$message .= "<br>".$this->getErrorString("Something went wrong while asking the Mendeley API.");
		}

		return $mendeleyResult->{'document_ids'};
	}

	private function getMappingInformation( $options ) {
		$output = "= Mendeley Mapping Informations (mendeley_id) -> (mw_id*)) =\n";

		$documentList = $this->getMendeleyDocumentList($output, $options);

		foreach ( $documentList as $documentId ) {
			$document = MendeleyDoc::constructWithDocumentId( $documentId );
			$output .= "==== $document->title ( $documentId ) -> (";

			$mappedIds = $this->getMappingsByMendeleyId( $documentId ) ;
			foreach ( $mappedIds as $mwid) {
				$output .= "$mwid, ";
			}
			$output = preg_replace("/, $/", "", $output);
			$output .= ") ====\n\n";
		}

		return $output;
	}

	private function getMappingsByMendeleyId( $documentId ) {
		$return = array();

		$dbr = wfGetDB( DB_SLAVE );
		$result = $dbr->select(
				MENDELEY_DB_TABLE_NAME,
				array( MENDELEY_DB_TABLE_COL_MW_ID ),
				MENDELEY_DB_TABLE_COL_MENDELEY_ID.' = \''.$documentId.'\'',
				__METHOD__
		);

		foreach ( $result as $row ) {
			$return[] = $row->{MENDELEY_DB_TABLE_COL_MW_ID};
		}

		return $return;
	}

	private function getMappingByMediawikiId( $mediawikiId ) {
		$return = "";
		$return = $this->getErrorString("The internal id \"$mediawikiId\" is not mapped to an mendeley ".
				"document id. Please add first a mapping by using function ".
				"{{#".MENDELEY_NAMESPACE.":".MENDELEY_FUNC_MAPPING."|".MENDELEY_OPT_MW_ID.
				"=<mediawiki_id>|".MENDELEY_OPT_MENDELEY_DOC_ID."=<mendeley_doc_id>}}.");

		$dbr = wfGetDB( DB_SLAVE );
		$result = $dbr->select(
				MENDELEY_DB_TABLE_NAME,
				array( MENDELEY_DB_TABLE_COL_MENDELEY_ID ),
				MENDELEY_DB_TABLE_COL_MW_ID.' = \''.$mediawikiId.'\'',
				__METHOD__
		);


		foreach ( $result as $row ) {
			return $row->{MENDELEY_DB_TABLE_COL_MENDELEY_ID};
		}

		return -1;
	}

	private function generateAutoMapping( $options ) {
		$output = '';

		$documentList = $this->getMendeleyDocumentList( $output, $options );
		$values = array();

		foreach ( $documentList as $documentId ) {

			// If there already exists a mapping, then don't generate an auto mapping.
			if ( count( $this->getMappingsByMendeleyId( $documentId ) ) > 0 ) {
				continue;
			}

			$document = MendeleyDoc::constructWithDocumentId($documentId);

			$authorCount = count( $document->authors );
			$author = '';
			if ( $authorCount == 0 ) {
				$author = 'NoAuthor';
			} else if ( $authorCount >= 1 ) {
				$author = strtolower( $document->authors[0]->{'surname'} );
			}

			$mediawiki_id = $author.$document->year;
			$mediawiki_id = preg_replace("/ /", "_", $mediawiki_id);
			
			$counter = 0;
			$tmp_mediawiki_id = $mediawiki_id;
			
			while ( $this->getMappingByMediawikiId($mediawiki_id) != -1 ||
					$this->array_contains($values, $mediawiki_id) != false ) {
				$mediawiki_id = $tmp_mediawiki_id."_$counter";
				$counter++;
			}
				
			$values[] = array(
					MENDELEY_DB_TABLE_COL_MENDELEY_ID => $documentId,
					MENDELEY_DB_TABLE_COL_MW_ID => $mediawiki_id
			);
		}

		$dbw = wfGetDB( DB_MASTER );
		$dbw->insert( MENDELEY_DB_TABLE_NAME, $values, __METHOD__ );
		$dbw->commit( __METHOD__ );

		return $output;
	}

	private function addMapping( $options ) {
		$mediawikiId = $options[MENDELEY_OPT_MW_ID];
		$mendeleyId = $options[MENDELEY_OPT_MENDELEY_DOC_ID];

		if ( !isset( $mediawikiId ) || !isset( $mendeleyId ) ) {
			return "\n".$this->getErrorString("Both parameters \"".MENDELEY_OPT_MENDELEY_DOC_ID."\" and \"".
					MENDELEY_OPT_MW_ID."\" must be set to define a mapping.");
		}

		if ( $this->getMappingByMediawikiId($mediawikiId) != -1 ) {
			return '';
		}

		$values = array();
		$values[] = array(
				MENDELEY_DB_TABLE_COL_MENDELEY_ID => $mendeleyId,
				MENDELEY_DB_TABLE_COL_MW_ID => $mediawikiId
		);

		$dbw = wfGetDB( DB_MASTER );
		$dbw->insert( MENDELEY_DB_TABLE_NAME, $values, __METHOD__ );
		$dbw->commit( __METHOD__ );
	}

	function array_contains( $array, $searchValue ) {
		foreach ( $array as $key => $value ) {
	
			if ( is_array( $value ) ) {
				$contains = $this->array_contains( $value, $searchValue );
				if ( $contains == true ) {
					return true;
				}
			}
			else {
				if ( $searchValue == $value ) {
					return true;
				}
			}
		}
	
		return false;
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
				$name = trim( $pair[0] );
				$value = trim( $pair[1] );
				$results[$name] = $value;
			}
		}
		//Now you've got an array that looks like this:
		// 		[foo] => bar
		//      [apple] => orange

		return $results;
	}

	private function getErrorString( $errorMsg ) {
		return "<span style=\"color:red\">ERROR: ".$errorMsg."</span>";
	}

}

?>