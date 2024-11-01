<?php
/*===========================================*/
/*=== Insertion de chaque article de flux ===*/
/*===========================================*/
// Fonction d'insertion
function wpfcrf_insert_all_feeds_items($fluxList = array(), $combine = true) {
	if(!empty($fluxList)) {
		// Combinaison des flux en un seul
		if(get_site_option('wpfcrf_import_method')) {
			if(get_site_option('wpfcrf_import_method') == "xmlreader") {
				$feedsItems = wp_fcrf_combination_xmlreader($fluxList, $combine);
			} else {
				$feedsItems = wp_fcrf_combination_simplexml($fluxList, $combine);
			}
		} else {
			$feedsItems = wp_fcrf_combination_xmlreader($fluxList, $combine);
		}

		if(!empty($feedsItems['feeds'])) {
			foreach($feedsItems['feeds'] as $feedItem) {
				// Vérifie si l'article du flux existe déjà
				$check = wpfcrf_check_feed_item($feedItem);

				// Insertion de l'ensemble des articles de flux (si inexistant)
				if(!$check) {
					wpfcrf_insert_feed_item($feedItem); // Insertion des articles supplémentaires
				}
			}
		}
	}
}

// Fonction d'insertion d'un flux unique
function wpfcrf_insert_feed_item($item) {
	// Remet la date au bon format
	$format 	= 'Y-m-d H:i:s';
	if(!empty($item['date'])) {
		$date 		= date($format, $item['date']);
		$date_gmt 	= gmdate($format, $item['date']);
	} else {
		$date = date($format);
		$date_gmt = gmdate($format);
	}

	// Paramètres
	$params = array(
		'post_title'    => $item['title'],
		'post_content'  => $item['desc'],
		'post_status'   => 'publish',
		'post_type'     => 'wpfcrf_feeds',
		'post_date'     => $date,
		'post_date_gmt' => $date_gmt,
		'meta_input'		=> array(
			'wpfcrf_related_post_type'	=> 'wpfcrf_sources',
			'wpfcrf_feed_post_url'		=> $item['link'],
			'wpfcrf_feed_source_name'	=> $item['feed_name'],
			'wpfcrf_feed_source_id'		=> intval($item['feed_ID']),
			'wpfcrf_feed_source_link'	=> $item['feed_link'],
			'wpfcrf_feed_source_slug'	=> $item['feed_slug'],
		)
	);

	// Tableau des données à ajouter pour la source du flux
	$feedTab = apply_filters(
		'wpfcrf_feeds_add',
		$params,
		$item
	);

	// Insert la source dans la liste du custom post type
	$insertFeed = wp_insert_post($feedTab);
}

// Fonction de vérification de l'existence d'un flux
function wpfcrf_check_feed_item($item) {
	$posts = get_posts(array(
		'name' => sanitize_title($item['title']),
		'post_status' => array('publish', 'future'),
	    'post_type' => 'wpfcrf_feeds',
	    'posts_per_page' => 1
	));

	if(!empty($posts)) {
		return true;
	} else {
		return false;
	}
/*	$r = get_page_by_title($item['title'], OBJECT, 'wpfcrf_feeds');
	if(!is_null($r)) {
		return true;
	} else {
		return false;
	}*/
}

