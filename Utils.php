<?php

/**
 * MendeleyAPI.
 */
include_once 'lib/MendeleyAPI/MendeleyAPI.php';

/**
 * Include MendeleyDBApi.php.
 */
include_once 'MendeleyDbApi.php';

class Utils {

	public static function getErrorString( $errorMsg ) {
		return "<span style=\"color:red\">ERROR: " . $errorMsg . "</span><br>";
	}

	public static function getWarningString( $errorMsg ) {
		return "<span style=\"color:orange\">WARNING: " . $errorMsg . "</span><br>";
	}
	
	public static function generateMediaWikiIdByDocument ( $document ) {
		$authorCount = count( $document->authors );
		$author = '';
		if ( $authorCount == 0 ) {
			$author = 'NoAuthor';
		} else if ( $authorCount >= 1 ) {
			$author = strtolower( $document->authors[ 0 ]->{'surname'} );
		}

		$mediaWikiId = $author . $document->year;
		$mediaWikiId = preg_replace( "/ /", "_", $mediaWikiId );

		// Check, if the calculated MediaWiki id already exists inside the database.
		$counter = 0;
		$tmp_mediawiki_id = $mediaWikiId;
		$values = MendeleyDbApi::getInstance()->getSimilarMediaWikiIds( $mediaWikiId );
		
		while ( Utils::array_contains( $values, $mediaWikiId ) ) {
			$mediaWikiId = $tmp_mediawiki_id . "_$counter";
			$counter++;
		}
		
		return $mediaWikiId;
	}
	
	public static function array_contains( $array, $searchValue ) {
		foreach ( $array as $key => $value ) {
		
			if ( is_array( $value ) ) {
				$contains = $this->array_contains( $value, $searchValue );
				if ( $contains == true ) {
					return true;
				}
			} else {
				if ( $searchValue == $value ) {
					return true;
				}
			}
		}
		
		return false;
	}

}
