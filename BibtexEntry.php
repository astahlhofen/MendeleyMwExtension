<?php

class BibtexEntry {

	public $mendeleyDocumentId;
	
	protected $bibtex_field_address='address';
	protected $bibtex_field_annote='annote';
	protected $bibtex_field_author='author';
	protected $bibtex_field_booktitle='booktitle';
	protected $bibtex_field_chapter='chapter';
	protected $bibtex_field_crossref='crossref';
	protected $bibtex_field_edition='edition';
	protected $bibtex_field_editor='editor';
	protected $bibtex_field_eprint='eprint';
	protected $bibtex_field_howpublished='howpublished';
	protected $bibtex_field_institution='institution';
	protected $bibtex_field_journal='journal';
	protected $bibtex_field_key='key';
	protected $bibtex_field_month='month';
	protected $bibtex_field_note='note';
	protected $bibtex_field_number='number';
	protected $bibtex_field_organization='organization';
	protected $bibtex_field_pages='pages';
	protected $bibtex_field_publisher='publisher';
	protected $bibtex_field_school='school';
	protected $bibtex_field_series='series';
	protected $bibtex_field_title='title';
	protected $bibtex_field_type='type';
	protected $bibtex_field_url='url';
	protected $bibtex_field_volume='volume';
	protected $bibtex_field_year='year';

	protected $bibtex_entry_article = 'article';
	protected $bibtex_entry_book = 'book';
	protected $bibtex_entry_booklet = 'booklet';
	protected $bibtex_entry_conference = 'conference';
	protected $bibtex_entry_inbook = 'inbook';
	protected $bibtex_entry_incollection = 'incollection';
	protected $bibtex_entry_inproceedings = 'inproceedings';
	protected $bibtex_entry_manual = 'manual';
	protected $bibtex_entry_mastersthesis = 'mastersthesis';
	protected $bibtex_entry_misc = 'misc';
	protected $bibtex_entry_phdthesis = 'phdthesis';
	protected $bibtex_entry_proceedings = 'proceedings';
	protected $bibtex_entry_techreport = 'techreport';
	protected $bibtex_entry_unpublished = 'unpublished';

	protected $mendeley_entry_Bill = 'Bill';
	protected $mendeley_entry_Book = 'Book';
	protected $mendeley_entry_Book_Section = 'Book Section';
	protected $mendeley_entry_Case = 'Case';
	protected $mendeley_entry_Computer_Program = 'Computer Program';
	protected $mendeley_entry_Conference_Proceedings = 'Conference Proceedings';
	protected $mendeley_entry_Encyclopedia_Article = 'Encyclopedia Article';
	protected $mendeley_entry_Film = 'Film';
	protected $mendeley_entry_Generic = 'Generic';
	protected $mendeley_entry_Hearing = 'Hearing';
	protected $mendeley_entry_Journal_Article = 'Journal Article';
	protected $mendeley_entry_Magazine_Article = 'Magazine Article';
	protected $mendeley_entry_Newspaper_Article = 'Newspaper Article';
	protected $mendeley_entry_Patent = 'Patent';
	protected $mendeley_entry_Report = 'Report';
	protected $mendeley_entry_Statute = 'Statute';
	protected $mendeley_entry_Television_Broadcast = 'Television Broadcast';
	protected $mendeley_entry_Thesis = 'Thesis';
	protected $mendeley_entry_Web_Page = 'Web Page';
	protected $mendeley_entry_Working_Paper = 'Working Paper';

