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
    $url_wikipedia   = "https://fr.wikipedia.org/w/api.php?action=query&prop=extracts&titles=" . $titre_page_wiki . "&inprop=url&format=json";
    $htmlContent2    = file_get_contents($url_wikipedia);
    $obj2            = json_decode($htmlContent2, true);
    $extract_contenu = $obj2['query']['pages'][$wikipedia_id]['extract'];
    $html2           = str_get_html($extract_contenu);

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
    $url_wikipedia   = "https://fr.wikipedia.org/w/api.php?action=query&prop=revisions&rvprop=content&rvsection=0&rvparse=1&pageids=" . $wikipedia_id . "&inprop=url&format=json";
    $htmlContent2    = file_get_contents($url_wikipedia);
    $obj2            = json_decode($htmlContent2, true);
    $extract_contenu = $obj2['query']['pages'][$wikipedia_id]['revisions'][0]['*'];
    $html            = str_get_html($extract_contenu);
    foreach ($html->find('span.url') as $element) {
        echo '<i class="tiny">Site Officiel de ' . $titre . '</i><a href="//' . $element->plaintext . '" rel="nofollow" target="_blank" class="tinytext" title="Site Officiel de ' . $titre . '"> <i class="fa fa-certificate"></i></a>';
    }

}
?>
