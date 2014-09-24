<?php

$host = 'intdb3';
$dbname = 'epic';
$user='russellh';
$pass='sl58jySL%*JY';



function getImage($url, $useCurl = true) {

    if ($useCurl) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
        $rawdata=curl_exec ($ch);
        curl_close ($ch);

        return $rawdata;
    } else {
       if ($rawdata = file_get_contents($url))
           return $rawdata;
       else
           die ('file_get_contents failed');
    }
}


try {


  # MySQL with PDO_MYSQL
  $db = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);

}
catch(PDOException $e) {
    echo $e->getMessage();
}



$partcode = $_REQUEST['partcode'];


$stmt = $db->prepare("SELECT image, manufacturer FROM epic_or_products WHERE oracle_part_no = :partcode LIMIT 1");
$stmt->bindValue(':partcode', $partcode, PDO::PARAM_STR);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($rows) && !empty($rows[0]['image'])) {
    //print_r($rows); die();
    // Oracle knows this product
    $icomUrl = 'http://www.exertismicro-p.co.uk/ImagesPortal/UK/Catalogue/product/'.$rows[0]['image'];

    // grab image from cache if possibel, otherwise pull from Icom server
    if (file_exists($rows[0]['image'])) {
        // check how old the cached version is
        if (time()-filemtime($rows[0]['image']) > 72 * 3600 || (filesize($rows[0]['image']) == 0)) {
            // it's older than 72 hours, refresh the cache, by pulling from I-com again
            $im = getImage($icomUrl);

            if ($im !== FALSE) {
                // Cache image locally under two filenames - OR name and Exertis Micro-P Oracle partcode
                file_put_contents('./'.$rows[0]['image'], $im);
                $ext = pathinfo($rows[0]['image'], PATHINFO_EXTENSION);
                file_put_contents('./'.strtolower($rows[0]['manufacturer']).'-'.$partcode.'.'.$ext, $im);

                $failedfilename = './'.strtolower($rows[0]['manufacturer']).'-'.$partcode.'.FAILED.'.$ext;
                if (file_exists($failedfilename)) {
                    unlink('./'.strtolower($rows[0]['manufacturer']).'-'.$partcode.'.FAILED.'.$ext);
                }
            } else {
                $ext = pathinfo($rows[0]['image'], PATHINFO_EXTENSION);
                $failedfilename = './'.strtolower($rows[0]['manufacturer']).'-'.$partcode.'.FAILED.'.$ext;
                file_put_contents($failedfilename, $im);
            }
        } else {
            // use the cached version, is fresh enough and it's not empty
            $im = file_get_contents ($rows[0]['image']);
        }
    } else {
        // We don't have a cached image
        $im = getImage($icomUrl);
        // Cache image locally under two filenames - OR name and Exertis Micro-P Oracle partcode
        file_put_contents('./'.$rows[0]['image'], $im);
        $ext = pathinfo($rows[0]['image'], PATHINFO_EXTENSION);
        file_put_contents('./'.strtolower($rows[0]['manufacturer']).'-'.$partcode.'.'.$ext, $im);
    }

} else {

    // grab default image from cache if possible, otherwise pull from Icom server
    $imagefilename = 'product_default.gif';
    if (file_exists($imagefilename))
        $im = file_get_contents ($imagefilename);
    else {
        $im = getImage('http://www.exertismicro-p.co.uk/ImagesPortal/UK/Catalogue/'.$imagefilename);
        file_put_contents('./'.$imagefilename, $im);
    }
}


header("Content-type: image/jpeg");
echo $im;
?>