	protected $mendeley_field_authors = 'authors';
	protected $mendeley_field_keywords = 'keywords';
	protected $mendeley_field_tags = 'tags';
	protected $mendeley_field_title = 'title';
	protected $mendeley_field_website = 'website';
	protected $mendeley_field_year = 'year';
	protected $mendeley_field_abstract = 'abstract';
	protected $mendeley_field_advisor = 'advisor';
	protected $mendeley_field_applicationNumber = 'applicationNumber';
	protected $mendeley_field_articleColumn = 'articleColumn';
	protected $mendeley_field_associatedDate = 'associatedDate';
	protected $mendeley_field_chapter = 'chapter';
	protected $mendeley_field_city = 'city';
	protected $mendeley_field_code = 'code';
	protected $mendeley_field_codeNumber = 'codeNumber';
	protected $mendeley_field_codeSection = 'codeSection';
	protected $mendeley_field_codeVolume = 'codeVolume';
	protected $mendeley_field_committee = 'committee';
	protected $mendeley_field_counsel = 'counsel';
	protected $mendeley_field_country = 'country';
	protected $mendeley_field_dateAccessed = 'dateAccessed';
	protected $mendeley_field_day = 'day';
	protected $mendeley_field_department = 'department';
	protected $mendeley_field_edition = 'edition';
	protected $mendeley_field_genre = 'genre';
	protected $mendeley_field_institution = 'institution';
	protected $mendeley_field_internationalAuthor = 'internationalAuthor';
	protected $mendeley_field_internationalNumber = 'internationalNumber';
	protected $mendeley_field_internationalTitle = 'internationalTitle';
	protected $mendeley_field_internationalUserType = 'internationalUserType';
	protected $mendeley_field_issue = 'issue';
	protected $mendeley_field_language = 'language';
	protected $mendeley_field_lastUpdate = 'lastUpdate';
	protected $mendeley_field_legalStatus = 'legalStatus';
	protected $mendeley_field_length = 'length';
	protected $mendeley_field_medium = 'medium';
	protected $mendeley_field_month = 'month';
	protected $mendeley_field_notes = 'notes';
	protected $mendeley_field_originalPublication = 'originalPublication';
	protected $mendeley_field_owner = 'owner';
	protected $mendeley_field_pages = 'pages';
	protected $mendeley_field_publication = 'publication';
	protected $mendeley_field_publication_outlet = 'publication_outlet';
	protected $mendeley_field_publicLawNumber = 'publicLawNumber';
	protected $mendeley_field_publisher = 'publisher';
	protected $mendeley_field_published_in = 'published_in';
	protected $mendeley_field_reprintEdition = 'reprintEdition';
	protected $mendeley_field_reviewedArticle = 'reviewedArticle';
	protected $mendeley_field_revisionNumber = 'revisionNumber';
	protected $mendeley_field_sections = 'sections';
	protected $mendeley_field_series = 'series';
	protected $mendeley_field_seriesEditor = 'seriesEditor';
	protected $mendeley_field_seriesNumber = 'seriesNumber';
	protected $mendeley_field_session = 'session';
	protected $mendeley_field_shortTitle = 'shortTitle';
	protected $mendeley_field_sourceType = 'sourceType';
	protected $mendeley_field_type = 'type';
	protected $mendeley_field_userType = 'userType';
	protected $mendeley_field_volume = 'volume';

	protected $required_bibtexs = array();
	protected $optional_bibtexs = array();

	protected $mendeley_bibtex_entry_mapping = array();
	protected  $bibtex_mendeley_field_mapping = array();

