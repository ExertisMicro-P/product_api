<?php
/*
 * Imports the file from Open Range Media Pool
 * See http://mediapool.getthespec.com/mediapool/downloads.aspx
 *
 * File maybe be entiure catalogue, or daily updates
 */


/*
 * STATUS
 * Need to write code which pulls from a file, rather than a URL.
 * Have asked Cliff to implement a file mover
 *
 * To be tested.
 * No testing perofrmed yet.
 * This needs to live on ma-webproxy-04, with lookuporid.php
 *
 * getimage will live on PagodaBox http://product-api.gopagoda.com
 */

$ormediapoolurl = 'http://mediapool.getthespec.com/MediaPoolServices/MediaList.txt?u=ca0fd82e-199c-4b80-92d7-a97132698d47';
$daily = '&t=D';
$complete = '&t=C';


$filename = $ormediapoolurl.$daily;

$in = fopen($filename, 'rb');

if (!empty($in)) {

    $fields = array('product_id','media_type', 'media_id', 'display_order', 'manufacturer_name', 'description', 'filesize', 'width', 'height', 'last_updated', 'url');

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
        exit();
    }


    while($line = fread($in)) {
       // pipe delimited
       $parts = explode('|', $line);

       // check if product already exists in our table
       $stmt = $db->prepare("SELECT product_id FROM epic_or_mediapool WHERE product_id = :id LIMIT 1");
        $stmt->bindValue(':id', $parts[0], PDO::PARAM_STR);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            // insert new record
            $product_id =  array_shift($parts);
            $values = "'".implode ("','", $parts)."'";
            $keys = "'".implode ("','", $fields)."'";

            $sql = "INSERT INTO 'epic_or_mediapool' (".$keys.") VALUES (".$values.")";
            echo $sql."\n";
            //$count = $conn->exec($sql);
        } else {
            // update existing record
            $flds = array_shift($fields); // ignore product_id field
            $vals = $parts;
            $thisproduct_id = array_shift($vals);
            $sets = array();
            foreach($flds as $k=>$f) {
                $sets[] = $f.'="'.$vals[$k].'"';
            }

            $setclause = implode(', ',$sets);

            $sql = "UPDATE 'epic_or_mediapool' SET ".$setclause." WHERE 'product_id'=".$thisproduct_id;
            echo $sql."\n";
            //$count = $conn->exec($sql);
        }

    }

}





?>
