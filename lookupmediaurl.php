<?php

/*
 * This runs on ma-webproxy-04 and just looks up the Open Range ORxxxxxxxxx.jpg
 * filename given an oracle partcode
 *
 * http://apps2.exertismicro-p.co.uk/product_api/lookuporid.php?p=FS26361F3019L513
 *
 * Will return e.g. OR800000029065.jpg
 * This can then be used to construct the image URL - see getimage.php (which runs publicly on PagodaBox
 *
 * If &src=mp is added to the URL, then it attempts to fetch image from the OR Media Pool.
 * It will return the entire image URL.
 */

$host = 'intdb3';
$dbname = 'epic';
$user='russellh';
$pass='sl58jySL%*JY';




try {


  # MySQL with PDO_MYSQL
  $db = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);

}
catch(PDOException $e) {
    echo $e->getMessage();
}



$partcode = $_REQUEST['p'];


/// set src=mp on the query string to grab details from the media poll rather than the standard OR image
if (isset($_REQUEST['src']))
    $source = $_REQUEST['src'];
else
    $source = 'std';

// remove anything after the @
$splitforrawcode = explode('@',$partcode);

$partcode = $splitforrawcode[0];

$stmt = $db->prepare("SELECT product_id, image, manufacturer FROM epic_or_products WHERE oracle_part_no = :partcode LIMIT 1");
$stmt->bindValue(':partcode', $partcode, PDO::PARAM_STR);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);




if (!empty($rows)) {
    // we found a match for our oracle partcode

    if ($source!='mp') {

        // return a standard OR image
        if(!empty($rows[0]['image'])) {
            ////print_r($rows); die();
            // Oracle knows this product

            echo  $rows[0]['image']; // e.g. OR800000029065.jpg
            exit();

        }

    } else {
        // attempt to get image from media pool
        $stmt = $db->prepare("SELECT id, url FROM epic_or_mediapool WHERE product_id = :id order by GREATEST(width,height) DESC LIMIT 1");
        $stmt->bindValue(':id', $rows[0]['product_id'], PDO::PARAM_STR);
        $stmt->execute();
        $media = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($media)) {
            $url = str_replace('http://mediapool.getthespec.com/media.jpg?m=','http://product-api.gopagoda.com/get/',$media[0]['url']);
            echo $url.'.jpg';
            exit();
        }

    }
}

echo 'product_default.gif';
?>
