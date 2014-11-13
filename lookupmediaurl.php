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
$user = 'russellh';
$pass = 'sl58jySL%*JY';

$manual_images_path = './images/';
//$manual_images_url = 'http://apps2.exertismicro-p.co.uk/product_api/images/';
$manual_images_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/images/';
//$manual_images_url = 'http://product-api.gopagoda.com/images/';


try {


    # MySQL with PDO_MYSQL
    $db = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
} catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}



$partcode = $_REQUEST['p'];


/// set src=mp on the query string to grab details from the media poll rather than the standard OR image
if (isset($_REQUEST['src']))
    $source = $_REQUEST['src'];
else
    $source = 'std';

// remove anything after the @
$splitforrawcode = explode('@', $partcode);

$partcode = $splitforrawcode[0];

// try to get a manually sourced image first
if (file_exists($manual_images_path . $partcode . '.jpg')) {
    /* echo "<pre>";
      var_dump($_SERVER);
      echo "</pre>";
     */
    echo $manual_images_url . $partcode . '.jpg';
    exit();
} else {

    // no manaually sourced, image, go huntiung for Open Range matchup
    $stmt = $db->prepare("SELECT product_id, image, manufacturer FROM epic_or_products WHERE oracle_part_no = :partcode LIMIT 1");
    $stmt->bindValue(':partcode', $partcode, PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);


    if (!empty($rows)) {
        // Open Range knows about our oracle partcode

        if ($source != 'mp') {

            // return a standard OR image
            if (!empty($rows[0]['image'])) {
                ////print_r($rows); die();
                // Oracle knows this product

                echo $rows[0]['image']; // e.g. OR800000029065.jpg
                exit();
            }
        } else {
            // src == mp - attempt to get from media pool
            $stmt = $db->prepare("SELECT id, url FROM epic_or_mediapool WHERE product_id = :id AND media_type='IMG' order by GREATEST(width,height) DESC, display_order ASC LIMIT 1");
            $stmt->bindValue(':id', $rows[0]['product_id'], PDO::PARAM_STR);
            $stmt->execute();
            $media = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($media)) {
                $url = str_replace('http://mediapool.getthespec.com/media.jpg?m=', 'http://product-api.gopagoda.com/get/', urldecode($media[0]['url']));
                echo $url . '.jpg';
                exit();
            }
        } // if source
    } // if empty rows
} // if we have manually sourced image


// nothing matched or found, return default
echo 'product_default.gif';
?>
