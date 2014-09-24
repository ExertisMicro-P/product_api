<?php

/*
 * This runs on ma-webproxy-04 and just looks up the Open Range ORxxxxxxxxx.jpg
 * filename given an oracle partcode
 *
 * http://apps2.exertismicro-p.co.uk/product_api/lookuporid.php?p=FS26361F3019L513
 *
 * Will return e.g. OR800000029065.jpg
 * This can then be used to construct the image URL - see getimage.php (which runs publicly on PagodaBox
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


$stmt = $db->prepare("SELECT image, manufacturer FROM epic_or_products WHERE oracle_part_no = :partcode LIMIT 1");
$stmt->bindValue(':partcode', $partcode, PDO::PARAM_STR);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($rows) && !empty($rows[0]['image'])) {
    //print_r($rows); die();
    // Oracle knows this product

   echo  $rows[0]['image']; // e.g. OR800000029065.jpg

} else
    return 'product_default.gif';
?>
