<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

function idlSetSection($sectionsArray, $line) {
  //echo "<pre>sectionsArray : ";print_r($sectionsArray);echo "</pre>";
  //echo "<pre>sectionsArray : ";print_r($line);echo "</pre>";
  //$previous_key = ''; 
  foreach ($sectionsArray as $key => $value) {
    if (strrpos($line, $key) !== false) {
      //echo "line : ".$line;
      //$sectionsArray[$previous_key] = false;
      foreach ($sectionsArray as $k => $v) {
        $sectionsArray[$k] = false;
      }
      $sectionsArray[$key] = true;
      break;
    }/*else{
      $previous_key = $key; 
    }*/
  }
  //echo "<pre>sectionsArray : ";print_r($sectionsArray);echo "</pre>";
  return $sectionsArray;
}
function arrayToParagraphs($data) {
  $index = 0;
  $array2 = array();
  foreach ($data['array'] as $k => $line) {
    $skip_flag = false;
    foreach ($data['skip'] as $skip) {
      if (strrpos($line, $skip) !== false) {
        $skip_flag = true;
      }
    }
    if ($skip_flag === true || ($k == 0 && ctype_space($line))) {
      continue;
    }
    if (ctype_space($line)) {
      if ($index > 0) {
        $array2[$index] .= "<br/>";
      }
      $index++;
      continue;
    }

    if ($array2[$index] != '') {
      //$array2[$index] .= " ";
    }
    $array2[$index] .= $line;
  }
  return $array2;
}

//$articleId=$_REQUEST['id'];
$app = Factory::getApplication();
$articleId = $app->input->getInt('id');

$article = JTable::getInstance('Content', 'JTable');
$article_return = $article->load($articleId);
$date_c = $article->created;

$introtextn = $article->introtext;
//truncate introtext
$needle = '. ';
$introtext = strstr($introtextn, $needle, true);
// oracle intro issue fix
if (trim($introtext) == "" && $article->introtext != "") {
  $introtext = $article->introtext;
}
$introtext = (strlen($introtext) > 180) ? substr($introtext, 0, 180) . '...' : $introtext;
$title = $article->title;
$itemid = $article->id;
$catitemid = $article->catid;
//print_r($article->created);
$finalArr = array();

$article->fulltext = str_replace("<title>","",$article->fulltext) ;
$article->fulltext = str_replace("<textarea>","",$article->fulltext) ;
$article->fulltext  = str_replace("<head>","",$article->fulltext ) ;
$article->fulltext  = str_replace("</head>","",$article->fulltext ) ;
$article->fulltext  = str_replace("<body>","",$article->fulltext ) ;
$article->fulltext  = str_replace("</body>","",$article->fulltext ) ;
$article->fulltext  = str_replace("-->","",$article->fulltext ) ;
$article->fulltext  = str_replace("<!--","",$article->fulltext ) ;
$article->fulltext  = str_replace("<html>","",$article->fulltext ) ;
$article->fulltext  = str_replace("</html>","",$article->fulltext ) ;
$article->fulltext  = str_replace("<canvas>","",$article->fulltext ) ;
$article->fulltext  = str_replace("</canvas>","",$article->fulltext ) ;


$article->fulltext  = str_replace('<body bgcolor="#FFFFFF" text="#000000">',"",$article->fulltext ) ;


$article->fulltext  = str_replace("<plaintext>","",$article->fulltext ) ;
$article->fulltext  = str_replace("<iframe>","iframe",$article->fulltext ) ;
$article->fulltext  = str_replace(' <meta name="referrer"> ',"meta name = refer",$article->fulltext ) ;
$article->fulltext  = str_replace("<select>","select",$article->fulltext ) ;
$article->fulltext  = str_replace("<template>","template",$article->fulltext ) ;
$article->fulltext  = str_replace("<iframe src>","iframe src",$article->fulltext ) ;



$arr = explode("\n", $article->fulltext);
$fallback = false;
//echo "<pre>";print_r($arr);echo "</pre>";
//echo $catitemid;
//die;
//RockyLinux
/*
if($catitemid==219){ 
  $sectionsArray = array(
    "Synopsis" => false, 
    "Background" => false, 
    "Affected packages" => false, 
    "Description" => false, 
    "Impact" => false, 
    "Workaround" => false, 
    "Resolution" => false, 
    "References" => false, 
    "Availability" => false, 
    "Concerns" => false, 
    "License" => false, 
  );
}
*/

//Gentoo
if ($catitemid == 91) {
  $sectionsArray = array(
    "Synopsis" => false,
    "Background" => false,
    "Affected packages" => false,
    "Description" => false,
    "Impact" => false,
    "Workaround" => false,
    "Resolution" => false,
    "References" => false,
    "Availability" => false,
    "Concerns" => false,
    "License" => false,
  );
}
//Oracle
if ($catitemid == 217) {
  $sectionsArray = array(
    "i386:" => false,
    "x86_64:" => false,
    "SRPMS:" => false,
    "aarch64:" => false,
    "Description of changes:" => false,
    "Related CVEs:" => false,
  );
}

//Mageia
if ($catitemid == 203) {
  $sectionsArray = array(
    "Synopsis" => false,
    "Publication date:" => false,
    "URL:" => false,
    "Type:" => false,
    "Affected Mageia releases:" => false,
    "CVE:" => false,
    "References:" => false,
    "SRPMS:" => false,
  );
}

//RedHat
if ($catitemid == 98) {
  $sectionsArray = array(
    "Synopsis:" => false,
    "Advisory ID:" => false,
    "Product:" => false,
    "Advisory URL:" => false,
    "Issue date:" => false,
    "CVE Names:" => false,
    "Summary:" => false,
    "Relevant releases/architectures:" => false,
    "Problem description:" => false,
    "Description:" => false,
    "Topic:" => false,
    "Solution:" => false,
    "Bugs fixed" => false,
    "Package List:" => false,
    "References:" => false,
    "Contact:" => false,
  );
}

//ScientificLinux
if ($catitemid == 200) {
  $sectionsArray = array(
    "Synopsis:" => false,
    "Advisory ID:" => false,
    "Issue Date:" => false,
    "CVE Numbers:" => false,
    "Security Fix(es):" => false,
    "Bug Fix(es):" => false,
  );
}

//Slackware
if ($catitemid == 99) {
  $sectionsArray = array(
    "Here are the details from the Slackware" => false,
    "Where to find the new packages:" => false,
    "MD5 signatures:" => false,
    "Installation instructions:" => false,
  );
}
// SUSE
if ($catitemid == 100) {
  $sectionsArray = array(
    "Container Advisory ID :" => false,
    "Container Tags        :" => false,
    "Container Release     :" => false,
    "Severity              :" => false,
    "Type                  :" => false,
    "References            :" => false,
  );
  $separator = 0;
}

//OpenSUSE
if ($catitemid == 202) {
  $sectionsArray = array(
    "Announcement ID:" => false,
    "Rating:" => false,
    "References:         " => false,
    "Cross-References:" => false,
    "CVSS scores:" => false,
    "Affected Products:" => false,
    "Description:" => false,
    "Patch Instructions:" => false,
    "Package List:" => false,
    "References:" => false,

  );
}
//print_r($arr); die();  // Jacob
// Ubuntu
if ($catitemid == 172) {
}
$space_index = 0;
//$finalArr['Resolution']='<pre>';

if ($catitemid == 202) {
  $desp_found_202 = false;
}

