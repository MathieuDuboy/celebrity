<?php
include('simple_html_dom.php');

// déclaration de la fonction get_datas_from_wiki();
function get_datas_from_wiki($titre_page_wiki)
{
    // Ici on déclare le nombre max de paragraphes dans la biographgie ... ca peut souvent être un gros gros pavé !
    $limite_paragraphes_bio = 3;

    // Premiere section (Parse du contenu d'intro seulement)
    $url_wikipedia = "https://fr.wikipedia.org/w/api.php?action=query&exintro&prop=extracts&titles=" . $titre_page_wiki . "&inprop=url&format=json";
    $htmlContent2  = file_get_contents($url_wikipedia);
    $obj2          = json_decode($htmlContent2, true);
    $pages         = $obj2['query']['pages'];
    $wikipedia_id  = key($pages);

    $extract_contenu = $obj2['query']['pages'][$wikipedia_id]['extract'];
    $titre           = $obj2['query']['pages'][$wikipedia_id]['title'];

    // Affichage des données <p> de la premiere section
    $html = str_get_html($extract_contenu);
    foreach ($html->find('*') as $el) {
        echo '<p>' . $el->innertext . '</p>';
    }

    // Extract de la page entière
    $url_wikipedia   = "https://fr.wikipedia.org/w/api.php?action=query&prop=extracts|pageprops&titles=" . $titre_page_wiki . "&inprop=url&format=json";
    $htmlContent2    = file_get_contents($url_wikipedia);
    $obj2            = json_decode($htmlContent2, true);
    $extract_contenu = $obj2['query']['pages'][$wikipedia_id]['extract'];
    $html2           = str_get_html($extract_contenu);

    // Récupération du numero de recherche dans WIKIDATA ! (pour le bloc bleu de droite)
    $wikidata_item = $obj2['query']['pages'][$wikipedia_id]['pageprops']['wikibase_item'];

    $enregistrement_h2         = 0;
    $nb_limite_paragraphes_bio = 0;
    // Recherche si cette section Biographie existe (Elle est toujours n°1 quoi qu'il arrive)
    foreach ($html2->find('span[id=Biographie]', 0) as $element) {
        // on va chercher le premier <h3> qui correspond toujours à la Biographie
        echo '<p><b>Biographie</b></p>';
        foreach ($html2->find('*') as $el) {
            //echo $el->plaintext.'<br />';
            if (($el->tag == 'h2') && ($enregistrement_h2 != 0)) {
                break;
            }
            if ($el->tag == 'h3') {
                $nb_limite_paragraphes_bio++;
                echo '<p><b>' . $el->innertext . '</b> : ';
            }
            if (($el->tag == 'h2') && ($enregistrement_h2 == 0)) {
                $enregistrement_h2++;
            }
            if ($el->tag == 'p') {
                if ($enregistrement_h2 != 0) {
                    echo $el->innertext . '</p>';
                    if ($nb_limite_paragraphes_bio === $limite_paragraphes_bio)
                        break;
                }
            }
        }
        break;
    }

    // CECI CONCERNE  LA FILMOGRAPHIE
    $url_wikipedia               = "https://fr.wikipedia.org/w/api.php?action=parse&prop=sections&page=" . $titre_page_wiki . '&format=json';
    $htmlContent2                = file_get_contents($url_wikipedia);
    $obj2                        = json_decode($htmlContent2, true);
    //echo '<pre>'; print_r($obj2); echo '</pre>';
    $sections                    = $obj2['parse']['sections'];
    $numero_section_filmographie = "";
    $index_section == "";
    $numero_section              = array();
    $number_section              = array();
    $nom_section                 = array();
    foreach ($sections as $section) {
        // recherche du numero de section de la Filmographie
        if ($section['anchor'] == 'Filmographie') {
            $numero_section_filmographie = $section['number'];
            $index_section  = $section['index'];
        }
        if (strpos($section['number'], $numero_section_filmographie) === 0) {
            if ($section['number'] != $numero_section_filmographie) {
                $numero_section[] = $section['index'];
                $nom_section[]    = $section['anchor'];
                $number_section[] = $section['number'];
            }
        }
    }
    if ($index_section != "" && sizeof($number_section) == 0) {
        echo '<p><b>Filmographie</b></p>';
        $url_wikipedia   = "https://fr.wikipedia.org/w/api.php?action=parse&prop=text&section=" . $index_section . "&page=" . $titre_page_wiki . "&format=json";
        $htmlContent2    = file_get_contents($url_wikipedia);
        $obj2            = json_decode($htmlContent2, true);
        $extract_contenu = $obj2['parse']['text']['*'];
        $html            = str_get_html($extract_contenu);
        foreach ($html->find('ul') as $element) {
            $element_clair = preg_replace('#<a.*?>(.*?)</a>#i', '\1', $element);
            echo $element_clair;
        }
    } else {
        $a = 0;
        echo '<p><b>Filmographie</b></p>';
        foreach ($numero_section as $section) {
            $url_wikipedia = "https://fr.wikipedia.org/w/api.php?action=parse&prop=text&section=" . $section . "&page=" . $titre_page_wiki . "&format=json";
            $htmlContent2  = file_get_contents($url_wikipedia);
            $obj2          = json_decode($htmlContent2, true);
            $number        = $number_section[$a];
            if (strlen($number) == 3) {
                echo '<p><b>' . $nom_section[$a] . '</b></p>';
                $extract_contenu = $obj2['parse']['text']['*'];
                $html            = str_get_html($extract_contenu);
                foreach ($html->find('ul') as $element) {
                    $element_clair = preg_replace('#<a.*?>(.*?)</a>#i', '\1', $element);
                    echo $element_clair;
                }
                $a++;
            }
        }
    }
    // FIN DE LA FILMOGRAPHIE

    // Affichage de la source
    echo '<i class="tiny">Source : Wikipedia </i><a href="https://fr.wikipedia.org/wiki/' . $titre_page_wiki . '" rel="nofollow" target="_blank" class="tinytext" title="voir la page Wikipedia"> <i class="fa fa-external-link-alt"></i></a><br />';

    // Ici on parse uniquement le bloc du côté droit
    // Recherche du site officiel via un parse du DOM (impossible de faire autrement à mon avis ...)
    $url_wikipedia   = "https://www.wikidata.org/w/api.php?action=wbgetentities&sites=enwiki&props=claims&ids=".$wikidata_item."&languages=fr&format=json";
    $htmlContent2    = file_get_contents($url_wikipedia);
    $obj2            = json_decode($htmlContent2, true);
    // echo '<pre>'; print_r($obj2); echo '</pre>';
    // Faites afficher la ligne du dessus pour visualiser les propriétés disponibles dans le tableau.

    $extract_contenu = $obj2['entities'][$wikidata_item]['claims'];
    // Il suffit alors maintenant d'accéder aux n° de propriétés lorsqu'elle sont disponibles
    // P856 = Site officiel
    if(isset($extract_contenu['P856'])) {
      $site_web = $extract_contenu['P856'][0]['mainsnak']['datavalue']['value'];
      echo '<i class="tiny">Site Officiel de ' . $titre . '</i><a href="'.$site_web.'" rel="nofollow" target="_blank" class="tinytext" title="Site Officiel de ' . $titre . '"> <i class="fa fa-certificate"></i></a><br />';
    }

    // Pour P2031 : Format de la date de ce type +2003-00-00T00:00:00Z
    if(isset($extract_contenu['P2031'])) {
      $date_actif = $extract_contenu['P2031'][0]['mainsnak']['datavalue']['value']['time'];
      $tab_date = explode("+", $date_actif);
      $date_uniquement = $tab_date[1];
      $tab_date2 = explode("-", $date_uniquement);
      $annee = $tab_date2[0];
      echo '<i class="tiny">Actif depuis : '.$annee.'</i><br />';
    }

    // Pour P2048 la taille est en cm et contient un + devant ... Exemple : +159
    if(isset($extract_contenu['P2048'])) {
      $taille = $extract_contenu['P2048'][0]['mainsnak']['datavalue']['value']['amount'];
      $taille = str_replace("+","",$taille);
      echo '<i class="tiny">Taille de '.$titre.' : '.$taille.'cm</i><br />';
    }

}
?>
