<?php

include_once 'Utils.php';
include_once 'lib/MendeleyAPI/MendeleyAPI.php';

class MendeleyDbApi {

	private static $instance;

	private function __construct() {
	}

	public static function getInstance() {
		if ( self::$instance == null ) {
			self::$instance = new MendeleyDbApi();
		}
		return self::$instance;
	}

	/**
	 * @return Returns an array with all mendeley ids, stored in the database.
	 */
	public function getMendeleyIds() {
		$dbr = wfGetDB( DB_SLAVE );
		$result = $dbr->select( MENDELEY_DB_TABLE_NAME, array(MENDELEY_DB_TABLE_COL_MENDELEY_ID), "", __METHOD__);

		$arr = array();
		foreach ($result as $row) {
			$arr[] = $row->{MENDELEY_DB_TABLE_COL_MENDELEY_ID};
		}

		return $arr;
	}

	/**
	 * Returns the mendeley id, on which the given MediaWiki id is pointing.
	 *
	 * @param unknown $mediawikiId
	 * @return number
	 */
	public function getMappingByMediawikiId( $mediawikiId ) {
		$return = "";
		$return = Utils::getErrorString(
				"The internal id \"$mediawikiId\" is not mapped to an mendeley " .
				"document id. Please add first a mapping by using function " . "{{#" . MENDELEY_NAMESPACE .
				":" . MENDELEY_FUNC_MAPPING . "|" . MENDELEY_OPT_MW_ID . "=<mediawiki_id>|" .
				MENDELEY_OPT_MENDELEY_DOC_ID . "=<mendeley_doc_id>}}." );

		$dbr = wfGetDB( DB_SLAVE );
		$result = $dbr
		->select( MENDELEY_DB_TABLE_NAME, array(
				MENDELEY_DB_TABLE_COL_MENDELEY_ID
		), MENDELEY_DB_TABLE_COL_MW_ID . ' = \'' . $mediawikiId . '\'', __METHOD__ );

		foreach ( $result as $row ) {
			return $row->{MENDELEY_DB_TABLE_COL_MENDELEY_ID};
		}

		return -1;
	}

	/**
	 * Removes a mapping, specified by the given MediaWiki id.
	 *
	 * @param unknown $mediawikiId
	 */
	public function removeMappingByMediawikiId( $mediawikiId ) {
		$dbw = wfGet( DB_MASTER );
		$dbw->delete( MENDELEY_DB_TABLE_NAME, MENDELEY_DB_TABLE_COL_MENDELEY_ID . "=\'" . $mediawikiId . "\'", __METHOD__ );
		$dbw->commit(__METHOD__);
	}

	/**
	 * Returns a document, specified by the current version and mendeley id.
	 *
	 * First, this method search inside the db cache. If a result is found and also the versions are equal,
	 * the unserialized document is returned.
	 * If no result was found, the MendeleyAPI creates us the document by its mendeley id. The document is also cached
	 * in the database.
	 * If only the version of the document is not equal to the cached version, the cached document and its version is
	 * updated inside the database.
	 *
	 * @param unknown $mendeleyId
	 * @param unknown $documentVersion
	 * @return MendeleyDoc|mixed
	 */
	public function getDocumentByMendeleyId( $mendeleyId, $documentVersion ) {
		$dbr = wfGetDB ( DB_SLAVE );
		$result = $dbr -> select( MENDELEY_DB_TABLE_NAME,
				array(
						MENDELEY_DB_TABLE_COL_MENDELEY_ID,
						MENDELEY_DB_TABLE_COL_DOC_VERSION,
						MENDELEY_DB_TABLE_COL_CACHED_DOC,
						MENDELEY_DB_TABLE_COL_MW_ID
				),
				MENDELEY_DB_TABLE_COL_MENDELEY_ID .' = \'' . $mendeleyId . '\'',
				__METHOD__
		);

		$dbCachedDoc = "";
		$dbDocumentVersion = "";
		$dbMendeleyId = "";
		$dbMediaWikiId = "";

		$hasResult = false;
		foreach ( $result as $row ) {
			$dbCachedDoc = $row->{ MENDELEY_DB_TABLE_COL_CACHED_DOC };
			$dbDocumentVersion = $row->{ MENDELEY_DB_TABLE_COL_DOC_VERSION };
			$dbMendeleyId = $row->{ MENDELEY_DB_TABLE_COL_MENDELEY_ID };
			$dbMediaWikiId = $row->{ MENDELEY_DB_TABLE_COL_MW_ID };
			$hasResult = true;
		}

		// No result, ask MendeleyAPI for document and store it inside the database.
		if ( !$hasResult ) {
			$document = MendeleyDoc::constructWithDocumentId($mendeleyId);
				
				
			$this->addDocument( $mediawikiId, $mendeleyId, $document->version, $document );
			return $document;
		}

		if ( $dbDocumentVersion != $documentVersion ) {
			$document = MendeleyDoc::constructWithDocumentId($mendeleyId);

			$values = array(
					MENDELEY_DB_TABLE_COL_DOC_VERSION => $document->version,
					MENDELEY_DB_TABLE_COL_CACHED_DOC => serialize( $document )
			);

			$condition = array(
					MENDELEY_DB_TABLE_COL_MENDELEY_ID => $mendeleyId
			);

			$this->updateValues( MENDELEY_DB_TABLE_NAME, $values, $condition );
			return $document;
		}

		return unserialize( $dbCachedDoc );
	}

