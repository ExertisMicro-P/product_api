<?php

/*
 * This runs publicaly and first looks up the correct Open Range image based on the given partcode
 * It then tries to fetch and cache the image
 */

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

try {


    # MySQL with PDO_MYSQL
    $db = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
} catch (PDOException $e) {
    echo $e->getMessage();
}


if (empty($_REQUEST['p']))
    die('please specify a partcode');

$partcode = strtoupper($_REQUEST['p']);



if (!empty($partcode)) {
    //print_r($rows); die();
    // Oracle knows this product
    //$lookupUrl = 'http://www.exertismicro-p.co.uk/ImagesPortal/UK/Catalogue/product/'.$rows[0]['image'];
    $lookupUrl = 'http://apps2.exertismicro-p.co.uk/product_api/lookuporid.php?p=' . $partcode;

    $orimagename = file_get_contents($lookupUrl);
    if (empty($orimagename)) {
        // grab default image from cache if possible, otherwise pull from Icom server
        $imagefilename = 'product_default.gif';
        if (file_exists($imagefilename))
            $im = file_get_contents($imagefilename);
        else {
            $im = getImage('http://www.exertismicro-p.co.uk/ImagesPortal/UK/Catalogue/' . $imagefilename);
            file_put_contents('./' . $imagefilename, $im);
        }

        header("Content-type: image/jpeg");
        echo $im;
        exit();
    }


    $icomUrl = 'http://www.exertismicro-p.co.uk/ImagesPortal/UK/Catalogue/product/' . $orimagename;

    // grab image from cache if possibel, otherwise pull from Icom server
    if (file_exists($orimagename)) {
        // check how old the cached version is
        if (time() - filemtime($orimagename) > 72 * 3600 || (filesize($orimagename) == 0)) {
            // it's older than 72 hours, refresh the cache, by pulling from I-com again
            $im = getImage($icomUrl);

            if ($im !== FALSE) {
                // Cache image locally under two filenames - OR name and Exertis Micro-P Oracle partcode
                file_put_contents('./' . $orimagename, $im);
                $ext = pathinfo($orimagename, PATHINFO_EXTENSION);
                file_put_contents('./' . $partcode . '.' . $ext, $im);

                $failedfilename = './' . $partcode . '.FAILED.' . $ext;
                if (file_exists($failedfilename)) {
                    unlink('./' . $partcode . '.FAILED.' . $ext);
                }
            } else {
                $ext = pathinfo($rows[0]['image'], PATHINFO_EXTENSION);
                $failedfilename = './' . $partcode . '.FAILED.' . $ext;
                file_put_contents($failedfilename, $im);
            }
        } else {
            // use the cached version, is fresh enough and it's not empty
            $im = file_get_contents($orimagename);
        }
    } else {
        // We don't have a cached image
        $im = getImage($icomUrl);
        // Cache image locally under two filenames - OR name and Exertis Micro-P Oracle partcode
        file_put_contents('./' . $orimagename, $im);
        $ext = pathinfo($orimagename, PATHINFO_EXTENSION);
        file_put_contents('./' . $partcode . '.' . $ext, $im);
    }
} else {

    // grab default image from cache if possible, otherwise pull from Icom server
    $imagefilename = 'product_default.gif';
    if (file_exists($imagefilename))
        $im = file_get_contents($imagefilename);
    else {
        $im = getImage('http://www.exertismicro-p.co.uk/ImagesPortal/UK/Catalogue/' . $imagefilename);
        file_put_contents('./' . $imagefilename, $im);
    }
}


header("Content-type: image/jpeg");
echo $im;
?>