function wp_fcrf_date_format($date = "") {
	// Modifie quelques formats de dates français malencontreux => Format 'D. d F H:i:s'
	$impuretes = array("Lun.", "Mar.", "Mer.", "Jeu.", "Ven.", "Sam.", "Dim.", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche", "janvier", "février", "fevrier", "mars", "avril", "mai", "juin", "juillet", "août", "aout", "septembre", "octobre", "novembre", "décembre", "decembre");
	// $date = str_ireplace($impuretes, "", $date);
	$impuretes2 = array("Mon.", "Tue.", "Wed.", "Thu.", "Fri.", "Sat.", "Sun.", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday", "january", "february", "february", "march", "april", "may", "june", "july", "august", "august", "september", "october", "november", "december", "december");
	$date = str_ireplace($impuretes, $impuretes2, $date);
	if(DateTime::createFromFormat('D. d F H:i:s', $date) !== FALSE) {
		$date = date_create_from_format('D. d F H:i:s', $date);
		$date = $date->format('Y-m-d');
	}

	// Transforme en timestamp
	$date = strtotime($date);

	return $date;
}

/*=======================================*/
/*=== Combinaison des flux en un seul ===*/
/*=======================================*/
// http://www.dynamic-mess.com/php/lire-un-flux-rss-avec-php-simple-xml-6-30/
// https://rythie.com/blog/blog/2011/02/27/using-a-hybrid-of-xmlreader-and-simplexml/
// https://www.ibm.com/developerworks/library/x-xmlphp2/index.html
// XMLReader => le meilleur (plus rapide)
function wp_fcrf_combination_xmlreader($fluxList = array(), $combine = true) {
	// Tableau des flux et/ou erreurs
	$feeds = array();

	// Combinaison des flux RSS
	if($combine === true) {
		$feed = array(); // Flux RSS à remplir (combiné)
		$feedError = array(); // Flux avec erreur (URL, etc.)

		// Parcourt de l'ensemble des flux à combiner
		foreach($fluxList as $flux) {
			// Autorise la gestion des erreurs internes de libxml
			libxml_use_internal_errors(true);
			
			// Instancie XMLReader() => Plus rapide que simplexml
			$reader = new XMLReader();

			// Ouvre la source
		    if($reader->open($flux['link'])) {
				$reader->setParserProperty(XMLReader::VALIDATE, true);
				if($reader->isValid()) {
					$i = 0;
					while($reader->read()) {
						if($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'item') {
							$nodeText = $reader->readOuterXML();
							if($xml = simplexml_load_string($nodeText)) {
								// Récupère les images si nécessaire
								$enclosure = (array) $xml->enclosure;
								$imageAtts = $enclosure['@attributes'];
								$feed[] = array(
									'feed_ID'	=> intval($flux['ID']),
									'feed_slug'	=> $flux['slug'],
									'feed_link'	=> $flux['link'],
									'feed_name'	=> (!empty($flux['name'])) ? (string) $flux['name'] : (string) $xml->$title,
									'title'		=> (string) $xml->title,
									'desc'  	=> (string) $xml->description,
									'link'  	=> (string) $xml->link,
									'date'  	=> wp_fcrf_date_format($xml->pubDate),
									'image'		=> $imageAtts,
								);
							} else {
								$feedError[] = array(
									'error_type'=> "simplexml_load",
									'feed_ID'	=> intval($flux['ID']),
									'feed_slug'	=> $flux['slug'],
									'feed_name' => $flux['name'],
									'feed_link' => $flux['link'],
									'nodeText'	=> $nodeText
								);
							}
						}
					}
				} else {
					$feedError[] = array(
						'error_type'=> "isValid",
						'feed_ID'	=> intval($flux['ID']),
						'feed_slug'	=> $flux['slug'],
						'feed_name' => $flux['name'],
						'feed_link' => $flux['link']
					);
				}
				// Fermeture du flux (performance)
				$reader->close($flux['link']);
			} else {
				$feedError[] = array(
					'error_type'=> "open",
					'feed_ID'	=> intval($flux['ID']),
					'feed_slug'	=> $flux['slug'],
					'feed_name' => $flux['name'],
					'feed_link' => $flux['link']
				);
			}
		}

		// Retourne les erreurs potentielles dans une notice propre
		if(isset($feedError) && !empty($feedError)) {
			new wpfcrf_feeds_importation_admin_notice_error($feedError);
		}

		// Trie les flux
		usort($feed, function($a, $b) {
			return strtotime($b['date']) - strtotime($a['date']);
		});

		$feeds = array(
			'feeds' => $feed,
			'feeds_errors' => $feedError
		);
	}

	return $feeds;
}

function wp_fcrf_combination_simplexml($fluxList = array(), $combine = true) {
	// Tableau des flux et/ou erreurs
	$feeds = array();

	// Combinaison des flux RSS
	if($combine === true) {
		$feed = array(); // Flux RSS à remplir (combiné)
		$feedError = array(); // Flux avec erreur (URL, etc.)

		// Parcourt de l'ensemble des flux à combiner
		foreach($fluxList as $flux) {
			// Autorise la gestion des erreurs internes de libxml
			libxml_use_internal_errors(true);

			if($rss = @simplexml_load_file($flux['link'])) {
				// Récupération des données (title/channel)
				$data = $rss->channel;
				$title = $data->title;

				foreach($data->item as $key => $valeur) {
					// Récupère les images si nécessaire
					$enclosure = (array) $valeur->enclosure;
					$imageAtts = $enclosure['@attributes'];

					$item = array(
						'feed_ID'	=> intval($flux['ID']),
						'feed_slug'	=> $flux['slug'],
						'feed_link'	=> $flux['link'],
						'feed_name'	=> (!empty($flux['name'])) ? (string) $flux['name'] : (string) $title,
						'title'		=> (string) $valeur->title,
						'desc'  	=> (string) $valeur->description,
						'link'  	=> (string) $valeur->link,
						'date'  	=> wp_fcrf_date_format($valeur->pubDate),
						'image'		=> $imageAtts,
					);
					array_push($feed, $item);
				}
			} else {
				$feedError[] = array(
					'feed_ID'	=> intval($flux['ID']),
					'feed_slug'	=> $flux['slug'],
					'feed_name' => $flux['name'],
					'feed_link' => $flux['link']
				);
			}
		}

		// Retourne les erreurs potentielles dans une notice propre
		if(isset($feedError) && !empty($feedError)) {
			new wpfcrf_feeds_importation_admin_notice_error($feedError);
		}

		// Trie les flux
		usort($feed, function($a, $b) {
			return strtotime($b['date']) - strtotime($a['date']);
		});

		$feeds = array(
			'feeds' => $feed,
			'feeds_errors' => $feedError
		);
	}

	return $feeds;
}
?>