	function __construct() {
		$this->required_bibtexs[$this->bibtex_entry_article] = array(
				$this->bibtex_field_author,
				$this->bibtex_field_title,
				$this->bibtex_field_journal,
				$this->bibtex_field_year
		);

		$this->required_bibtexs[$this->bibtex_entry_book] = array(
				array ( $this->bibtex_field_author, $this->bibtex_field_editor ),
				$this->bibtex_field_title,
				$this->bibtex_field_publisher,
				$this->bibtex_field_year
		);

		$this->required_bibtexs[$this->bibtex_entry_booklet] = array(
				$this->bibtex_field_title
		);

		$this->required_bibtexs[$this->bibtex_entry_conference] = array(
				$this->bibtex_field_author,
				$this->bibtex_field_title,
				$this->bibtex_field_booktitle,
				$this->bibtex_field_year
		);

		$this->required_bibtexs[$this->bibtex_entry_inbook] = array(
				$this->bibtex_field_author,
				$this->bibtex_field_title,
				array ( $this->bibtex_field_chapter, $this->bibtex_field_pages ),
				$this->bibtex_field_publisher,
				$this->bibtex_field_year,
		);

		$this->required_bibtexs[$this->bibtex_entry_incollection] = array(
				$this->bibtex_field_author,
				$this->bibtex_field_title,
				$this->bibtex_field_booktitle,
				$this->bibtex_field_publisher,
				$this->bibtex_field_year,
		);

		$this->required_bibtexs[$this->bibtex_entry_inproceedings] = array(
				$this->bibtex_field_author,
				$this->bibtex_field_title,
				$this->bibtex_field_booktitle,
				$this->bibtex_field_year
		);

		$this->required_bibtexs[$this->bibtex_entry_manual] = array(
				$this->bibtex_field_title
		);

		$this->required_bibtexs[$this->bibtex_entry_mastersthesis] = array(
				$this->bibtex_field_author,
				$this->bibtex_field_title,
				$this->bibtex_field_school,
				$this->bibtex_field_year
		);

		$this->required_bibtexs[$this->bibtex_entry_misc] = array(
		);

		$this->required_bibtexs[$this->bibtex_entry_phdthesis] = array(
				$this->bibtex_field_author,
				$this->bibtex_field_title,
				$this->bibtex_field_school,
				$this->bibtex_field_year
		);

		$this->required_bibtexs[$this->bibtex_entry_proceedings] = array(
				$this->bibtex_field_title,
				$this->bibtex_field_year
		);

		$this->required_bibtexs[$this->bibtex_entry_techreport] = array(
				$this->bibtex_field_author,
				$this->bibtex_field_title,
				$this->bibtex_field_institution,
				$this->bibtex_field_year
		);

		$this->required_bibtexs[$this->bibtex_entry_unpublished] = array(
				$this->bibtex_field_author,
				$this->bibtex_field_title,
				$this->bibtex_field_note
		);

		$this->optional_bibtexs[$this->bibtex_entry_article] = array(
				$this->bibtex_field_volume,
				$this->bibtex_field_number,
				$this->bibtex_field_pages,
				$this->bibtex_field_month,
				$this->bibtex_field_note,
				$this->bibtex_field_key
		);

		$this->optional_bibtexs[$this->bibtex_entry_book] = array(
				array( $this->bibtex_field_volume, $this->bibtex_field_number ),
				$this->bibtex_field_series,
				$this->bibtex_field_address,
				$this->bibtex_field_edition,
				$this->bibtex_field_month,
				$this->bibtex_field_note,
				$this->bibtex_field_key
		);

		$this->optional_bibtexs[$this->bibtex_entry_booklet] = array(
				$this->bibtex_field_author,
				$this->bibtex_field_howpublished,
				$this->bibtex_field_address,
				$this->bibtex_field_month,
				$this->bibtex_field_year,
				$this->bibtex_field_note,
				$this->bibtex_field_key
		);

		$this->optional_bibtexs[$this->bibtex_entry_conference] = array(
				$this->bibtex_field_editor,
				array( $this->bibtex_field_volume, $this->bibtex_field_number ),
				$this->bibtex_field_series,
				$this->bibtex_field_pages,
				$this->bibtex_field_address,
				$this->bibtex_field_month,
				$this->bibtex_field_organization,
				$this->bibtex_field_publisher,
				$this->bibtex_field_note,
				$this->bibtex_field_key
		);

		$this->optional_bibtexs[$this->bibtex_entry_inbook] = array(
				array( $this->bibtex_field_volume, $this->bibtex_field_number ),
				$this->bibtex_field_series,
				$this->bibtex_field_type,
				$this->bibtex_field_chapter,
				$this->bibtex_field_pages,
				$this->bibtex_field_address,
				$this->bibtex_field_edition,
				$this->bibtex_field_month,
				$this->bibtex_field_note,
				$this->bibtex_field_key
		);

		$this->optional_bibtexs[$this->bibtex_entry_incollection] = array(
				$this->bibtex_field_author,
				array( $this->bibtex_field_volume, $this->bibtex_field_number ),
				$this->bibtex_field_series,
				$this->bibtex_field_type,
				$this->bibtex_field_chapter,
				$this->bibtex_field_pages,
				$this->bibtex_field_address,
				$this->bibtex_field_edition,
				$this->bibtex_field_month,
				$this->bibtex_field_note,
				$this->bibtex_field_key
		);

		$this->optional_bibtexs[$this->bibtex_entry_inproceedings] = array(
				$this->bibtex_field_editor,
				array( $this->bibtex_field_volume, $this->bibtex_field_number ),
				$this->bibtex_field_series,
				$this->bibtex_field_pages,
				$this->bibtex_field_address,
				$this->bibtex_field_month,
				$this->bibtex_field_organization,
				$this->bibtex_field_publisher,
				$this->bibtex_field_note,
				$this->bibtex_field_key
		);

		$this->optional_bibtexs[$this->bibtex_entry_manual] = array(
				$this->bibtex_field_author,
				$this->bibtex_field_organization,
				$this->bibtex_field_address,
				$this->bibtex_field_edition,
				$this->bibtex_field_month,
				$this->bibtex_field_year,
				$this->bibtex_field_note,
				$this->bibtex_field_key
		);

		$this->optional_bibtexs[$this->bibtex_entry_mastersthesis] = array(
				$this->bibtex_field_type,
				$this->bibtex_field_address,
				$this->bibtex_field_month,
				$this->bibtex_field_note,
				$this->bibtex_field_key
		);

		$this->optional_bibtexs[$this->bibtex_entry_misc] = array(
				$this->bibtex_field_author,
				$this->bibtex_field_title,
				$this->bibtex_field_howpublished,
				$this->bibtex_field_month,
				$this->bibtex_field_year,
				$this->bibtex_field_note,
				$this->bibtex_field_key
		);

		$this->optional_bibtexs[$this->bibtex_entry_phdthesis] = array(
				$this->bibtex_field_type,
				$this->bibtex_field_address,
				$this->bibtex_field_month,
				$this->bibtex_field_note,
				$this->bibtex_field_key
		);

		$this->optional_bibtexs[$this->bibtex_entry_proceedings] = array(
				$this->bibtex_field_editor,
				array( $this->bibtex_field_volume, $this->bibtex_field_number ),
				$this->bibtex_field_series,
				$this->bibtex_field_address,
				$this->bibtex_field_month,
				$this->bibtex_field_publisher,
				$this->bibtex_field_organization,
				$this->bibtex_field_note,
				$this->bibtex_field_key
		);

		$this->optional_bibtexs[$this->bibtex_entry_techreport] = array(
				$this->bibtex_field_type,
				$this->bibtex_field_number,
				$this->bibtex_field_address,
				$this->bibtex_field_month,
				$this->bibtex_field_note,
				$this->bibtex_field_key
		);

		$this->optional_bibtexs[$this->bibtex_entry_unpublished] = array(
				$this->bibtex_field_month,
				$this->bibtex_field_year,
				$this->bibtex_field_key
		);

		$this->mendeley_bibtex_entry_mapping = array(
				$this->mendeley_entry_Bill =>  $this->bibtex_entry_misc,
				$this->mendeley_entry_Book => $this->bibtex_entry_book,
				$this->mendeley_entry_Book_Section => $this->bibtex_entry_inbook,
				$this->mendeley_entry_Case => $this->bibtex_entry_misc,
				$this->mendeley_entry_Computer_Program => $this->bibtex_entry_misc,
				$this->mendeley_entry_Conference_Proceedings => $this->bibtex_entry_conference,
				$this->mendeley_entry_Encyclopedia_Article => $this->bibtex_entry_misc,
				$this->mendeley_entry_Film => $this->bibtex_entry_misc,
				$this->mendeley_entry_Generic => $this->bibtex_entry_misc,
				$this->mendeley_entry_Hearing => $this->bibtex_entry_misc,
				$this->mendeley_entry_Journal_Article => $this->bibtex_entry_article,
				$this->mendeley_entry_Magazine_Article => $this->bibtex_entry_incollection,
				$this->mendeley_entry_Newspaper_Article => $this->bibtex_entry_incollection,
				$this->mendeley_entry_Patent => $this->bibtex_entry_misc,
				$this->mendeley_entry_Report => $this->bibtex_entry_techreport,
				$this->mendeley_entry_Statute => $this->bibtex_entry_misc,
				$this->mendeley_entry_Television_Broadcast => $this->bibtex_entry_misc,
				$this->mendeley_entry_Thesis => $this->bibtex_entry_mastersthesis,
				$this->mendeley_entry_Web_Page => $this->bibtex_entry_misc,
				$this->mendeley_entry_Working_Paper => $this->bibtex_entry_unpublished,
		);

		$this->bibtex_mendeley_field_mapping = array(
				$this->bibtex_field_address => $this->mendeley_field_city,
				$this->bibtex_field_annote => $this->mendeley_field_tags,
				$this->bibtex_field_booktitle => $this->mendeley_field_title,
				$this->bibtex_field_chapter => $this->mendeley_field_chapter,
				$this->bibtex_field_crossref => $this->mendeley_field_originalPublication,
				$this->bibtex_field_edition => $this->mendeley_field_edition,
				$this->bibtex_field_editor => $this->mendeley_field_publisher,
				$this->bibtex_field_eprint => $this->mendeley_field_website,
				$this->bibtex_field_howpublished => $this->mendeley_field_published_in,
				$this->bibtex_field_institution => $this->mendeley_field_institution,
				$this->bibtex_field_journal => $this->mendeley_field_publication_outlet,
				$this->bibtex_field_key => 'not_mapped',
				$this->bibtex_field_month => $this->mendeley_field_month,
				$this->bibtex_field_note => $this->mendeley_field_notes,
				$this->bibtex_field_number => $this->mendeley_field_volume,
				$this->bibtex_field_organization => $this->mendeley_field_institution,
				$this->bibtex_field_pages => $this->mendeley_field_pages,
				$this->bibtex_field_publisher => $this->mendeley_field_publisher,
				$this->bibtex_field_school => $this->mendeley_field_institution,
				$this->bibtex_field_series => $this->mendeley_field_series,
				$this->bibtex_field_title => $this->mendeley_field_title,
				$this->bibtex_field_type => $this->mendeley_field_type,
				$this->bibtex_field_url => $this->mendeley_field_website,
				$this->bibtex_field_volume => $this->mendeley_field_volume,
				$this->bibtex_field_year => $this->mendeley_field_year,
		);
	}