foreach ($arr as $key => $line) {
  //$line=trim($line);


  //openSUSE
  if ($catitemid == 202) {
    /*
    if($key==1){
      $finalArr['title']=$line;
    }
    if($key==2 && strpos($line, '_____') !== false){
      $flag=true;
      $flag2=false;
      $description=false;
      $patch=false;
      $list=false;
      $references=false;
    }

    if($key>2 && strpos($line, '_____') !== false){
      $flag=false;
      $flag2=true;
      $description=false;
      $patch=false;
      $list=false;
      $references=false;
    }
    if($key>2 && strpos($line, 'Description:') !== false){
      $flag=false;
      $flag2=false;
      $description=true;
      $patch=false;
      $list=false;
      $references=false;
    }
    if($key>2 && strpos($line, 'Patch Instructions:') !== false){
      $flag=false;
      $flag2=false;
      $description=false;
      $patch=true;
      $list=false;
      $references=false;
    }
    if($key>2 && strpos($line, 'Package List:') !== false){
      $flag=false;
      $flag2=false;
      $description=false;
      $patch=false;
      $list=true;
      $references=false;
    }
    if($key>10 && strpos($line, 'References:') !== false){
      $flag=false;
      $flag2=false;
      $description=false;
      $patch=false;
      $list=false;
      $references=true;
    }
    if($flag){
      $finalArr['block1'][$key]=$line;
    }
    if($flag2){
      $finalArr['block2'][$key].=$line."\n";
    }
    if($description){
      $finalArr['description'][$key].=$line."\n";
    }
    if($patch){
      $finalArr['patch'][$key].=$line."\n";
    }
    if($list){
      $finalArr['list'][$key].=$line."\n";
    }
    if($references){
      $finalArr['references'][$key].=$line."\n";
    }
    */

    if (strpos($line, "_____________________________") !== false) {
      continue;
    }
    if ($line == "") {
      $block_index++;
      continue;
    }

    $sectionsArray = idlSetSection($sectionsArray, $line);

    if ($sectionsArray['Announcement ID:'] == true) {
      $finalArr['announcement_id'][] = $line;
      $finalArr['advisory_info'][] = $line;
    } else if ($sectionsArray['Rating:'] == true) {
      $finalArr['rating'][] = $line;
      $finalArr['advisory_info'][] = $line;
    } else if ($sectionsArray['References:         '] == true) {
      $finalArr['references1'][0] .= $line . "\n";
      $finalArr['advisory_info'][] = $line;
    } else if ($sectionsArray['Cross-References:'] == true) {
      $finalArr['cross_references'][0] .= $line . "\n";
      $finalArr['advisory_info'][] = $line;
    } else if ($sectionsArray['CVSS scores:'] == true) {
      $finalArr['cvss_scores'][0] .= $line . "\n";
      $finalArr['advisory_info'][] = $line;
    } else if ($sectionsArray['Affected Products:'] == true) {
      $finalArr['affected_products'][0] .= $line . "\n";
      if (strpos($line, "_____________________________") !== false) {
        continue;
      }
      $finalArr['affected_products'][] .= $line . "\n";
      $finalArr['advisory_info'][] = $line;
    } else if ($sectionsArray['Description:'] == true) {
      $finalArr['description'][] .= $line . "\n";
      $desp_found_202 = true;
    } else if ($sectionsArray['Patch Instructions:'] == true) {
      $finalArr['patch'][] .= $line . "\n";
    } else if ($sectionsArray['Package List:'] == true) {
      $finalArr['list'][] .= $line . "\n";
    } else if ($sectionsArray['References:'] == true) {
      $finalArr['references'][] = $line;
    } else {
      if ($finalArr['block'][$block_index] != '') {
        $finalArr['block'][$block_index] .= " " . $line;
      } else {
        $finalArr['block'][$block_index] .= $line;
      }
    }
  }

  //archlinux  

  if ($catitemid == 198) {
    if ($key == 0) {
      $finalArr['title'] = $line;
    }
    if ($key == 1 && strpos($line, '======') !== false) {
      $flag = true;
      $flag2 = false;
      $description = false;
      $patch = false;
      $list = false;
      $references = false;
      $impact = false;
    }
    if ($key > 2 && strpos($line, 'Summary') !== false) {
      $flag = false;
      $flag2 = false;
      $summary = true;
      $description = false;
      $patch = false;
      $list = false;
      $references = false;
      $impact = false;
    }
    if ($key > 4 && strpos($line, 'Resolution') !== false) {
      $flag = false;
      $flag2 = false;
      $summary = false;
      $description = false;
      $patch = true;
      $list = false;
      $references = false;
      $impact = false;
    }
    if ($key > 6 && strpos($line, 'Workaround') !== false) {
      $flag = false;
      $flag2 = false;
      $summary = false;
      $description = false;
      $patch = false;
      $list = true;
      $references = false;
      $impact = false;
    }
    if ($key > 8 && strpos($line, 'Description') !== false) {
      $flag = false;
      $flag2 = false;
      $summary = false;
      $description = true;
      $patch = false;
      $list = false;
      $references = false;
      $impact = false;
    }
    if ($key > 6 && strpos($line, 'Impact') !== false) {
      $flag = false;
      $flag2 = false;
      $summary = false;
      $description = false;
      $patch = false;
      $list = false;
      $references = false;
      $impact = true;
    }
    if ($key > 12 && strpos($line, 'References') !== false) {
      $flag = false;
      $flag2 = false;
      $summary = false;
      $description = false;
      $patch = false;
      $list = false;
      $references = true;
      $impact = false;
    }
    if ($flag) {
      $finalArr['block1'][$key] = $line;
    }
    if ($summary) {
      $finalArr['summary'][$key] = $line;
    }
    if ($patch) {
      $finalArr['resolution'][$key] .= $line . "\n";
    }
    if ($list) {
      $finalArr['workaround'][$key] = $line;
    }
    if ($description) {
      $finalArr['description'][$key] .= $line . "\n";
    }
    if ($references) {
      $finalArr['references'][$key] .= $line . "\n";
    }
    if ($impact) {
      $finalArr['impact'][$key] = $line;
    }
  }

  //CentOS
  if ($catitemid == 199) {
    if ($line == "") {
      //continue;
    }
    if ($key >= 5 && strpos($line, "The following updated files") !== false) {
      $updated_files = true;
      $source = false;
      $project = false;
      $twitter = false;
      $announce_mailing_list = false;
    }
    if (strpos($line, "Source:") !== false) {
      $updated_files = false;
      $source = true;
      $project = false;
      $twitter = false;
    }
    if (strpos($line, "--") !== false) {
      $updated_files = false;
      $source = false;
      $project = true;
      $twitter = false;
      $announce_mailing_list = false;
      continue;
    }
    if (strpos($line, "Twitter:") !== false) {
      $updated_files = false;
      $source = false;
      $project = false;
      $twitter = true;
      $announce_mailing_list = false;
    }
    if (strpos($line, "______________") !== false) {
      $updated_files = false;
      $source = false;
      $project = false;
      $twitter = false;
      $announce_mailing_list = true;
      continue;
    }

    if ($key == 1) {
      $finalArr['severity'] = $line;
    }
    if ($key == 3) {
      $finalArr['upstream_details'] = $line;
    }
    if ($key >= 5 && $updated_files == true) {
      $finalArr['updated_files'][] .= $line . "\n";
    }
    if ($source == true) {
      if ($line == "") {
        continue;
      }
      $finalArr['source'][] .= $line . "\n";
    }
    if ($project == true) {
      /*$line = str_replace("{", "[", $line);
      $line = str_replace("}", "]", $line);*/
      $finalArr['project'][] = $line;
    }
    if ($twitter == true) {
      $finalArr['twitter'][] = $line;
    }
    if ($announce_mailing_list == true) {
      $finalArr['announce_mailing_list'][] = $line;
    }
  }

  //DebianLTS
  if ($catitemid == 197) {
	$line = trim($line,"\n");
    $line = trim($line,"\r");
    
    if (strpos($line, "- --------------------------") !== false) {
      continue;
    }
    if ($line == "") {
      $block_index++;
      //continue;
      $space_index++;
    }
    if (strpos($line, "Package        :") !== false) {
      $finalArr['package'] = $line;
      $finalArr['advisory_info'][] = $line;
    } else if (strpos($line, "Version        :") !== false) {
      $finalArr['version'] = $line;
      $finalArr['advisory_info'][] = $line;
    } else if (strpos($line, "CVE ID         :") !== false) {
      $finalArr['cve_id'] = $line;
      $finalArr['advisory_info'][] = $line;
    } else if (strpos($line, "Debian Bug     :") !== false) {
      $finalArr['debian_bug'] = $line;
      $finalArr['advisory_info'][] = $line;
      $is_description = true;
    } else if ($is_description === true) {
      $finalArr['description'][] .= $line . "\n";
    } else {
      if ($finalArr['block'][$block_index] != '') {
        $finalArr['block'][$block_index] .= " " . $line . "\n";
      } else {
        $finalArr['block'][$block_index] .= $line . "\n";
      }
    }


    //cve fix
    if ($space_index >= 2 && $space_index < 3) {
      //$finalArr['advisory_info'][] = $line; // Jacob disabled
    }


    //desc fix	
    if ($space_index >= 3) {
      $finalArr['original'][] = $line;
    }
  }

  //Debian 
  if ($catitemid == 87) {
    $line = str_replace('Mailing list: debian-security-announce@lists.debian.org','',$line);
    if (strpos($line, "- --------------------------") !== false) {
      continue;
    }
    if (strpos($line, "---------------------------------------------------------------------------------") !== false) {
      break;
    }

    if (trim($line) == "") {
      //$block_index++;
      //continue;
      $space_index++;
    }
    if (strpos($line, "Package        :") !== false  || (  strpos($line,"Package") !== false && strpos($line," :") !== false )) {
      $finalArr['package'] = $line;
      //$finalArr['advisory_info'][] = $line;
    } else if (strpos($line, "CVE ID         :") !== false || (  strpos($line,"CVE ID") !== false && strpos($line," :") !== false ) ) {
      $finalArr['cve_id'] = $line;
      // $finalArr['advisory_info'][] = $line;

    } else if (strpos($line, "Debian Bug     :") !== false) {
      $finalArr['debian_bug'] = $line;
      // $finalArr['advisory_info'][] = $line;
      $is_description = true;
    } else if ($is_description === true) {
      $finalArr['description'][] .= $line . "\n";
    } else {
      if ($finalArr['block'][$block_index] != '') {
        $finalArr['block'][$block_index] .= " " . $line . "\n";
      } else {
        //$finalArr['block'][$block_index] .= $line;

      }
    }
    //cve fix
    if ($space_index >= 2 && $space_index < 3) {
      if(strpos($line, "Package        :") !== false || strpos($line, "CVE ID         :") !== false){
        $finalArr['advisory_info'][] = $line;
      }
      
    }
    if((!isset($finalArr['advisory_info']) || count(array_filter($finalArr['advisory_info'])) == 0) && isset($finalArr['package']) && isset($finalArr['cve_id'])){
      $finalArr['advisory_info'][0] = "";
      $finalArr['advisory_info'][1] = $finalArr['package'];
      $finalArr['advisory_info'][2] = $finalArr['cve_id'];
    }
    //desc fix	
    if ($space_index >= 3) {
      $finalArr['original'][] = $line;
    }
  }
  //Fedora
  if ($catitemid == 89) {
    if (strpos($line, "-------------------------------------") !== false || strpos($line, "_________________________") !== false) {
      continue;
    }
    if ($line == "") {
      $block_index++;
      //continue;
    }
    if (strpos($line, "Name        :") !== false) {
      $finalArr['name'] = $line;
      $finalArr['advisory_info'][] = $line;
    } else if (strpos($line, "Product     :") !== false) {
      $finalArr['product'] = $line;
      $finalArr['advisory_info'][] = $line;
    } else if (strpos($line, "Version     :") !== false) {
      $finalArr['version'] = $line;
      $finalArr['advisory_info'][] = $line;
    } else if (strpos($line, "Release     :") !== false) {
      $finalArr['release'] = $line;
      $finalArr['advisory_info'][] = $line;
    } else if (strpos($line, "URL         :") !== false) {
      $finalArr['url'] = $line;
      $finalArr['advisory_info'][] = $line;
    } else if (strpos($line, "Summary     :") !== false) {
      $finalArr['summary'] = $line;
      $finalArr['advisory_info'][] = $line;
    } else if (strpos($line, "Description :") !== false) {
      //  $finalArr['Description'][] = $line."\n";
      $Desc = 1;
    } else if ($Desc && strpos($line, "Update Information:") === false) {
      $finalArr['Description'][] = $line . "\n";
    } else if (strpos($line, "Update Information:") !== false) {
      $Desc = 0;
      $update_information = true;
      $ChangeLog = false;
      $References = false;
      $update_can_install = false;
      $all_packages_signed = false;
      $mailing_list = false;
    } else if (strpos($line, "ChangeLog:") !== false) {
      $update_information = false;
      $ChangeLog = true;
      $References = false;
      $update_can_install = false;
      $all_packages_signed = false;
      $mailing_list = false;
    } else if (strpos($line, "References:") !== false) {
      $update_information = false;
      $ChangeLog = false;
      $References = true;
      $update_can_install = false;
      $all_packages_signed = false;
      $mailing_list = false;
    } else if (strpos($line, "This update can be installed") !== false) {
      $update_information = false;
      $ChangeLog = false;
      $References = false;
      $update_can_install = true;
      $all_packages_signed = false;
      $mailing_list = false;
    } else if (strpos($line, "All packages are signed") !== false) {
      $update_information = false;
      $ChangeLog = false;
      $References = false;
      $update_can_install = false;
      $all_packages_signed = true;
      $mailing_list = false;
    } else if (strpos($line, "package-announce mailing list") !== false) {
      $update_information = false;
      $ChangeLog = false;
      $References = false;
      $update_can_install = false;
      $all_packages_signed = false;
      $mailing_list = true;
    }

    if ($update_information == true) {
      $finalArr['update_information'][] .= $line . "\n";
    } else if ($ChangeLog == true) {
      $finalArr['ChangeLog'][] .= $line . "\n";
    } else if ($References == true) {
      $finalArr['References'][] .= $line . "\n";
    } else if ($update_can_install == true) {
      $finalArr['update_can_install'][] .= $line . "\n";
    } else if ($all_packages_signed == true) {
      $finalArr['all_packages_signed'][] = $line;
    } else if ($mailing_list == true) {
      $finalArr['mailing_list'][] = $line;
    } else {
      if ($finalArr['block'][$block_index] != '') {
        $finalArr['block'][$block_index] .= " " . $line . "\n";
      } else {
        $finalArr['block'][$block_index] .= $line . "\n";
      }
    }
  }

  //Gentoo
  //fallback check
  $datefallback = "2010-01-01";
  if ($date_c > $datefallback) {
    $fallback = false;
  } else {
    $fallback = true;;
  }

  if ($catitemid == 91) {
    if (!$fallback) {
      if (strpos($line, "- - - - - - - - - - - - - -") !== false || strpos($line, "======") !== false) {
        continue;
      }
      if ($line == "") {
        $block_index++;
        //continue;
      }
      if (strpos($line, "Severity:") !== false) {
        $finalArr['serverity'] = $line;
        $finalArr['advisory_info'][] = $line;
      } else if (strpos($line, "Title:") !== false) {
        $finalArr['title'] = $line;
        $finalArr['advisory_info'][] = $line;
      } else if (strpos($line, "Date:") !== false) {
        $finalArr['date'] = $line;
        $finalArr['advisory_info'][] = $line;
      } else if (strpos($line, "Bugs:") !== false) {
        $finalArr['bugs'] = $line;
        $finalArr['advisory_info'][] = $line;
      } else if (strpos($line, "ID:") !== false) {
        $finalArr['id'] = $line;
        $finalArr['advisory_info'][] = $line;
      } else {
        $sectionsArray = idlSetSection($sectionsArray, $line);
      }

      if ($sectionsArray['Synopsis'] == true) {
        $finalArr['Synopsis'][] .= $line . "\n";
      } else if ($sectionsArray['Background'] == true) {
        $finalArr['Background'][] .= $line . "\n";
      } else if ($sectionsArray['Affected packages'] == true) {
        $finalArr['Affected_packages'][] .= $line . "\n";
      } else if ($sectionsArray['Description'] == true) {
        $finalArr['Description'][] .= $line . "\n";
      } else if ($sectionsArray['Impact'] == true) {
        $finalArr['Impact'][] .= $line . "\n";
      } else if ($sectionsArray['Workaround'] == true) {
        $finalArr['Workaround'][] .= $line . "\n";
      } else if ($sectionsArray['Resolution'] == true) {
        $finalArr['Resolution'][] .= $line . "\n";
      } else if ($sectionsArray['References'] == true) {
        $finalArr['References'][] .= $line . "\n";
      } else if ($sectionsArray['Availability'] == true) {
        $finalArr['Availability'][] .= $line . "\n";
      } else if ($sectionsArray['Concerns'] == true) {
        $finalArr['Concerns'][] .= $line . "\n";
      } else if ($sectionsArray['License'] == true) {
        $finalArr['License'][] .= $line . "\n";
      } else {
        if ($finalArr['block'][$block_index] != '') {
          $finalArr['block'][$block_index] .= " " . $line;
        } else {
          $finalArr['block'][$block_index] .= $line;
        }
      }
    } else {
      //fallback

      if (strpos($line, "- - - ") !== false || strpos($line, "- - - ----") !== false) {
        continue;
      }
      if ($line == "") {
        $block_index++;
        //continue;
      }
      if (strpos($line, "PACKAGE :") !== false) {
        $finalArr['serverity'] = $line;
        $finalArr['advisory_info'][] = $line;
      } else if (strpos($line, "SUMMARY :") !== false) {
        $finalArr['title'] = $line;
        $finalArr['advisory_info'][] = $line;
      } else if (strpos($line, "DATE :") !== false) {
        $finalArr['date'] = $line;
        $finalArr['advisory_info'][] = $line;
      } else if (strpos($line, "EXPLOIT :") !== false) {
        $finalArr['bugs'] = $line;
        $finalArr['advisory_info'][] = $line;
      } else if (strpos($line, "VERSIONS AFFECTED :") !== false) {
        $finalArr['id'] = $line;
        $finalArr['advisory_info'][] = $line;
      } else if (strpos($line, "CVE :") !== false) {
        $finalArr['id'] = $line;
        $finalArr['advisory_info'][] = $line;
      } else {
        $finalArr['Description'][] .= $line . "\n";
        //$sectionsArray = idlSetSection($sectionsArray,$line);
      }
    }
  }
  //Mageia
  if ($catitemid == 203) {
    if ($line == "") {
      $block_index++;
      //continue;
    }
    
    $sectionsArray = idlSetSection($sectionsArray, $line);
    


    if ($sectionsArray['Publication date:'] == true) {
      $finalArr['publication_date'][] = $line;
      $finalArr['advisory_info'][] = $line;
    } else if ($sectionsArray['URL:'] == true) {
      $finalArr['url'][] = $line;
      $finalArr['advisory_info'][] = $line;
    } else if ($sectionsArray['Type:'] == true) {
      $finalArr['type'][] = $line;
      $finalArr['advisory_info'][] = $line;
    } else if ($sectionsArray['Affected Mageia releases:'] == true) {
      $finalArr['affected_mageia_releases'][] .= $line . "\n";
      $finalArr['advisory_info'][] = $line;
    } else if ($sectionsArray['CVE:'] == true) {
      $finalArr['CVE'][] .= $line . "\n";
      $finalArr['advisory_info'][] = $line;
      $finalArr['description'][] .= $line . "\n";
      /* }else if(strpos($line, "CVE-")!==false){
         $finalArr['CVE'][] .= $line."\n";
         $finalArr['advisory_info'][] = $line;*/ // Jacob disabled
    } else if ($sectionsArray['References:'] == true) {
      $finalArr['references'][] = $line;
      // $finalArr['advisory_info'][] = $line;
      $Ref = 1;
    } else if ($Ref && strpos($line, "SRPMS:") === false) {
      $finalArr['references'][] = $line;
    } else if ($sectionsArray['SRPMS:'] == true) {
      $Ref = 0;
      $finalArr['SRPMS'][] = $line;
      $finalArr['advisory_info'][] = $line;
    } else {
      if ($finalArr['block'][$block_index] != '') {
        $finalArr['block'][$block_index] .= " " . $line;
      } else {
        $finalArr['block'][$block_index] .= $line;
      }
    }
  }


  //Oracle
  if ($catitemid == 217) {
    if ($line == "") {
      $block_index++;
      $tmp_flag = true;
      //continue;
    }

    if (strpos($line, "________") !== false) {
      break;
    }

    $sectionsArray = idlSetSection($sectionsArray, $line);

    if ($sectionsArray['i386:'] == true) {
      $finalArr['i386'][] .= $line . "\n";
      //$finalArr['advisory_info'][] = $line;
    } else if ($sectionsArray['SRPMS:'] == true) {
      $finalArr['SRPMS'][] .= $line . "\n";
      //$finalArr['advisory_info'][] = $line;

    } else if ($sectionsArray['Related CVEs:'] == true) {
      $finalArr['related_cves'][0] .= $line . "\n";
      $finalArr['advisory_info'][] .= $line . "\n";
    } else if ($sectionsArray['x86_64:'] == true) {
      $finalArr['x86_64'][] .= $line . "\n";
      //$finalArr['advisory_info'][] = $line;
    } else if ($sectionsArray['aarch64:'] == true) {
      $finalArr['aarch64'][] .= $line . "\n";

      //$finalArr['advisory_info'][] = $line;
    } else if ($sectionsArray['Description of changes:'] == true) {
      $finalArr['advisory_info'][] = $line;
      $finalArr['description'][] .= $line . "\n";
    } else {
      if ($finalArr['block'][$block_index] != '') {
        $finalArr['block'][$block_index] .= " " . $line;
      } else {
        $finalArr['block'][$block_index] .= $line;
      }
    }
  }

  //RedHat
  if ($catitemid == 98) {
    if (strpos($line, "=====================") !== false || strpos($line, "-----BEGIN PGP SIGNED MESSAGE-----") !== false || strpos($line, "Hash: SHA256") !== false || strpos($line, "---------------------------------------------------------------------") !== false) {


      // if(strpos($line, "=====================")!==false || strpos($line, "-----BEGIN PGP SIGNED MESSAGE-----")!==false || strpos($line, "Hash: SHA256")!==false){
      continue;
    }
    if ($line == "") {
      $block_index++;
      // continue;
    }

    $sectionsArray = idlSetSection($sectionsArray, $line);

    if ($sectionsArray['Synopsis:'] == true) {
      $finalArr['synopsis'][] = $line;
      //$finalArr['advisory_info'][] = $line;
    } else if ($sectionsArray['Advisory ID:'] == true) {
      $finalArr['advisory_id'][] = $line;
      $finalArr['advisory_info'][] = $line;
    } else if ($sectionsArray['Product:'] == true) {
      $finalArr['product'][] = $line;
      $finalArr['advisory_info'][] = $line;
    } else if ($sectionsArray['Advisory URL:'] == true) {
      $finalArr['advisory_url'][] = $line;
      $finalArr['advisory_info'][] = $line;
    } else if ($sectionsArray['Issue date:'] == true) {
      $finalArr['issue_date'][] = $line;
      $finalArr['advisory_info'][] = $line;
    } else if ($sectionsArray['CVE Names:'] == true) {
      $finalArr['cve_names'][0]  .= $line . "\n";
      //$finalArr['advisory_info'][] = $line;
    } else if ($sectionsArray['Summary:'] == true) {
      $finalArr['summary'][] = $line;
    } else if ($sectionsArray['Relevant releases/architectures:'] == true) {
      $finalArr['relevant_releases_architectures'][] = $line;
    } else if ($sectionsArray['Description:'] == true) {
      $finalArr['description'][] .= $line . "\n";
    } else if ($sectionsArray['Problem description:'] == true) {
      $finalArr['Problem description'][] = $line;
    } else if ($sectionsArray['Topic:'] == true) {
      $finalArr['topic'][] = $line;
    } else if ($sectionsArray['Solution:'] == true) {
      //  $finalArr['solution'][] = $line;
      $finalArr['solution'][] = $line . "\n";
    } else if ($sectionsArray['Bugs fixed'] == true) {
        if (!isset($finalArr['bugs_fixed'])) {
            $finalArr['bugs_fixed'] = [];
        }
        $existing_text = implode('', $finalArr['bugs_fixed']);
        $current_text  = $existing_text . $line . "\n";
        
        // Count characters excluding spaces
        $char_count = strlen(str_replace(' ', '', $current_text));
    
        if ($char_count <= 1300) {
            $finalArr['bugs_fixed'][] .= $line . "\n";
        }else{
            if (!$read_more_added_bug) {
                if($finalArr['bugs_fixed'][0] && strpos($finalArr['advisory_url'][0], 'Advisory URL:') !== false){
                    $url = trim(str_replace('Advisory URL:', '', $finalArr['advisory_url'][0]));
                    $finalArr['bugs_fixed'][] .= "\n";
                    $finalArr['bugs_fixed'][] .= "\n";
                    $finalArr['bugs_fixed'][] .= '<a class="btn btn-primary button subbutton" href="'.$url.'" target="_blank">Read the Full Advisory</a>';
                    $read_more_added_bug = 1;
                }
            }
        }
      // $finalArr['bugs_fixed'][] = $line;
    } else if ($sectionsArray['Package List:'] == true) {
        if (!isset($finalArr['package_list'])) {
            $finalArr['package_list'] = [];
        }
        $existing_text = implode('', $finalArr['package_list']);
        $current_text  = $existing_text . $line . "\n";
        
        // Count characters excluding spaces
        $char_count = strlen(str_replace(' ', '', $current_text));
    
        if ($char_count <= 1300) {
            $finalArr['package_list'][] .= $line . "\n";
        }else{
            if (!$read_more_added) {
                if($finalArr['advisory_url'][0] && strpos($finalArr['advisory_url'][0], 'Advisory URL:') !== false){
                    $url = trim(str_replace('Advisory URL:', '', $finalArr['advisory_url'][0]));
                    $finalArr['package_list'][] .= "\n";
                    $finalArr['package_list'][] .= "\n";
                    $finalArr['package_list'][] .= '<a class="btn btn-primary button subbutton" href="'.$url.'" target="_blank">Read the Full Advisory</a>';
                    $read_more_added = 1;
                }
            }
        }
      // $finalArr['package_list'][] .= $line . "\n";
    } else if ($sectionsArray['References:'] == true) {
      if (!isset($finalArr['references'])) {
            $finalArr['references'] = [];
        }
        $existing_text = implode('', $finalArr['references']);
        $current_text  = $existing_text . $line . "\n";
        
        // Count characters excluding spaces
        $char_count = strlen(str_replace(' ', '', $current_text));
    
        if ($char_count <= 1300) {
            $finalArr['references'][] .= $line . "\n";
        }else{
            if (!$read_more_reference) {
                if($finalArr['advisory_url'][0] && strpos($finalArr['advisory_url'][0], 'Advisory URL:') !== false){
                    $url = trim(str_replace('Advisory URL:', '', $finalArr['advisory_url'][0]));
                    $finalArr['references'][] .= "\n";
                    $finalArr['references'][] .= "\n";
                    $finalArr['references'][] .= '<a class="btn btn-primary button subbutton" href="'.$url.'" target="_blank">Read the Full Advisory</a>';
                    $read_more_reference = 1;
                }
            }
        }
      // $finalArr['references'][] .= $line . "\n";
    } else if ($sectionsArray['Contact:'] == true) {
      $finalArr['contact'][] = $line;
    } else {
      if ($finalArr['block'][$block_index] != '') {
        $finalArr['block'][$block_index] .= " " . $line;
      } else {
        $finalArr['block'][$block_index] .= $line;
      }
    }
  }

  //ScientificLinux
  $sdatefallback = "2010-01-01";
  if ($date_c > $sdatefallback) {
    $sfallback = false;
  } else {
    $sfallback = true;;
  }

  if ($catitemid == 200) {

    if (!$sfallback) {
      if (strpos($line, "--") !== false) {
        continue;
      }
      if ($line == "") {
        $block_index++;
        //continue;
      }
      $sectionsArray = idlSetSection($sectionsArray, $line);


      if ($sectionsArray['Synopsis:'] == true) {
        $finalArr['synopsis'][] .= $line . "\n";
        $finalArr['advisory_info'][] = $line;
      } else if ($sectionsArray['Advisory ID:'] == true) {
        $finalArr['advisory_id'][] = $line;
        $finalArr['advisory_info'][] = $line;
      } else if ($sectionsArray['Issue Date:'] == true) {
        $finalArr['issue_date'][] = $line;
        $finalArr['advisory_info'][] = $line;
      } else if ($sectionsArray['CVE Numbers:'] == true) {
        $finalArr['cve_numbers'][] = $line;
        $finalArr['advisory_info'][] = $line;
      } else if ($sectionsArray['Security Fix(es):'] == true) {
        $finalArr['security_fixes'][] .= $line . "\n";
      } else if ($sectionsArray['Bug Fix(es):'] == true) {
        $finalArr['bug_fixes'][] = $line;
      } else {
        if ($finalArr['block'][$block_index] != '') {
          $finalArr['block'][$block_index] .= " " . $line;
        } else {
          $finalArr['block'][$block_index] .= $line;
        }
      }
    } else {

      //fallback
      // $finalArr['description'] = explode("\n", $article->fulltext);
      if (strpos($line, "- - - ") !== false || strpos($line, "- - - ----") !== false) {
        continue;
      }
      if ($line == "") {
        $block_index++;
        //continue;
      }

      $finalArr['description'][] .= $line;
      //$sectionsArray = idlSetSection($sectionsArray,$line);
    }
  }




  //Slackware
  if ($catitemid == 99) {
    if (strpos($line, "+---------") !== false || strpos($line, "+-----+") !== false || strpos($line, "-----BEGIN PGP SIGNED MESSAGE-----") !== false || strpos($line, "Hash: SHA1") !== false) {
      continue;
    }
    if ($line == "") {
      $block_index++;
      //continue;
    }

    $sectionsArray = idlSetSection($sectionsArray, $line);

    if ($sectionsArray['Here are the details from the Slackware'] == true) {
      $finalArr['slackware_changelog'][0] .= $line . "\n";
    } else if ($sectionsArray['Where to find the new packages:'] == true) {
      $finalArr['where_find_new_packages'][] .= $line . "\n";
    } else if ($sectionsArray['MD5 signatures:'] == true) {
      $finalArr['MD5_signatures'][] .= $line . "\n";
    } else if ($sectionsArray['Installation instructions:'] == true) {
      $finalArr['installation_instructions'][] = $line;
    } else {
      if ($finalArr['block'][$block_index] != '') {
        $finalArr['block'][$block_index] .= " " . $line;
      } else {
        $finalArr['block'][$block_index] .= $line;
      }
    }
  }

  //SUSE
  $suse_datefallback = "2012-01-01";
  
  if (strtotime($date_c) > strtotime($suse_datefallback)) {
    $suse_fallback = false;
  } else {
    $suse_fallback = true;
  }

  // SUSE
  if ($catitemid == 100) {
    /*
    if(strpos($line, "-----------------------------------------------------------------")!==false && $separator<2){
      $separator++;
      continue;
    }
    if($line==""){
      $block_index++;
      continue;
    }

    if($separator>1){
      $sectionsArray['References            :'] = false;
    }
    
    $sectionsArray = idlSetSection($sectionsArray,$line);

    if($sectionsArray['Container Advisory ID :'] == true){
          $finalArr['container_advisory_id'][] = $line;
      }else if($sectionsArray['Container Tags        :'] == true){
          $finalArr['container_tags'][] = $line;
      }else if($sectionsArray['Container Release     :'] == true){
          $finalArr['container_release'][] = $line;
      }else if($sectionsArray['Severity              :'] == true){
          $finalArr['severity'][] = $line;
      }else if($sectionsArray['Type                  :'] == true){
          $finalArr['type'][] = $line;
      }else if($sectionsArray['References            :'] == true){
          $finalArr['references'][] = $line;
      }else if($separator>1){
          $finalArr['description'][] = $line;
		  unset( $finalArr['description'][0]);
      }else{
      if($finalArr['block'][$block_index]!=''){
        $finalArr['block'][$block_index] .= " ".$line;
      }else{
        $finalArr['block'][$block_index] .= $line;
      }
    }
	  */
    if (!$suse_fallback) {
      $new = 1;
      if ($key == 0) {

        if (!empty($line)) {
          $finalArr['title'] = $line;
        }
      }
      /*old version before 2020 for SUSE */
      if ($key == 1) {
        if (!empty($line)) {
          $finalArr['title'] = $line;
        }
      }
      if ($key >= 2 && strpos($line, 'Announcement ID:') !== false) {
        $flag = true;
        $flag2 = false;
        $description = false;
        $updateinst = false;
        $list = false;
        $references = false;
      }
      if ($key > 6 && strpos($line, '_____') !== false) {
        $flag = false;
        $flag2 = true;
        $description = false;
        $updateinst = false;
        $list = false;
        $references = false;
      }
      if ($key > 0 && strpos($line, '---------------------------------------') !== false) {
        $flag = true;
        $flag2 = false;
        $description = false;
        $updateinst = false;
        $list = false;
        $references = false;
      }
      if ($key > 4 && strpos($line, 'References   ') !== false) {
        $flag = false;
        $flag2 = true;
        $description = false;
        $updateinst = false;
        $list = false;
        $references = false;
      }
      if ($key > 4 && strpos($line, 'References:') !== false) {
        $flag = false;
        $flag2 = true;
        $description = false;
        $updateinst = false;
        $list = false;
        $references = false;
      }
      if ($key > 6 && strpos($line, '_____') !== false) {
        $flag = false;
        $flag2 = false;
        $description = false;
        $updateinst = false;
        $list = false;
        $references = false;
      }
      if ($key > 6 && strpos($line, '---------------------------------------') !== false) {
        $flag = false;
        $flag2 = false;
        $description = true;
        $updateinst = false;
        $list = false;
        $references = false;
      }
      if ($key > 8 && strpos($line, 'Advisory ID: ') !== false) {
        $flag = false;
        $flag2 = false;
        $description = false;
        $updateinst = true;
        $list = false;
        $references = false;
      }
      if ($key > 8 && strpos($line, 'Description:') !== false) {
        $flag = false;
        $flag2 = false;
        $description = false;
        $updateinst = true;
        $list = false;
        $references = false;
      }
      if ($key > 15 && strpos($line, 'Special Instructions and Notes:') !== false) {
        $flag = false;
        $flag2 = false;
        $description = false;
        $updateinst = false;
        $list = false;
        $references = false;
      }
      /*if($key>10 && strpos($line, '---------------------------------------') !== false){
      $flag=false;
      $flag2=false;
      $updateinst=false;
      $list=false;
      $references=false;
    }*/

  
      if ($key > 65) {
    // continue;
    
    }

      if ($flag) {
        $finalArr['block1'][$key] = $line;
        unset($finalArr['block1'][1]);
      }
      if ($flag2) {
        $line = str_replace('References:', '', $line);
        $line = str_replace('References : ', '', $line);

        // $finalArr['block2'][$key] = $line;

        if (!isset($finalArr['block2'])) {
            $finalArr['block2'] = [];
        }

        if (!isset($char_count_ref)) {
            $char_count_ref = 0;
        }

        // Count characters in current line (excluding spaces)
        $line_char_count = strlen(str_replace(' ', '', $line));

        // Check if adding this line stays within 1000 char limit
        if (($char_count_ref + $line_char_count) <= 700) {
            $finalArr['block2'][$key] = $line;
            $char_count_ref += $line_char_count;
        } else {
            $char_count_ref += $line_char_count;
            if (!$read_more_reference) {
                // if (!empty($finalArr['advisory_url'][0]) && strpos($finalArr['advisory_url'][0], 'Advisory URL:') !== false) {
                    $url = 'https://www.suse.com/support/update/';
                    // $url = trim(str_replace('Advisory URL:', '', $finalArr['advisory_url'][0]));
                    $finalArr['block2'][$key] = '<a class="btn btn-primary button subbutton" href="' . $url . '" target="_blank">Read the Full Advisory</a>';
                    $read_more_reference = 1;
                // }
            }
        }
      }
      if ($description) {
        // $finalArr['description'][$key] = $line;
        if (!isset($finalArr['description'])) {
            $finalArr['description'] = [];
        }

        if (!isset($char_count_des)) {
            $char_count_des = 0;
        }

        // Count characters in current line (excluding spaces)
        $line_char_count = strlen(str_replace(' ', '', $line));

        // Check if adding this line stays within 1000 char limit
        if (($char_count_des + $line_char_count) <= 700) {
            $finalArr['description'][$key] = $line;
            $char_count_des += $line_char_count;
        } else {
            $char_count_des += $line_char_count;
            if (!$read_more_description) {
                // if (!empty($finalArr['advisory_url'][0]) && strpos($finalArr['advisory_url'][0], 'Advisory URL:') !== false) {
                    $url = 'https://www.suse.com/support/update/';
                    // $url = trim(str_replace('Advisory URL:', '', $finalArr['advisory_url'][0]));
                    $finalArr['description'][$key] = '<a class="btn btn-primary button subbutton" href="' . $url . '" target="_blank">Read the Full Advisory</a>';
                    $read_more_description = 1;
                // }
            }
        }
      }
      if ($updateinst) {
        $line = str_replace('Description:', '', $line);
        // $finalArr['advisoryid'][$key] .= $line . "\n";
        if (!isset($finalArr['advisoryid'])) {
            $finalArr['advisoryid'] = [];
        }

        if (!isset($char_count_des)) {
            $char_count_adv = 0;
        }

        // Count characters in current line (excluding spaces)
        $line_char_count = strlen(str_replace(' ', '', $line));

        // Check if adding this line stays within 1000 char limit
        if (($char_count_des + $line_char_count) <= 700) {
            $finalArr['advisoryid'][$key] = $line. "\n";;
            $char_count_des += $line_char_count;
        } else {
            $char_count_des += $line_char_count;
            if (!$read_more_description) {
                // if (!empty($finalArr['advisory_url'][0]) && strpos($finalArr['advisory_url'][0], 'Advisory URL:') !== false) {
                    $url = 'https://www.suse.com/support/update/';
                    // $url = trim(str_replace('Advisory URL:', '', $finalArr['advisory_url'][0]));
                    $finalArr['advisoryid'][$key] = '<a class="btn btn-primary button subbutton" href="' . $url . '" target="_blank">Read the Full Advisory</a>';
                    $read_more_description = 1;
                // }
            }
        }
      }
      if ($list) {
        $finalArr['package_information'][$key] = $line;
      }
      if ($references) {
        // $finalArr['references'][$key] = $line;
        if (!isset($finalArr['references'])) {
            $finalArr['references'] = [];
        }

        if (!isset($char_count_ref)) {
            $char_count_ref = 0;
        }

        // Count characters in current line (excluding spaces)
        $line_char_count = strlen(str_replace(' ', '', $line));

        // Check if adding this line stays within 1000 char limit
        if (($char_count_ref + $line_char_count) <= 700) {
            $finalArr['references'][$key] = $line;
            $char_count_ref += $line_char_count;
        } else {
            $char_count_ref += $line_char_count;
            if (!$read_more_reference) {
                // if (!empty($finalArr['advisory_url'][0]) && strpos($finalArr['advisory_url'][0], 'Advisory URL:') !== false) {
                    $url = 'https://www.suse.com/support/update/';
                    // $url = trim(str_replace('Advisory URL:', '', $finalArr['advisory_url'][0]));
                    $finalArr['description'][$key] = '<a class="btn btn-primary button subbutton" href="' . $url . '" target="_blank">Read the Full Advisory</a>';
                    $read_more_reference = 1;
                // }
            }
        }
      }
    } else {
      //fallback
      // $finalArr['description'] = explode("\n", $article->fulltext);
      if (strpos($line, "- - - ") !== false || strpos($line, "- - - ----") !== false) {
        continue;
      }
      if ($line == "") {
        $block_index++;
        //continue;
      }

      $finalArr['description'][] .= $line."\n";
    }
  }

  // Ubuntu
  if ($catitemid == 172) {
    if ($key == 1) {
      $finalArr['title'] = $line;
    }
    if ($key == 2 && strpos($line, '_____') !== false) {
      $flag = true;
      $flag2 = false;
      $description = false;
      $updateinst = false;
      $list = false;
      $references = false;
    }
    if ($key > 2 && strpos($line, '_____') !== false) {
      $flag = false;
      $flag2 = true;
      $description = false;
      $updateinst = false;
      $list = false;
      $references = false;
    }
    if ($key > 2 && strpos($line, '==========================================================================') !== false) {
      $flag = false;
      $flag2 = false;
      $description = true;
      $updateinst = false;
      $list = false;
      $references = false;
    }
    if ($key > 2 && strpos($line, 'Update instructions') !== false) {
      $flag = false;
      $flag2 = false;
      $description = false;
      $updateinst = true;
      $list = false;
      $references = false;
    }
    if ($key > 2 && strpos($line, 'Package Information:') !== false) {
      $flag = false;
      $flag2 = false;
      $description = false;
      $updateinst = false;
      $list = true;
      $references = false;
    }
    if ($key > 10 && strpos($line, 'References:') !== false) {
      $flag = false;
      $flag2 = false;
      $description = false;
      $updateinst = false;
      $list = false;
      $references = true;
    }
    if ($flag) {
      $finalArr['block1'][$key] = $line;
    }
    if ($flag2) {
      $finalArr['block2'][$key] = $line;
    }
    if ($description) {
      $finalArr['description'][$key] .= $line . "\n";
    }
    if ($updateinst) {
      $finalArr['update_instructions'][$key] .= $line . "\n";
    }
    if ($list) {
      $finalArr['package_information'][$key] .= $line . "\n";
    }
    if ($references) {
      $finalArr['references'][$key] = $line;
    }
  }
  /*echo $line; echo '<hr>'; */
}
// $finalArr['Resolution'].='</pre>';
if ($catitemid == 202) {
  if ($desp_found_202 === false) {

    $finalArr['description'] = explode("\n", $article->fulltext);
  }
}


