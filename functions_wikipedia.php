<?php
include('simple_html_dom.php');

// déclaration de la fonction get_datas_from_wiki();
function get_datas_from_wiki($titre_page_wiki)
{
    // Recherche de l'ID de la page à partir de son URL (titre de la page Wikipedia)
    $lien_page_wiki = "https://fr.wikipedia.org/w/api.php?action=query&format=json&titles=" . $titre_page_wiki;
    $htmlContent    = file_get_contents($lien_page_wiki);
    $objet          = json_decode($htmlContent, true);
    $pages          = $objet['query']['pages'];
    $wikipedia_id   = key($pages);

    // Premiere section
    $url_wikipedia   = "https://fr.wikipedia.org/w/api.php?action=query&exintro&prop=extracts&pageids=" . $wikipedia_id . "&inprop=url&format=json";
    $htmlContent2    = file_get_contents($url_wikipedia);
    $obj2            = json_decode($htmlContent2, true);
    $extract_contenu = $obj2['query']['pages'][$wikipedia_id]['extract'];
    $titre           = $obj2['query']['pages'][$wikipedia_id]['title'];
    // Affichage des données <p> de la premiere section
    $html = str_get_html($extract_contenu);
    foreach ($html->find('*') as $el) {
        echo '<p>' . $el->innertext . '</p>';
    }

    // récupération du texte de la Biographie (si elle existe)
    $url_wikipedia   = "https://fr.wikipedia.org/w/api.php?action=query&prop=extracts&pageids=" . $wikipedia_id . "&inprop=url&format=json";
    $htmlContent2    = file_get_contents($url_wikipedia);
    $obj2            = json_decode($htmlContent2, true);
    $extract_contenu = $obj2['query']['pages'][$wikipedia_id]['extract'];
    $html            = str_get_html($extract_contenu);

    $enregistrement = 0;
    // Recherche si cette section Biographie existe
    foreach ($html->find('span[id=Biographie]', 0) as $element) {
        // on va chercher le premier <h3> qui correspond toujours à la Biographie
        echo '<p><b>Biographie</b></p>';
        foreach ($html->find('*') as $el) {
            if (($el->tag == 'h3') && ($enregistrement != 0)) {
                break;
            }
            if (($el->tag == 'h3') && ($enregistrement == 0)) {
                $enregistrement++;
            }
            if ($el->tag == 'p') {
                if ($enregistrement != 0) {
                    echo '<p>' . $el->innertext . '</p>';
                }
            }
        }
        break;
    }

    // Affichage de la source
    echo '<i class="tiny">Source : Wikipedia </i><a href="https://fr.wikipedia.org/wiki/' . $titre_page_wiki . '" rel="nofollow" target="_blank" class="tinytext" title="voir la page Wikipedia"> <i class="fa fa-external-link-alt"></i></a><br />';

    //Rechercher le site officiel de l'artiste s'il existe
    $url_wikipedia   = "https://fr.wikipedia.org/w/api.php?action=query&prop=revisions&rvprop=content&rvsection=0&rvparse=1&pageids=" . $wikipedia_id . "&inprop=url&format=json";
    $htmlContent2    = file_get_contents($url_wikipedia);
    $obj2            = json_decode($htmlContent2, true);
    $extract_contenu = $obj2['query']['pages'][$wikipedia_id]['revisions'][0]['*'];
    $html            = str_get_html($extract_contenu);
    foreach ($html->find('span.url') as $element) {
        echo '<i class="tiny">Site Officiel de ' . $titre . '</i><a href="' . $element->plaintext . '" rel="nofollow" target="_blank" class="tinytext" title="Site Officiel de ' . $titre . '"> <i class="fa fa-certificate"></i></a>';
    }
}
?>
