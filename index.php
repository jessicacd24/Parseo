<?php

require('simple_html_dom.php');

$html = file_get_html('https://www.masterd.es/cursos-de-formacion-mantenimiento-industrial-g11');

$numberOfCourses = 0;
$numberOfLinks = 0;
$numberOfDescription = 0;

foreach($html->find('div[id=listado-grupo] span') as $element)
{
    //echo $element->plaintext."\n";
    $name[$numberOfCourses] = strtolower($element->plaintext);
    $originalname[$numberOfCourses] = $element->plaintext;
    $numberOfCourses++;
}

foreach($html->find('div[id=listado-grupo] a') as $link) {
    //echo $link->href . "\n";
    $links[$numberOfLinks] = $link->href;
    $numberOfLinks++;
}

//print_r($links[1]);

foreach($html->find('div[id=listado-grupo] a') as $link)
{
    $links = file_get_html($link->href);
    foreach($links->find('div[id=contenido-ficha]') as $links)
    {
        //echo $links->plaintext."\n";
        $description[$numberOfDescription] = $links->plaintext;
        $numberOfDescription++;
    }
}

$fp = fopen ("cursos_masterd.csv","r") or die ("Problemas con el archivo");
//$fpFind = fopen ("cursos_encontrados.csv","a") or die ("Problemas con el archivo");
//$fpNotFind = fopen ("cursos_noencontrados.csv","w") or die ("Problemas con el archivo");

$counterFind = 0;
$counterNotFind = 0;

while ($data = fgetcsv ($fp, 100, ";")) {

    foreach ($data as $columna) {
        $info = explode(",", $columna);

        $term = strtolower(stripslashes($info[1]));
        $termToSearch = str_replace('"','',$term);

        $resultsearch = array_search($termToSearch, $name);

        if ($resultsearch) {
//            fwrite($fpFind, $info[0].";");
//            fwrite($fpFind, $info[1]."\n");
            $nameFind[$counterFind] = $originalname[$resultsearch];
            $linksFind[$counterFind] = $links[$resultsearch];
            $id[$counterFind] = $info[0];
            $descriptionFind[$counterFind] = $description[$resultsearch];
            $counterFind++;
       }
        else {
////            fwrite($fpNotFind, $name[$counter]."\n");
           $nameNotFind[$counterNotFind] = $originalname[$counterNotFind];
//            $linksNotFind[$counterNotFind] = $links[$counterNotFind];
            $descriptionNotFind[$counterNotFind] = $description[$counterNotFind];
            $counterNotFind++;
        }
    }
}
//print_r($nameFind);
//print_r($id);
//print_r($linksFind);
//print_r($descriptionFind);

//print_r($nameNotFind);
//print_r($linksNotFind);
//print_r($descriptionNotFind);

fclose ($fp);
//fclose ($fpFind);
//fclose ($fpNotFind);

