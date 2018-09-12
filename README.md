# Celebrity
## But du script

Ce script permet de récupérer sur Wikipédia :
- Elements du Texte d'introduction
- Elements du Premier paragraphe de la Biographie (si présente)
- Lien vers le site officiel (si présent)

## Installation
1. Télécharger les fichiers (**functions_wikipedia.php** et **simple_html_dom.php**) sur votre hébergement Web dans le meme répertoire que le template "Fiche détail célébrité"
2. Remplacer la fonction actuelle permettant de récupérer l'intro de Wikipédia par la fonction **get_datas_from_wiki()**

## Utilisation
1. Récupérer la variable PHP du titre de la page Wikipédia
2. Inclure **functions_wikipedia.php** dans le template avec ````include('functions_wikipedia.php');````
2. Insérer la variable dans la fonction comme ceci : ````get_datas_from_wiki($titre_page_wiki);````