//echo "<pre>"; 
//print_r(json_decode($article->introtext));

//print_r($finalArr['references']); die();  // Jacob

switch ($catitemid) {
  case '219':
    $article->fulltext = ltrim($article->fulltext, "\\");
    $article->fulltext = rtrim($article->fulltext, "\\");
    if (strtotime($date_c) <= strtotime("2022-10-02 09:36:44")) {
      $article->fulltext = str_replace("'", '"', $article->fulltext);
    }
    $article->fulltext = str_replace(': none,', ': "none",', $article->fulltext);
    $article->fulltext = str_replace(': None,', ': "None",', $article->fulltext);
    //print_r($article->fulltext);
    //print_r(json_decode($article->fulltext));
    //exit;
    foreach (json_decode($article->fulltext) as $arry_single_key => $arry_single) {
      if ($arry_single_key == "affectedProducts") {
        $finalArr['block1'][$arry_single_key] = "Affected Products: " . $arry_single[0];
      }
      if ($arry_single_key == "name") {
        $finalArr['block1'][$arry_single_key] = "Name: " . $arry_single;
      }
      if ($arry_single_key == "fixes") {
        $finalArr['fixes'] = $arry_single;
      }
      if ($arry_single_key == "cves") {
        foreach ($arry_single as $arry_single_single) {
          //var_dump($arry_single_single);
          //die;
          if (is_string($arry_single_single)) {
            $cves = explode(":::", $arry_single_single);
            $finalArr['cvesfinal'][] = $cves;
          } elseif (is_object($arry_single_single)) {
            $finalArr['cvesfinal'][] = ["", $arry_single_single->sourceLink];
            //	break;
          }
        }
      }
      $finalArr[$arry_single_key] = $arry_single;
    }
    //print_r($finalArr);
    //exit;
    break;
  case '198':
    $finalArr['description'] = arrayToParagraphs(array(
      'array' => $finalArr['description'],
      'skip' => array(
        "Description",
        "========",
      )
    ));
    $references_array = array();
    foreach ($finalArr['references'] as $key => $value) {
      if (strrpos($value, "References") !== false || strrpos($value, "==========") !== false || $value == "") {
        continue;
      }
      $references_array[] = $value;
    }
    $finalArr['references'] = $references_array;
    $finalArr['references'] = arrayToParagraphs(array(
      'array' => $finalArr['references'],
      'skip' => array(
        "References",
        "========",
      )
    ));
    $finalArr['summary'] = arrayToParagraphs(array(
      'array' => $finalArr['summary'],
      'skip' => array(
        "Summary",
        "=======",
      )
    ));
    $finalArr['resolution'] = arrayToParagraphs(array(
      'array' => $finalArr['resolution'],
      'skip' => array(
        "Resolution",
        "========",
      )
    ));
    $finalArr['workaround'] = arrayToParagraphs(array(
      'array' => $finalArr['workaround'],
      'skip' => array(
        "Workaround",
        "========",
      )
    ));
    if (is_array($finalArr['impact'])) {
      $finalArr2['impact'] = array_values($finalArr['impact']);
      unset($finalArr2['impact'][0]);
      unset($finalArr2['impact'][1]);
      $finalArr['impact'] = array_values($finalArr2['impact']);
    }
    /*
    $finalArr['impact'] = arrayToParagraphs(array(
      'array' => $finalArr['impact'],
      'skip' => array(
        "Impact",
        "======", 
      )
    ));*/
    break;
  case '87':
    $finalArr['description'] = arrayToParagraphs(array(
      'array' => $finalArr['description'],
      'skip' => array()
    ));
    break;
  case '197':
    /* $finalArr['description'] = arrayToParagraphs(array(
      'array' => $finalArr['description'],
      'skip' => array()
    ));*/
    //fallback fix
    if (empty($finalArr['original'])) {
      $finalArr['original'] =   $finalArr['description'];
    }
    break;
  case '89':
    $finalArr['References'] = arrayToParagraphs(array(
      'array' => $finalArr['References'],
      'skip' => array("References:")
    ));
    $finalArr['update_information'] = arrayToParagraphs(array(
      'array' => $finalArr['update_information'],
      'skip' => array("Update Information:")
    ));
    $finalArr['ChangeLog'] = arrayToParagraphs(array(
      'array' => $finalArr['ChangeLog'],
      'skip' => array("ChangeLog:")
    ));
    $finalArr['update_can_install'] = arrayToParagraphs(array(
      'array' => $finalArr['update_can_install'],
      'skip' => array()
    ));
    break;
  case '91':
    $finalArr['Description'] = arrayToParagraphs(array(
      'array' => $finalArr['Description'],
      'skip' => array("Description")
    ));
    $finalArr['Synopsis'] = arrayToParagraphs(array(
      'array' => $finalArr['Synopsis'],
      'skip' => array("Synopsis")
    ));
    $finalArr['Background'] = arrayToParagraphs(array(
      'array' => $finalArr['Background'],
      'skip' => array("Background")
    ));
    $finalArr['Affected_packages'] = arrayToParagraphs(array(
      'array' => $finalArr['Affected_packages'],
      'skip' => array("Affected packages")
    ));
    $finalArr['Resolution'] = arrayToParagraphs(array(
      'array' => $finalArr['Resolution'],
      'skip' => array("Resolution")
    ));
    $finalArr['Impact'] = arrayToParagraphs(array(
      'array' => $finalArr['Impact'],
      'skip' => array("Impact")
    ));
    $finalArr['Workaround'] = arrayToParagraphs(array(
      'array' => $finalArr['Workaround'],
      'skip' => array("Workaround")
    ));
    $finalArr['References'] = arrayToParagraphs(array(
      'array' => $finalArr['References'],
      'skip' => array("References")
    ));
    $finalArr['Availability'] = arrayToParagraphs(array(
      'array' => $finalArr['Availability'],
      'skip' => array("Availability")
    ));
    $finalArr['Concerns'] = arrayToParagraphs(array(
      'array' => $finalArr['Concerns'],
      'skip' => array("Concerns")
    ));
    $finalArr['License'] = arrayToParagraphs(array(
      'array' => $finalArr['License'],
      'skip' => array("License")
    ));
    break;
  case '203':
   
    if(!isset($finalArr['description'])){
      $finalArr['description'] = explode("\n",$article->introtext);
    }
    $finalArr['description'] = arrayToParagraphs(array(
      'array' => $finalArr['description'],
      'skip' => array(
        "description",
      )
    ));
    $finalArr['CVE'] = arrayToParagraphs(array(
      'array' => $finalArr['CVE'],
      'skip' => array(
        "description",
      )
    ));
   
    break;
  case '98':
    $finalArr['summary'] = arrayToParagraphs(array(
      'array' => $finalArr['summary'],
      'skip' => array(
        "Summary:",
      )
    ));

    $finalArr['description'] = arrayToParagraphs(array(
      'array' => $finalArr['description'],
      'skip' => array(
        "Description:",
      )
    ));
    $finalArr['package_list'] = arrayToParagraphs(array(
      'array' => $finalArr['package_list'],
      'skip' => array(
        "Package List:",
      )
    ));
    $finalArr['solution'] = arrayToParagraphs(array(
      'array' => $finalArr['solution'],
      'skip' => array(
        "Solution:",
      )
    ));
    break;
  case '200':
    $finalArr['security_fixes'] = arrayToParagraphs(array(
      'array' => $finalArr['security_fixes'],
      'skip' => array(
        "Security Fix(es):",
      )
    ));
    $finalArr['Synopsis'] = arrayToParagraphs(array(
      'array' => $finalArr['Synopsis'],
      'skip' => array(
        "Synopsis:",
      )
    ));
    break;
  case '99':
    $finalArr['slackware_changelog'] = arrayToParagraphs(array(
      'array' => $finalArr['slackware_changelog'],
      'skip' => array()
    ));
    $finalArr['where_find_new_packages'] = arrayToParagraphs(array(
      'array' => $finalArr['where_find_new_packages'],
      'skip' => array("Where to find the new packages:")
    ));
    $finalArr['MD5_signatures'] = arrayToParagraphs(array(
      'array' => $finalArr['MD5_signatures'],
      'skip' => array("MD5 signatures:")
    ));
    break;
  case '100':
    /* $finalArr['advisoryid'] = arrayToParagraphs(array(
      'array' => $finalArr['advisoryid'],
      'skip' => array()
    ));*/
    break;
}