	/**
	 * Returns an array with all MediaWikiIds, pointing to the given mendeley id.
	 *
	 * @param unknown $mendeleyId
	 * @return multitype:NULL
	 */
	public function getMappingsByMendeleyId( $mendeleyId ) {
		$return = array();

		$dbr = wfGetDB( DB_SLAVE );
		$result = $dbr
		->select( MENDELEY_DB_TABLE_NAME, array(
				MENDELEY_DB_TABLE_COL_MW_ID
		), MENDELEY_DB_TABLE_COL_MENDELEY_ID . ' = \'' . $mendeleyId . '\'', __METHOD__ );

		foreach ( $result as $row ) {
			$return[ ] = $row->{ MENDELEY_DB_TABLE_COL_MW_ID };
		}

		return $return;
	}

	/**
	 * Returns an array with similar MediaWiki ids to the specified one, which are already stored inside the database.
	 *
	 * @param unknown $mediaWikiId
	 */
	public function getSimilarMediaWikiIds( $mediaWikiId ) {
		$return = array();
		
		$dbr = wfGetDB( DB_SLAVE );
		$result = $dbr->select( 
				MENDELEY_DB_TABLE_NAME,
				array(
					MENDELEY_DB_TABLE_COL_MW_ID		
				),
				MENDELEY_DB_TABLE_COL_MW_ID . ' like \'' . $mediaWikiId . '%\'',
				__METHOD__ 
		);
		
		foreach( $result as $row ) {
			$return[ ] = $row->{ MENDELEY_DB_TABLE_COL_MW_ID }; 
		}
		return $return;
	}

	/**
	 * Adds a document to the database.
	 *
	 * @param unknown $mediaWikiId
	 * @param unknown $mendeleyId
	 * @param unknown $documentVersion
	 * @param unknown $cachedDocument
	 */
	public function addDocument( $mediaWikiId, $mendeleyId, $documentVersion, $document ) {
		$values = array( );
		$values[ ] = array(
				MENDELEY_DB_TABLE_COL_MENDELEY_ID => $mendeleyId,
				MENDELEY_DB_TABLE_COL_MW_ID => $mediaWikiId,
				MENDELEY_DB_TABLE_COL_DOC_VERSION => $documentVersion,
				MENDELEY_DB_TABLE_COL_CACHED_DOC => serialize( $document )
		);
		$this->addValues(MENDELEY_DB_TABLE_NAME, $values);
	}


	/**
	 * Adds a new document to the database, specified by its mendeley id. Optional, a predefined
	 * mediaWikiId can be used for the mapping. This is useful for user defined mappings. If no
	 * mediaWikiId is specified, it's autogenerated by author names.
	 *
	 * @param unknown $mendeleyId
	 * @param unknown $mediaWikiId
	 */
	public function addNewDocument( $mendeleyId, $mediaWikiId = null ) {
		$document = MendeleyDoc::constructWithDocumentId( $mendeleyId );

		if ( !isset( $mediaWikiId )) {
			$mediaWikiId = Utils::generateMediaWikiIdByDocument( $document );
		}

		MendeleyDbApi::getInstance()->addDocument($mediaWikiId, $mendeleyId, $document->version, $document );
	}

	/**
	 * Add specified values to the database.
	 *
	 * @param unknown $table
	 * @param array $values
	 */
	public function addValues( $table, array $values ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->begin();
		$dbw->insert( $table, $values, __METHOD__ );
		$dbw->commit( __METHOD__ );
	}

	/**
	 * Update specified values inside the database.
	 *
	 * @param unknown $table
	 * @param array $values
	 * @param array $condition
	 */
	public function updateValues( $table, array $values, array $condition ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->begin();
		$dbw->update( $table, $values, $condition, __METHOD__ );
		$dbw->commit( __METHOD__ );
	}
}
