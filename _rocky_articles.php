<?php
function call_fix($query = "") {
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://apollo.build.resf.org/v2/advisories/' . $query,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
}





define('_JEXEC', 1);

define('JPATH_BASE', '../');
require_once JPATH_BASE . 'includes/defines.php';
require_once JPATH_BASE . 'includes/framework.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\WebAseet\WebAssetManger;

$container = \Joomla\CMS\Factory::getContainer();
$container->alias('session.web', 'session.web.site')
    ->alias('session', 'session.web.site')
    ->alias('JSession', 'session.web.site')
    ->alias(\Joomla\CMS\Session\Session::class, 'session.web.site')
    ->alias(\Joomla\Session\Session::class, 'session.web.site')
    ->alias(\Joomla\Session\SessionInterface::class, 'session.web.site');

// Instantiate the application.
$app = $container->get(\Joomla\CMS\Application\SiteApplication::class);
// Set the application as global app
\Joomla\CMS\Factory::$application = $app;


//$app = Factory::getApplication('site');

// Instantiate the Joomla application.


//Get data from API RockyLinux IDL

function getRequest($url) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    )); //libraries\src\Http\Transport\cacert.pem
    $joomlaCACertificatesPath = JPATH_SITE . '/libraries/src/Http/Transport/cacert.pem';
    $joomlaCACertificatesPath = JPATH_SITE . '/libraries/vendor/composer/ca-bundle/res/cacert.pem';
    curl_setopt($curl, CURLOPT_CAINFO, $joomlaCACertificatesPath);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);
    $response = curl_exec($curl);

    $error = curl_error($curl);
    //echo $error;
    curl_close($curl);
    if ($response === false) {
        throw new Exception($error);
    }

    return $response;
}

//webhash table update data function

function check_webhash() {
    global $subMsg;
    $newHash = "";
    $url = "https://apollo.build.resf.org/v2/advisories";

    try {
        $response = getRequest($url);
    } catch (Exception $ex) {
        echo ("Error with get request in checkwebhash function, rocky linux website may be down");
    }
    $newHash =  $updatedHash = md5($response);
    $finalDate = strftime("%B %d, %Y");
    $db = Factory::getDbo();
    $db->setQuery("SELECT hash from rockyLinuxWebHash WHERE id = -1");
    $rows = $db->loadObjectList();
    foreach ($rows as $row) {

        $currentHash = $row->hash;
        if ($currentHash != $newHash) {
            $updateNulls = true;

            // Create an object for the record we are going to update.
            $object = new stdClass();
            $object->id = -1;
            $object->hash = $newHash;
            $object->date = $finalDate;

            $object->published_date = date('Y-m-d H:i:s', strtotime($finalDate));
            $object->identifier = "web hash";
            $object->modified = $finalDate;

            $result = $db->updateObject('rockyLinuxWebHash', $object, 'id', $updateNulls);
        }
    }
}

