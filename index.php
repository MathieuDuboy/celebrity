<html>
<head>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
</head>
  <body>
  <?php
    // Insert de la page functions_wikipedia.php
    include("functions_wikipedia.php");

    // Remplacer ici "Mylène_Farmer" par la variable contenant le titre de la page Wikipedia
    // Exemple : get_datas_from_wiki($titre_de_la_page_wikipedia);
    get_datas_from_wiki("Mylène_Farmer");
  ?>
  </body>
</html>
