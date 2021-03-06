<?php

/*
 * This runs publicaly and first looks up the correct Open Range image based on the given partcode
 * It then tries to fetch and cache the image
 */

// Set to mp if you want to fetch images from the OR Media Portal
$source = 'mp';

function getImage($url, $useCurl = false) {

    if ($useCurl) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        $rawdata = curl_exec($ch);
        curl_close($ch);

        return $rawdata;
    } else {
        if ($rawdata = file_get_contents($url))
            return $rawdata;
        else
            die('file_get_contents failed');
    }
}



if (empty($_REQUEST['p']))
    die('please specify a partcode');

$partcode = strtoupper($_REQUEST['p']);


/// set src=mp on the query string to grab details from the media poll rather than the standard OR image
if (isset($_REQUEST['src']))
    $source = $_REQUEST['src'];
else
    $source = 'std';


if (!empty($partcode)) {
    //print_r($rows); die();
    // Oracle knows this product
    //$lookupUrl = 'http://www.exertismicro-p.co.uk/ImagesPortal/UK/Catalogue/product/'.$rows[0]['image'];
    $lookupUrl = 'http://apps2.exertismicro-p.co.uk/product_api/lookuporid.php?p=' . $partcode. ($source=='mp' ? '&src=mp' : '');

    $orimagename = file_get_contents($lookupUrl);
    if (empty($orimagename) && !isset($_REQUEST['force'])) {
        // grab default image from cache if possible, otherwise pull from Icom server
        $imagefilename = 'product_default.gif';
        if (file_exists($imagefilename))
            $im = file_get_contents($imagefilename);
        else {
            $im = getImage('http://www.exertismicro-p.co.uk/ImagesPortal/UK/Catalogue/' . $imagefilename);
            file_put_contents('./images/' . $imagefilename, $im);
        }

        header("Content-type: image/jpeg");
        echo $im;
        exit();
    }

    // otherwise pull from Icom server, or from the OR Media Pool
    if ($source=='mp')
        $libraryUrl = $orimagename.'&v=HR';// make sure we get the largest size, otherwise it defaults to 300px
    else
        $libraryUrl = 'http://www.exertismicro-p.co.uk/ImagesPortal/UK/Catalogue/product/' . $orimagename;

    // grab image from cache if possible, otherwise pull from Icom server, or OR MediaPool
    if (file_exists('./images/'.$orimagename)) {
        // check how old the cached version is
        if (time() - filemtime('./images/'.$orimagename) > 72 * 3600 || (filesize('./images/'.$orimagename) == 0)) {
            // it's older than 72 hours, refresh the cache, by pulling from I-com again
            $im = getImage($libraryUrl);

            if ($im !== FALSE) {
                // Cache image locally under two filenames - OR name and Exertis Micro-P Oracle partcode
                file_put_contents('./images/' . $orimagename, $im);
                $ext = pathinfo('./images/'.$orimagename, PATHINFO_EXTENSION);
                file_put_contents('./images/' . $partcode . '.' . $ext, $im);

                $failedfilename = './images/' . $partcode . '.FAILED.' . $ext;
                if (file_exists($failedfilename)) {
                    unlink('./images/'. $partcode . '.FAILED.' . $ext);
                }
            } else {
                $ext = pathinfo($orimagename, PATHINFO_EXTENSION);
                $failedfilename = './images/' . $partcode . '.FAILED.' . $ext;
                file_put_contents($failedfilename, $im);
            }
        } else {
            // use the cached version, is fresh enough and it's not empty
            $im = file_get_contents('./images/'.$orimagename);
        }
    } else {
        // We don't have a cached image
        $im = getImage($libraryUrl);
        // Cache image locally under two filenames - OR name and Exertis Micro-P Oracle partcode
        if ($source!='mp') {
            file_put_contents('./images/' . $orimagename, $im);
            $ext = pathinfo('./images/'.$orimagename, PATHINFO_EXTENSION);
        } else
            $ext='jpg';
        file_put_contents('./images/' . $partcode . '.' . $ext, $im);
    }
} else {

    // grab default image from cache if possible, otherwise pull from Icom server
    $imagefilename = 'product_default.gif';
    if (file_exists('./images/'.$imagefilename))
        $im = file_get_contents('./images/'.$imagefilename);
    else {
        $im = getImage('http://www.exertismicro-p.co.uk/ImagesPortal/UK/Catalogue/' . $imagefilename);
        file_put_contents('./images/' . $imagefilename, $im);
    }
}


header("Content-type: image/jpeg");
echo $im;
?>
