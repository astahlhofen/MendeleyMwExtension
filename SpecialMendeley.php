<?php

include_once 'lib/MendeleyAPI/MendeleyAPI.php';

define( 'SECTION_MENDELEY_MAPPINGS', '= Mendeley Citation Key Mappings =' );

define( 'CSS_FOLDER_SECTION', "folder-section" );
define( 'CSS_FOLDER_CONTENT', "folder-content" );

if ( isset( $_REQUEST[ "folder" ] ) ) {
	$folderId = $_REQUEST[ "folder" ];

	$mendeleyApi = new MendeleyAPI();
	$folderInformation = $mendeleyApi->get( "folders/" . $folderId, array(
					"items" => 1000
			) );

	$documentList = $folderInformation->{"document_ids"};

	$output .= "<ol>";
	foreach ( $documentList as $documentId ) {
		$doc = MendeleyDoc::constructWithDocumentId( $documentId );
		$output .= "<li>" . $doc->title . "</li>";
	}
	$output .= "</li>";

	echo $output;

	exit;
}

class SpecialMendeley extends SpecialPage {

	private $mendeleyAPI;

	private $wikiTextCollector;
	private $htmlCollector;
	private $tocHierarchyCollector;

	function __construct() {
		parent::__construct( 'Mendeley', '', true );

		$this->mendeleyAPI = new MendeleyAPI();
	}

	function execute( $par ) {
		global $wgScriptPath;

		$this->wikiTextCollector = array();
		$this->htmlCollector = array();
		$this->tocHierarchyCollector = array();

		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();

		// Include jQuery.
		$output
				->addHtml( 
						'<script type="text/javascript" src="' . $wgScriptPath .
								'/extensions/Mendeley/js/jquery-1.8.3.min.js"></script>' );

		// Include Mendeley Mapping API.
		$output
				->addHtml( 
						'<script type="text/javascript" src="' . $wgScriptPath .
								'/extensions/Mendeley/js/mendeley-mapping-api.js"></script>' );

		// Include CSS style.
		$output
				->addHtml( 
						'<link href="' . $wgScriptPath .
								'/extensions/Mendeley/css/mendeley.css" rel="stylesheet" type="text/css">' );

		// Create a section for mappings.
		$output->addWikiText( SECTION_MENDELEY_MAPPINGS );

		// Create Folder sections.
		$this->createFolderSections( $output );

		// Generate the table of content.
		$this->generateTableOfContent( $output );

		var_dump( 
				$test = array(
						array(
								"1" => "test1"
						) => array(
								array(
										"11", "test11"
								), array(
										"12", "test12"
								)
						)
				) );
	}

	function createFolderSections( &$output ) {
		$folderList = $this->mendeleyAPI->get( "folders" );

		// Get hierarchical folder structure.
		$folderHierarchy = array();
		$folderNames = array();
		$folderDocuments = array();

		foreach ( $folderList as $folderEntry ) {
			$folderId = $folderEntry->{'id'};
			$parent = $folderEntry->{'parent'};
			$folderName = $folderEntry->{'name'};
			$size = $folderEntry->{'size'};

			// Save the folder name.
			$folderNames[ $folderId ] = $folderName;

			// Saves the size of the folder.
			$folderDocuments[ $folderId ] = $size;

			// Insert id into folder hierarchy.
			if ( $parent != -1 ) {
				$isAdded = $this->addChildFolder( $folderHierarchy, $parent, $folderId, $folderNames );

				// If the parent was not found, then add the parent to the top level.
				if ( !$isAdded ) {
					$folderHierarchy[ $parent ] = array(
							$folderId => array()
					);
				}
			} else {
				$folderHierarchy[ $folderId ] = array();
			}
		}

		foreach ( $folderHierarchy as $folderId => $children ) {
			$this->generateFolderSection( $output, $folderId, $children, $folderNames, 1 );
		}
	}

	function generateFolderSection( &$output, $folderId, $children, $folderNames, $depth ) {
		$headerLength = "";
		for ( $i = 0; $i < $depth + 1; $i++ ) {
			$headerLength .= "=";
		}

		$output
				->addWikiText( 
						$headerLength . "<span id=\"$folderId\" class=\"" . CSS_FOLDER_SECTION . "\">" .
								$folderNames[ $folderId ] . "</span>" . $headerLength );
		$output
				->addHtml( 
						'<div class = "' . CSS_FOLDER_CONTENT . ' mw-content-text" id="content-' . $folderId .
								'"></div>' );

		if ( count( $children ) > 0 ) {

			foreach ( $children as $folderId => $children ) {
				$this->generateFolderSection( $output, $folderId, $children, $folderNames, $depth + 1 );
			}

		}
	}

	function addChildFolder( &$folderHierarchy, $parentFolderId, $childFolderId, $folderNames ) {
		// First check, if the folder contains already the childFolderId, but it's inserted to the top level. If it's found,
		// then set $valueToAdd to the children of the childFolderId.

		$valueToAdd = array();
		foreach ( $folderHierarchy as $folderId => $value ) {
			if ( $folderId == $childFolderId ) {
				$valueToAdd = $value;
				unset( $folderHierarchy[ $folderId ] );
				break;
			}
		}

		// Try to find the parent folder and add the child folder first.
		foreach ( $folderHierarchy as $folderId => &$value ) {

			if ( $folderId == $parentFolderId ) {
				$value[ $childFolderId ] = $valueToAdd;
				return true;

			} else {

				if ( count( $value ) > 0 ) {
					$isAdded = $this->addChildFolder( $value, $parentFolderId, $childFolderId, $folderNames );

					if ( $isAdded ) {
						return true;
					}
				}
			}
		}
		return false;
	}

	private function addTocEntry( $levelHierarchy, $header, $id ) {
		$currentLevel = &$this->tocHierarchyCollector;

		foreach ( $levelHierarchy as $level ) {
			$currentLevel = &$currentLevel[ $level ];
			if ( $currentLevel == null ) {
				$currentLevel = array();
			}
		}

		$currentLevel[ ] = array(
				$header, $id
		);
	}

	private function generateTableOfContent( &$output ) {
		$html = '<table id="toc">';
		$html .= '<tr><td><div id="toctitle"><h2>Inhaltserverzeichnis</h2><span class="toctoggle">';
		$html .= '	[<a id="togglelink" class="internal" href="#">Verbergen</a>]';
		$html .= '</span></div>';
		$html .= '';
		$html .= '';
		$html .= '';
		$html .= '</td></tr></table>';
		$output->addHtml( $html );
	}
}

?>