// function to update the Joomla Articles
function checkRocky() {
    global $subMsg;
    $db = Factory::getDbo();
    $url = "https://apollo.build.resf.org/v2/advisories";
    try {
        $response = getRequest($url);
    } catch (Exception $ex) {
        echo ("Error with get request in checkrocky function, rocky linux website may be down");
    }
    $advisData = json_decode($response);
    $advisoryList = $advisData->advisories;
    $fulldate = strftime("%B %d, %Y");
    $placeHolder  = strtotime("now");
    $trueClause = True;
    $falseClause = False;
    $updateCase = True;
    $insertNewCase = True;
    $checker = 0;

    $state = 1;
    $asset_id = 0;
    $catid = 219;
    $access = 1;
    $created_by = 62;
    $created_by_alias = 'LinuxSecurity.com Team';
    $modified = $placeHolder;
    $publish_up = $placeHolder;
    $created = $placeHolder;
    $language = '*';
    $images = [
        "image_intro_alt" => 'RockyLinux Distribution',
        "float_intro" => "",
        "image_intro_caption" => 'RockyLinux Distribution',
        "image_fulltext_caption" => 'RockyLinux Distribution',
        "float_fulltext" => "images/distros-large/rockylinux.png",
        "image_fulltext" => "images/distros-large/rockylinux.png",
        "image_fulltext_alt" => "'RockyLinux Distribution'",
        "image_intro" => "images/distros-large/rockylinux.png"
    ];

    $images = json_encode($images);

    $attribs = [
        "helix_ultimate_image" => "images/distros-large/rockylinux.png"
    ];
    $attribs = json_encode($attribs);
    $db->setQuery("SELECT id, hash, identifier from rockyLinuxWebHash");
    $rows = $db->loadObjectList();
    $rowLength = count($rows);
    foreach ($advisoryList as $advisory) {
        // temp variables that reset after each advisory
        $title = '';
        $newTitle = '';
        $listA = [];
        $listB = [];
        $fullText = '';
        $alias = '';
        $synopsis = '';
        $rockyInsert1 = '';
        $advisTuple1 = '';
        $count = 0;
        $cont = 0;
        // this for loop takes the entire json file and parses it per advisory // EX: full details on advisory will be stored in the variable fulltext. When for loop finishes, 
        // it iterates to the next advisory and the string fulltext is reset back to an empty string until changed again.


        $query = "";
        //fetch  same as above
        foreach ((array)$advisory as $key => $details) {
            if ($key == "name") {
                $query = $details;
                $title = 'Rocky Linux: ' . $details;
                $finalHashedTitle = $hashedTitle =  md5($title);
                $titleTuple = [$finalHashedTitle, $fulldate, $title, $fulldate];
            }
        }
        //	echo "<pre>";
        //	echo "first";
        //	var_dump($advisory->fixes);
        //	echo "first";
        if (count($advisory->fixes) < 1) {
            $ressponse = call_fix($query);
            $fixes = json_decode($ressponse);
            //print_r($fixes);
            $advisory->fixes = $fixes->advisory->fixes;
        }
        //echo "sect";
        //	var_dump($advisory->fixes);
        //	echo "sect";
        $fullText = json_encode($advisory);
        //  if the hash is not the same but matches an already inserted title, meaning it changed then update the hash at that ID and identifier set truecase true to insert into xu5gc content

        foreach ($rows as $row) {

            if ($row->hash != $finalHashedTitle) {

                if ($row->identifier == $title) {


                    try {
                        $updateCase = True;
                        $query = $db->getQuery(true);
                        $query->clear();
                        $fields = array(
                            $db->quoteName('hash') . ' = ' . $db->quote($finalHashedTitle),
                            $db->quoteName('modified') . ' = ' . $db->quote($placeHolder),

                        );
                        $conditions = array(
                            $db->quoteName('identifier') . ' = ' . $db->quote($title)
                        );

                        $query->update($db->quoteName('rockyLinuxWebHash'))
                            ->set($fields)
                            ->where($conditions);

                        $db->setQuery($query);


                        $db->execute();
                    } catch (Exception $ex) {

                        echo ("Error updating rockyLinuxWebHash in check rocky function");
                    }
                } else {
                    $updateCase = False;
                }
            }
        }

        //if hash is not in hash table, it keeps track of hash table length row 0 is id, row 1 is hash, row 2 is title
        foreach ($rows as $row) {
            if ($row->identifier == $title) {
                $cont += 1;
            } elseif ($row->identifier != $title) {
                $count += 1;
            }
        }
        $totalLength = $cont + $count;
        // if the length of the hash table is the same, then insert new hashed title
        if ($totalLength == $rowLength && $cont == 0) {
            $insertNewCase = True;
            try {
                $obj = new stdClass();
                $obj->hash = $finalHashedTitle;
                $obj->date = $fulldate;
                $obj->identifier = $title;
                $obj->modified = $fulldate;

                // Insert the object into the user profile table.
                $result = $db->insertObject('rockyLinuxWebHash', $obj);
            } catch (Exception $ex) {

                echo ("Error inserting new advis into rockyLinuxWebHash in rockycheck function");
            }
        } elseif ($totalLength != $rowLength) {
            $insertNewCase = False;
        }

        foreach ((array)$advisory as $key => $details) {
            if ($updateCase == True) {
                if ($key == "publishedAt") {
                    $publishedDate = $details;
                    try {
                        $query = $db->getQuery(true);
                        $query->clear();

                        $fields = array(
                            $db->quoteName('published_date') . ' = ' . $db->quote($publishedDate)

                        );
                        $conditions = array(
                            $db->quoteName('identifier') . ' = ' . $db->quote($title)
                        );

                        $query->update($db->quoteName('rockyLinuxWebHash'))
                            ->set($fields)
                            ->where($conditions);

                        $db->setQuery($query);


                        $db->execute();
                    } catch (Exception $ex) {

                        echo ("Error inserting date into rockyLinuxWebHash in rockycheck function");
                    }
                }
            }

            $alias = str_replace("--", "-", str_replace(":", "-", str_replace(" ", "-", strtolower($title))));
            if ($key == "synopsis") {
                $synopsis = $details;
                $listA = explode(":", $details);
                if (count($listA) >= 2) {
                    $listB = explode(",", $listA[1]);
                    $newTitle = $title . " " . $listB[0];
                } elseif (count($listA) < 2) {
                    $listB = explode(",", $listA[0]);
                    $newTitle = $title . " " . $listB[0];
                }
            }


            //$alias = str_replace(":", "", str_replace(" ", "-", strtolower($newTitle))); 
        }

        //	echo "<br/>".$alias = str_replace("--","-",str_replace(":", "-", str_replace(" ", "-", strtolower($title))));


        // set articles publish date and created date in article
        $publish_up =  str_replace("T", " ", substr($advisory->publishedAt, 0, 19));

        $created =  str_replace("T", " ", substr($advisory->publishedAt, 0, 19));

        $created1 = explode("T", substr($advisory->publishedAt, 0, 19));

        $created1  = $created1[1];

        $alias = str_replace("rocky-linux", "rocky-linux-" . trim($listB[0]), $alias . '-' . $created1);

              
       // Phrases to remove
$remove = [
    'bug fix',
    'enhancement',
    'bug fix and enhancement update',
    'update',
    'security update'
];
// Remove each phrase
$alias = str_ireplace($remove, '', $alias);

        $alias = str_replace("--", "-", str_replace(":", "-", str_replace(" ", "-", strtolower($alias))));

        $alias = strtolower($alias);
$alias = str_replace([" ", ":"], "-", $alias);
$alias = preg_replace('/-+/', '-', $alias); // replace multiple dashes with one


        $alias = str_replace('.','',$alias);
        $alias = trim($alias,".");

        $newTitle = $newTitle .' Security Advisories Updates';



        // get the article if already exist in Joomla contnt and wabhash failed to get
        $db->setQuery("SELECT * from #__content where catid = 219 &&  `title` = " . $db->quote($newTitle));
        $articles = $db->loadObjectList();
        if (count($articles)) {
            $insertNewCase = false;
            $updateCase = true;
        } else {
            $insertNewCase = true;
            $updateCase = false;
        }

        if (strpos($newTitle, "RLBA") !== false) {
            continue;
        }
        //update already inserted title
        if ($updateCase == true) {
            try {
                $query = $db->getQuery(true);
                $query->clear();
                if (is_numeric($modified)) {
                    $modified = date('Y-m-d H:i:s', $modified);
                    //  die;
                }
                $fields = array(
                    $db->quoteName('title') . ' = ' . $db->quote($newTitle),
                    $db->quoteName('alias') . ' = ' . $db->quote($alias),

                    $db->quoteName('fulltext') . ' = ' . $db->quote($fullText),
                    $db->quoteName('introtext') . ' = ' . $db->quote($synopsis),

                    $db->quoteName('asset_id') . ' = ' . $db->quote($asset_id),
                    $db->quoteName('state') . ' = ' . $db->quote($state),

                    $db->quoteName('catid') . ' = ' . $db->quote($catid),
                    $db->quoteName('created') . ' = ' . $db->quote($created),


                    $db->quoteName('created_by') . ' = ' . $db->quote($created_by),
                    $db->quoteName('created_by_alias') . ' = ' . $db->quote($created_by_alias),

                    $db->quoteName('modified') . ' = ' . $db->quote($modified),
                    $db->quoteName('publish_up') . ' = ' . $db->quote($publish_up),

                    $db->quoteName('language') . ' = ' . $db->quote($language),
                    $db->quoteName('images') . ' = ' . $db->quote($images),

                    $db->quoteName('attribs') . ' = ' . $db->quote($attribs),
                    $db->quoteName('access') . ' = ' . $db->quote($access),

                );
                $conditions = array(
                    $db->quoteName('title') . ' = ' . $db->quote($newTitle)
                );

                $query->update($db->quoteName('#__content'))
                    ->set($fields)
                    ->where($conditions);

                $db->setQuery($query);


                $db->execute();
            } catch (Exception $ex) {
                echo ("Error updating xu5gc content, potentially no updates to current titles or something is broken in rockycheck function");
            }
        } elseif ($insertNewCase == true) {
            try {
                if (is_numeric($modified)) {
                    $modified = date('Y-m-d H:i:s', $modified);
                    //  die;
                }
                $obj = new stdClass();
                $obj->title = $newTitle;
                $obj->alias = $alias;
                $obj->fullText = $fullText;
                $obj->introtext = $synopsis;
                $obj->asset_id = $asset_id;
                $obj->state = $state;
                $obj->catid = $catid;
                $obj->created = $created;

                $obj->created_by = $created_by;
                $obj->created_by_alias = $created_by_alias;
                $obj->modified = $modified;

                $obj->publish_up = $publish_up;

                $obj->language = $language;

                $obj->images = $images;
                $obj->attribs = $attribs;
                $obj->access = $access;


                // Insert the object into the user profile table.
                $result = $db->insertObject('#__content', $obj);

                $sql = "INSERT INTO #__workflow_associations (item_id, stage_id, extension) 
SELECT c.id as item_id, '1', 'com_content.article' FROM #__content AS c 
WHERE c.id = " . $obj->id;
                $query = $db->getQuery(true);
                $query->clear();

                $db->setQuery($sql);


                $db->execute();
            } catch (Exception $ex) {
                //echo "<pre>";
                //prnt
                //var_dump($ex);
                // die("".__LINE__);
                echo ("Error inserting new advisory into xu5gc content in rockycheck function");
            }
        }
    }
    $db = Factory::getDbo();
    $sql ="INSERT INTO xu5gc_workflow_associations (item_id, stage_id, extension) 
    SELECT c.id as item_id, '1', 'com_content.article' FROM xu5gc_content AS c 
    WHERE NOT EXISTS (SELECT wa.item_id FROM xu5gc_workflow_associations AS wa WHERE wa.item_id = c.id);";
$query = $db->getQuery(true);
$query->clear();

$db->setQuery($sql);


$db->execute();


    if ($updateCase == True ||  $insertNewCase == True) {
        return 0;
    } elseif ($updateCase == False || $insertNewCase == False) {
        return 1;
    }
}



check_webhash();
$ret = checkRocky();
if ($ret == 0) {
    echo "Changes made, database updated" . PHP_EOL;
} elseif ($ret == 1) {
    echo ("No changes made" . PHP_EOL);
}