	public function parseMendeleyJson( $json ) {
		$this->mendeleyDocumentId = $json->{'documentId'};
		
		// First get the type of article.
		$mendeleyType = 0;
		if (isset( $json->{$this->mendeley_field_type} ) ) {
			$mendeleyType = $json->{$this->mendeley_field_type};
		}

		$bibtexType = $this->mendeley_bibtex_entry_mapping[$mendeleyType];
		if ( !isset( $bibtexType ) ) {
			$bibtexType = $this->bibtex_entry_misc;
		}

		// Authors.
		$bibtexAuthors = $this->bibtex_field_author." = { ";
		$mendeleyAuthors = $json->{$this->mendeley_field_authors};
		foreach ( $mendeleyAuthors as $author ) {
			$forename = $author->{'forename'};
			if ( strlen($forename) == 1 ) {
				$forename .= ".";
			}

			$bibtexAuthors .= $forename." {".$author->{'surname'}."} and ";
		}
		$bibtexAuthors = preg_replace("/ and $/", "", $bibtexAuthors);
		$bibtexAuthors .= "},";

		$output = "<code>@".$bibtexType." { test2002,".PHP_EOL;
		$output .= $bibtexAuthors.PHP_EOL;

		// Required fields.
		foreach ( $this->required_bibtexs[$bibtexType] as $requiredField ) {
			$value = NULL;

			if ( is_array($requiredField ) ) {
				foreach ( $requiredField as $field ) {
					$mappedMendeleyType = $this->bibtex_mendeley_field_mapping[$field];
					if ( isset($json->{$mappedMendeleyType}) ) {
						$value = $json->{$mappedMendeleyType};
						if ( $value != 0 ) {
							break;
						}
					}
				}
			}
			else {
				$mappedMendeleyType = $this->bibtex_mendeley_field_mapping[$requiredField];
				
				if ( isset($json->{$mappedMendeleyType}) ) {
					$value = $json->{$mappedMendeleyType};
				}
			}

			if ( isset( $value ) ) {	
				$output .= $requiredField." = {". $value . "},".PHP_EOL;
			}
		}

		// Optional fields.
		foreach ( $this->optional_bibtexs[$bibtexType] as $optionalField ) {

			$value = NULL;

			if ( is_array($optionalField ) ) {
				foreach ( $optionalField as $field ) {
					$mappedMendeleyType = $this->bibtex_mendeley_field_mapping[$field];
					if ( isset($json->{$mappedMendeleyType}) ) {
						$value = $json->{$mappedMendeleyType};
						if ( isset($value) ) {
							break;
						}
					}
				}
			}
			else {
				$mappedMendeleyType = $this->bibtex_mendeley_field_mapping[$optionalField];
				if ( isset($json->{$mappedMendeleyType}) ) {
					$value = $json->{$mappedMendeleyType};
				}
			}

			if ( isset( $value ) ) {
				$output .= $optionalField." = {". $value . "},".PHP_EOL;
			}
		}

		$output = preg_replace("/,$/", "", $output);

		$output .= "}</code>";

		return $output;
	}
}

?>