//echo  $catitemid ;
if(in_array($catitemid,[200]) && isset($finalArr["block"])){
  $finalArr["description"] = $finalArr["block"] ;
  unset($finalArr["block"] );
}

//die;
//if(is_array($finalArr['description']) == ""){
//$finalArr['description'] = explode("\n"," \n".$article->fulltext);
//}

//echo "<pre>";print_r($finalArr);echo "</pre>";die;
function truncateHtml($text, $length = 1000, $options = array())
{
  $default = array(
    'ending' => '...',
    'exact' => true,
    'html' => true
  );
  $options = array_merge($default, $options);
  extract($options);

  if ($html) {
    if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
      return $text;
    }
    $totalLength = mb_strlen(strip_tags($ending));
    $openTags = array();
    $truncate = '';

    preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
    foreach ($tags as $tag) {
      if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
        if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
          array_unshift($openTags, $tag[2]);
        } else if (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
          $pos = array_search($closeTag[1], $openTags);
          if ($pos !== false) {
            array_splice($openTags, $pos, 1);
          }
        }
      }
      $truncate .= $tag[1];

      $contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
      if ($contentLength + $totalLength > $length) {
        $left = $length - $totalLength;
        $entitiesLength = 0;
        if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
          foreach ($entities[0] as $entity) {
            if ($entity[1] + 1 - $entitiesLength <= $left) {
              $left--;
              $entitiesLength += mb_strlen($entity[0]);
            } else {
              break;
            }
          }
        }

        $truncate .= mb_substr($tag[3], 0, $left + $entitiesLength);
        break;
      } else {
        $truncate .= $tag[3];
        $totalLength += $contentLength;
      }
      if ($totalLength >= $length) {
        break;
      }
    }
  } else {
    if (mb_strlen($text) <= $length) {
      return $text;
    } else {
      $truncate = mb_substr($text, 0, $length - mb_strlen($ending));
    }
  }
  if (!$exact) {
    $spacepos = mb_strrpos($truncate, ' ');
    if (isset($spacepos)) {
      if ($html) {
        $bits = mb_substr($truncate, $spacepos);
        preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
        if (!empty($droppedTags)) {
          foreach ($droppedTags as $closingTag) {
            if (!in_array($closingTag[1], $openTags)) {
              array_unshift($openTags, $closingTag[1]);
            }
          }
        }
      }
      $truncate = mb_substr($truncate, 0, $spacepos);
    }
  }
  $truncate .= $ending;

  if ($html) {
    foreach ($openTags as $tag) {
      $truncate .= '</' . $tag . '>';
    }
  }

  return $truncate;
}
