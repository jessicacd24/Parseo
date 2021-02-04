<?php

require('simple_html_dom.php');

$html = file_get_html('https://www.masterd.es/cursos-de-formacion-mantenimiento-industrial-g11');

$numberOfCourses = 0;
$numberOfLinks = 0;
$numberOfDescription = 0;

///////////////////////////Finding the names of the courses////////////////////////////////////////

    foreach($html->find('div[id=listado-grupo] span') as $element)
    {
        //echo $element->plaintext."\n";
        $name[$numberOfCourses] = strtolower($element->plaintext);
        $originalName[$numberOfCourses] = $element->plaintext;
        $numberOfCourses++;
    }

/////////////////////////////Finding the links of the courses///////////////////////////////////////

    foreach($html->find('div[id=listado-grupo] a') as $link)
    {
        //echo $link->href . "\n";
        $links[$numberOfLinks] = $link->href;
        $url[$numberOfLinks] = serialize($links[$numberOfLinks]);
        $numberOfLinks++;
    }

//////////////////////////////////////Finding the descriptions of the courses///////////////////////

    foreach($html->find('div[id=listado-grupo] a') as $link)
    {
        $links = file_get_html($link->href);
        foreach($links->find('div[id=contenido-ficha]') as $links)
        {
            //echo html_entity_decode($links->plaintext."\n");
            $description[$numberOfDescription] = html_entity_decode($links->plaintext);
            $numberOfDescription++;
        }
    }

/////////////////Matching courses with the CSV file and $allFoundCourses array/////////////////////////

$fp = fopen ("cursos_masterd.csv","r") or die ("Problemas con el archivo");

$counterFound = 0;
$counterNotFound = 0;

    while ($data = fgetcsv ($fp, 100, ";")) {

        foreach ($data as $columna) {
            $info = explode(",", $columna);

            $term = strtolower(stripslashes($info[1]));
            $termToSearch = str_replace('"','',$term);

            $resultsearch = array_search($termToSearch, $name);

            if ($resultsearch) {
                $nameFound[$counterFound] = $originalName[$resultsearch];
                $linksFound[$counterFound] = unserialize($url[$resultsearch]);
                $id[$counterFound] = $info[0];
                $descriptionFound[$counterFound] = $description[$resultsearch];
                $counterFound++;
           }
        }
    }

////////////////////////////////////notFoundCourses arrays//////////////////////////////////////////

$auxCount = 0;
$p = 0;

    for ($i = 0; $i < sizeof($originalName); $i++) {
        for ($j = 0; $j < sizeof($nameFound); $j++) {
            if ($originalName[$i] !== $nameFound[$j]) {
                $auxCount++;
            }
        }
        if ($auxCount == 6) {
            $nameNotFound[$p] = $originalName[$i];
            $linksNotFound[$p] = unserialize($url[$i]);
            $descriptionNotFound[$p] = $description[$i];
            $p++;
        }
        $auxCount = 0;
    }

/////////////////////////////FILE coursesFound.xml/////////////////////////////////////////////
$objectXML = new XMLWriter();

$objectXML->openUri("coursesFound.xml");
$objectXML->setIndent(true);
$objectXML->setIndentString("\t");
$objectXML->startDocument('1.0', 'utf-8');

$objectXML->startElement("courses");

    for ($i = 0; $i < sizeof($nameFound); $i++) {
        $objectXML->startElement("course");

        $objectXML->startElement("title");
        $objectXML->text($nameFound[$i]);
        $objectXML->endElement();
        $objectXML->startElement("url");
        $objectXML->text($linksFound[$i]);
        $objectXML->endElement();
        $objectXML->startElement("id");
        $objectXML->text($id[$i]);
        $objectXML->endElement();
        $objectXML->startElement("description");
        $objectXML->text($descriptionFound[$i]);
        $objectXML->endElement();

        $objectXML->endElement();
    }

$objectXML->endElement();
$objectXML->endDocument();

/////////////////////////////////FILE coursesNotFound.xml//////////////////////////////////////////////////////////////

$objectXML2 = new XMLWriter();

$objectXML2->openUri("coursesNotFound.xml");
$objectXML2->setIndent(true);
$objectXML2->setIndentString("\t");
$objectXML2->startDocument('1.0', 'utf-8');

$objectXML2->startElement("courses");

    for ($i = 0; $i < sizeof($nameNotFound); $i++) {
        $objectXML2->startElement("course");

        $objectXML2->startElement("title");
        $objectXML2->text($nameNotFound[$i]);
        $objectXML2->endElement();
        $objectXML2->startElement("url");
        $objectXML2->text($linksNotFound[$i]);
        $objectXML2->endElement();
        $objectXML2->startElement("description");
        $objectXML2->text($descriptionNotFound[$i]);
        $objectXML2->endElement();

        $objectXML2->endElement();
    }

$objectXML2->endElement();
$objectXML2->endDocument();

fclose ($fp); //closing CSV file